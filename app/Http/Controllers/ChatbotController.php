<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Google\Cloud\Dialogflow\V2\Client\SessionsClient;
use Google\Cloud\Dialogflow\V2\QueryInput;
use Google\Cloud\Dialogflow\V2\TextInput;
use Google\Cloud\Dialogflow\V2\DetectIntentRequest;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Services\FirestoreService;
use Symfony\Component\DomCrawler\Crawler;

class ChatbotController extends Controller
{
    private $firestoreService;

    public function __construct(FirestoreService $firestoreService)
    {
        $this->firestoreService = $firestoreService;
    }

    public function sendMessage(Request $request)
    {
        $message = $request->input('message');
        $sessionId = $request->input('session_id', session()->getId());
        $userId = auth()->id();

        // Deteksi kategori pertanyaan untuk context-aware responses
        $questionCategory = $this->categorizeQuestion($message);

        $response = [
            'message' => 'Maaf, terjadi kesalahan. Silakan coba lagi nanti.',
            'suggestions' => [],
        ];
        $source = 'error';

        try {
            // Layer 1: Enhanced Dialogflow dengan preprocessing
            Log::info("Layer 1: Dialogflow processing for category: {$questionCategory}");
            $preprocessedMessage = $this->preprocessMessage($message);
            $dialogflowResponse = $this->detectIntent($preprocessedMessage);

            if ($this->isDialogflowResponseValid($dialogflowResponse, $message)) {
                $response['message'] = $dialogflowResponse['text'];
                $source = 'dialogflow';
                Log::info("Dialogflow successful response");

                // Get contextual suggestions based on category and current response
                $response['suggestions'] = $this->getSmartSuggestions($questionCategory, $dialogflowResponse['text'], $message);
            } else {
                // Layer 2: Enhanced Firestore search with semantic matching
                Log::info("Layer 2: Firestore semantic search");
                $firestoreAnswer = $this->firestoreService->searchKnowledgeBase($message, 0.75, 70.0);

                if ($firestoreAnswer) {
                    $response['message'] = $firestoreAnswer;
                    $source = 'firestore';
                    Log::info("Firestore match found");

                    $response['suggestions'] = $this->getSmartSuggestions($questionCategory, $firestoreAnswer, $message);
                } else {
                    // Layer 3: Enhanced OpenAI with better context
                    Log::info("Layer 3: OpenAI with enhanced context");

                    $contextData = null;
                    if ($this->requiresWebContext($message)) {
                        Log::info("Fetching web context for enhanced response");
                        $contextData = $this->scrapeWebsiteForActivities();
                    }

                    // Get conversation history for better context
                    $conversationContext = $this->getConversationHistory($sessionId, 3);

                    $openAIResult = $this->fallbackWithOpenAI($message, $contextData, false, $questionCategory, $conversationContext);

                    if (isset($openAIResult['answer']) && !empty($openAIResult['answer'])) {
                        $response['message'] = $openAIResult['answer'];
                        $response['suggestions'] = $openAIResult['suggestions'] ?? $this->getSmartSuggestions($questionCategory, $openAIResult['answer'], $message);
                        $source = 'openai';
                        Log::info("OpenAI successful response");

                        // Enhanced learning: Store with better categorization
                        if ($contextData === null) {
                            $this->firestoreService->addKnowledgeBase($message, $openAIResult['answer'], $questionCategory);
                            Log::info("Knowledge base enhanced with category: {$questionCategory}");
                        }
                    } else {
                        $response['message'] = "Maaf, saya tidak dapat menemukan jawaban untuk pertanyaan itu saat ini. Silakan coba dengan pertanyaan yang berbeda atau hubungi admin GenBI Cirebon.";
                        $response['suggestions'] = $this->getFallbackSuggestions($questionCategory);
                        $source = 'openai_fail';
                        Log::warning("All layers failed, providing fallback suggestions");
                    }
                }
            }

            // Enhanced logging with performance metrics
            if ($source !== 'error') {
                $this->firestoreService->addChatLog($sessionId, $message, $response['message'], $source, $userId, $questionCategory);
                $this->firestoreService->updateSystemMetrics($source, $questionCategory);
                Log::info("Enhanced logging completed with category: {$questionCategory}");
            }
        } catch (\Exception $e) {
            Log::error('Chatbot Enhanced Error: ' . $e->getMessage() . ' in ' . $e->getFile() . ' on line ' . $e->getLine());
            $this->firestoreService->addErrorLog($e->getMessage(), $message, [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'category' => $questionCategory,
                'session_id' => $sessionId
            ]);

            $response['suggestions'] = $this->getFallbackSuggestions($questionCategory);
        }

        return response()->json($response);
    }

    /**
     * Enhanced message preprocessing
     */
    private function preprocessMessage(string $message): string
    {
        // Normalisasi common variations
        $replacements = [
            'genbi' => 'GenBI',
            'bank indonesia' => 'Bank Indonesia',
            'beasiswa bi' => 'beasiswa Bank Indonesia',
            'cirebon' => 'Cirebon',
        ];

        $processed = $message;
        foreach ($replacements as $search => $replace) {
            $processed = str_ireplace($search, $replace, $processed);
        }

        return trim($processed);
    }

    /**
     * Enhanced question categorization
     */
    private function categorizeQuestion(string $question): string
    {
        $question = strtolower($question);

        $categories = [
            'beasiswa' => ['beasiswa', 'scholarship', 'bantuan', 'dana', 'biaya', 'syarat beasiswa'],
            'pendaftaran' => ['daftar', 'registrasi', 'syarat', 'pendaftaran', 'cara bergabung', 'join'],
            'kegiatan' => ['kegiatan', 'acara', 'event', 'program', 'agenda', 'aktivitas'],
            'organisasi' => ['komisariat', 'anggota', 'struktur', 'pengurus', 'jumlah'],
            'informasi_umum' => ['apa itu', 'pengertian', 'definisi', 'tentang', 'mengenai'],
            'kontak' => ['kontak', 'alamat', 'telepon', 'email', 'hubungi'],
            'sejarah' => ['sejarah', 'awal', 'didirikan', 'berdiri', 'terbentuk'],
            'manfaat' => ['manfaat', 'keuntungan', 'benefit', 'kelebihan'],
            'lokasi' => ['dimana', 'lokasi', 'tempat', 'alamat'],
        ];

        foreach ($categories as $category => $keywords) {
            foreach ($keywords as $keyword) {
                if (strpos($question, $keyword) !== false) {
                    return $category;
                }
            }
        }

        return 'umum';
    }

    /**
     * Smart contextual suggestions based on category and response
     */
    private function getSmartSuggestions(string $category, string $response, string $originalQuestion): array
    {
        $baseUrl = "https://genbicirebon.org";

        $suggestionMap = [
            'beasiswa' => [
                'Syarat beasiswa GenBI',
                'Cara mendaftar beasiswa',
                'Manfaat beasiswa BI'
            ],
            'pendaftaran' => [
                'Syarat pendaftaran GenBI',
                'Timeline pendaftaran',
                'Dokumen yang diperlukan'
            ],
            'kegiatan' => [
                'Program unggulan GenBI',
                'Agenda kegiatan terbaru',
                'Cara mengikuti kegiatan'
            ],
            'organisasi' => [
                'Struktur organisasi GenBI',
                'Peran anggota GenBI',
                'Cara menjadi pengurus'
            ],
            'informasi_umum' => [
                'Visi misi GenBI Cirebon',
                'Sejarah GenBI',
                'Prestasi GenBI Cirebon'
            ],
            'kontak' => [
                'Media sosial GenBI',
                'Alamat sekretariat',
                'Admin GenBI Cirebon'
            ],
            'sejarah' => [
                'Perkembangan GenBI',
                'Tokoh penting GenBI',
                'Milestone GenBI Cirebon'
            ],
            'manfaat' => [
                'Program pengembangan diri',
                'Networking GenBI',
                'Pelatihan untuk anggota'
            ],
            'lokasi' => [
                'Kampus mitra GenBI',
                'Tempat kegiatan rutin',
                'Sekretariat GenBI'
            ]
        ];

        // Get base suggestions for the category
        $suggestions = $suggestionMap[$category] ?? $suggestionMap['informasi_umum'];

        // Enhance suggestions based on response content analysis
        $responseWords = explode(' ', strtolower($response));

        // If response mentions specific topics, adjust suggestions
        if (in_array('beasiswa', $responseWords)) {
            $suggestions[0] = 'Info lengkap beasiswa BI';
        }

        if (in_array('kegiatan', $responseWords) || in_array('program', $responseWords)) {
            $suggestions[1] = 'Kegiatan GenBI bulan ini';
        }

        if (in_array('anggota', $responseWords) || in_array('bergabung', $responseWords)) {
            $suggestions[2] = 'Cara bergabung GenBI';
        }

        // Avoid suggesting the same topic as the current question
        $originalWords = explode(' ', strtolower($originalQuestion));
        $suggestions = array_filter($suggestions, function ($suggestion) use ($originalWords) {
            $suggestionWords = explode(' ', strtolower($suggestion));
            $intersection = array_intersect($originalWords, $suggestionWords);
            return count($intersection) < 2; // Allow if less than 2 words overlap
        });

        // Ensure we have 3 suggestions
        if (count($suggestions) < 3) {
            $fallbackSuggestions = [
                'Website GenBI Cirebon',
                'Contact person GenBI',
                'Info terbaru GenBI'
            ];

            $suggestions = array_merge(array_values($suggestions), $fallbackSuggestions);
        }

        return array_slice(array_values($suggestions), 0, 3);
    }

    /**
     * Fallback suggestions when all systems fail
     */
    private function getFallbackSuggestions(string $category): array
    {
        $fallbackMap = [
            'beasiswa' => ['Info beasiswa BI', 'Syarat beasiswa', 'Cara mendaftar'],
            'kegiatan' => ['Program GenBI', 'Agenda kegiatan', 'Cara ikut serta'],
            'organisasi' => ['Tentang GenBI', 'Struktur organisasi', 'Cara bergabung'],
            'default' => ['Apa itu GenBI?', 'Kegiatan GenBI', 'Hubungi admin']
        ];

        return $fallbackMap[$category] ?? $fallbackMap['default'];
    }

    /**
     * Enhanced Dialogflow response validation
     */
    private function isDialogflowResponseValid($dialogflowResponse, string $originalMessage): bool
    {
        if (!$dialogflowResponse || empty($dialogflowResponse['text'])) {
            return false;
        }

        if ($dialogflowResponse['is_fallback']) {
            return false;
        }

        // Check confidence threshold
        if (isset($dialogflowResponse['confidence']) && $dialogflowResponse['confidence'] < 0.6) {
            return false;
        }

        $response = strtolower(trim($dialogflowResponse['text']));

        // Invalid response patterns
        $invalidPatterns = ['sorry', 'tidak mengerti', 'maaf', 'i don\'t understand'];
        foreach ($invalidPatterns as $pattern) {
            if (str_contains($response, $pattern)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Check if message requires web context
     */
    private function requiresWebContext(string $message): bool
    {
        $webContextKeywords = [
            'terbaru',
            'terkini',
            'sekarang',
            'saat ini',
            'hari ini',
            'kegiatan',
            'acara',
            'event',
            'berita',
            'artikel',
            'update'
        ];

        $messageLower = strtolower($message);
        foreach ($webContextKeywords as $keyword) {
            if (strpos($messageLower, $keyword) !== false) {
                return true;
            }
        }

        return false;
    }

    /**
     * Get conversation history for context
     */
    private function getConversationHistory(string $sessionId, int $limit = 3): array
    {
        try {
            return $this->firestoreService->getConversationHistory($sessionId, $limit);
        } catch (\Exception $e) {
            Log::warning("Failed to get conversation history: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Enhanced OpenAI fallback with better prompting
     */
    private function fallbackWithOpenAI(string $text, ?string $externalContext = null, bool $suggestionsOnly = false, string $category = 'umum', array $conversationHistory = [])
    {
        $apiKey = env('OPENROUTER_API_KEY');

        $contextualPrompt = $this->buildContextualPrompt($category, $conversationHistory, $externalContext);

        $promptAction = $suggestionsOnly
            ? "Tugasmu HANYA memberikan 3 saran pertanyaan lanjutan yang sangat relevan dengan kategori '{$category}' dan pertanyaan pengguna. JANGAN menjawab pertanyaan pengguna."
            : "Jawab pertanyaan pengguna secara ringkas, informatif, dan sesuai dengan kategori '{$category}'. Pastikan jawaban akurat dan relevan dengan GenBI Cirebon. Kemudian berikan 3 saran pertanyaan lanjutan yang sangat spesifik dan relevan (maksimal 4 kata per saran).";

        $systemPrompt = "{$contextualPrompt} {$promptAction} Format respons HANYA dalam bentuk JSON valid seperti ini: {\"answer\": \"Jawabanmu di sini.\", \"suggestions\": [\"Saran 1\", \"Saran 2\", \"Saran 3\"]}. Jika hanya diminta saran, isi field 'answer' dengan string kosong.";

        try {
            $response = Http::timeout(45)->withHeaders([
                'Authorization' => 'Bearer ' . $apiKey,
                'Content-Type' => 'application/json',
                'HTTP-Referer' => request()->getSchemeAndHttpHost(),
                'X-Title' => 'Genbi Cirebon Chatbot',
            ])->post('https://openrouter.ai/api/v1/chat/completions', [
                "model" => "openai/gpt-3.5-turbo",
                "messages" => [
                    ["role" => "system", "content" => $systemPrompt],
                    ["role" => "user", "content" => $text]
                ],
                "response_format" => ["type" => "json_object"],
                "temperature" => 0.3, // Reduced for more consistent responses
                "max_tokens" => 500,
            ]);

            if ($response->successful()) {
                $data = json_decode($response->json()['choices'][0]['message']['content'], true);
                return [
                    'answer' => $data['answer'] ?? ($suggestionsOnly ? '' : 'Gagal memformat jawaban.'),
                    'suggestions' => $data['suggestions'] ?? $this->getFallbackSuggestions($category),
                ];
            }

            Log::error('OpenAI Enhanced HTTP Error: ' . $response->body());
            return [
                'answer' => 'Maaf, saya sedang mengalami kendala teknis (API).',
                'suggestions' => $this->getFallbackSuggestions($category)
            ];
        } catch (\Exception $e) {
            Log::error('OpenAI Enhanced Exception: ' . $e->getMessage());
            return [
                'answer' => 'Maaf, koneksi ke asisten AI sedang bermasalah.',
                'suggestions' => $this->getFallbackSuggestions($category)
            ];
        }
    }

    /**
     * Build contextual prompt based on category and history
     */
    private function buildContextualPrompt(string $category, array $conversationHistory, ?string $externalContext): string
    {
        $baseContext = "Kamu adalah 'GenBI Assistant', asisten AI yang ramah, informatif, dan ahli tentang GenBI Cirebon (Generasi Baru Indonesia Cirebon), sebuah komunitas penerima beasiswa Bank Indonesia. Website resmi adalah genbicirebon.org.";

        $categoryContext = [
            'beasiswa' => "Fokus pada informasi beasiswa Bank Indonesia, syarat, manfaat, dan proses pendaftaran.",
            'kegiatan' => "Fokus pada kegiatan, program, dan acara GenBI Cirebon.",
            'organisasi' => "Fokus pada struktur organisasi, anggota, dan sistem kerja GenBI Cirebon.",
            'pendaftaran' => "Fokus pada cara bergabung, syarat, dan proses menjadi anggota GenBI.",
        ];

        $contextPrompt = $baseContext . " " . ($categoryContext[$category] ?? "Berikan informasi umum tentang GenBI Cirebon.");

        if (!empty($conversationHistory)) {
            $contextPrompt .= " Riwayat percakapan sebelumnya: " . json_encode($conversationHistory);
        }

        if ($externalContext) {
            $contextPrompt .= " Informasi tambahan: {$externalContext}";
        }

        return $contextPrompt;
    }

    private function detectIntent(string $text)
    {
        try {
            $projectId = env('DIALOGFLOW_PROJECT_ID');
            $sessionId = session()->getId();

            $envPath = env('DIALOGFLOW_CREDENTIALS');
            $cleanPath = str_replace('storage/', '', $envPath);
            $credentialsPath = storage_path($cleanPath);

            if (!file_exists($credentialsPath)) {
                Log::error("File kredensial Dialogflow tidak ditemukan di: {$credentialsPath}");
                return null;
            }

            Log::info("Enhanced Dialogflow - Text: '{$text}', Session: {$sessionId}, Project: {$projectId}");

            $sessionsClient = new SessionsClient(['credentials' => $credentialsPath]);
            $session = $sessionsClient->sessionName($projectId, $sessionId);

            $textInput = (new TextInput())
                ->setText($text)
                ->setLanguageCode('id');

            $queryInput = (new QueryInput())->setText($textInput);

            $request = (new DetectIntentRequest())
                ->setSession($session)
                ->setQueryInput($queryInput);

            $response = $sessionsClient->detectIntent($request);
            $queryResult = $response->getQueryResult();

            $fulfillmentText = $queryResult->getFulfillmentText();
            $intentName = $queryResult->getIntent() ? $queryResult->getIntent()->getDisplayName() : 'No Intent';
            $isFallback = $queryResult->getIntent() ? $queryResult->getIntent()->getIsFallback() : true;
            $confidence = $queryResult->getIntentDetectionConfidence();

            Log::info("Enhanced Dialogflow Response - Intent: '{$intentName}', Text: '{$fulfillmentText}', Fallback: {$isFallback}, Confidence: {$confidence}");

            $sessionsClient->close();

            return [
                'text' => $fulfillmentText,
                'intent_name' => $intentName,
                'is_fallback' => $isFallback,
                'confidence' => $confidence,
            ];
        } catch (\Exception $e) {
            Log::error("Enhanced Dialogflow Error: " . $e->getMessage() . " | File: " . $e->getFile() . " | Line: " . $e->getLine());
            return null;
        }
    }

    private function scrapeWebsiteForActivities(): ?string
    {
        try {
            $url = 'https://genbicirebon.org/kegiatan';
            $response = Http::get($url);

            if (!$response->successful()) {
                Log::warning("Gagal mengakses {$url}. Status: " . $response->status());
                return null;
            }

            $crawler = new Crawler($response->body());

            $activities = $crawler->filter('.blog-item')->slice(0, 5)->each(function (Crawler $node) {
                $titleNode = $node->filter('.blog-title a');
                $title = $titleNode->count() ? $titleNode->text('Judul tidak ditemukan') : 'Judul tidak ditemukan';

                $dateNode = $node->filter('.blog-meta span')->first();
                $date = $dateNode->count() ? $dateNode->text('Tanggal tidak ditemukan') : 'Tanggal tidak ditemukan';

                return "- {$title} (dipublikasikan sekitar {$date})";
            });

            if (empty($activities)) {
                Log::info('Tidak ada item kegiatan yang ditemukan di website menggunakan selector yang ada.');
                return "Saat ini tidak ada informasi kegiatan terbaru yang bisa ditampilkan dari website.";
            }

            return "Berikut adalah beberapa kegiatan atau berita terbaru dari website genbicirebon.org:\n" . implode("\n", $activities);
        } catch (\Exception $e) {
            Log::error('Enhanced Scraping Error: ' . $e->getMessage());
            return null;
        }
    }

    // Keep existing test methods unchanged
    public function testDialogflow()
    {
        try {
            $projectId = env('DIALOGFLOW_PROJECT_ID');
            $envPath = env('DIALOGFLOW_CREDENTIALS');
            $cleanPath = str_replace('storage/', '', $envPath);
            $credentialsPath = storage_path($cleanPath);
            $sessionId = uniqid('test-');

            if (!file_exists($credentialsPath)) {
                return response()->json([
                    'status' => 'error',
                    'message' => "File kredensial tidak ditemukan di: {$credentialsPath}",
                ], 500);
            }

            Log::info("Menguji Dialogflow dengan project: {$projectId}, credentials: {$credentialsPath}");

            $sessionsClient = new SessionsClient(['credentials' => $credentialsPath]);
            $session = $sessionsClient->sessionName($projectId, $sessionId);

            $textInput = (new TextInput())->setText('Apa itu GenBI?')->setLanguageCode('id');
            $queryInput = (new QueryInput())->setText($textInput);
            $request = (new DetectIntentRequest())->setSession($session)->setQueryInput($queryInput);

            $response = $sessionsClient->detectIntent($request);
            $queryResult = $response->getQueryResult();

            $fulfillmentText = $queryResult->getFulfillmentText();
            $intentName = $queryResult->getIntent() ? $queryResult->getIntent()->getDisplayName() : 'No Intent';
            $isFallback = $queryResult->getIntent() ? $queryResult->getIntent()->getIsFallback() : true;
            $confidence = $queryResult->getIntentDetectionConfidence();

            Log::info("Dialogflow test respons - Intent: '{$intentName}', Text: '{$fulfillmentText}', Fallback: {$isFallback}, Confidence: {$confidence}");

            $sessionsClient->close();

            return response()->json([
                'status' => 'success',
                'message' => 'Koneksi Dialogflow berhasil!',
                'response' => $fulfillmentText ?: 'Tidak ada respons teks dari Dialogflow.',
                'intent_name' => $intentName,
                'is_fallback' => $isFallback,
                'confidence' => $confidence,
            ]);
        } catch (\Exception $e) {
            Log::error("Dialogflow Test Error: " . $e->getMessage() . " | File: " . $e->getFile() . " | Line: " . $e->getLine());
            $this->firestoreService->addErrorLog($e->getMessage(), 'Test Dialogflow', ['file' => $e->getFile(), 'line' => $e->getLine()]);
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal menghubungkan ke Dialogflow: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function sendMessageDialogflowOnly(Request $request)
    {
        $message = $request->input('message');
        $sessionId = $request->input('session_id', session()->getId());

        $response = [
            'message' => 'Dialogflow tidak memberikan respons',
            'source' => 'dialogflow_fail',
            'debug_info' => [],
        ];

        try {
            Log::info("DIALOGFLOW ONLY TEST - Input: '{$message}', Session: {$sessionId}");

            $dialogflowResponse = $this->detectIntent($message);

            if ($dialogflowResponse) {
                $response['message'] = $dialogflowResponse['text'] ?: 'Dialogflow mengembalikan respons kosong';
                $response['source'] = $dialogflowResponse['is_fallback'] ? 'dialogflow_fallback' : 'dialogflow_success';
                $response['debug_info'] = [
                    'intent_name' => $dialogflowResponse['intent_name'] ?? 'unknown',
                    'confidence' => $dialogflowResponse['confidence'] ?? 0,
                    'is_fallback' => $dialogflowResponse['is_fallback'] ?? true,
                    'fulfillment_text' => $dialogflowResponse['text'] ?? '',
                ];

                Log::info("DIALOGFLOW RESPONSE", $response['debug_info']);
            } else {
                $response['message'] = 'Dialogflow gagal total - tidak ada respons';
                $response['source'] = 'dialogflow_error';
                $response['debug_info'] = ['error' => 'No response from Dialogflow'];

                Log::error("DIALOGFLOW FAILED - No response returned");
            }
        } catch (\Exception $e) {
            Log::error('DIALOGFLOW ONLY ERROR: ' . $e->getMessage() . ' in ' . $e->getFile() . ' on line ' . $e->getLine());

            $response['message'] = 'Error: ' . $e->getMessage();
            $response['source'] = 'dialogflow_exception';
            $response['debug_info'] = [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ];
        }

        return response()->json($response);
    }

    /**
     * Get performance analytics
     */
    public function getPerformanceAnalytics(Request $request)
    {
        try {
            $days = $request->input('days', 7);
            $analytics = $this->firestoreService->getPerformanceAnalytics($days);

            return response()->json([
                'status' => 'success',
                'data' => $analytics
            ]);
        } catch (\Exception $e) {
            Log::error('Performance Analytics Error: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to get performance analytics'
            ], 500);
        }
    }
}

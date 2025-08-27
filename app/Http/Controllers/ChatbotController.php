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

        // === Tambahan untuk testing ===
        $startOverall = microtime(true);
        $activeUsers = count(session()->all());
        Log::info("TESTING START - Pesan: '{$message}', User Aktif: {$activeUsers}");

        $response = [
            'message' => 'Maaf, terjadi kesalahan. Silakan coba lagi nanti.',
            'suggestions' => [],
        ];
        $source = 'error';

        try {
            // Ambil context percakapan sebelumnya untuk konteks yang lebih baik
            $conversationHistory = $this->getConversationHistory($sessionId, 3); // Ambil 3 pesan terakhir

            // Layer 1: Coba Dialogflow untuk intent dasar (sapaan, dll)
            $startDialogflow = microtime(true);
            Log::info("Layer 1: Mencoba Dialogflow untuk: '{$message}'");
            $dialogflowResponse = $this->detectIntent($message);
            $durationDialogflow = round((microtime(true) - $startDialogflow) * 1000, 2);
            Log::info("DURASI Dialogflow: {$durationDialogflow} ms | User Aktif: {$activeUsers}");

            // Periksa apakah Dialogflow memberikan respons yang valid dan bukan fallback
            if ($this->isValidDialogflowResponse($dialogflowResponse, $message)) {
                $response['message'] = $dialogflowResponse['text'];
                $source = 'dialogflow';
                Log::info("Dialogflow berhasil memberikan jawaban: '{$dialogflowResponse['text']}'");

                // Generate suggestions dinamis dari OpenAI berdasarkan context
                $startSuggestions = microtime(true);
                $suggestions = $this->generateDynamicSuggestions($message, $dialogflowResponse['text'], $conversationHistory);
                $durationSuggestions = round((microtime(true) - $startSuggestions) * 1000, 2);
                Log::info("DURASI Generate Suggestions: {$durationSuggestions} ms");

                $response['suggestions'] = $suggestions;
            } else {
                // Log detail mengapa Dialogflow tidak digunakan
                $this->logDialogflowFailure($dialogflowResponse, $message);

                // Layer 2: Cari jawaban di Firestore Knowledge Base dengan scoring
                $startFirestore = microtime(true);
                Log::info("Layer 2: Mencari di Firestore Knowledge Base");
                $firestoreAnswer = $this->searchFirestoreWithRelevance($message, $conversationHistory);
                $durationFirestore = round((microtime(true) - $startFirestore) * 1000, 2);
                Log::info("DURASI Firestore: {$durationFirestore} ms | User Aktif: {$activeUsers}");

                if ($firestoreAnswer && $firestoreAnswer['confidence'] > 0.6) { // Threshold confidence
                    $response['message'] = $firestoreAnswer['answer'];
                    $source = 'firestore';
                    Log::info("Firestore berhasil memberikan jawaban dengan confidence: " . $firestoreAnswer['confidence']);

                    // Generate suggestions dinamis
                    $startSuggestions = microtime(true);
                    $suggestions = $this->generateDynamicSuggestions($message, $firestoreAnswer['answer'], $conversationHistory);
                    $durationSuggestions = round((microtime(true) - $startSuggestions) * 1000, 2);
                    Log::info("DURASI Generate Suggestions: {$durationSuggestions} ms");

                    $response['suggestions'] = $suggestions;
                } else {
                    Log::info("Layer 3: Fallback ke OpenAI");

                    // Layer 3: Fallback ke OpenAI dengan enhanced context
                    $contextData = $this->gatherContextualData($message);

                    $startOpenAI = microtime(true);
                    $openAIResult = $this->enhancedOpenAIFallback($message, $contextData, $conversationHistory);
                    $durationOpenAI = round((microtime(true) - $startOpenAI) * 1000, 2);
                    Log::info("DURASI OpenAI (Enhanced): {$durationOpenAI} ms | User Aktif: {$activeUsers}");

                    if (isset($openAIResult['answer']) && !empty($openAIResult['answer'])) {
                        $response['message'] = $openAIResult['answer'];
                        $response['suggestions'] = $openAIResult['suggestions'] ?? [];
                        $source = 'openai';
                        Log::info("OpenAI berhasil memberikan jawaban");

                        // Learning Loop: Simpan pengetahuan baru ke Firestore dengan metadata
                        if ($contextData === null || !isset($contextData['from_website'])) {
                            $this->firestoreService->addKnowledgeBaseWithMetadata($message, $openAIResult['answer'], [
                                'confidence' => 0.8,
                                'context_keywords' => $this->extractKeywords($message),
                                'timestamp' => now()
                            ]);
                            Log::info("Knowledge base umum baru ditambahkan dengan metadata: '{$message}'");
                        }
                    } else {
                        $response['message'] = "Maaf, saya tidak dapat menemukan jawaban untuk pertanyaan itu saat ini. Silakan coba dengan pertanyaan yang lebih spesifik.";
                        $source = 'openai_fail';
                        Log::warning("Semua layer gagal untuk: '{$message}'");

                        // Berikan fallback suggestions
                        $response['suggestions'] = $this->getFallbackSuggestions();
                    }
                }
            }

            // Simpan log percakapan dan update metrik
            if ($source !== 'error') {
                $this->firestoreService->addChatLogWithContext($sessionId, $message, $response['message'], $source, $userId, $conversationHistory);
                $this->firestoreService->updateSystemMetrics($source);
                Log::info("Chat log dan metrik berhasil disimpan dengan source: {$source}");
            }
        } catch (\Exception $e) {
            Log::error('Chatbot Controller Error: ' . $e->getMessage() . ' in ' . $e->getFile() . ' on line ' . $e->getLine());
            $this->firestoreService->addErrorLog($e->getMessage(), $message, ['file' => $e->getFile(), 'line' => $e->getLine()]);
        }

        $durationOverall = round((microtime(true) - $startOverall) * 1000, 2);
        Log::info("TOTAL DURASI request '{$message}' : {$durationOverall} ms | Final Source: {$source} | User Aktif: {$activeUsers}");

        return response()->json($response);
    }

    private function isValidDialogflowResponse($dialogflowResponse, $message)
    {
        if (!$dialogflowResponse || empty($dialogflowResponse['text']) || $dialogflowResponse['is_fallback']) {
            return false;
        }

        $responseText = strtolower(trim($dialogflowResponse['text']));

        // Check for generic failure responses
        $failureIndicators = [
            'sorry',
            'tidak mengerti',
            'tidak tahu',
            'maaf',
            'tidak bisa',
            'coba lagi',
            'tidak paham',
            'kurang jelas'
        ];

        foreach ($failureIndicators as $indicator) {
            if (str_contains($responseText, $indicator)) {
                return false;
            }
        }

        // Check confidence threshold
        if (isset($dialogflowResponse['confidence']) && $dialogflowResponse['confidence'] < 0.7) {
            return false;
        }

        return true;
    }

    private function logDialogflowFailure($dialogflowResponse, $message)
    {
        if (!$dialogflowResponse) {
            Log::warning("Dialogflow gagal total untuk kueri: '{$message}'");
        } elseif (empty($dialogflowResponse['text'])) {
            Log::warning("Dialogflow mengembalikan respons kosong untuk: '{$message}'");
        } elseif ($dialogflowResponse['is_fallback']) {
            Log::warning("Dialogflow fallback triggered untuk: '{$message}' - Response: " . ($dialogflowResponse['text'] ?? 'null'));
        } else {
            Log::warning("Dialogflow response tidak memenuhi kriteria untuk: '{$message}' - Response: '{$dialogflowResponse['text']}'");
        }
    }

    private function getConversationHistory($sessionId, $limit = 3)
    {
        try {
            return $this->firestoreService->getChatHistory($sessionId, $limit);
        } catch (\Exception $e) {
            Log::error("Error getting conversation history: " . $e->getMessage());
            return [];
        }
    }

    private function searchFirestoreWithRelevance($message, $conversationHistory)
    {
        try {
            $keywords = $this->extractKeywords($message);
            $contextKeywords = $this->extractContextKeywords($conversationHistory);

            return $this->firestoreService->searchKnowledgeBaseWithScoring($message, $keywords, $contextKeywords);
        } catch (\Exception $e) {
            Log::error("Error searching Firestore with relevance: " . $e->getMessage());
            return null;
        }
    }

    private function extractKeywords($text)
    {
        // Simple keyword extraction - dapat diperbaiki dengan NLP library
        $stopWords = ['adalah', 'yang', 'dan', 'atau', 'dengan', 'untuk', 'dari', 'ke', 'di', 'pada', 'oleh', 'akan', 'dapat', 'sudah', 'telah', 'ini', 'itu', 'saya', 'kamu', 'kami', 'mereka'];

        $words = str_word_count(strtolower($text), 1, 'àáâãäåæçèéêëìíîïñòóôõöøùúûüý');
        $keywords = array_filter($words, function ($word) use ($stopWords) {
            return strlen($word) > 3 && !in_array($word, $stopWords);
        });

        return array_values($keywords);
    }

    private function extractContextKeywords($conversationHistory)
    {
        $contextKeywords = [];
        foreach ($conversationHistory as $chat) {
            $messageKeywords = $this->extractKeywords($chat['user_message'] ?? '');
            $responseKeywords = $this->extractKeywords($chat['bot_response'] ?? '');
            $contextKeywords = array_merge($contextKeywords, $messageKeywords, $responseKeywords);
        }

        return array_unique($contextKeywords);
    }

    private function gatherContextualData($message)
    {
        $contextData = null;

        // Check for activity/news related queries
        if (preg_match('/(kegiatan|acara|event|berita|artikel|terbaru|terkini|agenda|program)/i', $message)) {
            Log::info("Mendeteksi kata kunci kegiatan/berita. Mengambil data dari website...");
            $websiteData = $this->scrapeWebsiteForActivities();
            if ($websiteData) {
                $contextData = [
                    'type' => 'website_activities',
                    'data' => $websiteData,
                    'from_website' => true
                ];
            }
        }

        // Check for contact/location related queries
        if (preg_match('/(kontak|alamat|lokasi|dimana|telepon|email|hubungi)/i', $message)) {
            $contextData = [
                'type' => 'contact_info',
                'data' => $this->getContactInformation(),
                'from_website' => false
            ];
        }

        return $contextData;
    }

    private function getContactInformation()
    {
        return "GenBI Cirebon dapat dihubungi melalui:\n" .
            "- Website: genbicirebon.org\n" .
            "- Media sosial resmi GenBI Cirebon\n" .
            "- Melalui kegiatan-kegiatan yang diselenggarakan di Cirebon";
    }

    private function generateDynamicSuggestions($userMessage, $botResponse, $conversationHistory = [])
    {
        $apiKey = env('OPENROUTER_API_KEY');

        // Build context dari conversation history
        $historyContext = '';
        if (!empty($conversationHistory)) {
            $historyContext = "\n\nKonteks percakapan sebelumnya:\n";
            foreach (array_reverse($conversationHistory) as $chat) {
                $historyContext .= "User: " . ($chat['user_message'] ?? '') . "\n";
                $historyContext .= "Assistant: " . ($chat['bot_response'] ?? '') . "\n";
            }
        }

        $systemPrompt = "Kamu adalah GenBI Assistant yang cerdas. Berdasarkan pertanyaan user dan jawaban yang telah diberikan, buatlah 3 saran pertanyaan lanjutan yang:
1. Sangat relevan dan natural mengalir dari topik yang sedang dibahas
2. Membantu user menggali lebih dalam tentang GenBI Cirebon
3. Singkat dan jelas (maksimal 6 kata per saran)
4. Bervariasi dalam jenis (informatif, praktis, dan eksplorasi)

{$historyContext}

Format respons HANYA dalam JSON valid: {\"suggestions\": [\"Saran 1\", \"Saran 2\", \"Saran 3\"]}";

        try {
            $response = Http::timeout(30)->withHeaders([
                'Authorization' => 'Bearer ' . $apiKey,
                'Content-Type' => 'application/json',
                'HTTP-Referer' => request()->getSchemeAndHttpHost(),
                'X-Title' => 'Genbi Cirebon Chatbot',
            ])->post('https://openrouter.ai/api/v1/chat/completions', [
                "model" => "openai/gpt-3.5-turbo",
                "messages" => [
                    ["role" => "system", "content" => $systemPrompt],
                    ["role" => "user", "content" => "User bertanya: \"{$userMessage}\"\nSaya menjawab: \"{$botResponse}\"\n\nBuatkan 3 saran pertanyaan lanjutan yang natural dan relevan."]
                ],
                "response_format" => ["type" => "json_object"],
                "temperature" => 0.6,
                "max_tokens" => 200,
            ]);

            if ($response->successful()) {
                $data = json_decode($response->json()['choices'][0]['message']['content'], true);
                return $data['suggestions'] ?? $this->getFallbackSuggestions();
            }

            Log::error('Generate Suggestions HTTP Error: ' . $response->body());
            return $this->getFallbackSuggestions();
        } catch (\Exception $e) {
            Log::error('Generate Suggestions Exception: ' . $e->getMessage());
            return $this->getFallbackSuggestions();
        }
    }

    private function enhancedOpenAIFallback($text, $contextData = null, $conversationHistory = [])
    {
        $apiKey = env('OPENROUTER_API_KEY');

        // Build conversation context
        $historyContext = '';
        if (!empty($conversationHistory)) {
            $historyContext = "\n\nRiwayat percakapan:\n";
            foreach (array_reverse($conversationHistory) as $chat) {
                $historyContext .= "User: " . ($chat['user_message'] ?? '') . "\n";
                $historyContext .= "Assistant: " . ($chat['bot_response'] ?? '') . "\n";
            }
        }

        $siteContext = "Kamu adalah 'GenBI Assistant', asisten AI yang sangat ramah, informatif, dan ahli tentang GenBI Cirebon (Generasi Baru Indonesia Cirebon). GenBI adalah komunitas penerima beasiswa Bank Indonesia yang fokus pada pengembangan diri, leadership, dan kontribusi sosial. Website resmi adalah genbicirebon.org.{$historyContext}";

        $contextInjection = '';
        if ($contextData) {
            $contextInjection = "\n---INFORMASI TERKINI---\n{$contextData['data']}\n-------------------\n";
        }

        $systemPrompt = "{$siteContext}{$contextInjection}

Tugas kamu:
1. Jawab pertanyaan user secara akurat, informatif, dan ramah
2. Gunakan informasi terkini jika tersedia
3. Berikan jawaban yang natural dan mengalir dari konteks percakapan sebelumnya
4. Generate 3 saran pertanyaan lanjutan yang sangat relevan dan natural

Format respons HANYA dalam JSON valid:
{\"answer\": \"Jawaban lengkap dan informatif\", \"suggestions\": [\"Saran 1\", \"Saran 2\", \"Saran 3\"]}";

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
                "temperature" => 0.5,
                "max_tokens" => 600,
            ]);

            if ($response->successful()) {
                $data = json_decode($response->json()['choices'][0]['message']['content'], true);
                return [
                    'answer' => $data['answer'] ?? 'Maaf, gagal memformat jawaban.',
                    'suggestions' => $data['suggestions'] ?? $this->getFallbackSuggestions(),
                ];
            }

            Log::error('Enhanced OpenAI Fallback HTTP Error: ' . $response->body());
            return ['answer' => 'Maaf, saya sedang mengalami kendala teknis (API).', 'suggestions' => $this->getFallbackSuggestions()];
        } catch (\Exception $e) {
            Log::error('Enhanced OpenAI Fallback Exception: ' . $e->getMessage());
            return ['answer' => 'Maaf, koneksi ke asisten AI sedang bermasalah.', 'suggestions' => $this->getFallbackSuggestions()];
        }
    }

    private function getFallbackSuggestions()
    {
        $fallbackSuggestions = [
            ["Apa itu GenBI?", "Program beasiswa BI", "Cara bergabung GenBI"],
            ["Kegiatan GenBI Cirebon", "Syarat beasiswa BI", "Benefit menjadi GenBI"],
            ["Komunitas GenBI", "Alumni GenBI", "Peran GenBI masyarakat"],
            ["Kontak GenBI Cirebon", "Website GenBI", "Media sosial GenBI"]
        ];

        return $fallbackSuggestions[array_rand($fallbackSuggestions)];
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

            Log::info("Menginisiasi Dialogflow - Text: '{$text}', Session: {$sessionId}, Project: {$projectId}");
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

            $sessionsClient->close();

            return [
                'text' => $fulfillmentText,
                'intent_name' => $intentName,
                'is_fallback' => $isFallback,
                'confidence' => $confidence,
            ];
        } catch (\Exception $e) {
            Log::error("Dialogflow Error: " . $e->getMessage() . " | File: " . $e->getFile() . " | Line: " . $e->getLine());
            return null;
        }
    }

    private function scrapeWebsiteForActivities(): ?string
    {
        try {
            $url = 'https://genbicirebon.org/kegiatan';
            $response = Http::timeout(30)->get($url);

            if (!$response->successful()) {
                Log::warning("Gagal mengakses {$url}. Status: " . $response->status());
                return null;
            }

            $crawler = new Crawler($response->body());

            // Try multiple selectors for better compatibility
            $selectors = [
                '.blog-item',
                '.post-item',
                '.activity-item',
                '.news-item',
                'article',
                '.content-item'
            ];

            $activities = [];
            foreach ($selectors as $selector) {
                $activities = $crawler->filter($selector)->slice(0, 5)->each(function (Crawler $node) {
                    // Try multiple title selectors
                    $titleSelectors = ['.blog-title a', '.post-title a', '.title a', 'h2 a', 'h3 a', '.entry-title a'];
                    $title = 'Judul tidak ditemukan';

                    foreach ($titleSelectors as $titleSelector) {
                        $titleNode = $node->filter($titleSelector);
                        if ($titleNode->count() > 0) {
                            $title = $titleNode->text();
                            break;
                        }
                    }

                    // Try multiple date selectors
                    $dateSelectors = ['.blog-meta span', '.post-date', '.date', '.published', 'time'];
                    $date = 'Tanggal tidak ditemukan';

                    foreach ($dateSelectors as $dateSelector) {
                        $dateNode = $node->filter($dateSelector)->first();
                        if ($dateNode->count() > 0) {
                            $date = $dateNode->text();
                            break;
                        }
                    }

                    return "- {$title} (dipublikasikan: {$date})";
                });

                if (!empty($activities)) {
                    break; // Found activities with this selector
                }
            }

            if (empty($activities)) {
                Log::info('Tidak ada item kegiatan yang ditemukan di website dengan selector yang tersedia.');
                return "Saat ini tidak ada informasi kegiatan terbaru yang bisa ditampilkan dari website. Silakan kunjungi genbicirebon.org untuk informasi terkini.";
            }

            return "Berikut adalah beberapa kegiatan atau berita terbaru dari website genbicirebon.org:\n" . implode("\n", $activities);
        } catch (\Exception $e) {
            Log::error('Scraping Error: ' . $e->getMessage());
            return null;
        }
    }

    // Existing methods (testDialogflow, sendMessageDialogflowOnly, resetKnowledgeBase) remain the same
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

            $startDialogflow = microtime(true);
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

            $durationDialogflow = round((microtime(true) - $startDialogflow) * 1000, 2);
            Log::info("Dialogflow test durasi: {$durationDialogflow} ms | Intent: '{$intentName}', Confidence: {$confidence}");

            $sessionsClient->close();

            return response()->json([
                'status' => 'success',
                'message' => 'Koneksi Dialogflow berhasil!',
                'response' => $fulfillmentText ?: 'Tidak ada respons teks dari Dialogflow.',
                'intent_name' => $intentName,
                'is_fallback' => $isFallback,
                'confidence' => $confidence,
                'duration_ms' => $durationDialogflow,
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

            $startDialogflow = microtime(true);
            $dialogflowResponse = $this->detectIntent($message);
            $durationDialogflow = round((microtime(true) - $startDialogflow) * 1000, 2);
            Log::info("DURASI Dialogflow Only: {$durationDialogflow} ms");

            if ($dialogflowResponse && !empty($dialogflowResponse['text'])) {
                $response['message'] = $dialogflowResponse['text'];
                $response['source'] = 'dialogflow';
                $response['debug_info'] = $dialogflowResponse;
                Log::info("Dialogflow Only Response: '{$dialogflowResponse['text']}'");
            }
        } catch (\Exception $e) {
            Log::error("Dialogflow Only Error: " . $e->getMessage() . " | File: " . $e->getFile() . " | Line: " . $e->getLine());
        }

        return response()->json($response);
    }

    public function resetKnowledgeBase()
    {
        $success = $this->firestoreService->resetKnowledgeBase();

        if ($success) {
            return response()->json([
                'status' => 'success',
                'message' => 'Knowledge base berhasil direset.'
            ]);
        } else {
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal mereset knowledge base. Lihat log untuk detail.'
            ], 500);
        }
    }
}

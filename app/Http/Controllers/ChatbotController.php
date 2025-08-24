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

        $response = [
            'message' => 'Maaf, terjadi kesalahan. Silakan coba lagi nanti.',
            'suggestions' => [],
        ];
        $source = 'error';

        try {
            Log::info("Processing message: '{$message}'");

            // Layer 1: Coba Dialogflow untuk intent dasar (sapaan, dll)
            $dialogflowResponse = $this->detectIntent($message);

            if ($this->isDialogflowResponseValid($dialogflowResponse)) {
                Log::info("Dialogflow berhasil memberikan jawaban: " . $dialogflowResponse['text']);
                $response['message'] = $dialogflowResponse['text'];
                $source = 'dialogflow';

                // Dapatkan sugesti cerdas dari OpenAI
                $openAIResult = $this->fallbackWithOpenAI($message, null, true);
                $response['suggestions'] = $openAIResult['suggestions'] ?? [];
            } else {
                Log::info("Dialogflow tidak memberikan jawaban yang valid, mencoba Firebase Knowledge Base");

                // Layer 2: Cari jawaban di Firestore Knowledge Base
                $firestoreAnswer = $this->firestoreService->searchKnowledgeBase($message);

                if ($firestoreAnswer && !empty(trim($firestoreAnswer))) {
                    Log::info("Firebase Knowledge Base berhasil memberikan jawaban: " . $firestoreAnswer);
                    $response['message'] = $firestoreAnswer;
                    $source = 'firestore';

                    // Dapatkan sugesti cerdas dari OpenAI
                    $openAIResult = $this->fallbackWithOpenAI($message, null, true);
                    $response['suggestions'] = $openAIResult['suggestions'] ?? [];
                } else {
                    Log::info("Firebase Knowledge Base tidak memiliki jawaban, menggunakan OpenAI");

                    // Layer 3: Fallback ke OpenAI dengan konteks tambahan jika perlu
                    $contextData = null;

                    // Cek apakah pertanyaan memerlukan data real-time dari website
                    if ($this->isRealTimeQuery($message)) {
                        Log::info("Mendeteksi pertanyaan real-time, scraping website...");
                        $contextData = $this->scrapeWebsiteForActivities();
                    }

                    $openAIResult = $this->fallbackWithOpenAI($message, $contextData);

                    if (isset($openAIResult['answer']) && !empty(trim($openAIResult['answer']))) {
                        Log::info("OpenAI berhasil memberikan jawaban");
                        $response['message'] = $openAIResult['answer'];
                        $response['suggestions'] = $openAIResult['suggestions'] ?? [];
                        $source = 'openai';

                        // Learning Loop: Simpan pengetahuan baru ke Firestore
                        // Hanya simpan jawaban umum, bukan yang spesifik waktu
                        if ($contextData === null && $this->shouldSaveToKnowledgeBase($message, $openAIResult['answer'])) {
                            $saved = $this->firestoreService->addKnowledgeBase($message, $openAIResult['answer']);
                            if ($saved) {
                                Log::info("Knowledge base baru ditambahkan: '{$message}'");
                            } else {
                                Log::warning("Gagal menyimpan ke knowledge base: '{$message}'");
                            }
                        }
                    } else {
                        Log::warning("OpenAI tidak memberikan jawaban yang valid");
                        $response['message'] = "Maaf, saya tidak dapat menemukan jawaban untuk pertanyaan itu saat ini. Silakan coba dengan pertanyaan yang lebih spesifik.";
                        $source = 'openai_fail';
                    }
                }
            }

            // Simpan log percakapan
            if ($source !== 'error') {
                $logSaved = $this->firestoreService->addChatLog($sessionId, $message, $response['message'], $source, $userId);
                if (!$logSaved) {
                    Log::warning("Gagal menyimpan chat log untuk session: {$sessionId}");
                }
            }

            Log::info("Response berhasil digenerate dari source: {$source}");
        } catch (\Exception $e) {
            Log::error('Chatbot Controller Error: ' . $e->getMessage() . ' in ' . $e->getFile() . ' on line ' . $e->getLine());

            // Simpan error log ke Firestore
            try {
                $this->firestoreService->addErrorLog($e->getMessage(), $message);
            } catch (\Exception $logError) {
                Log::error('Failed to save error log: ' . $logError->getMessage());
            }
        }

        return response()->json($response);
    }

    /**
     * Validasi apakah response Dialogflow valid dan bukan fallback
     */
    private function isDialogflowResponseValid($dialogflowResponse): bool
    {
        return $dialogflowResponse !== null
            && isset($dialogflowResponse['text'])
            && !empty(trim($dialogflowResponse['text']))
            && isset($dialogflowResponse['is_fallback'])
            && !$dialogflowResponse['is_fallback']
            && !$this->isGenericResponse($dialogflowResponse['text']);
    }

    /**
     * Cek apakah response adalah jawaban generic/default
     */
    private function isGenericResponse(string $text): bool
    {
        $genericResponses = [
            'maaf saya tidak mengerti',
            'bisa diulangi',
            'tidak faham',
            'default welcome intent',
            'i didn\'t get that',
            'can you say that again',
            'sorry, i don\'t understand'
        ];

        $lowerText = strtolower(trim($text));
        foreach ($genericResponses as $generic) {
            if (strpos($lowerText, $generic) !== false) {
                return true;
            }
        }

        return false;
    }

    /**
     * Cek apakah pertanyaan memerlukan data real-time
     */
    private function isRealTimeQuery(string $message): bool
    {
        $realTimeKeywords = [
            'kegiatan',
            'acara',
            'event',
            'berita',
            'artikel',
            'terbaru',
            'terkini',
            'hari ini',
            'minggu ini',
            'bulan ini',
            'sekarang',
            'saat ini',
            'agenda'
        ];

        $lowerMessage = strtolower($message);
        foreach ($realTimeKeywords as $keyword) {
            if (strpos($lowerMessage, $keyword) !== false) {
                return true;
            }
        }

        return false;
    }

    /**
     * Tentukan apakah jawaban layak disimpan ke knowledge base
     */
    private function shouldSaveToKnowledgeBase(string $question, string $answer): bool
    {
        // Jangan simpan jika terlalu pendek
        if (strlen(trim($answer)) < 10) {
            return false;
        }

        // Jangan simpan jawaban yang mengandung kata kunci temporal
        $temporalKeywords = ['hari ini', 'sekarang', 'saat ini', 'terbaru', 'minggu ini', 'bulan ini'];
        $lowerAnswer = strtolower($answer);

        foreach ($temporalKeywords as $keyword) {
            if (strpos($lowerAnswer, $keyword) !== false) {
                return false;
            }
        }

        // Jangan simpan jika mengandung error message
        $errorKeywords = ['maaf', 'tidak dapat', 'bermasalah', 'kesalahan', 'kendala'];
        foreach ($errorKeywords as $keyword) {
            if (strpos($lowerAnswer, $keyword) !== false) {
                return false;
            }
        }

        return true;
    }

    private function scrapeWebsiteForActivities(): ?string
    {
        try {
            $url = 'https://genbicirebon.org/kegiatan';
            Log::info("Scraping website: {$url}");

            $response = Http::timeout(30)->get($url);

            if (!$response->successful()) {
                Log::warning("Gagal mengakses {$url}. Status: " . $response->status());
                return null;
            }

            $crawler = new Crawler($response->body());

            // Multiple selectors untuk berbagai kemungkinan struktur HTML
            $selectors = [
                '.blog-item',
                '.news-item',
                '.activity-item',
                'article',
                '.post',
                '.content-item'
            ];

            $activities = [];
            foreach ($selectors as $selector) {
                $items = $crawler->filter($selector)->slice(0, 5);
                if ($items->count() > 0) {
                    $activities = $items->each(function (Crawler $node) {
                        // Multiple selectors untuk title
                        $title = $this->extractText($node, [
                            '.blog-title a',
                            '.title a',
                            'h1 a',
                            'h2 a',
                            'h3 a',
                            '.blog-title',
                            '.title',
                            'h1',
                            'h2',
                            'h3'
                        ], 'Judul tidak ditemukan');

                        // Multiple selectors untuk date
                        $date = $this->extractText($node, [
                            '.blog-meta span',
                            '.date',
                            '.published',
                            '.post-date',
                            '.meta span',
                            'time',
                            '.timestamp'
                        ], 'Tanggal tidak ditemukan');

                        return "- {$title} (dipublikasikan: {$date})";
                    });
                    break; // Stop setelah menemukan content
                }
            }

            if (empty($activities)) {
                Log::info('Tidak ada item kegiatan yang ditemukan dengan selector yang tersedia');
                return "Saat ini informasi kegiatan terbaru sedang tidak tersedia dari website. Silakan kunjungi langsung genbicirebon.org untuk informasi terkini.";
            }

            $result = "Berikut adalah kegiatan atau berita terbaru dari website GenBI Cirebon:\n" . implode("\n", $activities);
            Log::info("Berhasil scraping " . count($activities) . " item kegiatan");
            return $result;
        } catch (\Exception $e) {
            Log::error('Scraping Error: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Helper method untuk extract text dengan multiple selectors
     */
    private function extractText(Crawler $node, array $selectors, string $default = ''): string
    {
        foreach ($selectors as $selector) {
            try {
                $element = $node->filter($selector);
                if ($element->count() > 0) {
                    return trim($element->text());
                }
            } catch (\Exception $e) {
                continue;
            }
        }
        return $default;
    }

    private function fallbackWithOpenAI(string $text, ?string $externalContext = null, bool $suggestionsOnly = false)
    {
        $apiKey = env('OPENROUTER_API_KEY');

        if (empty($apiKey)) {
            Log::error('OpenAI API Key tidak ditemukan');
            return ['answer' => 'Konfigurasi sistem belum lengkap.', 'suggestions' => []];
        }

        $siteContext = "Kamu adalah 'GenBI Assistant', asisten AI yang ramah, informatif, dan ahli tentang GenBI Cirebon (Generasi Baru Indonesia Cirebon), sebuah komunitas penerima beasiswa Bank Indonesia. Website resmi adalah genbicirebon.org. Jawablah semua pertanyaan dalam konteks ini dengan bahasa Indonesia yang natural dan informatif.";

        $promptAction = "";
        if ($suggestionsOnly) {
            $promptAction = "Tugasmu HANYA memberikan 3 saran pertanyaan lanjutan yang relevan dengan pertanyaan pengguna. JANGAN menjawab pertanyaan pengguna. Saran harus singkat dan menarik.";
        } else {
            $promptAction = "Jawab pertanyaan pengguna secara ringkas, informatif, dan ramah. Berikan jawaban yang akurat dalam konteks GenBI Cirebon. Setelah menjawab, berikan 3 saran pertanyaan lanjutan yang relevan dan menarik (maksimal 5 kata per saran).";
        }

        $contextInjection = "";
        if ($externalContext) {
            $contextInjection = "INFORMASI TERKINI dari website:\n---\n{$externalContext}\n---\nGunakan informasi ini untuk memberikan jawaban yang akurat dan terkini.\n\n";
        }

        $systemPrompt = "{$siteContext}\n\n{$contextInjection}{$promptAction}\n\nFormat respons HARUS dalam bentuk JSON valid seperti ini: {\"answer\": \"Jawabanmu di sini\", \"suggestions\": [\"Saran 1\", \"Saran 2\", \"Saran 3\"]}. Jika hanya diminta saran, isi field 'answer' dengan string kosong.";

        try {
            Log::info("Mengirim request ke OpenAI untuk: " . substr($text, 0, 50) . "...");

            $response = Http::timeout(45)->withHeaders([
                'Authorization' => 'Bearer ' . $apiKey,
                'Content-Type' => 'application/json',
                'HTTP-Referer' => request()->getSchemeAndHttpHost(),
                'X-Title' => 'GenBI Cirebon Chatbot',
            ])->post('https://openrouter.ai/api/v1/chat/completions', [
                "model" => "openai/gpt-3.5-turbo",
                "messages" => [
                    ["role" => "system", "content" => $systemPrompt],
                    ["role" => "user", "content" => $text]
                ],
                "response_format" => ["type" => "json_object"],
                "temperature" => 0.4,
                "max_tokens" => 500,
            ]);

            if ($response->successful()) {
                $content = $response->json()['choices'][0]['message']['content'] ?? '';
                $data = json_decode($content, true);

                if (json_last_error() === JSON_ERROR_NONE) {
                    Log::info("OpenAI response berhasil diparsing");
                    return [
                        'answer' => $data['answer'] ?? ($suggestionsOnly ? '' : 'Gagal memformat jawaban.'),
                        'suggestions' => array_slice($data['suggestions'] ?? [], 0, 3), // Maksimal 3 saran
                    ];
                } else {
                    Log::error('OpenAI JSON parsing error: ' . json_last_error_msg() . ' | Content: ' . $content);
                    return ['answer' => 'Maaf, terjadi kesalahan dalam memproses respons AI.', 'suggestions' => []];
                }
            }

            Log::error('OpenAI HTTP Error: ' . $response->status() . ' | ' . $response->body());
            return ['answer' => 'Maaf, layanan AI sedang mengalami gangguan.', 'suggestions' => []];
        } catch (\Exception $e) {
            Log::error('OpenAI Exception: ' . $e->getMessage());
            return ['answer' => 'Maaf, koneksi ke layanan AI bermasalah.', 'suggestions' => []];
        }
    }

    private function detectIntent(string $text)
    {
        try {
            $projectId = env('DIALOGFLOW_PROJECT_ID');
            $sessionId = session()->getId();
            $credentialsPath = storage_path(env('FIREBASE_CREDENTIALS'));

            if (empty($projectId) || !file_exists($credentialsPath)) {
                Log::warning('Dialogflow configuration incomplete');
                return null;
            }

            Log::info("Mengirim request ke Dialogflow: " . substr($text, 0, 50) . "...");

            $sessionsClient = new SessionsClient(['credentials' => $credentialsPath]);
            $session = $sessionsClient->sessionName($projectId, $sessionId);

            $textInput = (new TextInput())->setText($text)->setLanguageCode('id');
            $queryInput = (new QueryInput())->setText($textInput);

            $request = (new DetectIntentRequest())
                ->setSession($session)
                ->setQueryInput($queryInput);

            $response = $sessionsClient->detectIntent($request);
            $queryResult = $response->getQueryResult();

            $sessionsClient->close();

            $result = [
                'text' => $queryResult->getFulfillmentText(),
                'is_fallback' => $queryResult->getIntent()->getIsFallback(),
                'intent_name' => $queryResult->getIntent()->getDisplayName(),
                'confidence' => $queryResult->getIntentDetectionConfidence(),
            ];

            Log::info("Dialogflow response: Intent='{$result['intent_name']}', Fallback={$result['is_fallback']}, Confidence={$result['confidence']}");

            return $result;
        } catch (\Exception $e) {
            Log::error("Dialogflow Error: " . $e->getMessage());
            return null;
        }
    }
}

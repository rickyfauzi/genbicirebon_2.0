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
// [LOGGING ADDITION] Import File facade for session counting
use Illuminate\Support\Facades\File;

class ChatbotController extends Controller
{
    private $firestoreService;

    public function __construct(FirestoreService $firestoreService)
    {
        $this->firestoreService = $firestoreService;
    }

    public function sendMessage(Request $request)
    {
        // [LOGGING START] Record total request start time
        $requestStartTime = microtime(true);
        // [LOGGING END]

        $message = $request->input('message');
        $sessionId = $request->input('session_id', session()->getId());
        $userId = auth()->id();
        $response = [
            'message' => 'Maaf, terjadi kesalahan. Silakan coba lagi nanti.',
            'suggestions' => [],
        ];
        $source = 'error';

        // [LOGGING START] Initialize duration variables for each layer
        $dialogflowDuration = 0;
        $firestoreDuration = 0;
        $openAIDuration = 0;
        // [LOGGING END]

        try {
            // Layer 1: Coba Dialogflow untuk intent dasar (sapaan, dll)
            Log::info("Layer 1: Mencoba Dialogflow untuk: '{$message}'", ['session_id' => $sessionId]);

            // [LOGGING START] Time the Dialogflow call
            $dialogflowStartTime = microtime(true);
            $dialogflowResponse = $this->detectIntent($message);
            $dialogflowEndTime = microtime(true);
            $dialogflowDuration = ($dialogflowEndTime - $dialogflowStartTime) * 1000; // in milliseconds
            Log::info(
                "[PERFORMANCE] Dialogflow execution time",
                ['duration_ms' => round($dialogflowDuration, 2), 'session_id' => $sessionId]
            );
            // [LOGGING END]

            if (
                $dialogflowResponse &&
                !empty($dialogflowResponse['text']) &&
                !$dialogflowResponse['is_fallback'] &&
                trim($dialogflowResponse['text']) !== '' &&
                !str_contains(strtolower($dialogflowResponse['text']), 'sorry') &&
                !str_contains(strtolower($dialogflowResponse['text']), 'tidak mengerti')
            ) {
                $response['message'] = $dialogflowResponse['text'];
                $source = 'dialogflow';
                Log::info("Dialogflow berhasil memberikan jawaban: '{$dialogflowResponse['text']}'", ['session_id' => $sessionId]);

                // [LOGGING START] Time OpenAI call for suggestions
                $openAIStartTime = microtime(true);
                $openAIResult = $this->fallbackWithOpenAI($message, null, true);
                $openAIEndTime = microtime(true);
                $openAIDuration = ($openAIEndTime - $openAIStartTime) * 1000;
                Log::info(
                    "[PERFORMANCE] OpenAI (suggestions only) execution time",
                    ['duration_ms' => round($openAIDuration, 2), 'session_id' => $sessionId]
                );
                // [LOGGING END]

                $response['suggestions'] = $openAIResult['suggestions'] ?? [];
            } else {
                if (!$dialogflowResponse) {
                    Log::warning("Dialogflow gagal total untuk kueri: '{$message}'", ['session_id' => $sessionId]);
                } elseif (empty($dialogflowResponse['text'])) {
                    Log::warning("Dialogflow mengembalikan respons kosong untuk: '{$message}'", ['session_id' => $sessionId]);
                } elseif ($dialogflowResponse['is_fallback']) {
                    Log::warning("Dialogflow fallback triggered untuk: '{$message}' - Response: " . ($dialogflowResponse['text'] ?? 'null'), ['session_id' => $sessionId]);
                } else {
                    Log::warning("Dialogflow response tidak memenuhi kriteria untuk: '{$message}' - Response: '{$dialogflowResponse['text']}'", ['session_id' => $sessionId]);
                }

                // Layer 2: Cari jawaban di Firestore Knowledge Base
                Log::info("Layer 2: Mencari di Firestore Knowledge Base", ['session_id' => $sessionId]);

                // [LOGGING START] Time the Firestore call
                $firestoreStartTime = microtime(true);
                $firestoreAnswer = $this->firestoreService->searchKnowledgeBase($message);
                $firestoreEndTime = microtime(true);
                $firestoreDuration = ($firestoreEndTime - $firestoreStartTime) * 1000;
                Log::info(
                    "[PERFORMANCE] Firestore KB search time",
                    ['duration_ms' => round($firestoreDuration, 2), 'found' => !is_null($firestoreAnswer), 'session_id' => $sessionId]
                );
                // [LOGGING END]

                if ($firestoreAnswer) {
                    $response['message'] = $firestoreAnswer;
                    $source = 'firestore';
                    Log::info("Firestore berhasil memberikan jawaban", ['session_id' => $sessionId]);
                    $openAIResult = $this->fallbackWithOpenAI($message, null, true);
                    $response['suggestions'] = $openAIResult['suggestions'] ?? [];
                } else {
                    Log::info("Layer 3: Fallback ke OpenAI", ['session_id' => $sessionId]);
                    $contextData = null;
                    if (preg_match('/(kegiatan|acara|event|berita|artikel|terbaru|terkini)/i', $message)) {
                        Log::info("Mendeteksi kata kunci kegiatan/berita. Mengambil data dari website...", ['session_id' => $sessionId]);
                        $contextData = $this->scrapeWebsiteForActivities();
                    }

                    // [LOGGING START] Time the OpenAI fallback call
                    $openAIStartTime = microtime(true);
                    $openAIResult = $this->fallbackWithOpenAI($message, $contextData);
                    $openAIEndTime = microtime(true);
                    $openAIDuration = ($openAIEndTime - $openAIStartTime) * 1000;
                    Log::info(
                        "[PERFORMANCE] OpenAI (fallback) execution time",
                        ['duration_ms' => round($openAIDuration, 2), 'session_id' => $sessionId]
                    );
                    // [LOGGING END]

                    if (isset($openAIResult['answer']) && !empty($openAIResult['answer'])) {
                        $response['message'] = $openAIResult['answer'];
                        $response['suggestions'] = $openAIResult['suggestions'] ?? [];
                        $source = 'openai';
                        Log::info("OpenAI berhasil memberikan jawaban", ['session_id' => $sessionId]);
                        if ($contextData === null) {
                            $this->firestoreService->addKnowledgeBase($message, $openAIResult['answer']);
                            Log::info("Knowledge base umum baru ditambahkan: '{$message}'", ['session_id' => $sessionId]);
                        }
                    } else {
                        $response['message'] = "Maaf, saya tidak dapat menemukan jawaban untuk pertanyaan itu saat ini. Silakan coba dengan pertanyaan yang berbeda.";
                        $source = 'openai_fail';
                        Log::warning("Semua layer gagal untuk: '{$message}'", ['session_id' => $sessionId]);
                    }
                }
            }
            if ($source !== 'error') {
                $this->firestoreService->addChatLog($sessionId, $message, $response['message'], $source, $userId);
                $this->firestoreService->updateSystemMetrics($source);
                Log::info("Chat log dan metrik berhasil disimpan dengan source: {$source}", ['session_id' => $sessionId]);
            }
        } catch (\Exception $e) {
            Log::error('Chatbot Controller Error: ' . $e->getMessage() . ' in ' . $e->getFile() . ' on line ' . $e->getLine(), ['session_id' => $sessionId]);
            $this->firestoreService->addErrorLog($e->getMessage(), $message, ['file' => $e->getFile(), 'line' => $e->getLine()]);
        }

        // [LOGGING START] Final performance summary log
        $requestEndTime = microtime(true);
        $totalDuration = ($requestEndTime - $requestStartTime) * 1000;
        $activeUsers = $this->getActiveUsersCount();
        Log::notice(
            "[PERFORMANCE SUMMARY] Request processed.",
            [
                'session_id' => $sessionId,
                'final_source' => $source,
                'total_duration_ms' => round($totalDuration, 2),
                'dialogflow_ms' => round($dialogflowDuration, 2),
                'firestore_ms' => round($firestoreDuration, 2),
                'openai_ms' => round($openAIDuration, 2),
                'estimated_active_users' => $activeUsers,
                'user_message' => $message,
            ]
        );
        // [LOGGING END]

        return response()->json($response);
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
            Log::info("Dialogflow Response - Intent: '{$intentName}', Text: '{$fulfillmentText}', Fallback: {$isFallback}, Confidence: {$confidence}");
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

    // ... (rest of your methods like scrapeWebsiteForActivities and fallbackWithOpenAI remain unchanged)
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
            Log::error('Scraping Error: ' . $e->getMessage());
            return null;
        }
    }
    private function fallbackWithOpenAI(string $text, ?string $externalContext = null, bool $suggestionsOnly = false)
    {
        $apiKey = env('OPENROUTER_API_KEY');
        $siteContext = "Kamu adalah 'GenBI Assistant', asisten AI yang ramah, informatif, dan ahli tentang GenBI Cirebon (Generasi Baru Indonesia Cirebon), sebuah komunitas penerima beasiswa Bank Indonesia. Website resmi adalah genbicirebon.org. Jawablah semua pertanyaan dalam konteks ini.";
        $promptAction = $suggestionsOnly ? "Tugasmu HANYA memberikan 3 saran pertanyaan lanjutan yang relevan dengan pertanyaan pengguna. JANGAN menjawab pertanyaan pengguna." : "Jawab pertanyaan pengguna secara ringkas dan informatif. Setelah menjawab, berikan 3 saran pertanyaan lanjutan yang relevan dan sangat singkat (maksimal 4 kata per saran).";
        $contextInjection = $externalContext ? "Gunakan informasi tambahan berikut untuk menjawab pertanyaan pengguna secara akurat:\n---INFO TAMBAHAN---\n{$externalContext}\n-------------------\n" : "";
        $systemPrompt = "{$siteContext} {$contextInjection} {$promptAction} Format respons HANYA dalam bentuk JSON valid seperti ini: {\"answer\": \"Jawabanmu di sini.\", \"suggestions\": [\"Saran 1\", \"Saran 2\", \"Saran 3\"]}. Jika hanya diminta saran, isi field 'answer' dengan string kosong.";
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
                "temperature" => 0.4,
                "max_tokens" => 500,
            ]);
            if ($response->successful()) {
                $data = json_decode($response->json()['choices'][0]['message']['content'], true);
                return [
                    'answer' => $data['answer'] ?? ($suggestionsOnly ? '' : 'Gagal memformat jawaban.'),
                    'suggestions' => $data['suggestions'] ?? [],
                ];
            }
            Log::error('OpenAI Fallback HTTP Error: ' . $response->body());
            return ['answer' => 'Maaf, saya sedang mengalami kendala teknis (API).', 'suggestions' => []];
        } catch (\Exception $e) {
            Log::error('OpenAI Fallback Exception: ' . $e->getMessage());
            return ['answer' => 'Maaf, koneksi ke asisten AI sedang bermasalah.', 'suggestions' => []];
        }
    }


    // [LOGGING ADDITION START] New private method to estimate active users
    /**
     * Estimates the number of active users based on session files.
     * NOTE: This is an estimation and works best with the 'file' session driver.
     * For 'database' or 'redis', a different implementation would be needed.
     *
     * @return int
     */
    private function getActiveUsersCount(): int
    {
        if (config('session.driver') !== 'file') {
            Log::warning("[PERFORMANCE] Active user count is only supported for 'file' session driver. Current driver: " . config('session.driver'));
            return 1; // Return 1 for the current user.
        }

        try {
            $sessionPath = storage_path('framework/sessions');
            $lifetimeInSeconds = config('session.lifetime') * 60;
            $activeCount = 0;

            if (!File::isDirectory($sessionPath)) {
                return 0;
            }

            $files = File::files($sessionPath);
            $currentTime = time();

            foreach ($files as $file) {
                // Ignore dotfiles like .gitignore
                if ($file->getFilename()[0] === '.') {
                    continue;
                }

                if (($currentTime - $file->getMTime()) < $lifetimeInSeconds) {
                    $activeCount++;
                }
            }
            return $activeCount;
        } catch (\Exception $e) {
            Log::error("[PERFORMANCE] Could not count active sessions: " . $e->getMessage());
            return 0; // Return 0 on error
        }
    }
    // [LOGGING ADDITION END]


    // [UNIT TESTING LOGGING START] Updated test methods with performance logging
    public function testDialogflow()
    {
        $testStartTime = microtime(true);
        try {
            // ... (your existing logic is unchanged)
            $projectId = env('DIALOGFLOW_PROJECT_ID');
            $envPath = env('DIALOGFLOW_CREDENTIALS');
            $cleanPath = str_replace('storage/', '', $envPath);
            $credentialsPath = storage_path($cleanPath);
            $sessionId = uniqid('test-');
            if (!file_exists($credentialsPath)) {
                return response()->json(['status' => 'error', 'message' => "File kredensial tidak ditemukan di: {$credentialsPath}",], 500);
            }
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
            $sessionsClient->close();

            $testEndTime = microtime(true);
            $duration = ($testEndTime - $testStartTime) * 1000;
            Log::info("[UNIT TEST] Dialogflow connection test successful.", ['duration_ms' => round($duration, 2)]);

            return response()->json([
                'status' => 'success',
                'message' => 'Koneksi Dialogflow berhasil!',
                'response' => $fulfillmentText ?: 'Tidak ada respons teks dari Dialogflow.',
                'intent_name' => $intentName,
                'is_fallback' => $isFallback,
                'confidence' => $confidence,
                'duration_ms' => round($duration, 2)
            ]);
        } catch (\Exception $e) {
            Log::error("Dialogflow Test Error: " . $e->getMessage() . " | File: " . $e->getFile() . " | Line: " . $e->getLine());
            $this->firestoreService->addErrorLog($e->getMessage(), 'Test Dialogflow', ['file' => $e->getFile(), 'line' => $e->getLine()]);
            return response()->json(['status' => 'error', 'message' => 'Gagal menghubungkan ke Dialogflow: ' . $e->getMessage(),], 500);
        }
    }

    public function sendMessageDialogflowOnly(Request $request)
    {
        $testStartTime = microtime(true);
        $message = $request->input('message');
        $sessionId = $request->input('session_id', session()->getId());
        $response = ['message' => 'Dialogflow tidak memberikan respons', 'source' => 'dialogflow_fail', 'debug_info' => [],];

        try {
            $dialogflowResponse = $this->detectIntent($message);
            // ... (rest of your logic is unchanged)
            if ($dialogflowResponse) {
                $response['message'] = $dialogflowResponse['text'] ?: 'Dialogflow mengembalikan respons kosong';
                $response['source'] = $dialogflowResponse['is_fallback'] ? 'dialogflow_fallback' : 'dialogflow_success';
                $response['debug_info'] = [
                    'intent_name' => $dialogflowResponse['intent_name'] ?? 'unknown',
                    'confidence' => $dialogflowResponse['confidence'] ?? 0,
                    'is_fallback' => $dialogflowResponse['is_fallback'] ?? true,
                ];
            }
        } catch (\Exception $e) {
            // ... (your error handling is unchanged)
            Log::error('DIALOGFLOW ONLY ERROR: ' . $e->getMessage() . ' in ' . $e->getFile() . ' on line ' . $e->getLine());
            $response['message'] = 'Error: ' . $e->getMessage();
            $response['source'] = 'dialogflow_exception';
        }

        $testEndTime = microtime(true);
        $duration = ($testEndTime - $testStartTime) * 1000;
        $response['duration_ms'] = round($duration, 2);
        Log::info("[UNIT TEST] Dialogflow-only request processed.", [
            'duration_ms' => $response['duration_ms'],
            'source' => $response['source'],
            'user_message' => $message
        ]);

        return response()->json($response);
    }
    // [UNIT TESTING LOGGING END]
}

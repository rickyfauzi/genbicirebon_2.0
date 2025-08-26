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
            // Layer 1: Coba Dialogflow untuk intent dasar (sapaan, dll)
            $startDialogflow = microtime(true);
            Log::info("Layer 1: Mencoba Dialogflow untuk: '{$message}'");
            $dialogflowResponse = $this->detectIntent($message);
            $durationDialogflow = round((microtime(true) - $startDialogflow) * 1000, 2);
            Log::info("DURASI Dialogflow: {$durationDialogflow} ms | User Aktif: {$activeUsers}");

            // Periksa apakah Dialogflow memberikan respons yang valid dan bukan fallback
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
                Log::info("Dialogflow berhasil memberikan jawaban: '{$dialogflowResponse['text']}'");

                // Dapatkan sugesti cerdas dari OpenAI
                $startOpenAI = microtime(true);
                $openAIResult = $this->fallbackWithOpenAI($message, null, true);
                $durationOpenAI = round((microtime(true) - $startOpenAI) * 1000, 2);
                Log::info("DURASI OpenAI (Sugesti Saja): {$durationOpenAI} ms | User Aktif: {$activeUsers}");

                $response['suggestions'] = $openAIResult['suggestions'] ?? [];
            } else {
                // Log detail mengapa Dialogflow tidak digunakan
                if (!$dialogflowResponse) {
                    Log::warning("Dialogflow gagal total untuk kueri: '{$message}'");
                } elseif (empty($dialogflowResponse['text'])) {
                    Log::warning("Dialogflow mengembalikan respons kosong untuk: '{$message}'");
                } elseif ($dialogflowResponse['is_fallback']) {
                    Log::warning("Dialogflow fallback triggered untuk: '{$message}' - Response: " . ($dialogflowResponse['text'] ?? 'null'));
                } else {
                    Log::warning("Dialogflow response tidak memenuhi kriteria untuk: '{$message}' - Response: '{$dialogflowResponse['text']}'");
                }

                // Layer 2: Cari jawaban di Firestore Knowledge Base
                $startFirestore = microtime(true);
                Log::info("Layer 2: Mencari di Firestore Knowledge Base");
                $firestoreAnswer = $this->firestoreService->searchKnowledgeBase($message);
                $durationFirestore = round((microtime(true) - $startFirestore) * 1000, 2);
                Log::info("DURASI Firestore: {$durationFirestore} ms | User Aktif: {$activeUsers}");

                if ($firestoreAnswer) {
                    $response['message'] = $firestoreAnswer;
                    $source = 'firestore';
                    Log::info("Firestore berhasil memberikan jawaban");

                    // Dapatkan sugesti cerdas dari OpenAI
                    $startOpenAI = microtime(true);
                    $openAIResult = $this->fallbackWithOpenAI($message, null, true);
                    $durationOpenAI = round((microtime(true) - $startOpenAI) * 1000, 2);
                    Log::info("DURASI OpenAI (Sugesti Saja): {$durationOpenAI} ms | User Aktif: {$activeUsers}");

                    $response['suggestions'] = $openAIResult['suggestions'] ?? [];
                } else {
                    Log::info("Layer 3: Fallback ke OpenAI");

                    // Layer 3: Fallback ke OpenAI
                    $contextData = null;
                    if (preg_match('/(kegiatan|acara|event|berita|artikel|terbaru|terkini)/i', $message)) {
                        Log::info("Mendeteksi kata kunci kegiatan/berita. Mengambil data dari website...");
                        $contextData = $this->scrapeWebsiteForActivities();
                    }

                    $startOpenAI = microtime(true);
                    $openAIResult = $this->fallbackWithOpenAI($message, $contextData);
                    $durationOpenAI = round((microtime(true) - $startOpenAI) * 1000, 2);
                    Log::info("DURASI OpenAI (Jawaban Lengkap): {$durationOpenAI} ms | User Aktif: {$activeUsers}");

                    if (isset($openAIResult['answer']) && !empty($openAIResult['answer'])) {
                        $response['message'] = $openAIResult['answer'];
                        $response['suggestions'] = $openAIResult['suggestions'] ?? [];
                        $source = 'openai';
                        Log::info("OpenAI berhasil memberikan jawaban");

                        // Learning Loop: Simpan pengetahuan baru ke Firestore
                        if ($contextData === null) {
                            $this->firestoreService->addKnowledgeBase($message, $openAIResult['answer']);
                            Log::info("Knowledge base umum baru ditambahkan: '{$message}'");
                        }
                    } else {
                        $response['message'] = "Maaf, saya tidak dapat menemukan jawaban untuk pertanyaan itu saat ini. Silakan coba dengan pertanyaan yang berbeda.";
                        $source = 'openai_fail';
                        Log::warning("Semua layer gagal untuk: '{$message}'");
                    }
                }
            }

            // Simpan log percakapan dan update metrik
            if ($source !== 'error') {
                $this->firestoreService->addChatLog($sessionId, $message, $response['message'], $source, $userId);
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
            // Log::info("Dialogflow Response - Intent: '{$intentName}', Text: '{$fulfillmentText}', Fallback: {$isFallback}, Confidence: {$confidence}");
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

        $promptAction = $suggestionsOnly
            ? "Tugasmu HANYA memberikan 3 saran pertanyaan lanjutan yang relevan dengan pertanyaan pengguna. JANGAN menjawab pertanyaan pengguna."
            : "Jawab pertanyaan pengguna secara ringkas dan informatif. Setelah menjawab, berikan 3 saran pertanyaan lanjutan yang relevan dan sangat singkat (maksimal 4 kata per saran).";

        $contextInjection = $externalContext
            ? "Gunakan informasi tambahan berikut untuk menjawab pertanyaan pengguna secara akurat:\n---INFO TAMBAHAN---\n{$externalContext}\n-------------------\n"
            : "";

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
}

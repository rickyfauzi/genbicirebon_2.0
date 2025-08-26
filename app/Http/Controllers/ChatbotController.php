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

        // === START LOG PERF ===
        $startOverall = microtime(true);
        $activeUsers = count(session()->all());
        $startMemory = memory_get_usage(true);

        Log::info("[PERF] START request | User Aktif={$activeUsers} | Memory=" . round($startMemory / 1024 / 1024, 2) . "MB");

        $response = [
            'message' => 'Maaf, terjadi kesalahan. Silakan coba lagi nanti.',
            'suggestions' => [],
        ];
        $source = 'error';

        try {
            // Layer 1: Dialogflow
            $startDialogflow = microtime(true);
            $dialogflowResponse = $this->detectIntent($message);
            $durationDialogflow = round((microtime(true) - $startDialogflow) * 1000, 2);
            Log::info("[PERF] Durasi Layer=Dialogflow | Time={$durationDialogflow} ms | User Aktif={$activeUsers}");

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

                // OpenAI (saran saja)
                $startOpenAI = microtime(true);
                $openAIResult = $this->fallbackWithOpenAI($message, null, true);
                $durationOpenAI = round((microtime(true) - $startOpenAI) * 1000, 2);
                Log::info("[PERF] Durasi Layer=OpenAI(Suggestion) | Time={$durationOpenAI} ms | User Aktif={$activeUsers}");

                $response['suggestions'] = $openAIResult['suggestions'] ?? [];
            } else {
                // Layer 2: Firestore
                $startFirestore = microtime(true);
                $firestoreAnswer = $this->firestoreService->searchKnowledgeBase($message);
                $durationFirestore = round((microtime(true) - $startFirestore) * 1000, 2);
                Log::info("[PERF] Durasi Layer=Firestore | Time={$durationFirestore} ms | User Aktif={$activeUsers}");

                if ($firestoreAnswer) {
                    $response['message'] = $firestoreAnswer;
                    $source = 'firestore';

                    // OpenAI (saran saja)
                    $startOpenAI = microtime(true);
                    $openAIResult = $this->fallbackWithOpenAI($message, null, true);
                    $durationOpenAI = round((microtime(true) - $startOpenAI) * 1000, 2);
                    Log::info("[PERF] Durasi Layer=OpenAI(Suggestion) | Time={$durationOpenAI} ms | User Aktif={$activeUsers}");

                    $response['suggestions'] = $openAIResult['suggestions'] ?? [];
                } else {
                    // Layer 3: OpenAI full
                    $contextData = null;
                    if (preg_match('/(kegiatan|acara|event|berita|artikel|terbaru|terkini)/i', $message)) {
                        $contextData = $this->scrapeWebsiteForActivities();
                        Log::info("[PERF] Context scraping aktif untuk keyword kegiatan/berita");
                    }

                    $startOpenAI = microtime(true);
                    $openAIResult = $this->fallbackWithOpenAI($message, $contextData);
                    $durationOpenAI = round((microtime(true) - $startOpenAI) * 1000, 2);
                    Log::info("[PERF] Durasi Layer=OpenAI(Full Answer) | Time={$durationOpenAI} ms | User Aktif={$activeUsers}");

                    if (isset($openAIResult['answer']) && !empty($openAIResult['answer'])) {
                        $response['message'] = $openAIResult['answer'];
                        $response['suggestions'] = $openAIResult['suggestions'] ?? [];
                        $source = 'openai';
                    } else {
                        $source = 'openai_fail';
                    }
                }
            }

            // Simpan log percakapan & metrik
            if ($source !== 'error') {
                $this->firestoreService->addChatLog($sessionId, $message, $response['message'], $source, $userId);
                $this->firestoreService->updateSystemMetrics($source);
            }
        } catch (\Exception $e) {
            Log::error("[PERF] Exception Controller: " . $e->getMessage());
            $this->firestoreService->addErrorLog($e->getMessage(), 'sendMessage', ['file' => $e->getFile(), 'line' => $e->getLine()]);
        }

        $durationOverall = round((microtime(true) - $startOverall) * 1000, 2);
        $endMemory = memory_get_usage(true);
        $peakMemory = memory_get_peak_usage(true);

        Log::info("[PERF] TOTAL Request | Time={$durationOverall} ms | Source={$source} | User Aktif={$activeUsers} | Memory End=" . round($endMemory / 1024 / 1024, 2) . "MB | Peak=" . round($peakMemory / 1024 / 1024, 2) . "MB");

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

            $startDialogflow = microtime(true);
            $sessionsClient = new SessionsClient(['credentials' => $credentialsPath]);
            $session = $sessionsClient->sessionName($projectId, $sessionId);

            $textInput = (new TextInput())->setText($text)->setLanguageCode('id');
            $queryInput = (new QueryInput())->setText($textInput);
            $request = (new DetectIntentRequest())->setSession($session)->setQueryInput($queryInput);

            $response = $sessionsClient->detectIntent($request);
            $queryResult = $response->getQueryResult();

            $durationDialogflow = round((microtime(true) - $startDialogflow) * 1000, 2);
            Log::info("[PERF] Dialogflow detectIntent selesai | Time={$durationDialogflow} ms");

            $sessionsClient->close();

            return [
                'text' => $queryResult->getFulfillmentText(),
                'intent_name' => $queryResult->getIntent() ? $queryResult->getIntent()->getDisplayName() : 'No Intent',
                'is_fallback' => $queryResult->getIntent() ? $queryResult->getIntent()->getIsFallback() : true,
                'confidence' => $queryResult->getIntentDetectionConfidence(),
            ];
        } catch (\Exception $e) {
            Log::error("[PERF] Dialogflow Error: " . $e->getMessage());
            return null;
        }
    }

    private function scrapeWebsiteForActivities(): ?string
    {
        try {
            $url = 'https://genbicirebon.org/kegiatan';
            $startScrape = microtime(true);
            $response = Http::get($url);
            $durationScrape = round((microtime(true) - $startScrape) * 1000, 2);
            Log::info("[PERF] Scraping website selesai | Time={$durationScrape} ms | Status={$response->status()}");

            if (!$response->successful()) {
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

            return !empty($activities)
                ? "Berikut beberapa kegiatan: \n" . implode("\n", $activities)
                : null;
        } catch (\Exception $e) {
            Log::error("[PERF] Scraping Error: " . $e->getMessage());
            return null;
        }
    }

    private function fallbackWithOpenAI(string $text, ?string $externalContext = null, bool $suggestionsOnly = false)
    {
        $apiKey = env('OPENROUTER_API_KEY');
        $startOpenAI = microtime(true);

        try {
            $response = Http::timeout(45)->withHeaders([
                'Authorization' => 'Bearer ' . $apiKey,
                'Content-Type' => 'application/json',
            ])->post('https://openrouter.ai/api/v1/chat/completions', [
                "model" => "openai/gpt-3.5-turbo",
                "messages" => [
                    ["role" => "system", "content" => "context"],
                    ["role" => "user", "content" => $text]
                ],
                "response_format" => ["type" => "json_object"],
                "temperature" => 0.4,
                "max_tokens" => 500,
            ]);

            $durationOpenAI = round((microtime(true) - $startOpenAI) * 1000, 2);
            Log::info("[PERF] OpenAI API selesai | Time={$durationOpenAI} ms | HTTP={$response->status()}");

            if ($response->successful()) {
                $data = json_decode($response->json()['choices'][0]['message']['content'], true);
                return [
                    'answer' => $data['answer'] ?? '',
                    'suggestions' => $data['suggestions'] ?? [],
                ];
            }
            return ['answer' => '', 'suggestions' => []];
        } catch (\Exception $e) {
            Log::error("[PERF] OpenAI Error: " . $e->getMessage());
            return ['answer' => '', 'suggestions' => []];
        }
    }

    public function testDialogflow()
    {
        try {
            $projectId = env('DIALOGFLOW_PROJECT_ID');
            $envPath = env('DIALOGFLOW_CREDENTIALS');
            $cleanPath = str_replace('storage/', '', $envPath);
            $credentialsPath = storage_path($cleanPath);

            $startTest = microtime(true);
            $sessionsClient = new SessionsClient(['credentials' => $credentialsPath]);
            $sessionId = uniqid('test-');
            $session = $sessionsClient->sessionName($projectId, $sessionId);

            $textInput = (new TextInput())->setText('Apa itu GenBI?')->setLanguageCode('id');
            $queryInput = (new QueryInput())->setText($textInput);
            $request = (new DetectIntentRequest())->setSession($session)->setQueryInput($queryInput);
            $response = $sessionsClient->detectIntent($request);

            $durationTest = round((microtime(true) - $startTest) * 1000, 2);
            Log::info("[PERF] Test Dialogflow selesai | Time={$durationTest} ms");

            $sessionsClient->close();

            return response()->json([
                'status' => 'success',
                'message' => 'Koneksi berhasil',
                'duration_ms' => $durationTest,
            ]);
        } catch (\Exception $e) {
            Log::error("[PERF] Test Dialogflow Error: " . $e->getMessage());
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    public function sendMessageDialogflowOnly(Request $request)
    {
        $message = $request->input('message');
        $sessionId = $request->input('session_id', session()->getId());

        $response = ['message' => '', 'source' => 'dialogflow_fail'];

        try {
            $startDialogflow = microtime(true);
            $dialogflowResponse = $this->detectIntent($message);
            $durationDialogflow = round((microtime(true) - $startDialogflow) * 1000, 2);
            Log::info("[PERF] DialogflowOnly selesai | Time={$durationDialogflow} ms");

            if ($dialogflowResponse && !empty($dialogflowResponse['text'])) {
                $response['message'] = $dialogflowResponse['text'];
                $response['source'] = 'dialogflow';
            }
        } catch (\Exception $e) {
            Log::error("[PERF] DialogflowOnly Error: " . $e->getMessage());
        }

        return response()->json($response);
    }
}

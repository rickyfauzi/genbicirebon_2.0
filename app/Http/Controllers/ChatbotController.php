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
            // === LAYER 1: Dialogflow ===
            $dialogflowResponse = $this->detectIntent($message);

            if ($dialogflowResponse && !empty($dialogflowResponse['text']) && !$dialogflowResponse['is_fallback']) {
                $response['message'] = $dialogflowResponse['text'];
                $source = 'dialogflow';

                // Tambahkan rekomendasi pertanyaan dengan OpenAI
                $openAIResult = $this->fallbackWithOpenAI($message, null, true);
                $response['suggestions'] = $openAIResult['suggestions'] ?? [];
            } else {
                // === LAYER 2: Firestore Knowledge Base ===
                $firestoreAnswer = $this->firestoreService->searchKnowledgeBase($message);
                if ($firestoreAnswer) {
                    $response['message'] = $firestoreAnswer;
                    $source = 'firestore';

                    $openAIResult = $this->fallbackWithOpenAI($message, null, true);
                    $response['suggestions'] = $openAIResult['suggestions'] ?? [];
                } else {
                    // === LAYER 3: OpenAI dengan konteks tambahan (opsional scraping) ===
                    $contextData = null;

                    // Jika ada kata kunci event/kegiatan → scrape website
                    if (preg_match('/(kegiatan|acara|event|berita|artikel|terbaru|terkini)/i', $message)) {
                        Log::info("Kata kunci kegiatan/berita terdeteksi → ambil data dari website.");
                        $contextData = $this->scrapeWebsiteForActivities();
                    }

                    $openAIResult = $this->fallbackWithOpenAI($message, $contextData);

                    if (isset($openAIResult['answer']) && !empty($openAIResult['answer'])) {
                        $response['message'] = $openAIResult['answer'];
                        $response['suggestions'] = $openAIResult['suggestions'] ?? [];
                        $source = 'openai';

                        // Simpan hasil OpenAI ke Firestore agar lebih cepat di pertanyaan berikutnya
                        if ($contextData === null) {
                            $this->firestoreService->addKnowledgeBase($message, $openAIResult['answer']);
                            Log::info("Knowledge base baru ditambahkan dari fallback OpenAI: '{$message}'");
                        }
                    } else {
                        $response['message'] = "Maaf, saya tidak dapat menemukan jawaban untuk pertanyaan itu saat ini.";
                        $source = 'openai_fail';
                    }
                }
            }

            // Simpan log percakapan ke Firestore
            if ($source !== 'error') {
                $this->firestoreService->addChatLog($sessionId, $message, $response['message'], $source, $userId);
            }
        } catch (\Exception $e) {
            Log::error('Chatbot Controller Error: ' . $e->getMessage() . ' in ' . $e->getFile() . ' line ' . $e->getLine());
        }

        return response()->json($response);
    }

    /**
     * Scrape kegiatan terbaru dari website resmi GenBI Cirebon
     */
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

                return "- {$title} (dipublikasikan {$date})";
            });

            if (empty($activities)) {
                return "Saat ini tidak ada informasi kegiatan terbaru dari website.";
            }

            return "Berikut kegiatan/berita terbaru GenBI Cirebon:\n" . implode("\n", $activities);
        } catch (\Exception $e) {
            Log::error('Scraping Error: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Fallback ke OpenAI API
     */
    private function fallbackWithOpenAI(string $text, ?string $externalContext = null, bool $suggestionsOnly = false)
    {
        $apiKey = env('OPENROUTER_API_KEY');
        $siteContext = "Kamu adalah 'GenBI Assistant', asisten AI ramah dan informatif tentang GenBI Cirebon (Generasi Baru Indonesia Cirebon). Website resmi: genbicirebon.org.";

        $promptAction = $suggestionsOnly
            ? "Hanya buat 3 saran pertanyaan lanjutan relevan. Jangan jawab pertanyaan utama."
            : "Jawab pertanyaan secara ringkas. Tambahkan 3 saran pertanyaan lanjutan (maks 4 kata).";

        $contextInjection = $externalContext
            ? "Gunakan info tambahan berikut:\n---INFO---\n{$externalContext}\n---END---\n"
            : "";

        $systemPrompt = "{$siteContext}\n{$contextInjection}\n{$promptAction}\nFormat JSON valid:\n{\"answer\": \"...\", \"suggestions\": [\"...\", \"...\", \"...\"]}";

        try {
            $response = Http::timeout(45)->withHeaders([
                'Authorization' => 'Bearer ' . $apiKey,
                'Content-Type' => 'application/json',
                'HTTP-Referer'  => request()->getSchemeAndHttpHost(),
                'X-Title'       => 'GenBI Cirebon Chatbot',
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
                    'answer' => $data['answer'] ?? ($suggestionsOnly ? '' : 'Format jawaban gagal.'),
                    'suggestions' => $data['suggestions'] ?? [],
                ];
            }

            Log::error('OpenAI API Error: ' . $response->body());
            return ['answer' => 'Maaf, terjadi kendala teknis.', 'suggestions' => []];
        } catch (\Exception $e) {
            Log::error('OpenAI Exception: ' . $e->getMessage());
            return ['answer' => 'Maaf, AI sedang bermasalah.', 'suggestions' => []];
        }
    }

    /**
     * Detect intent dari Dialogflow
     */
    private function detectIntent(string $text)
    {
        try {
            $projectId = env('DIALOGFLOW_PROJECT_ID');
            $sessionId = session()->getId();
            $credentialsPath = storage_path(env('FIREBASE_CREDENTIALS'));

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

            return [
                'text' => $queryResult->getFulfillmentText(),
                'is_fallback' => $queryResult->getIntent()->getIsFallback(),
            ];
        } catch (\Exception $e) {
            Log::error("Dialogflow Error: " . $e->getMessage());
            return null;
        }
    }
}

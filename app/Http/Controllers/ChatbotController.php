<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Google\Cloud\Dialogflow\V2\Client\SessionsClient;
use Google\Cloud\Dialogflow\V2\QueryInput;
use Google\Cloud\Dialogflow\V2\TextInput;
use Google\Cloud\Dialogflow\V2\DetectIntentRequest;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Services\FirestoreService; // Pastikan Anda punya service ini
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
        $message = strtolower(trim($request->input('message'))); // Normalisasi input pengguna
        $sessionId = $request->input('session_id', session()->getId());
        $userId = auth()->id(); // Opsional, jika Anda punya sistem login

        $responseMessage = 'Maaf, terjadi kesalahan. Silakan coba lagi nanti.';
        $suggestions = [];
        $source = 'error';

        try {
            // ==================================================================
            // LAPISAN 1: DIALOGFLOW (Untuk Intent & Sapaan Dasar)
            // ==================================================================
            Log::info("Mencoba Dialogflow untuk: '{$message}'");
            $dialogflowResponse = $this->detectIntent($message);

            if ($dialogflowResponse && !$dialogflowResponse['is_fallback'] && !empty($dialogflowResponse['text'])) {
                Log::info("Dialogflow menemukan intent yang cocok.");
                $responseMessage = $dialogflowResponse['text'];
                $source = 'dialogflow';
                // Tetap panggil OpenAI hanya untuk mendapatkan sugesti cerdas
                $openAIResult = $this->fallbackWithOpenAI($message, null, true);
                $suggestions = $openAIResult['suggestions'] ?? [];
            } else {
                Log::info("Dialogflow fallback, melanjutkan ke Firestore KB.");
                // ==================================================================
                // LAPISAN 2: FIRESTORE KNOWLEDGE BASE (Pencarian Cerdas)
                // ==================================================================
                $firestoreAnswer = $this->firestoreService->searchKnowledgeBase($message);

                if ($firestoreAnswer) {
                    Log::info("Jawaban ditemukan di Firestore Knowledge Base.");
                    $responseMessage = $firestoreAnswer;
                    $source = 'firestore_kb';
                    // Tetap panggil OpenAI hanya untuk mendapatkan sugesti cerdas
                    $openAIResult = $this->fallbackWithOpenAI($message, null, true);
                    $suggestions = $openAIResult['suggestions'] ?? [];
                } else {
                    Log::info("Tidak ada jawaban di Firestore KB, melanjutkan ke OpenAI Fallback.");
                    // ==================================================================
                    // LAPISAN 3: OPENAI (Sebagai Fallback dan Sumber Pengetahuan Baru)
                    // ==================================================================
                    $contextData = null;
                    // Cek jika pertanyaan spesifik tentang berita/kegiatan
                    if (preg_match('/(kegiatan|acara|event|berita|artikel|terbaru|terkini)/i', $message)) {
                        Log::info("Mendeteksi kata kunci real-time. Melakukan web scraping...");
                        $contextData = $this->scrapeWebsiteForActivities();
                    }

                    $openAIResult = $this->fallbackWithOpenAI($message, $contextData);

                    if (isset($openAIResult['answer']) && !empty($openAIResult['answer'])) {
                        Log::info("OpenAI berhasil menghasilkan jawaban.");
                        $responseMessage = $openAIResult['answer'];
                        $suggestions = $openAIResult['suggestions'] ?? [];
                        $source = 'openai_fallback';

                        // --- MEKANISME BELAJAR (LEARNING LOOP) ---
                        // Simpan jawaban baru ke Knowledge Base JIKA bukan dari hasil scraping (bukan data real-time)
                        if ($contextData === null) {
                            Log::info("Menyimpan pengetahuan baru dari OpenAI ke Firestore KB.");
                            $this->firestoreService->addKnowledgeBase($message, $openAIResult['answer']);
                        }
                    } else {
                        Log::info("OpenAI gagal memberikan jawaban yang valid.");
                        $responseMessage = "Maaf, saya tidak dapat menemukan jawaban untuk pertanyaan itu saat ini.";
                        $source = 'openai_fail';
                    }
                }
            }

            // Simpan log percakapan
            $this->firestoreService->addChatLog($sessionId, $request->input('message'), $responseMessage, $source, $userId);
        } catch (\Exception $e) {
            Log::error('Chatbot Controller Error: ' . $e->getMessage() . ' in ' . $e->getFile() . ' on line ' . $e->getLine());
            // Simpan log error
            $this->firestoreService->addErrorLog($e->getMessage(), $request->input('message'));
        }

        return response()->json([
            'message' => $responseMessage,
            'suggestions' => $suggestions,
            'source' => $source // Opsional: untuk debugging di frontend
        ]);
    }

    // Fungsi detectIntent, scrapeWebsiteForActivities, dan fallbackWithOpenAI tetap sama
    // ... (salin fungsi-fungsi tersebut dari kode lama Anda di sini) ...
    private function scrapeWebsiteForActivities(): ?string
    {
        try {
            // Target URL halaman kegiatan/berita. Pastikan URL ini benar.
            $url = 'https://genbicirebon.org/kegiatan';
            $response = Http::get($url);

            if (!$response->successful()) {
                Log::warning("Gagal mengakses {$url}. Status: " . $response->status());
                return null;
            }

            $crawler = new Crawler($response->body());

            // Selector CSS ini harus disesuaikan dengan struktur HTML website Anda.
            // Inspeksi halaman web untuk menemukan selector yang tepat.
            // Contoh ini berasumsi setiap item berita ada di dalam div dengan class '.blog-item'
            $activities = $crawler->filter('.blog-item')->slice(0, 5)->each(function (Crawler $node) {
                // Selector untuk judul dan tanggal juga harus disesuaikan.
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

        $promptAction = "";
        if ($suggestionsOnly) {
            $promptAction = "Tugasmu HANYA memberikan 3 saran pertanyaan lanjutan yang relevan dengan pertanyaan pengguna. JANGAN menjawab pertanyaan pengguna.";
        } else {
            $promptAction = "Jawab pertanyaan pengguna secara ringkas dan informatif. Setelah menjawab, berikan 3 saran pertanyaan lanjutan yang relevan dan sangat singkat (maksimal 4 kata per saran).";
        }

        $contextInjection = "";
        if ($externalContext) {
            $contextInjection = "Gunakan informasi tambahan berikut untuk menjawab pertanyaan pengguna secara akurat:\n---INFO TAMBAHAN---\n{$externalContext}\n-------------------\n";
        }

        $systemPrompt = "{$siteContext} {$contextInjection} {$promptAction} Format respons HANYA dalam bentuk JSON valid seperti ini: {\"answer\": \"Jawabanmu di sini.\", \"suggestions\": [\"Saran 1\", \"Saran 2\", \"Saran 3\"]}. Jika hanya diminta saran, isi field 'answer' dengan string kosong.";

        try {
            $response = Http::timeout(45)->withHeaders([
                'Authorization' => 'Bearer ' . $apiKey,
                'Content-Type' => 'application/json',
                'HTTP-Referer'  => request()->getSchemeAndHttpHost(),
                'X-Title'       => 'Genbi Cirebon Chatbot',
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

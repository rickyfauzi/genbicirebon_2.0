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
                $botAnswer = $dialogflowResponse['text'];
                $response['message'] = $botAnswer;
                $source = 'dialogflow';
                Log::info("Dialogflow berhasil memberikan jawaban: '{$botAnswer}'");

                // Dapatkan sugesti cerdas dari OpenAI dengan KONTEKS jawaban Dialogflow
                $startOpenAI = microtime(true);
                // PERUBAHAN KUNCI: Kirim jawaban dari Dialogflow ($botAnswer) sebagai konteks
                $openAIResult = $this->fallbackWithOpenAI($message, $botAnswer, null, false);
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

                    // Dapatkan sugesti cerdas dari OpenAI dengan KONTEKS jawaban Firestore
                    $startOpenAI = microtime(true);
                    // PERUBAHAN KUNCI: Kirim jawaban dari Firestore ($firestoreAnswer) sebagai konteks
                    $openAIResult = $this->fallbackWithOpenAI($message, $firestoreAnswer, null, false);
                    $durationOpenAI = round((microtime(true) - $startOpenAI) * 1000, 2);
                    Log::info("DURASI OpenAI (Sugesti Saja): {$durationOpenAI} ms | User Aktif: {$activeUsers}");

                    $response['suggestions'] = $openAIResult['suggestions'] ?? [];
                } else {
                    Log::info("Layer 3: Fallback ke OpenAI untuk jawaban lengkap");

                    // Layer 3: Fallback ke OpenAI
                    $contextData = null;
                    if (preg_match('/(kegiatan|acara|event|berita|artikel|terbaru|terkini)/i', $message)) {
                        Log::info("Mendeteksi kata kunci kegiatan/berita. Mengambil data dari website...");
                        $contextData = $this->scrapeWebsiteForActivities();
                    }

                    $startOpenAI = microtime(true);
                    // Panggil OpenAI untuk memberikan JAWABAN dan SARAN
                    $openAIResult = $this->fallbackWithOpenAI($message, null, $contextData, true);
                    $durationOpenAI = round((microtime(true) - $startOpenAI) * 1000, 2);
                    Log::info("DURASI OpenAI (Jawaban Lengkap): {$durationOpenAI} ms | User Aktif: {$activeUsers}");

                    if (isset($openAIResult['answer']) && !empty(trim($openAIResult['answer']))) {
                        $response['message'] = $openAIResult['answer'];
                        $response['suggestions'] = $openAIResult['suggestions'] ?? [];
                        $source = 'openai';
                        Log::info("OpenAI berhasil memberikan jawaban");

                        // Learning Loop: Simpan pengetahuan baru ke Firestore jika tidak ada konteks eksternal
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

            $activities = $crawler->filter('.card-kegiatan')->slice(0, 5)->each(function (Crawler $node) {
                $titleNode = $node->filter('.kegiatan-title a');
                $title = $titleNode->count() ? $titleNode->text('Judul tidak ditemukan') : 'Judul tidak ditemukan';

                $dateNode = $node->filter('.date')->first();
                $date = $dateNode->count() ? $dateNode->text('Tanggal tidak ditemukan') : 'Tanggal tidak ditemukan';

                return "- {$title} (sekitar {$date})";
            });

            if (empty($activities)) {
                Log::info('Tidak ada kegiatan ditemukan di halaman kegiatan.');
                return "Saat ini tidak ada informasi kegiatan terbaru yang bisa ditampilkan dari website.";
            }

            return "Berikut adalah beberapa kegiatan terbaru dari website genbicirebon.org:\n" . implode("\n", $activities);
        } catch (\Exception $e) {
            Log::error('Scraping Error (kegiatan): ' . $e->getMessage());
            return null;
        }
    }

    private function scrapeWebsiteForBlog(): ?string
    {
        try {
            $url = 'https://genbicirebon.org/blog';
            $response = Http::get($url);

            if (!$response->successful()) {
                Log::warning("Gagal mengakses {$url}. Status: " . $response->status());
                return null;
            }

            $crawler = new Crawler($response->body());

            $blogs = $crawler->filter('.ud-single-blog')->slice(0, 5)->each(function (Crawler $node) {
                $titleNode = $node->filter('.ud-blog-title a');
                $title = $titleNode->count() ? $titleNode->text('Judul tidak ditemukan') : 'Judul tidak ditemukan';

                $dateNode = $node->filter('.ud-blog-date')->first();
                $date = $dateNode->count() ? $dateNode->text('Tanggal tidak ditemukan') : 'Tanggal tidak ditemukan';

                return "- {$title} (dipublikasikan sekitar {$date})";
            });

            if (empty($blogs)) {
                Log::info('Tidak ada artikel blog ditemukan.');
                return "Saat ini tidak ada berita terbaru yang bisa ditampilkan dari website.";
            }

            return "Berikut adalah beberapa berita terbaru dari website genbicirebon.org:\n" . implode("\n", $blogs);
        } catch (\Exception $e) {
            Log::error('Scraping Error (blog): ' . $e->getMessage());
            return null;
        }
    }


    /**
     * Berinteraksi dengan OpenAI untuk mendapatkan jawaban dan/atau saran.
     *
     * @param string $userQuery Pertanyaan dari pengguna.
     * @param string|null $botAnswer Jawaban yang sudah ada dari Dialogflow/Firestore untuk dijadikan konteks saran.
     * @param string|null $externalContext Konteks tambahan dari scraping website.
     * @param bool $generateFullAnswer Jika true, generate jawaban lengkap + saran. Jika false, HANYA generate saran.
     * @return array ['answer' => string, 'suggestions' => array]
     */
    private function fallbackWithOpenAI(string $userQuery, ?string $botAnswer = null, ?string $externalContext = null, bool $generateFullAnswer = true)
    {
        $apiKey = env('OPENROUTER_API_KEY');

        // Periksa apakah pertanyaan relevan dengan topik GenBI
        if (!$this->isRelevantToGenBI($userQuery)) {
            return [
                'answer' => 'Maaf, saya hanya dapat membantu menjawab pertanyaan seputar GenBI (Generasi Baru Indonesia), GenBI Cirebon, beasiswa Bank Indonesia, dan topik terkait Bank Indonesia. Silakan ajukan pertanyaan yang berkaitan dengan topik tersebut.',
                'suggestions' => [
                    'Apa itu GenBI?',
                    'Beasiswa Bank Indonesia',
                    'Kegiatan GenBI Cirebon'
                ]
            ];
        }

        $siteContext = "Kamu adalah 'GenBI Assistant', asisten AI khusus yang hanya menjawab pertanyaan tentang:
1. GenBI (Generasi Baru Indonesia) - komunitas penerima beasiswa Bank Indonesia
2. GenBI Cirebon - chapter lokal di Cirebon (website: genbicirebon.org)
3. Beasiswa Bank Indonesia - program beasiswa dari BI
4. Bank Indonesia - kebijakan, program, dan informasi umum BI

ATURAN PENTING:
- HANYA jawab pertanyaan yang berkaitan dengan topik di atas
- Jika pertanyaan di luar topik, tolak dengan sopan dan arahkan ke topik yang relevan
- Jawab dalam bahasa Indonesia dengan nada ramah dan informatif
- Berikan informasi yang akurat dan bermanfaat";

        $conversationContext = "";
        $promptAction = "";

        if ($generateFullAnswer) {
            $promptAction = "Analisis apakah pertanyaan pengguna relevan dengan topik GenBI, GenBI Cirebon, beasiswa Bank Indonesia, atau Bank Indonesia. 
        
        Jika RELEVAN: Jawab pertanyaan secara ringkas dan informatif, lalu berikan 3 saran pertanyaan lanjutan yang relevan (maksimal 5 kata per saran).
        
        Jika TIDAK RELEVAN: Tolak dengan sopan dan jelaskan bahwa Anda hanya membahas topik GenBI, kemudian berikan 3 saran pertanyaan yang relevan dengan GenBI.";

            $userInput = $userQuery;
        } else {
            $conversationContext = "Konteks percakapan saat ini:\n[PENGGUNA]: {$userQuery}\n[ASISTEN]: {$botAnswer}\n\n";
            $promptAction = "Berdasarkan konteks percakapan di atas, berikan 3 saran pertanyaan lanjutan yang relevan dengan topik GenBI/beasiswa BI. JANGAN menjawab lagi pertanyaan pengguna.";
            $userInput = "Berikan saran pertanyaan lanjutan seputar GenBI.";
        }

        $contextInjection = $externalContext
            ? "Gunakan informasi tambahan berikut untuk menjawab pertanyaan pengguna secara akurat:\n---INFO GENBI/BI---\n{$externalContext}\n-------------------\n"
            : "";

        $systemPrompt = "{$siteContext}\n\n{$conversationContext}{$contextInjection}\n\n{$promptAction}\n\nFormat respons HANYA dalam bentuk JSON valid seperti ini: {\"answer\": \"Jawabanmu di sini.\", \"suggestions\": [\"Saran 1\", \"Saran 2\", \"Saran 3\"]}. 

    Jika hanya diminta saran, isi field 'answer' dengan string kosong. 
    Pastikan semua saran pertanyaan berkaitan dengan GenBI, beasiswa BI, atau Bank Indonesia.";

        try {
            $response = Http::timeout(45)->withHeaders([
                'Authorization' => 'Bearer ' . $apiKey,
                'Content-Type' => 'application/json',
                'HTTP-Referer' => request()->getSchemeAndHttpHost(),
                'X-Title' => 'GenBI Cirebon Smart Assistant',
            ])->post('https://openrouter.ai/api/v1/chat/completions', [
                "model" => "openai/gpt-3.5-turbo",
                "messages" => [
                    ["role" => "system", "content" => $systemPrompt],
                    ["role" => "user", "content" => $userInput]
                ],
                "response_format" => ["type" => "json_object"],
                "temperature" => 0.3, // Lebih rendah untuk konsistensi
                "max_tokens" => 600, // Sedikit lebih banyak untuk penjelasan penolakan
            ]);

            if ($response->successful()) {
                $content = $response->json()['choices'][0]['message']['content'];
                $data = json_decode($content, true);

                // Validasi JSON dan struktur data
                if (json_last_error() !== JSON_ERROR_NONE) {
                    Log::error('OpenAI Fallback JSON Decode Error: ' . json_last_error_msg() . ' | Content: ' . $content);
                    return [
                        'answer' => 'Maaf, terjadi kesalahan format respons. Silakan coba lagi dengan pertanyaan seputar GenBI.',
                        'suggestions' => [
                            'Apa itu GenBI?',
                            'Cara daftar beasiswa BI',
                            'Program GenBI Cirebon'
                        ]
                    ];
                }

                // Pastikan suggestions berisi array dan relevan
                $suggestions = $data['suggestions'] ?? [];
                if (empty($suggestions) || !is_array($suggestions)) {
                    $suggestions = [
                        'Kegiatan GenBI Cirebon',
                        'Syarat beasiswa BI',
                        'Manfaat bergabung GenBI'
                    ];
                }

                return [
                    'answer' => $data['answer'] ?? ($generateFullAnswer ? 'Mohon ajukan pertanyaan seputar GenBI atau beasiswa Bank Indonesia.' : ''),
                    'suggestions' => array_slice($suggestions, 0, 3), // Maksimal 3 saran
                ];
            }

            Log::error('OpenAI Fallback HTTP Error: ' . $response->status() . ' | ' . $response->body());
            return [
                'answer' => 'Maaf, asisten sedang mengalami gangguan. Silakan tanya seputar GenBI atau beasiswa BI.',
                'suggestions' => [
                    'Apa itu GenBI?',
                    'Beasiswa Bank Indonesia',
                    'Kontak GenBI Cirebon'
                ]
            ];
        } catch (\Exception $e) {
            Log::error('OpenAI Fallback Exception: ' . $e->getMessage());
            return [
                'answer' => 'Maaf, koneksi bermasalah. Silakan coba lagi dengan pertanyaan tentang GenBI.',
                'suggestions' => [
                    'Program GenBI',
                    'Beasiswa BI 2024',
                    'GenBI Cirebon info'
                ]
            ];
        }
    }


    private function isRelevantToGenBI(string $query): bool
    {
        $query = strtolower(trim($query));

        // 1. Small talk keywords → selalu dianggap relevan
        $smallTalk = [
            'halo',
            'hai',
            'hi',
            'hello',
            'assalamualaikum',
            'kamu siapa',
            'siapa kamu',
            'kenalan dong',
            'terima kasih',
            'thanks',
            'makasih',
            'selamat pagi',
            'selamat siang',
            'selamat malam'
        ];
        foreach ($smallTalk as $talk) {
            if (strpos($query, $talk) !== false) {
                return true; // jangan ditolak
            }
        }

        // 2. Kata kunci relevan dengan GenBI/BI/beasiswa
        $relevantKeywords = [
            // GenBI related
            'genbi',
            'generasi baru indonesia',
            'gen bi',
            'gbi',

            // Bank Indonesia related
            'bank indonesia',
            'bi',
            'bank sentral',
            'kebijakan moneter',

            // Beasiswa related
            'beasiswa',
            'scholarship',
            'bantuan pendidikan',
            'bantuan kuliah',
            'penerima beasiswa',
            'program beasiswa',

            // GenBI Cirebon specific
            'cirebon',
            'genbicirebon.org',
            'genbi cirebon',

            // Activities & Programs
            'kegiatan genbi',
            'program genbi',
            'komunitas genbi',
            'anggota genbi',
            'alumni genbi',
            'pendaftaran genbi'
        ];
        foreach ($relevantKeywords as $keyword) {
            if (strpos($query, $keyword) !== false) {
                return true;
            }
        }

        // 3. Kata kunci tidak relevan (agar lebih tegas)
        $irrelevantKeywords = [
            'cuaca',
            'weather',
            'resep',
            'recipe',
            'olahraga',
            'sport',
            'film',
            'movie',
            'musik',
            'music',
            'game',
            'permainan',
            'politik',
            'gosip',
            'entertainment',
            'hiburan',
            'teknologi umum',
            'programming',
            'coding',
            'travel',
            'wisata'
        ];
        foreach ($irrelevantKeywords as $irrelevant) {
            if (strpos($query, $irrelevant) !== false) {
                return false;
            }
        }

        // 4. Default: anggap TIDAK relevan
        return false;
    }


    public function testDialogflow()
    {
        // ... (Fungsi ini tidak perlu diubah, biarkan seperti semula)
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
        // ... (Fungsi ini tidak perlu diubah, biarkan seperti semula)
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
        // ... (Fungsi ini tidak perlu diubah, biarkan seperti semula)
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

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

        // --- PROMPT ENGINEERING V3 (THE SOCIALLY-AWARE EXPERT) ---

        // 1. Identitas & Persona
        $systemIdentity = "Anda adalah 'GenBI Assistant', asisten AI yang ramah, komunikatif, dan seorang ahli tentang Generasi Baru Indonesia (GenBI).";

        // 2. Pembagian Kemampuan yang Jelas
        $knowledgeDomain = "Anda memiliki dua tingkat kemampuan:\n" .
            "1. Keahlian Utama: Pengetahuan mendalam tentang GenBI (umum dan Cirebon), Beasiswa Bank Indonesia, dan Bank Indonesia. Website rujukan: genbicirebon.org. Ini adalah fokus utama Anda.\n" .
            "2. Kemampuan Percakapan Sosial: Anda BISA dan HARUS merespons obrolan ringan dan pertanyaan personal (misal: 'halo', 'terima kasih', 'kamu lagi apa?', 'kamu robot?').";

        // 3. Aturan Interaksi Baru: The "Pivot Rule"
        $interactionRules = "ATURAN INTERAKSI:\n" .
            // PERUBAHAN KUNCI: Memperkenalkan "The Pivot Rule" untuk obrolan sosial
            "- Untuk pertanyaan sosial atau obrolan ringan: Jawablah dengan singkat, ramah, dan dalam persona Anda sebagai AI, LALU SEGERA ajukan pertanyaan atau pernyataan untuk mengembalikan percakapan ke topik utama (GenBI).\n" .
            "- Untuk pertanyaan yang membutuhkan pengetahuan faktual di luar Keahlian Utama Anda (misal: politik, sains, sejarah, gosip): Anda WAJIB menolak dengan sopan menggunakan kalimat persis ini: 'Maaf, saya hanya diprogram untuk menjawab pertanyaan seputar GenBI dan Beasiswa Bank Indonesia. Ada lagi yang bisa saya bantu mengenai GenBI?'";

        // 4. Tone atau Nada Bicara
        $tone = "Gunakan nada bicara yang bersahabat dan profesional. Buat pengguna merasa nyaman untuk bertanya apa saja seputar GenBI.";

        // 5. Contoh yang Diperbarui untuk Melatih Perilaku Baru
        $examples = "CONTOH INTERAKSI:\n" .
            "Contoh 1 (Topik Utama):\n" .
            "User: 'Apa saja syarat beasiswa Bank Indonesia?'\n" .
            "Expected JSON: {\"answer\": \"Tentu! Syarat umum untuk mendaftar Beasiswa Bank Indonesia biasanya meliputi status sebagai mahasiswa aktif, IPK minimal, serta tidak sedang menerima beasiswa lain. Syarat detail bisa berbeda setiap tahun, jadi pastikan cek pengumuman resmi ya!\", \"suggestions\": [\"Kapan beasiswa BI dibuka?\", \"Apa keuntungan jadi anggota GenBI?\", \"Kegiatan GenBI Cirebon apa saja?\"]}\n\n" .
            "Contoh 2 (Faktual Non-Relevan):\n" .
            "User: 'Siapa presiden Indonesia sekarang?'\n" .
            "Expected JSON: {\"answer\": \"Maaf, saya hanya diprogram untuk menjawab pertanyaan seputar GenBI dan Beasiswa Bank Indonesia. Ada lagi yang bisa saya bantu mengenai GenBI?\", \"suggestions\": [\"Apa itu GenBI?\", \"Bagaimana cara daftar GenBI?\", \"Apa saja kegiatan GenBI?\"]}\n\n" .
            // PERUBAHAN KUNCI: Menambahkan contoh untuk Social Chat dengan teknik Pivot
            "Contoh 3 (Obrolan Sosial):\n" .
            "User: 'Kamu lagi sibuk apa?'\n" .
            "Expected JSON: {\"answer\": \"Sebagai asisten AI, saya selalu siap sedia 24/7 untuk membantu! Ngomong-ngomong soal kesibukan, GenBI Cirebon juga punya banyak kegiatan seru lho. Apakah Anda tertarik untuk tahu lebih lanjut?\", \"suggestions\": [\"Apa saja kegiatan GenBI?\", \"Program kerja GenBI Cirebon\", \"Ceritakan tentang GenBI\"]}";


        // 6. Konteks Dinamis dan Tugas (Struktur ini tidak perlu diubah)
        $conversationContext = "";
        $promptAction = "";
        $userInput = $userQuery;
        if ($generateFullAnswer) {
            $contextInjection = $externalContext ? "Gunakan informasi tambahan berikut dari website untuk menjawab:\n---INFO TAMBAHAN---\n{$externalContext}\n-------------------\n" : "";
            $promptAction = "TUGAS ANDA SEKARANG: Berdasarkan pertanyaan pengguna, berikan jawaban yang informatif dan relevan sesuai ATURAN INTERAKSI di atas. Setelah itu, berikan 3 saran pertanyaan lanjutan yang relevan.";
        } else {
            $contextInjection = "";
            $conversationContext = "Konteks percakapan saat ini:\n[PENGGUNA]: {$userQuery}\n[ASISTEN]: {$botAnswer}\n\n";
            $promptAction = "TUGAS ANDA SEKARANG: Berdasarkan konteks percakapan di atas, tugasmu HANYA memberikan 3 saran pertanyaan lanjutan yang paling relevan. JANGAN menjawab lagi pertanyaan pengguna.";
            $userInput = "Berikan 3 saran pertanyaan lanjutan yang relevan.";
        }

        // 7. Format Output yang Wajib Diikuti
        $outputFormat = "Format output WAJIB dalam bentuk JSON yang valid seperti ini: {\"answer\": \"(jawaban Anda di sini)\", \"suggestions\": [\"saran 1\", \"saran 2\", \"saran 3\"]}. Jika tugas Anda hanya memberikan saran, isi field 'answer' dengan string kosong \"\".";

        // Gabungkan semua bagian prompt
        $systemPrompt = implode("\n\n", [
            $systemIdentity,
            $knowledgeDomain,
            $interactionRules,
            $tone,
            $examples,
            $contextInjection,
            $conversationContext,
            $promptAction,
            $outputFormat
        ]);

        try {
            // ... (sisa dari blok try-catch tetap sama)
            $response = Http::timeout(45)->withHeaders([
                'Authorization' => 'Bearer ' . $apiKey,
                'Content-Type' => 'application/json',
                'HTTP-Referer' => request()->getSchemeAndHttpHost(),
                'X-Title' => 'Genbi Cirebon Chatbot',
            ])->post('https://openrouter.ai/api/v1/chat/completions', [
                "model" => "openai/gpt-3.5-turbo",
                "messages" => [
                    ["role" => "system", "content" => $systemPrompt],
                    ["role" => "user", "content" => $userInput]
                ],
                "response_format" => ["type" => "json_object"],
                "temperature" => 0.5, // Sedikit menaikkan suhu untuk respons sosial yang lebih natural
                "max_tokens" => 500,
            ]);

            if ($response->successful()) {
                $content = $response->json()['choices'][0]['message']['content'];
                $data = json_decode($content, true);

                if (json_last_error() !== JSON_ERROR_NONE) {
                    Log::error('OpenAI Fallback JSON Decode Error: ' . json_last_error_msg() . ' | Content: ' . $content);
                    return ['answer' => 'Maaf, terjadi sedikit kesalahan format. Silakan coba lagi.', 'suggestions' => []];
                }

                return [
                    'answer' => $data['answer'] ?? ($generateFullAnswer ? 'Gagal memformat jawaban.' : ''),
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

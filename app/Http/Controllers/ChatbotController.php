<?php

namespace App\Http\Controllers;

use Google\Cloud\Dialogflow\V2\Client\SessionsClient;
use Illuminate\Http\Request;
use Google\Cloud\Dialogflow\V2\QueryInput;
use Google\Cloud\Dialogflow\V2\TextInput;
use Google\Cloud\Dialogflow\V2\DetectIntentRequest;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;

class ChatbotController extends Controller
{
    public function index()
    {
        return view('chatbot');
    }

    public function sendMessage(Request $request)
    {
        $request->validate([
            'message' => 'required|string',
        ]);

        try {
            $message = $request->input('message');
            $response = null;

            // Coba Dialogflow terlebih dahulu (karena sudah ada definisi GenBI)
            try {
                if (class_exists(SessionsClient::class)) {
                    $response = $this->detectIntent($message);

                    // Jika Dialogflow tidak memberikan jawaban atau jawaban generik
                    if ($this->shouldUseFallback($response)) {
                        Log::info("Menggunakan AI fallback untuk: " . $message);
                        $response = $this->smartFallbackAI($message);
                    }
                } else {
                    throw new \Exception('Dialogflow tidak tersedia');
                }
            } catch (\Exception $e) {
                Log::warning("Dialogflow error: " . $e->getMessage());
                $response = $this->smartFallbackAI($message);
            }

            return response()->json(['message' => $response]);
        } catch (\Exception $e) {
            Log::error("ChatBot Exception: " . $e->getMessage());
            return response()->json([
                'message' => 'Maaf, terjadi kesalahan. Silakan coba lagi nanti.'
            ], 500);
        }
    }

    public function detectIntent(string $text)
    {
        $projectId = 'websitebot-etqi';
        $sessionId = session()->getId();
        $credentialsPath = storage_path('app/google/dialogflow-credentials.json');

        $sessionsClient = new SessionsClient([
            'credentials' => $credentialsPath
        ]);

        $session = $sessionsClient->sessionName($projectId, $sessionId);

        $textInput = new TextInput();
        $textInput->setText($text);
        $textInput->setLanguageCode('id');

        $queryInput = new QueryInput();
        $queryInput->setText($textInput);

        $detectIntentRequest = new DetectIntentRequest();
        $detectIntentRequest->setSession($session);
        $detectIntentRequest->setQueryInput($queryInput);

        $response = $sessionsClient->detectIntent($detectIntentRequest);
        $queryResult = $response->getQueryResult();

        // Log confidence untuk monitoring
        $confidence = $queryResult->getIntentDetectionConfidence();
        Log::info("Dialogflow confidence: {$confidence} untuk '{$text}'");

        return $queryResult->getFulfillmentText();
    }

    private function shouldUseFallback(?string $response): bool
    {
        // Gunakan fallback jika:
        // 1. Response kosong
        // 2. Response terlalu pendek
        // 3. Response mengandung kata-kata generik/default

        if (empty($response) || strlen(trim($response)) < 5) {
            return true;
        }

        $genericResponses = [
            'maaf saya tidak mengerti',
            'bisa diulang',
            'tidak paham',
            'coba lagi',
            'default fallback',
            'saya tidak tahu'
        ];

        $response = strtolower($response);
        foreach ($genericResponses as $generic) {
            if (strpos($response, $generic) !== false) {
                return true;
            }
        }

        return false;
    }

    private function smartFallbackAI(string $text): string
    {
        // Cek scope terlebih dahulu
        if (!$this->isInScope($text)) {
            return $this->getOutOfScopeResponse();
        }

        return $this->callContextualAI($text);
    }

    private function isInScope(string $text): bool
    {
        $scopeKeywords = [
            // GenBI related
            'genbi',
            'gen bi',
            'generasi baru indonesia',
            'komunitas genbi',
            'chapter',
            'cabang',
            'wilayah',
            'daerah',
            'kota',
            'provinsi',

            // Beasiswa related
            'beasiswa',
            'scholarship',
            'bantuan pendidikan',
            'beasiswa bi',
            'beasiswa bank indonesia',
            'syarat beasiswa',
            'cara daftar beasiswa',
            'penerima beasiswa',
            'alumni beasiswa',

            // Bank Indonesia related  
            'bank indonesia',
            'bi',
            'central bank',
            'kebijakan moneter',
            'suku bunga',
            'inflasi',
            'rupiah',
            'ekonomi indonesia',

            // Pendidikan related
            'mahasiswa',
            'kuliah',
            'kampus',
            'universitas',
            'ipk',
            'gpa',
            'semester',
            'prestasi akademik',
            'wisuda',
            'fakultas',

            // Program/kegiatan related
            'workshop',
            'seminar',
            'pelatihan',
            'pengabdian masyarakat',
            'penelitian',
            'publikasi',
            'karya ilmiah',
            'kegiatan',
            'program',

            // Lokasi/organisasi related
            'jakarta',
            'bandung',
            'surabaya',
            'medan',
            'makassar',
            'yogyakarta',
            'semarang',
            'palembang',
            'bali',
            'manado',
            'balikpapan',
            'pontianak',
            'cirebon',
            'bogor',
            'tangerang',
            'bekasi',
            'depok'
        ];

        $text = strtolower($text);

        // Cek keyword langsung
        foreach ($scopeKeywords as $keyword) {
            if (strpos($text, $keyword) !== false) {
                return true;
            }
        }

        // Cek konteks pertanyaan yang masih relevan
        $contextualPhrases = [
            'ada di',
            'dimana saja',
            'di mana saja',
            'lokasi',
            'tempat',
            'berapa banyak',
            'jumlah',
            'total',
            'seberapa banyak',
            'selain',
            'lain',
            'lainnya',
            'yang lain'
        ];

        foreach ($contextualPhrases as $phrase) {
            if (strpos($text, $phrase) !== false) {
                // Jika ada konteks + ada referensi sebelumnya tentang GenBI = masih dalam scope
                return true;
            }
        }

        // Fuzzy matching untuk typo
        foreach ($scopeKeywords as $keyword) {
            if (similar_text(strtolower($keyword), $text, $percent) && $percent > 70) {
                return true;
            }
        }

        return false;
    }

    private function callContextualAI(string $text): string
    {
        $apiKey = env('OPENROUTER_API_KEY');

        if (!$apiKey) {
            return 'Maaf, layanan tidak tersedia saat ini. Silakan coba lagi nanti.';
        }

        // System prompt yang sangat fokus dan membatasi
        $systemPrompt = "Anda adalah asisten AI khusus untuk GenBI dan Bank Indonesia.

IDENTITAS:
- Nama: ChatBot GenBI 
- Fokus: Menjawab pertanyaan tentang GenBI, Beasiswa BI, dan Bank Indonesia
- Wilayah: Memahami GenBI di seluruh Indonesia

ATURAN RESPONS:
1. Gunakan Bahasa Indonesia yang ramah dan informatif
2. Jawaban 2-3 kalimat, langsung to the point
3. Jika tidak tahu PASTI, katakan: 'Untuk informasi terkini, silakan hubungi sekretariat GenBI atau Bank Indonesia terdekat'
4. DILARANG membuat informasi yang tidak akurat

KONTEKS GENBI:
- GenBI = komunitas mahasiswa penerima beasiswa Bank Indonesia
- Ada chapter/cabang di berbagai kota di Indonesia (Jakarta, Bandung, Surabaya, Medan, Cirebon, dll)
- Tujuan: pengembangan diri dan kontribusi untuk Indonesia
- Kegiatan: workshop, seminar, pengabdian masyarakat, penelitian

UNTUK PERTANYAAN LOKASI/WILAYAH:
- GenBI ada di banyak kota/universitas di Indonesia
- Setiap chapter memiliki kegiatan dan program masing-masing
- Jika ditanya lokasi spesifik yang tidak tahu pasti, arahkan ke sumber resmi

Jawab dengan informatif dan membantu!";

        try {
            $response = Http::timeout(25)->withHeaders([
                'Authorization' => 'Bearer ' . $apiKey,
                'Content-Type'  => 'application/json',
                'HTTP-Referer'  => 'https://genbicirebon.org/',
                'X-Title'       => 'GenBI Cirebon Chatbot',
            ])->post('https://openrouter.ai/api/v1/chat/completions', [
                "model" => "mistralai/mistral-7b-instruct",
                "messages" => [
                    ["role" => "system", "content" => $systemPrompt],
                    ["role" => "user", "content" => $text]
                ],
                "max_tokens" => 200,
                "temperature" => 0.1, // Rendah untuk konsistensi
                "top_p" => 0.8
            ]);

            if ($response->successful()) {
                $aiResponse = trim($response->json()['choices'][0]['message']['content'] ?? '');

                // Double check: pastikan response masih dalam scope
                if (!empty($aiResponse) && $this->validateAIResponse($aiResponse)) {
                    return $aiResponse;
                }
            } else {
                Log::error('OpenRouter API Error: ', $response->json());
            }
        } catch (\Exception $e) {
            Log::error('AI Fallback Exception: ' . $e->getMessage());
        }

        // Jika AI gagal, berikan respons fallback yang aman
        return 'Maaf, saya tidak dapat memberikan jawaban yang akurat untuk pertanyaan tersebut. Silakan hubungi sekretariat GenBI Cirebon untuk informasi lebih lanjut.';
    }

    private function validateAIResponse(string $response): bool
    {
        $response = strtolower($response);

        // Pastikan respons mengandung kata kunci yang relevan
        $validIndicators = [
            'genbi',
            'gen bi',
            'generasi baru indonesia',
            'beasiswa',
            'bank indonesia',
            'bi',
            'mahasiswa',
            'komunitas',
            'cirebon',
            'chapter',
            'cabang',
            'wilayah',
            'daerah',
            'jakarta',
            'bandung',
            'surabaya',
            'medan',
            'program',
            'kegiatan',
            'workshop',
            'seminar'
        ];

        foreach ($validIndicators as $indicator) {
            if (strpos($response, $indicator) !== false) {
                return true;
            }
        }

        // Cek apakah respons masih membahas topik yang relevan meskipun tidak ada keyword eksplisit
        $contextualWords = [
            'informasi',
            'hubungi',
            'sekretariat',
            'kantor',
            'resmi',
            'lebih lanjut',
            'detail',
            'akurat'
        ];

        foreach ($contextualWords as $word) {
            if (strpos($response, $word) !== false) {
                return true; // Respons mengarahkan ke sumber resmi = valid
            }
        }

        return false;
    }

    private function getOutOfScopeResponse(): string
    {
        $responses = [
            "Maaf, saya hanya dapat membantu pertanyaan seputar GenBI, beasiswa Bank Indonesia, dan informasi terkait BI. Ada yang ingin ditanyakan tentang topik tersebut?",

            "Pertanyaan Anda di luar cakupan yang dapat saya bantu. Saya khusus melayani informasi GenBI dan beasiswa Bank Indonesia. Silakan tanyakan hal yang berkaitan dengan itu.",

            "Saya didesain khusus untuk membantu informasi GenBI Cirebon dan program beasiswa Bank Indonesia. Apakah ada yang ingin Anda ketahui tentang GenBI?"
        ];

        return $responses[array_rand($responses)];
    }

    // Method untuk testing fallback AI
    public function fallbackTest(Request $request)
    {
        $message = $request->input('message', 'Test message');

        return response()->json([
            'message' => $message,
            'is_in_scope' => $this->isInScope($message),
            'ai_response' => $this->isInScope($message) ?
                $this->callContextualAI($message) :
                $this->getOutOfScopeResponse(),
            'should_use_fallback' => $this->shouldUseFallback('')
        ]);
    }
}

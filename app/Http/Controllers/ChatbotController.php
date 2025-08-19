<?php

namespace App\Http\Controllers;

use Google\Cloud\Dialogflow\V2\Client\SessionsClient;
use Illuminate\Http\Request;
use Google\Cloud\Dialogflow\V2\QueryInput;
use Google\Cloud\Dialogflow\V2\TextInput;
use Google\Cloud\Dialogflow\V2\DetectIntentRequest;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use App\Services\FirestoreService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class ChatbotController extends Controller
{
    protected $firestoreService;

    // Minimum confidence untuk Dialogflow (sesuai penelitian)
    const DIALOGFLOW_CONFIDENCE_THRESHOLD = 0.7;

    // Response time target sesuai skripsi (≤5 detik)
    const MAX_RESPONSE_TIME = 5;

    // Kategori utama sesuai skripsi (digunakan untuk fallback)
    const KNOWLEDGE_CATEGORIES = [
        'beasiswa_bi' => 'Informasi Beasiswa Bank Indonesia',
        'keanggotaan' => 'Prosedur Keanggotaan GenBI',
        'bank_indonesia' => 'Informasi Bank Indonesia',
        'faq_umum' => 'FAQ Umum Komunitas'
    ];

    public function __construct(FirestoreService $firestoreService)
    {
        $this->firestoreService = $firestoreService;
    }

    public function index()
    {
        // Ambil suggested questions untuk halaman chatbot
        $suggestions = $this->generateSuggestedQuestions();
        return view('chatbot', compact('suggestions'));
    }

    public function sendMessage(Request $request)
    {
        $startTime = microtime(true);
        $message = $request->input('message');
        $sessionId = session()->getId();
        $userId = auth()->id();

        try {
            // 1. Cek knowledge base lokal terlebih dahulu (untuk pertanyaan yang sudah pernah dijawab)
            $cachedResponse = $this->checkKnowledgeBase($message);
            if ($cachedResponse) {
                $this->logInteraction($sessionId, $message, $cachedResponse['answer'], 'knowledge_base', $userId, $startTime);
                return response()->json([
                    'message' => $cachedResponse['answer'],
                    'source' => 'knowledge_base',
                    // Gunakan AI untuk saran kontekstual
                    'suggestions' => $this->generateAIContextualSuggestions($message, $cachedResponse['answer'])
                ]);
            }

            // 2. Coba Dialogflow dengan confidence checking
            $dialogflowResponse = $this->detectIntentWithConfidence($message);

            if (
                $dialogflowResponse['confidence'] >= self::DIALOGFLOW_CONFIDENCE_THRESHOLD &&
                !empty($dialogflowResponse['text'])
            ) {
                // Save ke knowledge base untuk pembelajaran
                $this->saveToKnowledgeBase($message, $dialogflowResponse['text'], $dialogflowResponse['intent']);
                $this->logInteraction($sessionId, $message, $dialogflowResponse['text'], 'dialogflow', $userId, $startTime);

                return response()->json([
                    'message' => $dialogflowResponse['text'],
                    'source' => 'dialogflow',
                    'confidence' => $dialogflowResponse['confidence'],
                    // Gunakan AI untuk saran kontekstual
                    'suggestions' => $this->generateAIContextualSuggestions($message, $dialogflowResponse['text'])
                ]);
            }

            // 3. Fallback ke OpenAI dengan context-aware approach
            $openAIResponse = $this->intelligentFallbackAI($message, $dialogflowResponse);

            // Simpan hasil OpenAI ke knowledge base
            $category = $this->categorizeQuestion($message);
            $this->saveToKnowledgeBase($message, $openAIResponse, $category);
            $this->logInteraction($sessionId, $message, $openAIResponse, 'openai', $userId, $startTime);

            return response()->json([
                'message' => $openAIResponse,
                'source' => 'openai_fallback',
                // Gunakan AI untuk saran kontekstual
                'suggestions' => $this->generateAIContextualSuggestions($message, $openAIResponse)
            ]);
        } catch (\Exception $e) {
            Log::error('Chatbot error: ' . $e->getMessage());
            $this->firestoreService->addErrorLog($e->getMessage(), $message, $userId);

            return response()->json([
                'message' => 'Maaf, saya mengalami gangguan sementara. Silakan coba lagi atau hubungi admin GenBI Cirebon.',
                'source' => 'error',
                'error' => true
            ], 500);
        }
    }

    /**
     * Enhanced Dialogflow dengan confidence checking
     */
    private function detectIntentWithConfidence(string $text): array
    {
        $projectId = env('DIALOGFLOW_PROJECT_ID', 'websitebot-etqi');
        $sessionId = session()->getId();
        $credentialsPath = storage_path('app/google/dialogflow-credentials.json');

        try {
            $sessionsClient = new SessionsClient(['credentials' => $credentialsPath]);
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

            return [
                'text' => $queryResult->getFulfillmentText() ?? '',
                'confidence' => $queryResult->getIntentDetectionConfidence(),
                'intent' => $queryResult->getIntent()->getDisplayName() ?? 'unknown'
            ];
        } catch (\Exception $e) {
            Log::error('Dialogflow error: ' . $e->getMessage());
            return ['text' => '', 'confidence' => 0, 'intent' => 'unknown'];
        }
    }

    /**
     * Intelligent fallback dengan OpenAI - context-aware dan optimized
     */
    private function intelligentFallbackAI(string $message, array $dialogflowContext): string
    {
        $apiKey = env('OPENROUTER_API_KEY');

        // Context dari Dialogflow untuk memberikan hint ke OpenAI
        $contextHint = '';
        if (!empty($dialogflowContext['intent']) && $dialogflowContext['intent'] !== 'unknown') {
            $contextHint = "Pertanyaan ini mungkin berkaitan dengan: {$dialogflowContext['intent']}. ";
        }

        // System prompt yang lebih spesifik dan context-aware
        $systemPrompt = "Kamu adalah Asisten AI untuk komunitas GenBI (Generasi Baru Indonesia) Cirebon. 

KONTEKS ORGANISASI:
- GenBI adalah komunitas penerima beasiswa Bank Indonesia
- Fokus pada pengembangan leadership dan kontribusi sosial
- Memiliki kegiatan rutin seperti kajian ekonomi, bakti sosial, dan pengembangan diri

KATEGORI YANG HARUS KAMU KETAHUI:
1. Beasiswa Bank Indonesia (persyaratan, proses seleksi, benefits)
2. Keanggotaan GenBI (cara bergabung, kewajiban anggota, struktur organisasi)
3. Informasi Bank Indonesia (kebijakan moneter, program CSR, visi misi)
4. FAQ Umum (kegiatan rutin, jadwal, kontak pengurus)

ATURAN MENJAWAB:
- Jawab dengan bahasa yang ramah dan informatif
- Jika tidak yakin dengan jawaban spesifik, berikan informasi umum yang relevan
- Selalu akhiri dengan ajakan untuk menghubungi admin jika butuh info lebih detail
- Maksimal 150 kata per jawaban
- Gunakan format yang mudah dibaca

{$contextHint}

Jawab pertanyaan berikut dengan fokus pada konteks GenBI Cirebon:";

        try {
            $response = Http::timeout(self::MAX_RESPONSE_TIME - 1) // Sisakan 1 detik untuk processing
                ->withHeaders([
                    'Authorization' => 'Bearer ' . $apiKey,
                    'Content-Type'  => 'application/json',
                    'HTTP-Referer'  => 'https://genbicirebon.org/',
                    'X-Title'       => 'GenBI Cirebon Smart Chatbot',
                ])->post('https://openrouter.ai/api/v1/chat/completions', [
                    "model" => "openai/gpt-3.5-turbo", // Lebih cepat dan reliable
                    "messages" => [
                        ["role" => "system", "content" => $systemPrompt],
                        ["role" => "user", "content" => $message]
                    ],
                    "temperature" => 0.7,
                    "max_tokens" => 200, // Batasi untuk response yang fokus
                    "presence_penalty" => 0.1,
                    "frequency_penalty" => 0.1
                ]);

            if ($response->successful()) {
                $content = $response->json()['choices'][0]['message']['content'] ?? null;
                if (!empty($content)) {
                    return trim($content);
                }
            }
            Log::warning('OpenAI fallback failed', ['status' => $response->status(), 'response' => $response->json()]);
        } catch (\Exception $e) {
            Log::error('OpenAI API timeout or error: ' . $e->getMessage());
        }

        return "Maaf, saya belum bisa menjawab pertanyaan ini dengan baik. Untuk informasi yang lebih akurat, silakan:\n\n" .
            "📞 Hubungi admin GenBI Cirebon\n" .
            "📱 Chat WhatsApp: [nomor admin]\n" .
            "📧 Email: genbicirebon@email.com\n\n" .
            "Atau coba tanyakan hal lain tentang GenBI Cirebon!";
    }

    /**
     * [BARU] Generate contextual suggestions menggunakan AI berdasarkan percakapan.
     */
    private function generateAIContextualSuggestions(string $lastQuestion, string $lastAnswer): array
    {
        $cacheKey = 'contextual_suggestions_' . md5($lastQuestion . $lastAnswer);

        // Cache selama 1 jam untuk konteks yang sama
        return Cache::remember($cacheKey, 3600, function () use ($lastQuestion, $lastAnswer) {
            $apiKey = env('OPENROUTER_API_KEY');

            $systemPrompt = "Kamu adalah AI yang bertugas membuat saran pertanyaan lanjutan untuk sebuah chatbot. Berdasarkan pertanyaan terakhir user dan jawaban dari chatbot, buatkan 3 pertanyaan relevan sebagai saran. Pertanyaan harus singkat dan memancing rasa ingin tahu. Jawab HANYA dengan format JSON array berisi string. Contoh: [\"Pertanyaan 1?\", \"Pertanyaan 2?\", \"Pertanyaan 3?\"]";

            $userContent = "Pertanyaan User Terakhir: \"{$lastQuestion}\"\n\nJawaban Chatbot: \"{$lastAnswer}\"\n\nBuatkan 3 saran pertanyaan lanjutan.";

            try {
                $response = Http::timeout(4)->withHeaders([
                    'Authorization' => 'Bearer ' . $apiKey,
                    'Content-Type'  => 'application/json',
                ])->post('https://openrouter.ai/api/v1/chat/completions', [
                    "model" => "openai/gpt-3.5-turbo",
                    "messages" => [
                        ["role" => "system", "content" => $systemPrompt],
                        ["role" => "user", "content" => $userContent]
                    ],
                    "temperature" => 0.8,
                    "max_tokens" => 150
                ]);

                if ($response->successful()) {
                    $content = $response->json()['choices'][0]['message']['content'] ?? '';
                    $suggestions = json_decode($content, true);

                    // Validasi jika hasilnya adalah array dan tidak kosong
                    if (is_array($suggestions) && count($suggestions) > 0) {
                        return array_slice($suggestions, 0, 3); // Ambil maksimal 3
                    }
                }
            } catch (\Exception $e) {
                Log::error('Failed to generate AI contextual suggestions: ' . $e->getMessage());
            }

            // Fallback jika AI gagal atau format tidak sesuai
            return $this->getFallbackSuggestions();
        });
    }

    /**
     * Generate suggested questions awal menggunakan AI
     */
    private function generateSuggestedQuestions(): array
    {
        $cacheKey = 'chatbot_initial_suggestions_' . date('Y-m-d');

        return Cache::remember($cacheKey, 3600, function () {
            $apiKey = env('OPENROUTER_API_KEY');
            try {
                $response = Http::timeout(10)->withHeaders([
                    'Authorization' => 'Bearer ' . $apiKey,
                    'Content-Type'  => 'application/json',
                ])->post('https://openrouter.ai/api/v1/chat/completions', [
                    "model" => "openai/gpt-3.5-turbo",
                    "messages" => [
                        [
                            "role" => "system",
                            "content" => "Generate 6 pertanyaan yang sering ditanyakan tentang GenBI Cirebon dalam format JSON array. Pertanyaan harus mencakup: beasiswa BI, keanggotaan, kegiatan, dan info umum. Format: [\"pertanyaan1\", \"pertanyaan2\", ...]"
                        ],
                        [
                            "role" => "user",
                            "content" => "Buatkan suggested questions untuk chatbot GenBI Cirebon"
                        ]
                    ],
                    "temperature" => 0.8,
                    "max_tokens" => 300
                ]);

                if ($response->successful()) {
                    $content = $response->json()['choices'][0]['message']['content'] ?? '';
                    $suggestions = json_decode($content, true);

                    if (is_array($suggestions) && count($suggestions) > 0) {
                        return $suggestions;
                    }
                }
            } catch (\Exception $e) {
                Log::error('Failed to generate AI suggestions: ' . $e->getMessage());
            }

            // Fallback suggestions
            return $this->getFallbackSuggestions(6);
        });
    }

    /**
     * [REFACTORED] Menyediakan saran fallback statis jika AI gagal.
     */
    private function getFallbackSuggestions(int $count = 3): array
    {
        $allSuggestions = [
            "Apa persyaratan beasiswa Bank Indonesia?",
            "Bagaimana proses seleksi beasiswa BI?",
            "Berapa nominal beasiswa yang diberikan?",
            "Bagaimana cara mendaftar jadi anggota GenBI?",
            "Apa kewajiban anggota GenBI Cirebon?",
            "Apakah ada biaya keanggotaan?",
            "Apa visi misi Bank Indonesia?",
            "Program CSR apa saja yang ada di BI?",
            "Dimana alamat sekretariat GenBI Cirebon?",
            "Kapan jadwal kegiatan rutin?",
            "Siapa pengurus GenBI Cirebon saat ini?",
        ];

        shuffle($allSuggestions); // Acak agar tidak monoton
        return array_slice($allSuggestions, 0, $count);
    }

    /**
     * Check knowledge base untuk jawaban yang sudah ada
     */
    private function checkKnowledgeBase(string $question): ?array
    {
        try {
            // Placeholder: Logika pencarian similarity harus diimplementasikan di FirestoreService
            // Untuk saat ini, kita anggap FirestoreService dapat melakukannya.
            // $kb = $this->firestoreService->searchKnowledgeBase($question);
            // return $kb;
            return null; // Asumsi tidak ada implementasi similarity search, jadi selalu null
        } catch (\Exception $e) {
            Log::warning('Knowledge base search failed: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Kategorisasi pertanyaan untuk knowledge base
     */
    private function categorizeQuestion(string $question): string
    {
        $question = strtolower($question);
        if (strpos($question, 'beasiswa') !== false || strpos($question, 'scholarship') !== false) {
            return 'beasiswa_bi';
        }
        if (strpos($question, 'anggota') !== false || strpos($question, 'bergabung') !== false || strpos($question, 'daftar') !== false || strpos($question, 'member') !== false) {
            return 'keanggotaan';
        }
        if (strpos($question, 'bank indonesia') !== false || strpos($question, 'bi ') !== false || strpos($question, 'kebijakan') !== false || strpos($question, 'moneter') !== false) {
            return 'bank_indonesia';
        }
        return 'faq_umum';
    }

    /**
     * Simpan ke knowledge base untuk pembelajaran chatbot
     */
    private function saveToKnowledgeBase(string $question, string $answer, string $category): void
    {
        try {
            $this->firestoreService->addKnowledgeBase($question, $answer, $category);
        } catch (\Exception $e) {
            Log::warning('Failed to save to knowledge base: ' . $e->getMessage());
        }
    }

    /**
     * Log semua interaksi dengan metrics
     */
    private function logInteraction(string $sessionId, string $question, string $answer, string $source, $userId, float $startTime): void
    {
        $responseTime = round((microtime(true) - $startTime) * 1000, 2); // dalam ms

        try {
            $this->firestoreService->addChatLog($sessionId, $question, $answer, $source, $userId);
            $today = date('Y-m-d');
            $this->firestoreService->updateSystemMetrics($today, [
                'total_queries' => \Google\Cloud\Firestore\FieldValue::increment(1),
                'source_' . $source => \Google\Cloud\Firestore\FieldValue::increment(1),
                'avg_response_time' => $responseTime,
                'last_updated' => new \Google\Cloud\Core\Timestamp(new \DateTime())
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to log interaction: ' . $e->getMessage());
        }
    }

    /**
     * Test endpoint untuk debug
     */
    public function testFallback(Request $request)
    {
        if (app()->environment('local')) {
            $message = $request->input('message', 'Test message');
            $response = $this->intelligentFallbackAI($message, ['intent' => 'test']);
            return response()->json([
                'message' => $message,
                'response' => $response,
                'timestamp' => now()
            ]);
        }
        return response()->json(['error' => 'Endpoint hanya tersedia di environment local'], 403);
    }
}

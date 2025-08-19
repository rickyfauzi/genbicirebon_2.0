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

class ChatbotController extends Controller
{
    public function index()
    {
        return view('chatbot');
    }

    public function sendMessage(Request $request, FirestoreService $firestore)
    {
        $message = trim($request->input('message'));
        if (empty($message)) {
            return response()->json(['message' => 'Pertanyaan tidak boleh kosong.', 'suggests' => []]);
        }

        $sessionId = session()->getId();
        $userId = auth()->id();
        $today = date('Y-m-d');

        $response = '';
        $suggests = [];
        $source = 'dialogflow';

        try {
            // 1. Coba Dialogflow
            $response = $this->detectIntent($message);

            // 2. Kalau kosong, cari di Firebase (knowledge_base dari fallback sebelumnya)
            if (empty($response)) {
                $source = 'firebase';
                $response = $firestore->searchKnowledgeBase($message, 80.0); // Threshold 80%

                // 3. Kalau masih kosong, fallback ke OpenAI + generate suggests
                if (empty($response)) {
                    $source = 'openai';
                    $openAIResponse = $this->fallbackAI($message);

                    // Parse response dari OpenAI (format JSON)
                    $parsed = json_decode($openAIResponse, true);
                    $response = $parsed['answer'] ?? 'Maaf, saya tidak bisa menjawab saat ini.';
                    $suggests = $parsed['suggests'] ?? [];

                    // Simpan ke knowledge_base kalau sukses (tentukan category sederhana, bisa di-improve dengan classify)
                    if (!empty($response)) {
                        $category = $this->determineCategory($message); // Method helper untuk category
                        $firestore->addKnowledgeBase($message, $response, $category);
                    }
                }
            }

            // 4. Simpan percakapan ke Firestore
            $firestore->addChatLog($sessionId, $message, $response, $source, $userId);

            // 5. Update metrics (contoh: increment total_query dan source-specific)
            $updates = [
                'total_queries' => ['increment' => 1],
                $source . '_count' => ['increment' => 1],
            ];
            $firestore->updateSystemMetrics($today, $updates);

            return response()->json([
                'message' => $response,
                'suggests' => $suggests
            ]);
        } catch (\Exception $e) {
            Log::error('Chatbot error: ' . $e->getMessage());
            $firestore->addErrorLog($e->getMessage(), $message, $userId);
            return response()->json(['message' => 'Terjadi kesalahan. Coba lagi nanti.', 'suggests' => []], 500);
        }
    }

    private function detectIntent(string $text)
    {
        $projectId = 'websitebot-etqi'; // Ganti dengan Project ID milikmu
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

        return $queryResult->getFulfillmentText() ?? '';
    }

    private function fallbackAI(string $text)
    {
        $apiKey = env('OPENROUTER_API_KEY');

        // Prompt spesifik berdasarkan proposal: 4 kategori utama
        $prompt = "Kamu adalah chatbot GenBI Cirebon. Jawab hanya soal: 1) Info beasiswa Bank Indonesia, 2) Prosedur keanggotaan GenBI, 3) Tentang Bank Indonesia, 4) FAQ komunitas. Jawab akurat, ramah, dalam bahasa Indonesia. Jika pertanyaan di luar topik, arahkan kembali ke topik GenBI.

User: $text

Kembalikan dalam JSON: 
{
  \"answer\": \"Jawaban lengkap dan relevan\",
  \"suggests\": [\"Suggest 1?\", \"Suggest 2?\", \"Suggest 3?\"]  // 2-3 pertanyaan lanjutan relevan
}";

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $apiKey,
            'Content-Type'  => 'application/json',
            'HTTP-Referer'  => 'https://genbicirebon.org/',
            'X-Title'       => 'Genbi Cirebon Chatbot',
        ])->post('https://openrouter.ai/api/v1/chat/completions', [
            "model" => "mistralai/mistral-7b-instruct",
            "messages" => [["role" => "system", "content" => $prompt]],
            "temperature" => 0.7
        ]);

        if ($response->successful()) {
            return $response->json()['choices'][0]['message']['content'] ?? '{"answer": "Maaf, pertanyaan di luar topik GenBI. Coba tanya soal beasiswa?", "suggests": []}';
        }

        Log::error('Fallback AI error: ' . json_encode($response->json()));
        return '{"answer": "Maaf, saya tidak bisa menjawab saat ini.", "suggests": []}';
    }

    // Helper untuk tentukan category berdasarkan keyword sederhana
    private function determineCategory(string $message): string
    {
        $lowerMessage = strtolower($message);
        if (strpos($lowerMessage, 'beasiswa') !== false) return 'beasiswa';
        if (strpos($lowerMessage, 'keanggotaan') !== false || strpos($lowerMessage, 'daftar') !== false) return 'keanggotaan';
        if (strpos($lowerMessage, 'bank indonesia') !== false) return 'bank_indonesia';
        return 'faq'; // Default
    }

    // Method fallbackTest tetap untuk testing
    public function fallbackTest(Request $request)
    {
        // Kode testing sama seperti sebelumnya
        $apiKey = env('OPENROUTER_API_KEY');
        $userMessage = "Halo, apa kabar?";

        $response = Http::withHeaders([
            'Authorization' => "Bearer {$apiKey}",
            'Content-Type'  => 'application/json',
            'HTTP-Referer'  => 'https://genbicirebon.org/',
            'X-Title'       => 'Laravel Chatbot Test',
        ])->post('https://openrouter.ai/api/v1/chat/completions', [
            'model' => 'openai/gpt-3.5-turbo',
            'messages' => [
                ['role' => 'system', 'content' => 'You are a helpful assistant.'],
                ['role' => 'user', 'content' => $userMessage],
            ],
        ]);

        if ($response->successful()) {
            return $response->json();
        } else {
            return ['error' => ['status' => $response->status(), 'body' => $response->json()]];
        }
    }
}

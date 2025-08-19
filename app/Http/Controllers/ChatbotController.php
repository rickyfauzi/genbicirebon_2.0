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
            // Layer 1: Coba Dialogflow
            $dialogflowResponse = $this->detectIntent($message);

            if ($dialogflowResponse && !empty($dialogflowResponse['text']) && !$dialogflowResponse['is_fallback']) {
                $response['message'] = $dialogflowResponse['text'];
                $source = 'dialogflow';
                // Minta saran ke OpenAI berdasarkan jawaban Dialogflow
                $openAIResult = $this->fallbackWithOpenAI($message, true); // true = suggestions only
                $response['suggestions'] = $openAIResult['suggestions'] ?? [];
            } else {
                // Layer 2: Cari di Firestore Knowledge Base
                $firestoreAnswer = $this->firestoreService->searchKnowledgeBase($message);
                if ($firestoreAnswer) {
                    $response['message'] = $firestoreAnswer;
                    $source = 'firestore';
                    // Minta saran ke OpenAI berdasarkan jawaban Firestore
                    $openAIResult = $this->fallbackWithOpenAI($message, true);
                    $response['suggestions'] = $openAIResult['suggestions'] ?? [];
                } else {
                    // Layer 3: Fallback ke OpenAI untuk jawaban & saran
                    $openAIResult = $this->fallbackWithOpenAI($message);
                    if (!empty($openAIResult['answer'])) {
                        $response['message'] = $openAIResult['answer'];
                        $response['suggestions'] = $openAIResult['suggestions'] ?? [];
                        $source = 'openai';

                        // Learning Loop: Simpan pengetahuan baru ke Firestore
                        $this->firestoreService->addKnowledgeBase($message, $openAIResult['answer']);
                    }
                }
            }

            // Simpan log percakapan
            $this->firestoreService->addChatLog($sessionId, $message, $response['message'], $source, $userId);
        } catch (\Exception $e) {
            Log::error('Chatbot Controller Error: ' . $e->getMessage() . ' on line ' . $e->getLine());
        }

        return response()->json($response);
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

    private function fallbackWithOpenAI(string $text, bool $suggestionsOnly = false)
    {
        $apiKey = env('OPENROUTER_API_KEY');
        $siteContext = "GenBI (Generasi Baru Indonesia) Cirebon adalah komunitas penerima beasiswa Bank Indonesia. Website resminya adalah genbicirebon.com. Fokusnya adalah informasi beasiswa, kegiatan pengembangan diri anggota (seperti workshop, seminar), program sosial (seperti mengajar, bakti sosial), dan berita terbaru seputar komunitas. Jawab dengan bahasa Indonesia yang santai tapi profesional.";

        $promptAction = $suggestionsOnly
            ? "HANYA berikan 3 saran pertanyaan lanjutan singkat yang relevan dengan pertanyaan pengguna. JANGAN jawab pertanyaan pengguna."
            : "Jawab pertanyaan pengguna secara ringkas dan informatif berdasarkan konteks. Setelah menjawab, berikan 3 saran pertanyaan lanjutan yang relevan dan singkat (maksimal 4 kata per saran).";

        $systemPrompt = "Kamu adalah 'GenBI Assistant', asisten AI ramah dan ahli tentang GenBI Cirebon. Konteksmu adalah: {$siteContext}. {$promptAction} Format respons HANYA dalam bentuk JSON valid seperti ini: {\"answer\": \"Jawabanmu di sini.\", \"suggestions\": [\"Saran 1\", \"Saran 2\", \"Saran 3\"]}. Jika hanya diminta saran, isi 'answer' dengan string kosong.";

        try {
            $response = Http::timeout(30)->withHeaders([
                'Authorization' => 'Bearer ' . $apiKey,
                'Content-Type'  => 'application/json',
                'HTTP-Referer'  => request()->getSchemeAndHttpHost(),
                'X-Title'       => 'Genbi Cirebon Chatbot',
            ])->post('https://openrouter.ai/api/v1/chat/completions', [
                "model" => "openai/gpt-3.5-turbo",
                "messages" => [
                    ["role" => "system", "content" => $systemPrompt],
                    ["role" => "user", "content" => $text]
                ],
                "response_format" => ["type" => "json_object"],
                "temperature" => 0.5,
                "max_tokens" => 300,
            ]);

            if ($response->successful()) {
                $data = $response->json()['choices'][0]['message']['content'];
                $decodedData = json_decode($data, true);
                // Pastikan formatnya benar sebelum dikembalikan
                return [
                    'answer' => $decodedData['answer'] ?? ($suggestionsOnly ? '' : 'Gagal memformat jawaban.'),
                    'suggestions' => $decodedData['suggestions'] ?? [],
                ];
            }

            Log::error('OpenAI Fallback HTTP Error: ' . $response->body());
            return ['answer' => 'Maaf, saya sedang mengalami kendala teknis (API).', 'suggestions' => []];
        } catch (\Exception $e) {
            Log::error('OpenAI Fallback Exception: ' . $e->getMessage());
            return ['answer' => 'Maaf, koneksi ke asisten AI sedang bermasalah.', 'suggestions' => []];
        }
    }
}

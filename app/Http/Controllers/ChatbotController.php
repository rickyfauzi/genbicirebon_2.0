<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\FirestoreService;
use Illuminate\Support\Facades\Log;

class ChatbotController extends Controller
{
    protected $firestore;

    public function __construct(FirestoreService $firestore)
    {
        $this->firestore = $firestore;
    }

    public function chat(Request $request)
    {
        $message   = $request->input('message');
        $sessionId = $request->input('session_id', uniqid('webchat-'));

        Log::info("👤 User: {$message}");

        $answer = null;
        $source = 'unknown';

        // 1️⃣ Coba Dialogflow
        try {
            $answer = $this->askDialogflow($message);
            $source = 'dialogflow';
        } catch (\Exception $e) {
            Log::error("❌ Dialogflow error: " . $e->getMessage());
        }

        // 2️⃣ Kalau kosong, coba Firestore KB
        if (!$answer && $this->firestore->isConnected()) {
            $kbAnswer = $this->searchKnowledgeBase($message);
            if ($kbAnswer) {
                $answer = $kbAnswer;
                $source = 'firestore';
            }
        }

        // 3️⃣ Kalau masih kosong → fallback ke OpenAI
        if (!$answer) {
            $answer = $this->askOpenAI($message);
            $source = 'openai';

            // simpan ke KB
            if ($this->firestore->isConnected() && $answer) {
                $this->firestore->addKnowledgeBase($message, $answer);
            }
        }

        // 4️⃣ Simpan chat log
        if ($this->firestore->isConnected() && $answer) {
            $this->firestore->addChatLog($sessionId, $message, $answer, $source);
        }

        return response()->json([
            'session_id' => $sessionId,
            'message'    => $message,
            'answer'     => $answer,
            'source'     => $source,
        ]);
    }

    private function askDialogflow(string $message): ?string
    {
        // TODO: tambahin implementasi sesuai SDK Dialogflow
        throw new \Exception("Dialogflow belum diaktifkan");
    }

    private function searchKnowledgeBase(string $message): ?string
    {
        // bisa ditambah query keyword Firestore
        return null;
    }

    private function askOpenAI(string $message): ?string
    {
        try {
            $client = new \GuzzleHttp\Client();
            $res = $client->post('https://api.openai.com/v1/chat/completions', [
                'headers' => [
                    'Authorization' => 'Bearer ' . env('OPENAI_API_KEY'),
                    'Content-Type'  => 'application/json',
                ],
                'json' => [
                    'model'    => 'gpt-3.5-turbo',
                    'messages' => [['role' => 'user', 'content' => $message]],
                ],
            ]);

            $data = json_decode($res->getBody(), true);
            return $data['choices'][0]['message']['content'] ?? null;
        } catch (\Exception $e) {
            Log::error("❌ OpenAI error: " . $e->getMessage());
            return null;
        }
    }
}

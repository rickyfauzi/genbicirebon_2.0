<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Google\Cloud\Firestore\FirestoreClient;

class WebhookController extends Controller
{
    public function handle(Request $request)
    {
        Log::info('Webhook hit: payload received', ['body' => $request->all()]);

        $data = $request->json()->all();

        $queryText = $data['queryResult']['queryText'] ?? '';
        $intentName = $data['queryResult']['intent']['displayName'] ?? null;
        $sessionId = $data['session'] ?? null;

        Log::info("Parsed Dialogflow input", [
            'queryText' => $queryText,
            'intentName' => $intentName,
            'sessionId' => $sessionId,
        ]);

        $answer = $this->replyFromIntent($intentName, $queryText);

        try {
            $this->saveToFirestore($queryText, $answer, $sessionId, $intentName);
        } catch (\Exception $e) {
            Log::error("Firestore error", ['error' => $e->getMessage()]);
        }

        return response()->json([
            'fulfillmentText' => $answer,
            'source' => 'genbi-cirebon-webhook'
        ]);
    }

    private function replyFromIntent($intentName, $queryText)
    {
        $responses = [
            'Default Welcome Intent' => 'Halo! Saya chatbot GenBI. Ada yang bisa saya bantu?',
            'kontakgenbiintent' => 'Kamu bisa hubungi GenBI Cirebon via email genbicirebon@gmail.com atau Instagram @genbi.cirebon',
            'definisi.genbi' => 'GenBI (Generasi Baru Indonesia) adalah komunitas penerima beasiswa Bank Indonesia',
            'Default Fallback Intent' => 'Maaf, saya tidak mengerti. Bisa dijelaskan lagi?',
            'openai.auto' => 'Fitur AI sedang dalam pengembangan',
            null => 'Halo! Ada yang bisa saya bantu seputar GenBI Cirebon?'
        ];

        return $responses[$intentName] ?? $responses[null];
    }

    private function saveToFirestore($question, $answer, $sessionId, $intentName)
    {
        try {
            $firestore = new FirestoreClient([
                'keyFilePath' => storage_path('app/firebase/firebase_credentials.json'),
                'projectId' => 'your-project-id' // tambahkan ini
            ]);

            $firestore->collection('chat_history')->add([
                'session' => $sessionId ?? 'unknown-' . uniqid(),
                'intent' => $intentName ?? 'unknown',
                'question' => $question,
                'answer' => $answer,
                'timestamp' => now()->toDateTimeString(),
                'source' => 'web'
            ]);
        } catch (\Exception $e) {
            Log::error("Firestore save failed", ['error' => $e->getMessage()]);
            throw $e;
        }
    }
}

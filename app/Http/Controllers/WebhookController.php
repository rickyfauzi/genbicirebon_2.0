<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Google\Cloud\Firestore\FirestoreClient;

class WebhookController extends Controller
{
    public function handle(Request $request)
    {
        Log::info('Webhook hit: payload received', [
            'body' => $request->all()
        ]);

        $raw = $request->getContent();
        Log::info('🔥 RAW JSON', ['raw' => $raw]);

        // Decode JSON dari body request
        $data = json_decode($raw, true);

        $queryText = $data['queryResult']['queryText'] ?? '';
        $intentName = $data['queryResult']['intent']['displayName'] ?? null;
        $sessionId = $data['session'] ?? null;

        Log::info("Parsed Dialogflow input", [
            'queryText' => $queryText,
            'intentName' => $intentName,
            'sessionId' => $sessionId,
        ]);

        // Balasan berdasarkan intent dari Dialogflow
        $answer = $this->replyFromIntent($intentName, $queryText);

        // Simpan ke Firestore
        try {
            $this->saveToFirestore($queryText, $answer, $sessionId, $intentName);
            Log::info("Berhasil menyimpan ke Firestore");
        } catch (\Exception $e) {
            Log::error("Gagal menyimpan ke Firestore", ['message' => $e->getMessage()]);
        }

        // Respon ke Dialogflow
        return response()->json([
            'fulfillmentText' => $answer,
        ]);
    }

    private function replyFromIntent($intentName, $queryText)
    {
        Log::info("Membuat balasan berdasarkan intent", ['intent' => $intentName]);

        $responses = [
            'Default Welcome Intent' => 'Halo! Saya chatbot GenBI. Ada yang bisa saya bantu?',
            'kontakgenbiintent' => 'Kamu bisa menghubungi GenBI Cirebon melalui email genbicirebon@gmail.com atau Instagram kami di @genbi.cirebon',
            'definisi.genbi' => 'GenBI (Generasi Baru Indonesia) adalah komunitas penerima beasiswa Bank Indonesia yang aktif dalam kegiatan sosial, edukasi, dan pengembangan diri.',
            'Default Fallback Intent' => 'Maaf, saya tidak mengerti maksud Anda. Bisa dijelaskan lagi?',
            'openai.auto' => 'Saya belum bisa menjawab karena layanan AI sedang tidak aktif.',
        ];

        return $responses[$intentName] ?? "Saya belum paham maksudnya.";
    }

    private function saveToFirestore($question, $answer, $sessionId, $intentName)
    {
        Log::info("Inisialisasi koneksi ke Firestore");

        $firestore = new FirestoreClient([
            'keyFilePath' => storage_path('app/firebase/firebase_credentials.json'),
        ]);

        $collection = $firestore->collection('chat_history');

        $collection->add([
            'session' => $sessionId ?? 'unknown',
            'intent' => $intentName,
            'question' => $question,
            'answer' => $answer,
            'timestamp' => now()
        ]);
    }
}

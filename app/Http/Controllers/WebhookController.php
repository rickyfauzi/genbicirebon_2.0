<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Google\Cloud\Firestore\FirestoreClient;

class WebhookController extends Controller
{
    public function handle(Request $request)
    {
        // Log raw request untuk debugging
        Log::info('🔥 RAW JSON', ['raw' => $request->getContent()]);

        // Decode data JSON dari Dialogflow
        $data = json_decode($request->getContent(), true);

        // Jika format request tidak valid, beri respon error
        if (!$data || !isset($data['queryResult'])) {
            return response()->json([
                'fulfillmentText' => 'Format request tidak valid.'
            ], 400);
        }

        // Ambil data penting dari request
        $queryText = $data['queryResult']['queryText'] ?? '';
        $intentName = $data['queryResult']['intent']['displayName'] ?? 'Default Fallback Intent';
        $sessionId = $data['session'] ?? 'session-' . uniqid();

        Log::info("Request Dialogflow", [
            'query' => $queryText,
            'intent' => $intentName,
            'session' => $sessionId
        ]);

        // Dapatkan balasan berdasarkan intent
        $answer = $this->replyFromIntent($intentName, $queryText);

        // Simpan ke Firestore (jika gRPC terinstall)
        try {
            $this->saveToFirestore($queryText, $answer, $sessionId, $intentName);
            Log::info("Data tersimpan di Firestore");
        } catch (\Exception $e) {
            Log::error("Gagal menyimpan ke Firestore: " . $e->getMessage());
        }

        // Kirim balasan ke Dialogflow
        return response()->json([
            'fulfillmentText' => $answer,
        ]);
    }

    /**
     * Memberikan balasan berdasarkan intent
     */
    private function replyFromIntent($intentName, $queryText, $dialogflowResponse)
    {
        $responseJson = json_decode($dialogflowResponse, true);

        // Ambil langsung dari Dialogflow jika tersedia
        if (!empty($responseJson['queryResult']['fulfillmentText'])) {
            return $responseJson['queryResult']['fulfillmentText'];
        }

        // Jika tidak ada, fallback ke daftar manual
        $responses = [
            'Default Welcome Intent' => 'Halo! Saya chatbot GenBI. Ada yang bisa saya bantu?',
            'kontakgenbiintent' => '📧 Email: genbicirebon@gmail.com | 📱 IG: @genbi.cirebon',
            'definisi.genbi' => 'GenBI adalah komunitas penerima beasiswa Bank Indonesia.',
            'Default Fallback Intent' => 'Maaf, saya belum paham. Bisa diulangi?',
        ];

        return $responses[$intentName] ?? "Saya tidak mengerti pertanyaan Anda.";
    }


    /**
     * Simpan riwayat chat ke Firestore
     */
    private function saveToFirestore($question, $answer, $sessionId, $intentName)
    {
        // Jika gRPC tidak terinstall, lewati penyimpanan
        if (!extension_loaded('grpc')) {
            Log::warning("gRPC tidak aktif. Data tidak disimpan.");
            return;
        }

        $firestore = new FirestoreClient([
            'keyFilePath' => storage_path('app/firebase/firebase_credentials.json'),
        ]);

        $collection = $firestore->collection('chat_history');
        $collection->add([
            'session' => $sessionId,
            'intent' => $intentName,
            'question' => $question,
            'answer' => $answer,
            'timestamp' => now()->toDateTimeString()
        ]);
    }
}

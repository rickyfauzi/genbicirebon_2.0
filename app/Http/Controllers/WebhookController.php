<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Google\Cloud\Firestore\FirestoreClient;
// Tambahkan use statement ini
use Google\Cloud\Dialogflow\V2\SessionsClient;
use Google\Cloud\Dialogflow\V2\QueryInput;
use Google\Cloud\Dialogflow\V2\TextInput;

class WebhookController extends Controller
{
    /**
     * Method BARU: Menerima pesan dari frontend, mengirimkannya ke Dialogflow,
     * dan mengembalikan jawaban ke frontend.
     */
    public function handleChat(Request $request)
    {
        $message = $request->input('message');
        if (!$message) {
            return response()->json(['fulfillmentText' => 'Pesan tidak boleh kosong.'], 400);
        }

        try {
            // ID Proyek Google Cloud Anda
            $projectId = env('DIALOGFLOW_PROJECT_ID', 'ganti-dengan-project-id-anda');

            // Buat session ID unik untuk setiap user.
            // Untuk production, sebaiknya gunakan Laravel Session atau ID user yang login.
            $sessionId = $request->input('session_id', uniqid());

            // Inisialisasi Dialogflow SessionsClient
            $sessionsClient = new SessionsClient([
                'credentials' => storage_path('app/firebase/firebase_credentials.json')
            ]);
            $sessionName = $sessionsClient->sessionName($projectId, $sessionId);

            // Buat query input
            $textInput = new TextInput();
            $textInput->setText($message);
            $textInput->setLanguageCode('id'); // Ganti dengan kode bahasa agent Anda

            $queryInput = new QueryInput();
            $queryInput->setText($textInput);

            // Kirim request ke Dialogflow
            $response = $sessionsClient->detectIntent($sessionName, $queryInput);
            $queryResult = $response->getQueryResult();
            $fulfillmentText = $queryResult->getFulfillmentText();

            // Tutup koneksi client
            $sessionsClient->close();

            // Kirim jawaban dari Dialogflow kembali ke frontend
            return response()->json([
                'fulfillmentText' => $fulfillmentText,
                'session_id' => $sessionId // Kirim kembali session ID agar bisa dipakai lagi
            ]);
        } catch (\Exception $e) {
            Log::channel('stderr')->error('❌ [DIALOGFLOW API GAGAL] Gagal menghubungi API Dialogflow.', ['error' => $e->getMessage()]);
            return response()->json(['fulfillmentText' => 'Maaf, terjadi kesalahan pada server chatbot.'], 500);
        }
    }

    /**
     * Method LAMA: Ini adalah webhook yang akan dipanggil oleh Dialogflow.
     * Kode ini sudah benar dan tidak perlu diubah.
     */
    public function handle(Request $request)
    {
        // LOG 1: BUKTI BAHWA REQUEST DARI DIALOGFLOW SUDAH SAMPAI
        Log::channel('stderr')->info('✅ [WEBHOOK DARI DIALOGFLOW] Webhook URL berhasil diakses oleh Dialogflow.');

        // ... sisa kode handle() Anda sudah benar ...
        $rawPayload = $request->getContent();
        Log::channel('stderr')->info('📦 [WEBHOOK DATA MENTAH] Payload JSON dari Dialogflow:', ['raw' => $rawPayload]);

        $data = json_decode($rawPayload, true);

        $queryText = $data['queryResult']['queryText'] ?? '[TIDAK ADA]';
        $intentName = $data['queryResult']['intent']['displayName'] ?? '[KOSONG/NULL]';
        $sessionId = $data['session'] ?? '[TIDAK ADA]';

        Log::channel('stderr')->info('🔍 [WEBHOOK HASIL PARSING] Data yang berhasil dibaca:', [
            'Query' => $queryText,
            'Intent' => $intentName,
            'Session' => $sessionId,
        ]);

        if ($intentName === '[KOSONG/NULL]') {
            Log::channel('stderr')->error('❌ [MASALAH DIALOGFLOW] Nama Intent tidak terdeteksi. Cek Fulfillment di Dialogflow!');
        }

        $answer = $this->getAnswerForIntent($intentName);

        Log::channel('stderr')->info('💬 [WEBHOOK RESPONS] Jawaban yang akan dikirim:', ['answer' => $answer]);

        $this->saveToFirestore($queryText, $answer, $sessionId, $intentName);

        return response()->json([
            'fulfillmentText' => $answer,
        ]);
    }

    // Fungsi getAnswerForIntent dan saveToFirestore tidak perlu diubah
    private function getAnswerForIntent($intentName)
    {
        $responses = [
            'Default Welcome Intent' => 'Halo! Saya chatbot GenBI. Ada yang bisa saya bantu?',
            'TentangGenBIIntent' => 'GenBI (Generasi Baru Indonesia) adalah komunitas penerima beasiswa Bank Indonesia.',
            // ... intent lain
        ];

        return $responses[$intentName] ?? 'Maaf, saya tidak mengerti maksud Anda. Silakan coba lagi.';
    }

    private function saveToFirestore($question, $answer, $sessionId, $intentName)
    {
        try {
            Log::channel('stderr')->info('🔄 [FIRESTORE] Mencoba inisialisasi koneksi...');
            $firestore = new FirestoreClient([
                'keyFilePath' => storage_path('app/firebase/firebase_credentials.json'),
            ]);

            $collection = $firestore->collection('chat_history');
            $collection->add([
                'session' => $sessionId,
                'intent' => $intentName,
                'question' => $question,
                'answer' => $answer,
                'timestamp' => now()
            ]);
            Log::channel('stderr')->info('✅ [FIRESTORE] Berhasil menyimpan ke Firestore.');
        } catch (\Exception $e) {
            Log::channel('stderr')->error('❌ [FIRESTORE GAGAL] Tidak bisa menyimpan data.', ['error' => $e->getMessage()]);
        }
    }
}

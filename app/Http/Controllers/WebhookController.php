<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Google\Cloud\Firestore\FirestoreClient;

class WebhookController extends Controller
{
    public function handle(Request $request)
    {
        // LOG 1: BUKTI BAHWA REQUEST DARI DIALOGFLOW SUDAH SAMPAI
        Log::channel('stderr')->info('✅ [SAMBUNGAN BERHASIL] Webhook URL berhasil diakses oleh Dialogflow.');

        // LOG 2: TAMPILKAN SEMUA DATA MENTAH YANG DIKIRIM
        $rawPayload = $request->getContent();
        Log::channel('stderr')->info('📦 [DATA MENTAH] Payload JSON dari Dialogflow:', ['raw' => $rawPayload]);

        // Decode JSON untuk diproses
        $data = json_decode($rawPayload, true);

        // Ambil data penting dengan aman
        $queryText = $data['queryResult']['queryText'] ?? '[TIDAK ADA]';
        $intentName = $data['queryResult']['intent']['displayName'] ?? '[KOSONG/NULL]';
        $sessionId = $data['session'] ?? '[TIDAK ADA]';

        // LOG 3: TAMPILKAN HASIL PARSING
        Log::channel('stderr')->info('🔍 [HASIL PARSING] Data yang berhasil dibaca:', [
            'Query' => $queryText,
            'Intent' => $intentName,
            'Session' => $sessionId,
        ]);

        if ($intentName === '[KOSONG/NULL]') {
            Log::channel('stderr')->error('❌ [MASALAH DIALOGFLOW] Nama Intent tidak terdeteksi. Cek Fulfillment di Dialogflow!');
        }

        $answer = $this->getAnswerForIntent($intentName);

        // LOG 4: JAWABAN YANG AKAN DIKIRIM KEMBALI
        Log::channel('stderr')->info('💬 [RESPONS] Jawaban yang akan dikirim:', ['answer' => $answer]);

        // Coba simpan ke Firestore
        $this->saveToFirestore($queryText, $answer, $sessionId, $intentName);

        // Kirim balasan ke Dialogflow
        return response()->json([
            'fulfillmentText' => $answer,
        ]);
    }

    private function getAnswerForIntent($intentName)
    {
        $responses = [
            'Default Welcome Intent' => 'Halo! Saya chatbot GenBI. Ada yang bisa saya bantu?',
            'TentangGenBIIntent' => 'GenBI (Generasi Baru Indonesia) adalah komunitas penerima beasiswa Bank Indonesia.',
            // ... intent lain
        ];

        if ($intentName && array_key_exists($intentName, $responses)) {
            return $responses[$intentName];
        }

        return 'Maaf, saya tidak mengerti maksud Anda. Silakan coba lagi.';
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
            // LOG 5: TANGKAP ERROR SPESIFIK DARI FIRESTORE
            Log::channel('stderr')->error('❌ [FIRESTORE GAGAL] Tidak bisa menyimpan data.', ['error' => $e->getMessage()]);
        }
    }
}

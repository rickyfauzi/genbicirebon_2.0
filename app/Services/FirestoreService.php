<?php

namespace App\Services;

use Google\Cloud\Firestore\FirestoreClient;
use Google\Cloud\Firestore\FieldValue;
use Illuminate\Support\Facades\Log;

class FirestoreService
{
    protected $firestore;
    protected $knowledgeBase;
    protected $chatLogs;
    protected $errorLogs;

    public function __construct()
    {
        $this->firestore = new FirestoreClient([
            'keyFilePath' => storage_path(env('FIREBASE_CREDENTIALS')),
            'projectId' => env('DIALOGFLOW_PROJECT_ID'),
        ]);

        $this->knowledgeBase = $this->firestore->collection('knowledge_base');
        $this->chatLogs = $this->firestore->collection('chat_logs');
        $this->errorLogs = $this->firestore->collection('error_logs');
    }

    /**
     * Mencari jawaban di knowledge base menggunakan pencarian kata kunci.
     *
     * @param string $questionText
     * @return string|null
     */
    public function searchKnowledgeBase(string $questionText): ?string
    {
        // 1. Normalisasi dan ekstraksi kata kunci dari pertanyaan pengguna
        $keywords = $this->extractKeywords($questionText);

        if (empty($keywords)) {
            return null;
        }

        // 2. Cari dokumen yang mengandung salah satu dari kata kunci tersebut
        // Catatan: Firestore 'whereArrayContainsAny' hanya bisa mencari hingga 10 nilai dalam satu query.
        $query = $this->knowledgeBase->where('keywords', 'array-contains-any', array_slice($keywords, 0, 10));
        $documents = $query->documents();

        $bestMatch = null;
        $highestScore = 0;

        // 3. Cari dokumen dengan skor kecocokan tertinggi
        foreach ($documents as $document) {
            if ($document->exists()) {
                $docData = $document->data();
                $docKeywords = $docData['keywords'] ?? [];

                // Hitung skor berdasarkan jumlah kata kunci yang cocok
                $score = count(array_intersect($keywords, $docKeywords));

                if ($score > $highestScore) {
                    $highestScore = $score;
                    $bestMatch = $docData;
                }
            }
        }

        // 4. Jika ditemukan kecocokan yang cukup baik (misal > 2 kata kunci), kembalikan jawabannya
        if ($bestMatch && $highestScore > 1) { // Atur ambang batas skor di sini
            Log::info("Kecocokan ditemukan di KB dengan skor {$highestScore}. Pertanyaan: '{$bestMatch['question']}'");
            return $bestMatch['answer'];
        }

        return null;
    }

    /**
     * Menambahkan pengetahuan baru ke Firestore, termasuk membuat keywords.
     *
     * @param string $question
     * @param string $answer
     */
    public function addKnowledgeBase(string $question, string $answer)
    {
        // Hindari duplikasi pertanyaan yang sama persis
        $exactQuery = $this->knowledgeBase->where('question', '=', $question)->limit(1);
        if (!$exactQuery->documents()->isEmpty()) {
            Log::warning("Mencoba menambahkan duplikat knowledge base untuk: '{$question}'");
            return;
        }

        $data = [
            'question' => $question,
            'answer' => $answer,
            'category' => 'General', // atau kategori lain
            'keywords' => $this->extractKeywords($question),
            'created_at' => FieldValue::serverTimestamp(),
        ];

        $this->knowledgeBase->add($data);
    }

    // Fungsi-fungsi lain untuk logging
    public function addChatLog($sessionId, $question, $answer, $source, $userId = null)
    {
        $this->chatLogs->add([
            'session_id' => $sessionId,
            'question' => $question,
            'answer' => $answer,
            'source' => $source,
            'user_id' => $userId,
            'timestamp' => FieldValue::serverTimestamp(),
        ]);
    }

    public function addErrorLog($errorMessage, $userMessage)
    {
        $this->errorLogs->add([
            'error_message' => $errorMessage,
            'user_message' => $userMessage,
            'timestamp' => FieldValue::serverTimestamp(),
        ]);
    }

    /**
     * Helper function untuk mengubah teks menjadi array kata kunci yang bersih.
     *
     * @param string $text
     * @return array
     */
    private function extractKeywords(string $text): array
    {
        // Hapus tanda baca dan ubah ke huruf kecil
        $text = strtolower(preg_replace('/[^\p{L}\p{N}\s]/u', '', $text));

        // Daftar kata-kata umum (stopwords) dalam Bahasa Indonesia yang akan diabaikan
        $stopwords = ['di', 'ke', 'dari', 'yang', 'dan', 'atau', 'tapi', 'adalah', 'yaitu', 'dengan', 'ini', 'itu', 'saya', 'kamu', 'dia', 'apa', 'siapa', 'kapan', 'dimana', 'bagaimana', 'mengapa', 'tolong', 'jelaskan'];

        // Pisahkan menjadi kata-kata, filter kata kosong, dan hapus stopwords
        $keywords = array_filter(explode(' ', $text));
        $keywords = array_diff($keywords, $stopwords);

        // Kembalikan nilai unik untuk menghindari duplikasi
        return array_values(array_unique($keywords));
    }
}

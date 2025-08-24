<?php

namespace App\Services;

use Google\Cloud\Firestore\FirestoreClient;
use Google\Cloud\Core\Timestamp;
use Illuminate\Support\Facades\Log;

class FirestoreService
{
    protected $db;

    public function __construct()
    {
        try {
            $this->db = new FirestoreClient([
                'keyFilePath' => storage_path(env('FIREBASE_CREDENTIALS')),
                'projectId'   => env('FIREBASE_PROJECT_ID'),
            ]);
        } catch (\Exception $e) {
            Log::critical("Koneksi ke Firestore Gagal: " . $e->getMessage());
            $this->db = null;
        }
    }

    /**
     * Simpan log percakapan user ↔ chatbot
     */
    public function addChatLog(string $sessionId, string $question, string $answer, string $source, $userId = null)
    {
        if (!$this->db) return;

        $this->db->collection('chat_logs')->add([
            'session_id' => $sessionId,
            'question'   => $question,
            'answer'     => $answer,
            'source'     => $source, // "dialogflow" | "firestore" | "openai"
            'timestamp'  => new Timestamp(new \DateTime()),
            'user_id'    => $userId,
        ]);
    }

    /**
     * Cari jawaban di knowledge base berdasarkan kemiripan teks.
     * Menggunakan similar_text() untuk kesederhanaan.
     * Untuk produksi, pertimbangkan search engine seperti Algolia atau vector database.
     */
    public function searchKnowledgeBase(string $query, float $threshold = 75.0)
    {
        if (!$this->db) return null;

        $collection = $this->db->collection('knowledge_base');
        $documents = $collection->documents();

        $bestMatch = null;
        $highestSimilarity = 0;

        foreach ($documents as $doc) {
            if (!$doc->exists()) continue;

            $data = $doc->data();
            $question = $data['question'] ?? '';

            // Hitung persentase kemiripan
            similar_text(strtolower($query), strtolower($question), $percent);

            if ($percent > $highestSimilarity && $percent >= $threshold) {
                $highestSimilarity = $percent;
                $bestMatch = $data['answer'];
            }
        }

        return $bestMatch;
    }

    /**
     * Tambah entri baru ke knowledge base.
     */
    public function addKnowledgeBase(string $question, string $answer)
    {
        if (!$this->db) return;

        // Cek dulu apakah pertanyaan serupa sudah ada untuk menghindari duplikasi
        // Threshold tinggi (95%) berarti harus sangat mirip untuk dianggap duplikat.
        if ($this->searchKnowledgeBase($question, 95.0)) {
            Log::info("Knowledge base untuk '{$question}' sudah ada, tidak ditambahkan.");
            return;
        }

        $this->db->collection('knowledge_base')->add([
            'question'   => $question,
            'answer'     => $answer,
            'source'     => 'openai', // Menandakan ini hasil dari AI
            'createdAt'  => new Timestamp(new \DateTime()),
        ]);
    }
}

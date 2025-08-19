<?php

namespace App\Services;

use Google\Cloud\Firestore\FirestoreClient;
use Google\Cloud\Core\Timestamp;

class FirestoreService
{
    protected $db;

    public function __construct()
    {
        $this->db = new FirestoreClient([
            'keyFilePath' => base_path(env('FIREBASE_CREDENTIALS')),
            'projectId'   => env('FIREBASE_PROJECT_ID', 'chatbot'),
        ]);
    }

    /**
     * Simpan log percakapan user ↔ chatbot
     */
    public function addChatLog(string $sessionId, string $question, string $answer, string $source, $userId = null)
    {
        return $this->db->collection('chat_logs')->add([
            'session_id' => $sessionId,
            'question'   => $question,
            'answer'     => $answer,
            'source'     => $source, // "dialogflow" | "firebase" | "openai"
            'timestamp'  => new Timestamp(new \DateTime()),
            'user_id'    => $userId,
        ]);
    }

    /**
     * Simpan error log (ketika ada kegagalan)
     */
    public function addErrorLog(string $errorMessage, string $userMessage = null, $userId = null)
    {
        return $this->db->collection('error_logs')->add([
            'error_message' => $errorMessage,
            'user_message'  => $userMessage,
            'timestamp'     => new Timestamp(new \DateTime()),
            'user_id'       => $userId,
        ]);
    }

    /**
     * Update system metrics per hari (total query, success, fallback)
     */
    public function updateSystemMetrics(string $date, array $updates)
    {
        $docRef = $this->db->collection('system_metrics')->document($date);
        return $docRef->set($updates, ['merge' => true]);
    }

    /**
     * Tambah knowledge base baru dari fallback OpenAI
     */
    public function addKnowledgeBase(string $question, string $answer, string $category = 'general')
    {
        return $this->db->collection('knowledge_base')->add([
            'question'   => $question,
            'answer'     => $answer,
            'category'   => $category, // Berdasarkan 4 kategori dari proposal: beasiswa, keanggotaan, bank_indonesia, faq
            'timestamp'  => new Timestamp(new \DateTime()),
        ]);
    }

    /**
     * Cari jawaban serupa di knowledge_base berdasarkan similarity
     * Menggunakan Levenshtein distance untuk similarity sederhana
     */
    public function searchKnowledgeBase(string $query, float $threshold = 80.0)
    {
        $collection = $this->db->collection('knowledge_base');
        $documents = $collection->documents();

        $bestMatch = null;
        $bestSimilarity = 0;

        foreach ($documents as $doc) {
            $data = $doc->data();
            $question = $data['question'] ?? '';

            // Hitung similarity (persentase)
            similar_text(strtolower($query), strtolower($question), $percent);

            if ($percent > $bestSimilarity && $percent >= $threshold) {
                $bestSimilarity = $percent;
                $bestMatch = $data['answer'];
            }
        }

        return $bestMatch;
    }
}

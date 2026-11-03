<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Cbf_lib {

    // Menghitung Cosine Similarity antara dua vektor array
    private function cosine_similarity($vec1, $vec2) {
        $dotProduct = 0;
        $normA = 0;
        $normB = 0;

        foreach ($vec1 as $key => $valA) {
            $valB = isset($vec2[$key]) ? $vec2[$key] : 0;
            $dotProduct += ($valA * $valB);
            $normA += pow($valA, 2);
        }

        foreach ($vec2 as $valB) {
            $normB += pow($valB, 2);
        }

        if ($normA == 0 || $normB == 0) return 0;
        return $dotProduct / (sqrt($normA) * sqrt($normB));
    }

    // Fungsi utama mendapatkan rekomendasi
    public function get_recommendations($target_doc, $all_docs) {
        $corpus = [];
        $corpus['target'] = $target_doc;
        
        foreach ($all_docs as $doc) {
            $corpus[$doc['id']] = $doc['clean_description']; 
            // Kita pakai clean_description agar tidak pre-processing ulang tiap kali load
        }

        // 1. Hitung Term Frequency (TF)
        $tf = [];
        $df = []; // Document Frequency
        $totalDocs = count($corpus);

        foreach ($corpus as $doc_id => $text) {
            $words = explode(' ', $text);
            $word_count = array_count_values($words);
            $tf[$doc_id] = $word_count;

            // Hitung DF (berapa banyak dokumen yang mengandung kata ini)
            $unique_words = array_unique($words);
            foreach ($unique_words as $word) {
                if (!isset($df[$word])) $df[$word] = 0;
                $df[$word]++;
            }
        }

        // 2. Hitung Inverse Document Frequency (IDF)
        $idf = [];
        foreach ($df as $word => $freq) {
            $idf[$word] = log10($totalDocs / $freq);
        }

        // 3. Hitung Matriks TF-IDF
        $tfidf = [];
        foreach ($tf as $doc_id => $words) {
            foreach ($words as $word => $count) {
                // Bobot TF = jumlah kemunculan kata / total kata dalam dokumen (opsional dinormalisasi)
                // Di sini kita pakai jumlah kemunculan absolut (raw count) dikali IDF
                $tfidf[$doc_id][$word] = $count * $idf[$word];
            }
        }

        // 4. Hitung Kemiripan (Cosine Similarity)
        $similarities = [];
        $target_vector = $tfidf['target'];

        foreach ($all_docs as $doc) {
            $doc_id = $doc['id'];
            $similarity = $this->cosine_similarity($target_vector, $tfidf[$doc_id]);
            $similarities[] = [
                'wisata' => $doc,
                'similarity_score' => $similarity
            ];
        }

        // 5. Urutkan berdasarkan similarity tertinggi
        usort($similarities, function($a, $b) {
            return $b['similarity_score'] <=> $a['similarity_score'];
        });

        // KUNCI PERBAIKAN: Kembalikan SEMUA data yang sudah diurutkan. 
        // Pemotongan jumlah Top N akan dilakukan di Controller.
        return $similarities;
    }
}
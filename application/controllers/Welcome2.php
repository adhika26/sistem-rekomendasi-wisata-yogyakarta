<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Welcome2 extends CI_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->model('Wisata_model');
    }

    private function tokenize($text) {
        $text = trim($text);
        if (empty($text)) return [];
        return explode(' ', $text);
    }

    public function index() {
        // 1. AMBIL SELURUH DATA DARI DATABASE
        $all_documents = $this->Wisata_model->get_all_wisata();
        
        if (count($all_documents) < 6) {
            die("Dataset wisata Anda kurang dari 6.");
        }

        // TENTUKAN TARGET (Misal Embung Tambak Boyo, asumsikan index 0, sesuaikan jika beda ID)
        $target_doc = $all_documents[9]; 
        $target_id = $target_doc['id'];

        // 2. HITUNG GLOBAL TF & DF (DARI SELURUH DATABASE) AGAR NILAI IDF SAMA DENGAN SISTEM RIIL
        $tf_global = [];
        $df_global = [];
        $total_docs = count($all_documents); // N = Total Semua Data

        foreach ($all_documents as $doc) {
            $id = $doc['id'];
            $text = !empty($doc['clean_description']) ? $doc['clean_description'] : $doc['description'];
            $words = $this->tokenize($text);
            $total_words = count($words);
            
            if ($total_words > 0) {
                $word_counts = array_count_values($words); 
                foreach ($word_counts as $word => $count) {
                    if (empty(trim($word))) continue;
                    
                    $tf_global[$id][$word] = $count / $total_words; 
                    
                    if (!isset($df_global[$word])) $df_global[$word] = 0;
                    $df_global[$word]++;
                }
            }
        }

        // 3. HITUNG GLOBAL IDF & VEKTOR TF-IDF
        $idf_global = [];
        foreach ($df_global as $word => $df) {
            // Rumus IDF = log10(Total Semua Data / Dokumen Frequency)
            $idf_global[$word] = log10($total_docs / $df); 
        }

        $tfidf_vectors = [];
        foreach ($all_documents as $doc) {
            $id = $doc['id'];
            if (isset($tf_global[$id])) {
                foreach ($tf_global[$id] as $word => $tf) {
                    $tfidf_vectors[$id][$word] = $tf * $idf_global[$word];
                }
            }
        }

        // 4. HITUNG COSINE SIMILARITY TARGET VS SEMUA DOKUMEN (SEPERTI CBF_LIB.PHP)
        $target_vector = isset($tfidf_vectors[$target_id]) ? $tfidf_vectors[$target_id] : [];
        $mag_target = 0;
        foreach ($target_vector as $val) $mag_target += pow($val, 2);
        $mag_target = sqrt($mag_target);

        $similarities = [];
        $magnitudes = [];
        $dot_products = [];

        foreach ($all_documents as $doc) {
            $id = $doc['id'];
            if ($id == $target_id) continue; // Jangan bandingkan dengan diri sendiri

            $doc_vector = isset($tfidf_vectors[$id]) ? $tfidf_vectors[$id] : [];
            
            $dot_product = 0;
            $mag_doc = 0;

            foreach ($doc_vector as $val) $mag_doc += pow($val, 2);
            $mag_doc = sqrt($mag_doc);

            // Hitung Dot Product hanya pada kata yang beririsan dengan Target
            foreach ($target_vector as $word => $val_target) {
                if (isset($doc_vector[$word])) {
                    $dot_product += ($val_target * $doc_vector[$word]);
                }
            }

            $magnitudes[$id] = $mag_doc;
            $dot_products[$id] = $dot_product;

            if ($mag_target * $mag_doc > 0) {
                $similarities[$id] = $dot_product / ($mag_target * $mag_doc);
            } else {
                $similarities[$id] = 0;
            }
        }

        // 5. AMBIL 5 TERATAS (TOP 5) UNTUK DITAMPILKAN DI BAB 4
        arsort($similarities); // Urutkan dari terbesar ke terkecil
        $top_5_ids = array_slice(array_keys($similarities), 0, 5);

        // Siapkan Label untuk HTML (D0 = Target, D1-D5 = Top 5)
        $doc_labels = ['D0_TARGET' => $target_doc['nama']];
        $display_vectors = ['D0_TARGET' => $target_vector];
        $display_magnitudes = ['D0_TARGET' => $mag_target];
        
        $idx = 1;
        foreach ($top_5_ids as $id) {
            $doc_info = array_filter($all_documents, function($d) use ($id) { return $d['id'] == $id; });
            $doc_info = reset($doc_info);
            
            $lbl = 'D'.$idx;
            $doc_labels[$lbl] = $doc_info['nama'];
            $display_vectors[$lbl] = $tfidf_vectors[$id];
            $display_magnitudes[$lbl] = $magnitudes[$id];
            $idx++;
        }

        // Kumpulkan Kosakata HANYA DARI D0 s/d D5 agar tabel tidak ribuan kolom
        $display_terms = [];
        foreach ($display_vectors as $vec) {
            foreach (array_keys($vec) as $word) {
                $display_terms[$word] = true;
            }
        }
        $display_terms = array_keys($display_terms);
        sort($display_terms);

        // =========================================================================
        // MENCETAK TAMPILAN HTML UNTUK BAB 4
        // =========================================================================
        echo '<!DOCTYPE html><html lang="id"><head><title>Perhitungan Cosine Similarity Bab 4</title>';
        echo '<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">';
        echo '<style>
                body { padding: 30px; background-color: #f8f9fa; }
                .table-container { background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.05); margin-bottom: 40px; overflow-x: auto;}
                th { background-color: #0d6efd !important; color: white !important; white-space: nowrap; text-align: center; }
                td { font-size: 0.85rem; text-align: right; }
                td:first-child { text-align: left; }
              </style>';
        echo '<script id="MathJax-script" async src="https://cdn.jsdelivr.net/npm/mathjax@3/es5/tex-mml-chtml.js"></script>';
        echo '</head><body>';
        
        echo '<div class="container">';
        echo '<h2 class="text-center fw-bold text-primary mb-3">Cosine Similarity</h2>';

        // Legenda
        echo '<div class="table-container">';
        echo '<h5 class="fw-bold text-primary">Keterangan Dokumen Terpilih</h5>';
        echo '<ul class="list-group mb-3">';
        echo '<li class="list-group-item list-group-item-warning"><strong>D0 (Target) :</strong> ' . $doc_labels['D0_TARGET'] . '</li>';
        for ($i=1; $i<=5; $i++) {
            echo '<li class="list-group-item"><strong>D'.$i.' (Ranking '.$i.') :</strong> ' . $doc_labels['D'.$i] . '</li>';
        }
        echo '</ul></div>';

        // Tahap 1: Vektor TF-IDF (Subset Table)
        echo '<div class="table-container">';
        echo '<h5 class="fw-bold">Tahap 1: Pembentukan Vektor TF-IDF (Ditampilkan sebagian)</h5>';
        echo '<p class="small text-muted">Hanya menampilkan term/kata yang muncul pada dokumen Target dan Top 5 agar tabel dapat dibaca.</p>';
        echo '<div style="max-height: 400px; overflow-y: auto;">';
        echo '<table class="table table-bordered table-sm table-hover">';
        echo '<thead style="position: sticky; top: 0; z-index: 1;"><tr><th>Term (Kata)</th><th class="bg-warning text-dark !important">D0 (Target)</th><th>D1</th><th>D2</th><th>D3</th><th>D4</th><th>D5</th></tr></thead><tbody>';
        
        foreach ($display_terms as $term) {
            echo '<tr><td><strong>'.$term.'</strong></td>';
            $val_t = isset($display_vectors['D0_TARGET'][$term]) ? $display_vectors['D0_TARGET'][$term] : 0;
            echo '<td class="bg-warning bg-opacity-10 fw-bold">'.round($val_t, 4).'</td>';
            
            for ($i=1; $i<=5; $i++) {
                $val_s = isset($display_vectors['D'.$i][$term]) ? $display_vectors['D'.$i][$term] : 0;
                $color = ($val_t > 0 && $val_s > 0) ? 'text-success fw-bold' : 'text-muted';
                echo '<td class="'.$color.'">'.round($val_s, 4).'</td>';
            }
            echo '</tr>';
        }
        echo '</tbody></table></div></div>';

        // Tahap 2: Perhitungan Komponen Cosine
        echo '<div class="table-container">';
        echo '<h5 class="fw-bold">Tahap 2: Perhitungan Komponen Cosine</h5>';
        echo '<table class="table table-bordered text-center">';
        echo '<thead><tr>
                <th>Pembanding</th>
                <th>Dot Product ($A \cdot B$)</th>
                <th>Panjang Vektor Target ($|A|$)</th>
                <th>Panjang Vektor Sampel ($|B|$)</th>
              </tr></thead><tbody>';
        
        $idx = 1;
        foreach ($top_5_ids as $id) {
            $lbl = 'D'.$idx;
            echo '<tr>';
            echo '<td><strong>D0 vs '.$lbl.'</strong></td>';
            echo '<td>'.round($dot_products[$id], 6).'</td>';
            echo '<td class="bg-light">'.round($display_magnitudes['D0_TARGET'], 6).'</td>';
            echo '<td>'.round($display_magnitudes[$lbl], 6).'</td>';
            echo '</tr>';
            $idx++;
        }
        echo '</tbody></table></div>';

        // Tahap 3: Hasil Akhir
        echo '<div class="table-container">';
        echo '<h5 class="fw-bold text-success">Tahap 3: Hasil Akhir Kemiripan (Similarity)</h5>';
        echo '<table class="table table-bordered table-striped text-center align-middle">';
        echo '<thead><tr class="table-success">
                <th>Ranking</th>
                <th>Simbol</th>
                <th>Nama Wisata</th>
                <th>Skor Akhir (1.0)</th>
                <th>Persentase</th>
              </tr></thead><tbody>';
        
        $idx = 1;
        foreach ($top_5_ids as $id) {
            $lbl = 'D'.$idx;
            $sim = $similarities[$id];
            $persen = round($sim * 100, 2);
            
            echo '<tr>';
            echo '<td><span class="badge bg-primary rounded-circle p-2">#'.$idx.'</span></td>';
            echo '<td><strong>'.$lbl.'</strong></td>';
            echo '<td class="text-start">'.$doc_labels[$lbl].'</td>';
            echo '<td class="fw-bold">'.round($sim, 6).'</td>';
            echo '<td><span class="badge '.($persen > 0 ? 'bg-success' : 'bg-secondary').'">'.$persen.'%</span></td>';
            echo '</tr>';
            $idx++;
        }
        echo '</tbody></table></div>';

        echo '</div></body></html>';
    }
}
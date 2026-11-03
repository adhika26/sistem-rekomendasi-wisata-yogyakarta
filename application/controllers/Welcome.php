<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Welcome extends CI_Controller {

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
        // 1. Ambil seluruh kumpulan dokumen (Korpus)
        $documents = $this->Wisata_model->get_all_wisata(); 

        $tf_documents = [];
        $all_terms = [];
        $doc_labels = []; 

        // 2. TAHAP 1: Menghitung TF (Term Frequency) ke SEMUA DOKUMEN
        $doc_index = 1;
        foreach ($documents as $doc) {
            $text = !empty($doc['clean_description']) ? $doc['clean_description'] : $this->tokenize($doc['description']);
            
            $label = "D" . $doc_index; 
            $doc_labels[$label] = $doc['nama']; 

            $words = $this->tokenize($text);
            $total_words = count($words);
            
            if ($total_words > 0) {
                $word_counts = array_count_values($words); 
                
                foreach ($word_counts as $word => $count) {
                    if (empty(trim($word))) continue;
                    
                    $tf_documents[$label][$word] = $count / $total_words; 
                    $all_terms[$word] = true;
                }
            }
            $doc_index++;
        }

        $all_terms = array_keys($all_terms);
        sort($all_terms); 
        $total_docs = count($doc_labels);

        // TAHAP 2: Menghitung DF dan IDF ke SEMUA DOKUMEN
        $idf_scores = [];
        $df_scores = []; 

        foreach ($all_terms as $term) {
            $doc_count_with_term = 0;
            
            foreach ($doc_labels as $label => $nama) {
                if (isset($tf_documents[$label][$term])) {
                    $doc_count_with_term++;
                }
            }
            
            $df_scores[$term] = $doc_count_with_term;
            $idf_scores[$term] = log10($total_docs / $doc_count_with_term); 
        }

        // =========================================================================
        // PERSIAPAN UNTUK SAMPEL 10 DATA SAJA
        // =========================================================================
        // Ambil label dari 10 dokumen pertama (D1 sampai D10)
        $sample_doc_labels = array_slice($doc_labels, 0, 10, true);
        
        // Ambil HANYA term/kata yang muncul di 10 dokumen ini agar tabel tidak ribuan baris
        $sample_terms = [];
        foreach ($sample_doc_labels as $label => $nama) {
            if (isset($tf_documents[$label])) {
                foreach (array_keys($tf_documents[$label]) as $word) {
                    $sample_terms[$word] = true;
                }
            }
        }
        $sample_terms = array_keys($sample_terms);
        sort($sample_terms);


        // =========================================================================
        // MULAI MENCETAK TAMPILAN HTML
        // =========================================================================
        echo '<!DOCTYPE html><html lang="id"><head><title>Perhitungan TF-IDF Bab 4</title>';
        echo '<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">';
        echo '<style>
                body { padding: 30px; background-color: #f8f9fa; font-family: sans-serif; }
                .table-container { overflow-x: auto; background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.05); margin-bottom: 40px; }
                th { background-color: #0d6efd !important; color: white !important; white-space: nowrap; }
                td { white-space: nowrap; font-size: 0.85rem; }
                .section-title { border-bottom: 3px solid #0d6efd; padding-bottom: 10px; margin-top: 50px; margin-bottom: 20px; font-weight: bold; }
              </style></head><body>';
        
        echo '<div class="container-fluid">';
        echo '<h2 class="text-center fw-bold text-primary mb-4">Perhitungan TF-IDF</h2>';

        // =========================================================================
        // BAGIAN A: SAMPEL 10 DATA UNTUK BAB 4 UTAMA
        // =========================================================================
        

        // --- TABEL TF-IDF SAMPEL ---
        echo '<div class="table-container">';
        echo '<h4>Pembobotan Akhir TF-IDF (Hanya 10 Data)</h4>';
        echo '<p class="small text-muted">Hanya menampilkan Term/Kata yang muncul pada dokumen D1 - D10.</p>';
        echo '<div style="max-height: 500px; overflow-y: auto;">';
        echo '<table class="table table-bordered table-sm table-hover">';
        echo '<thead style="position: sticky; top: 0; z-index: 1;"><tr><th>Term (Kata)</th>';
        foreach ($sample_doc_labels as $label => $nama) echo "<th>{$label}</th>";
        echo '</tr></thead><tbody>';
        
        foreach ($sample_terms as $term) {
            echo '<tr><td><strong>' . $term . '</strong></td>';
            foreach ($sample_doc_labels as $label => $nama) {
                $tf = isset($tf_documents[$label][$term]) ? $tf_documents[$label][$term] : 0;
                $tfidf = $tf * $idf_scores[$term]; 
                
                $color = $tfidf > 0 ? 'text-danger fw-bold' : 'text-muted';
                echo '<td class="'.$color.'">' . round($tfidf, 4) . '</td>';
            }
            echo '</tr>';
        }
        echo '</tbody></table></div></div>';


        // =========================================================================
        // KESELURUHAN DATA UNTUK LAMPIRAN
        // =========================================================================
        echo '<h3 class="section-title mt-5">KESELURUHAN DATA</h3>';

        // --- TABEL 3: TF-IDF FULL ---
        echo '<div class="table-container">';
        echo '<h4>Pembobotan TF-IDF</h4>';
        echo '<div style="max-height: 500px; overflow-y: auto;">';
        echo '<table class="table table-bordered table-sm table-hover">';
        echo '<thead style="position: sticky; top: 0; z-index: 1;"><tr><th>Term (Kata)</th>';
        foreach ($doc_labels as $label => $nama) echo "<th>{$label}</th>";
        echo '</tr></thead><tbody>';
        foreach ($all_terms as $term) {
            echo '<tr><td><strong>' . $term . '</strong></td>';
            foreach ($doc_labels as $label => $nama) {
                $tf = isset($tf_documents[$label][$term]) ? $tf_documents[$label][$term] : 0;
                $tfidf = $tf * $idf_scores[$term];
                
                $color = $tfidf > 0 ? 'text-success fw-bold' : 'text-muted';
                echo '<td class="'.$color.'">' . round($tfidf, 4) . '</td>';
            }
            echo '</tr>';
        }
        echo '</tbody></table></div></div>';

        echo '</div></body></html>';
    }
}
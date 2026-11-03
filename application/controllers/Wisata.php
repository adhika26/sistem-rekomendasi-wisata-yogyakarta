<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Wisata extends CI_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->model('Wisata_model');
        $this->load->library('Nlp_lib');
        $this->load->library('Cbf_lib');
    }

    // Fungsi khusus untuk Bab 4: Pengujian Preprocessing
    public function pre() {
        // 1. Ambil 5 data terakhir dari database (berdasarkan ID terbaru)
        $wisata_terakhir = $this->db->order_by('id', 'DESC')->limit(5)->get('wisata')->result_array();
        
        if (empty($wisata_terakhir)) {
            die("Dataset wisata masih kosong. Silakan tambahkan data terlebih dahulu.");
        }

        // 2. Siapkan array sampel teks dari database
        $sampel_teks = [];
        foreach ($wisata_terakhir as $w) {
            // Menggabungkan nama dan deskripsi, persis seperti di fungsi detail()
            $sampel_teks[] = $w['nama'] . " " . $w['description'];
        }

        // Proses semua data terlebih dahulu dan simpan dalam array
        $data_raw = [];
        $data_cf = [];
        $data_cl = [];
        $data_sr = [];
        $data_stem = [];

        foreach($sampel_teks as $i => $val) {
            $data_raw[$i] = $val;
            $data_cf[$i] = $this->nlp_lib->casefolding($val);
            $data_cl[$i] = $this->nlp_lib->cleaning($val);
            $data_sr[$i] = $this->nlp_lib->stopwordremoval($val);
            $data_stem[$i] = $this->nlp_lib->preprocess($val);
        }

        // Menghitung jumlah data aktual (berjaga-jaga jika data di DB kurang dari 5)
        $jumlah_data = count($sampel_teks);

        // Styling HTML
        echo '<!DOCTYPE html><html lang="id"><head><title>Pengujian Preprocessing</title>';
        echo '<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">';
        echo '<style> 
                body { padding: 40px 20px; background-color: #f8f9fa; font-family: sans-serif; } 
                .table-title { border-left: 4px solid #0d6efd; padding-left: 10px; margin-top: 40px; margin-bottom: 15px; font-weight: bold; color: #333; }
                th { background-color: #0d6efd !important; color: white !important; text-align: center; }
                td { vertical-align: middle; }
              </style>';
        echo '</head><body>';
        echo '<div class="container" style="max-width: 1000px; background: white; padding: 30px; border-radius: 10px; box-shadow: 0 4px 15px rgba(0,0,0,0.05);">';
        echo '<h2 class="text-center fw-bold text-primary mb-2">Pengujian Text Preprocessing</h2>';
        echo '<p class="text-center text-muted border-bottom pb-4">Menampilkan transformasi tahap demi tahap menggunakan '.$jumlah_data.' data terakhir dari database.</p>';

        // ==========================================
        // TABEL 1: CASE FOLDING
        // ==========================================
        echo '<h4 class="table-title">Tahap 1: Case Folding</h4>';
        echo '<p class="small text-muted mb-2">Mengubah seluruh huruf menjadi huruf kecil (lowercase).</p>';
        echo '<table class="table table-bordered table-striped">';
        echo '<thead><tr><th width="5%">No</th><th width="47%">Input (Raw Data)</th><th width="48%">Output (Case Folding)</th></tr></thead><tbody>';
        for($i=0; $i<$jumlah_data; $i++) {
            echo '<tr><td class="text-center">'.($i+1).'</td><td>'.$data_raw[$i].'</td><td class="text-primary">'.$data_cf[$i].'</td></tr>';
        }
        echo '</tbody></table>';

        // ==========================================
        // TABEL 2: CLEANING
        // ==========================================
        echo '<h4 class="table-title">Tahap 2: Cleaning</h4>';
        echo '<p class="small text-muted mb-2">Menghapus angka, tanda baca, dan karakter selain huruf alfabet (a-z).</p>';
        echo '<table class="table table-bordered table-striped">';
        echo '<thead><tr><th width="5%">No</th><th width="47%">Input (Dari Case Folding)</th><th width="48%">Output (Cleaning)</th></tr></thead><tbody>';
        for($i=0; $i<$jumlah_data; $i++) {
            echo '<tr><td class="text-center">'.($i+1).'</td><td>'.$data_cf[$i].'</td><td class="text-primary">'.$data_cl[$i].'</td></tr>';
        }
        echo '</tbody></table>';

        // ==========================================
        // TABEL 3: STOPWORD REMOVAL
        // ==========================================
        echo '<h4 class="table-title">Tahap 3: Stopword Removal</h4>';
        echo '<p class="small text-muted mb-2">Menghilangkan kata hubung atau kata tidak penting (di, ke, dari, yang, dll).</p>';
        echo '<table class="table table-bordered table-striped">';
        echo '<thead><tr><th width="5%">No</th><th width="47%">Input (Dari Cleaning)</th><th width="48%">Output (Stopword Removal)</th></tr></thead><tbody>';
        for($i=0; $i<$jumlah_data; $i++) {
            echo '<tr><td class="text-center">'.($i+1).'</td><td>'.$data_cl[$i].'</td><td class="text-primary">'.$data_sr[$i].'</td></tr>';
        }
        echo '</tbody></table>';

        // ==========================================
        // TABEL 4: STEMMING
        // ==========================================
        echo '<h4 class="table-title">Tahap 4: Stemming</h4>';
        echo '<p class="small text-muted mb-2">Mengembalikan kata berimbuhan menjadi kata dasar menggunakan Sastrawi.</p>';
        echo '<table class="table table-bordered table-striped">';
        echo '<thead><tr><th width="5%">No</th><th width="47%">Input (Dari Stopword)</th><th width="48%">Output Final (Stemming)</th></tr></thead><tbody>';
        for($i=0; $i<$jumlah_data; $i++) {
            echo '<tr><td class="text-center">'.($i+1).'</td><td>'.$data_sr[$i].'</td><td class="text-success fw-bold">'.$data_stem[$i].'</td></tr>';
        }
        echo '</tbody></table>';

        echo '</div></body></html>';
    }
    
    public function detail($id) {
        $data['wisata'] = $this->Wisata_model->get_wisata_by_id($id);
        
        if(!$data['wisata']) {
            show_404();
        }

        // --- SKENARIO IDEAL (OPTIMASI) ---
        // Jika clean_description kosong di DB, lakukan preprocessing dan simpan ke DB.
        if (empty($data['wisata']['clean_description'])) {
            $gabungan_teks = $data['wisata']['nama'] . " " . $data['wisata']['description'];
            $clean_text = $this->nlp_lib->preprocess($gabungan_teks);
            
            $this->db->where('id', $id)->update('wisata', ['clean_description' => $clean_text]);
            $data['wisata']['clean_description'] = $clean_text;
        }

        $target_doc = $data['wisata']['clean_description'];
        $all_other_docs = $this->Wisata_model->get_all_wisata_except($id);

        // --- PREPROCESS DOKUMEN LAIN JIKA MASIH KOSONG ---
        foreach ($all_other_docs as &$doc) {
            if (empty($doc['clean_description'])) {
                $teks = $doc['nama'] . " " . $doc['description'];
                $doc['clean_description'] = $this->nlp_lib->preprocess($teks);
                $this->db->where('id', $doc['id'])->update('wisata', ['clean_description' => $doc['clean_description']]);
            }
        }

        // --- AMBIL REKOMENDASI DAN POTONG JADI 5 SAJA ---
        // 1. Ambil semua rekomendasi dari library
        $semua_rekomendasi = $this->cbf_lib->get_recommendations($target_doc, $all_other_docs);
        
        // 2. Potong array menjadi 5 data teratas saja untuk ditampilkan
        $data['rekomendasi'] = array_slice($semua_rekomendasi, 0, 5);

        $this->load->view('detail_view', $data);
    }
}
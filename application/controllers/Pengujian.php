<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Pengujian extends CI_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->model('Wisata_model');
        $this->load->library('Nlp_lib');
        $this->load->library('Cbf_lib');
    }

    public function index() {
        // 1. Ambil semua data wisata untuk ditampilkan di Dropdown pilihan
        $data['list_wisata'] = $this->db->order_by('nama', 'ASC')->get('wisata')->result_array();
        
        // 2. Tangkap inputan dari form
        $id_target = $this->input->post('id_wisata');
        $k_value = 10; // AUTO K = 5 (Tidak lagi mengambil dari dropdown)

        $data['hasil_uji'] = null;

        // 3. Jika ada wisata spesifik yang dipilih untuk diuji
        if ($id_target) {
            $wisata_target = $this->Wisata_model->get_wisata_by_id($id_target);
            $target_type = $wisata_target['type'];

            // --- PROSES CBF ---
            if (empty($wisata_target['clean_description'])) {
                $gabungan_teks = $wisata_target['nama'] . " " . $wisata_target['description'];
                $clean_text = $this->nlp_lib->preprocess($gabungan_teks);
                $this->db->where('id', $id_target)->update('wisata', ['clean_description' => $clean_text]);
                $target_doc = $clean_text;
            } else {
                $target_doc = $wisata_target['clean_description'];
            }

            $all_other_docs = $this->Wisata_model->get_all_wisata_except($id_target);

            foreach ($all_other_docs as &$doc) {
                if (empty($doc['clean_description'])) {
                    $teks = $doc['nama'] . " " . $doc['description'];
                    $doc['clean_description'] = $this->nlp_lib->preprocess($teks);
                    $this->db->where('id', $doc['id'])->update('wisata', ['clean_description' => $doc['clean_description']]);
                }
            }

            // Ambil 5 Rekomendasi Teratas
            $semua_rekomendasi = $this->cbf_lib->get_recommendations($target_doc, $all_other_docs);
            $rekomendasi_top_k = array_slice($semua_rekomendasi, 0, $k_value);

            // --- EVALUASI P@5 ---
            $jumlah_relevan = 0;
            $list_rekomendasi_detail = [];

            foreach ($rekomendasi_top_k as $rek) {
                $is_relevant = ($rek['wisata']['type'] == $target_type);
                
                if ($is_relevant) {
                    $jumlah_relevan++;
                }

                $list_rekomendasi_detail[] = [
                    'nama' => $rek['wisata']['nama'],
                    'kategori' => $rek['wisata']['type'],
                    'score' => round($rek['similarity_score'] * 100, 2),
                    'is_relevant' => $is_relevant
                ];
            }

            // Hitung Score Akhir Precision
            $precision_score = ($k_value > 0) ? ($jumlah_relevan / $k_value) : 0;

            // Simpan Hasil untuk ditampilkan
            $data['hasil_uji'] = [
                'target_nama' => $wisata_target['nama'],
                'target_kategori' => $target_type,
                'k_value' => $k_value,
                'jumlah_relevan' => $jumlah_relevan,
                'precision_score' => $precision_score,
                'detail_rekomendasi' => $list_rekomendasi_detail
            ];
            
            // Menyimpan ID yang dipilih agar dropdown tidak ter-reset
            $data['id_terpilih'] = $id_target;
        }

        $this->load->view('pengujian_view', $data);
    }
}
<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Home extends CI_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->model('Wisata_model');
    }

    // Menampilkan halaman utama
    public function index() {
        $this->load->view('home_view');
    }

    // Menerima request AJAX dari browser pengguna (Geolocation API)
    public function get_nearest() {
        $lat = $this->input->post('latitude');
        $lng = $this->input->post('longitude');

        if($lat && $lng) {
            // Ambil 5 wisata terdekat
            $wisata = $this->Wisata_model->get_nearest_wisata($lat, $lng, 5);
            echo json_encode(['status' => 'success', 'data' => $wisata]);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Koordinat tidak valid']);
        }
    }

    // Autocomplete pencarian nama wisata
    public function search_autocomplete() {
        $keyword = trim($this->input->get('q'));

        if ($keyword === '') {
            echo json_encode([]);
            return;
        }

        $hasil = $this->Wisata_model->search_wisata($keyword, 10);
        echo json_encode($hasil);
    }

    public function semua_wisata() {
        $this->load->model('Wisata_model');
        $data['wisata'] = $this->Wisata_model->get_all_wisata();

        // Mengekstrak daftar kategori unik dari data wisata yang ada
        $kategori_unik = [];
        foreach ($data['wisata'] as $w) {
            $kat_asli = !empty($w['nama_kategori']) ? $w['nama_kategori'] : $w['type'];
            // Ubah format penulisan agar rapi (misal: wisata_alam -> Wisata Alam)
            $kat_rapi = ucwords(str_replace(['_', '-'], ' ', $kat_asli));
            
            if (!isset($kategori_unik[$kat_asli])) {
                $kategori_unik[$kat_asli] = $kat_rapi;
            }
        }
        $data['kategori'] = $kategori_unik;

        $this->load->view('semua_wisata_view', $data);
    }
}
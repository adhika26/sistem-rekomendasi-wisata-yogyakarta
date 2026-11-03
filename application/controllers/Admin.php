<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Admin extends CI_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->model('Wisata_model');
        $this->load->library('Nlp_lib');
        
        // --- PROTEKSI HALAMAN ADMIN ---
        if ($this->session->userdata('logged_in') !== TRUE) {
            $this->session->set_flashdata('error', 'Akses ditolak! Anda harus login terlebih dahulu.');
            redirect('index.php/auth');
        }
        // Catatan: Di production, pastikan Anda menambahkan library Session
        // dan melakukan pengecekan login Admin di sini.
        // if(!$this->session->userdata('is_admin')) { redirect('login'); }
    }
    public function logout() {
        $this->session->sess_destroy();
        redirect('index.php/auth');
    }

    // Halaman daftar dataset
    public function index() {
        $data['wisata'] = $this->Wisata_model->get_all_wisata();
        $this->load->view('admin_view', $data);
    }

    // Halaman form tambah data
    public function tambah() {
        // Ambil data kategori untuk dikirim ke view form
        $data['kategori'] = $this->Wisata_model->get_all_kategori();
        $this->load->view('admin_form_view', $data);
    }

    // Halaman form edit data
    public function edit($id) {
        $wisata = $this->Wisata_model->get_wisata_by_id($id);

        if (!$wisata) {
            show_404();
        }

        $data['wisata'] = $wisata;
        $data['kategori'] = $this->Wisata_model->get_all_kategori();
        $this->load->view('admin_form_view', $data);
    }

    // Proses update data (termasuk Preprocessing NLP)
    public function update($id) {
        $wisata = $this->Wisata_model->get_wisata_by_id($id);

        if (!$wisata) {
            show_404();
        }

        try {
            $uploaded_image = $this->upload_image();
            $image = ($uploaded_image !== null) ? $uploaded_image : $wisata['image'];
        } catch (Exception $e) {
            $this->session->set_flashdata('admin_error', $e->getMessage());
            redirect('index.php/admin/edit/' . $id);
        }

        $nama = $this->input->post('nama', TRUE);
        $description = $this->input->post('description', TRUE);
        $id_kategori = $this->input->post('id_kategori', TRUE);

        $kategori = $this->db->get_where('kategori', array('id' => $id_kategori))->row_array();
        $type = $kategori ? $kategori['nama_kategori'] : $wisata['type'];

        $clean_description = $this->nlp_lib->preprocess($nama . ' ' . $description);

        $data = [
            'id_kategori' => $id_kategori,
            'nama' => $nama,
            'type' => $type,
            'vote_average' => $this->input->post('vote_average', TRUE),
            'vote_count' => $this->input->post('vote_count', TRUE),
            'htm_weekday' => $this->input->post('htm_weekday', TRUE),
            'htm_weekend' => $this->input->post('htm_weekend', TRUE),
            'latitude' => $this->input->post('latitude', TRUE),
            'longitude' => $this->input->post('longitude', TRUE),
            'image' => $image,
            'description' => $description,
            'clean_description' => $clean_description,
            // Data yang pernah diedit admin tidak akan ditimpa deskripsinya saat sync OSM
            'source' => 'manual'
        ];

        $this->Wisata_model->update_wisata($id, $data);
        $this->session->set_flashdata('admin_success', 'Data wisata berhasil diperbarui.');
        redirect('index.php/admin');
    }

    // Proses simpan data (termasuk Preprocessing NLP)
    public function simpan() {
        try {
            $uploaded_image = $this->upload_image();
        } catch (Exception $e) {
            $this->session->set_flashdata('admin_error', $e->getMessage());
            redirect('index.php/admin/tambah');
        }

        $nama = $this->input->post('nama', TRUE);
        $description = $this->input->post('description', TRUE);

        // --- PROSES NLP SAAT INPUT DATA ---
        // Kita gabungkan nama dan deskripsi untuk di-preprocess
        $gabungan_teks = $nama . " " . $description;
        $clean_description = $this->nlp_lib->preprocess($gabungan_teks);

        $id_kategori = $this->input->post('id_kategori', TRUE);
        $kategori = $this->db->get_where('kategori', array('id' => $id_kategori))->row_array();
        $type = $kategori ? $kategori['nama_kategori'] : '';

        $data = [
            'id_kategori' => $id_kategori, // Mengambil ID Kategori
            'nama' => $nama,
            'type' => $type,
            'vote_average' => $this->input->post('vote_average', TRUE),
            'vote_count' => $this->input->post('vote_count', TRUE),
            'htm_weekday' => $this->input->post('htm_weekday', TRUE),
            'htm_weekend' => $this->input->post('htm_weekend', TRUE),
            'latitude' => $this->input->post('latitude', TRUE),
            'longitude' => $this->input->post('longitude', TRUE),
            'image' => ($uploaded_image !== null) ? $uploaded_image : '',
            'description' => $description,
            'clean_description' => $clean_description, // Simpan teks bersihnya
            'source' => 'manual'
        ];

        $this->Wisata_model->insert_wisata($data);
        $this->session->set_flashdata('admin_success', 'Data wisata berhasil ditambahkan.');
        redirect('index.php/admin');
    }

    // Proses hapus data
    public function hapus($id) {
        $this->Wisata_model->delete_wisata($id);
        redirect('index.php/admin');
    }

    // Upload foto wisata dari komputer
    private function upload_image() {
        if (empty($_FILES['image_file']['name'])) {
            return null;
        }

        $file = $_FILES['image_file'];

        if (!isset($file['error']) || $file['error'] !== UPLOAD_ERR_OK) {
            throw new Exception('Upload foto gagal. Silakan coba lagi.');
        }

        if ($file['size'] > 2 * 1024 * 1024) {
            throw new Exception('Ukuran foto maksimal 2 MB.');
        }

        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $allowed_extensions = array('jpg', 'jpeg', 'png', 'webp');

        if (!in_array($extension, $allowed_extensions, true)) {
            throw new Exception('Format foto harus JPG, JPEG, PNG, atau WEBP.');
        }

        $allowed_mimes = array('image/jpeg', 'image/png', 'image/webp');
        $mime = '';

        if (function_exists('finfo_open')) {
            $finfo = finfo_open(FILEINFO_MIME_TYPE);

            if ($finfo) {
                $mime = finfo_file($finfo, $file['tmp_name']);
                finfo_close($finfo);
            }
        } elseif (function_exists('getimagesize')) {
            $info = getimagesize($file['tmp_name']);
            $mime = $info ? $info['mime'] : '';
        }

        if (!in_array($mime, $allowed_mimes, true)) {
            throw new Exception('File yang diunggah bukan gambar yang valid.');
        }

        $upload_dir = FCPATH . 'assets' . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'wisata' . DIRECTORY_SEPARATOR;

        if (!is_dir($upload_dir) && !mkdir($upload_dir, 0775, true) && !is_dir($upload_dir)) {
            throw new Exception('Folder upload tidak dapat dibuat.');
        }

        $filename = 'wisata_' . date('YmdHis') . '_' . bin2hex(random_bytes(4)) . '.' . $extension;
        $target = $upload_dir . $filename;

        if (!move_uploaded_file($file['tmp_name'], $target)) {
            throw new Exception('Foto gagal disimpan ke folder upload.');
        }

        $url = base_url('assets/uploads/wisata/' . $filename);

        if (strlen($url) > 166) {
            @unlink($target);
            throw new Exception('URL foto terlalu panjang untuk disimpan.');
        }

        return $url;
    }

    // Sinkronisasi data dari OpenStreetMap
    public function sync_osm() {
        if ($this->input->method() !== 'post') {
            show_404();
        }

        set_time_limit(0);
        $this->load->library('Osm_sync_lib');

        try {
            $stats = $this->osm_sync_lib->sync();
            $this->session->set_flashdata('sync_result', $stats);
        } catch (Exception $e) {
            $this->session->set_flashdata('sync_error', $e->getMessage());
        }

        redirect('index.php/admin');
    }
}
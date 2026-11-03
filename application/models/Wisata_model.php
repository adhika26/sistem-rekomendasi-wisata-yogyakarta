<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Wisata_model extends CI_Model {

    // Fungsi untuk mencari wisata terdekat dengan Haversine Formula
    public function get_nearest_wisata($lat, $lng, $limit = 5) {
        // 6371 adalah jari-jari bumi dalam kilometer
        $sql = "SELECT id, nama, image, latitude, longitude,
                (6371 * acos(
                    cos(radians(?)) * cos(radians(latitude)) * cos(radians(longitude) - radians(?)) + 
                    sin(radians(?)) * sin(radians(latitude))
                )) AS distance 
                FROM wisata 
                ORDER BY distance ASC 
                LIMIT ?";
                
        // Binding data untuk mencegah SQL Injection
        $query = $this->db->query($sql, array($lat, $lng, $lat, (int)$limit));
        return $query->result_array();
    }

    // --- TAMBAHAN UNTUK PANEL ADMIN ---

    // Mengambil semua data wisata
    // Mengambil semua data wisata beserta nama kategorinya (JOIN)
    public function get_all_wisata() {
        $this->db->select('wisata.*, kategori.nama_kategori');
        $this->db->from('wisata');
        // Gunakan LEFT JOIN agar data lama yang belum punya kategori tetap muncul
        $this->db->join('kategori', 'kategori.id = wisata.id_kategori', 'left');
        $this->db->order_by('wisata.id', 'ASC');
        return $this->db->get()->result_array();
    }

        public function get_all_wisata_() {
        $this->db->select('wisata.description');
        $this->db->from('wisata');
        // Gunakan LEFT JOIN agar data lama yang belum punya kategori tetap muncul
        $this->db->join('kategori', 'kategori.id = wisata.id_kategori', 'left');
        $this->db->order_by('wisata.id', 'ASC');
        return $this->db->get()->result_array();
    }

    // Mengambil seluruh data wisata apa adanya (untuk proses sinkronisasi)
    public function get_all_wisata_for_sync() {
        return $this->db->get('wisata')->result_array();
    }

    // Memperbarui data wisata
    public function update_wisata($id, $data) {
        $this->db->where('id', $id);
        return $this->db->update('wisata', $data);
    }

    // Menyimpan data wisata baru
    public function insert_wisata($data) {
        return $this->db->insert('wisata', $data);
    }

    // Menghapus data wisata
    public function delete_wisata($id) {
        $this->db->where('id', $id);
        return $this->db->delete('wisata');
    }

    // Pencarian nama wisata untuk autocomplete
    public function search_wisata($keyword, $limit = 10) {
        $this->db->select('wisata.id, wisata.nama, wisata.type, kategori.nama_kategori');
        $this->db->from('wisata');
        $this->db->join('kategori', 'kategori.id = wisata.id_kategori', 'left');
        $this->db->like('wisata.nama', $keyword);
        $this->db->order_by('wisata.nama', 'ASC');
        $this->db->limit((int) $limit);
        return $this->db->get()->result_array();
    }

    // Ambil detail satu wisata
    public function get_wisata_by_id($id) {
        return $this->db->get_where('wisata', ['id' => $id])->row_array();
    }

    // Ambil semua wisata KECUALI yang sedang dibuka (untuk dibandingkan)
    public function get_all_wisata_except($id) {
        $this->db->where('id !=', $id);
        return $this->db->get('wisata')->result_array();
    }

    // Mengambil semua data kategori untuk dropdown
    public function get_all_kategori() {
        return $this->db->get('kategori')->result_array();
    }
}
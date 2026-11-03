<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Welcome3 extends CI_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->model('Wisata_model');
    }

    public function index() {
        // 1. DATA LOKASI ASAL (USER)
        // Kordinat Universitas AMIKOM Yogyakarta
        $user_lokasi = "Universitas AMIKOM Yogyakarta";
        $user_lat = -7.759846;
        $user_lng = 110.398616;

        // Radius Bumi dalam kilometer
        $R = 6371.0;

        // Konversi Kordinat Asal ke Radian
        $lat1_rad = deg2rad($user_lat);
        $lng1_rad = deg2rad($user_lng);

        // 2. AMBIL SELURUH DATA WISATA DARI DATABASE
        $all_documents = $this->Wisata_model->get_all_wisata();
        
        if (count($all_documents) < 5) {
            die("Dataset wisata Anda kurang dari 5. Silakan tambahkan data terlebih dahulu.");
        }

        // 3. HITUNG HAVERSINE KE SELURUH DATA DATABASE (SEPERTI SISTEM RIIL)
        $all_calculations = [];

        foreach ($all_documents as $doc) {
            $dest_lat = floatval($doc['latitude']);
            $dest_lng = floatval($doc['longitude']);

            // Konversi Kordinat Tujuan ke Radian
            $lat2_rad = deg2rad($dest_lat);
            $lng2_rad = deg2rad($dest_lng);

            // Selisih Radian (Delta)
            $dlat = $lat2_rad - $lat1_rad;
            $dlng = $lng2_rad - $lng1_rad;

            // Rumus Komponen 'a'
            $a = pow(sin($dlat / 2), 2) + cos($lat1_rad) * cos($lat2_rad) * pow(sin($dlng / 2), 2);

            // Rumus Komponen 'c'
            $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

            // Jarak Akhir 'd'
            $d = $R * $c;

            $all_calculations[] = [
                'nama' => $doc['nama'],
                'lat_deg' => $dest_lat,
                'lng_deg' => $dest_lng,
                'lat_rad' => $lat2_rad,
                'lng_rad' => $lng2_rad,
                'dlat' => $dlat,
                'dlng' => $dlng,
                'a' => $a,
                'c' => $c,
                'd' => $d
            ];
        }

        // 4. URUTKAN DARI JARAK TERDEKAT (ASCENDING) DAN AMBIL TOP 5
        usort($all_calculations, function($a, $b) {
            return $a['d'] <=> $b['d'];
        });

        $top_5_calculations = array_slice($all_calculations, 0, 5);
        
        // Beri Simbol W1 - W5 untuk 5 data terdekat
        $calculations = [];
        $idx = 1;
        foreach ($top_5_calculations as $calc) {
            $calculations['W'.$idx] = $calc;
            $idx++;
        }

        // =========================================================================
        // MENCETAK TAMPILAN HTML UNTUK BAB 4
        // =========================================================================
        echo '<!DOCTYPE html><html lang="id"><head><title>Perhitungan Haversine Bab 4</title>';
        echo '<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">';
        echo '<style>
                body { padding: 30px; background-color: #f8f9fa; }
                .table-container { background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.05); margin-bottom: 40px; overflow-x: auto;}
                th { background-color: #0d6efd !important; color: white !important; white-space: nowrap; text-align: center; vertical-align: middle; }
                td { font-size: 0.85rem; text-align: center; vertical-align: middle; }
              </style>';
        // Memanggil MathJax (Tanpa Polyfill)
        echo '<script id="MathJax-script" async src="https://cdn.jsdelivr.net/npm/mathjax@3/es5/tex-mml-chtml.js"></script>';
        echo '</head><body>';
        
        echo '<div class="container" style="max-width: 1100px;">';
        echo '<h2 class="text-center fw-bold text-primary mb-3">Lampiran Haversine Distance </h2>';

        // TAHAP 1: KONVERSI DERAJAT KE RADIAN (Hanya Top 5)
        echo '<div class="table-container">';
        echo '<h5 class="fw-bold">Tahap 1: Konversi Derajat ke Radian (Top 5 Terdekat)</h5>';
        echo '<p class="small text-muted mb-3">Mengonversi koordinat derajat menjadi Radian ($Radian = Derajat \times \frac{\pi}{180}$).</p>';
        echo '<table class="table table-bordered table-sm table-hover">';
        echo '<thead><tr>
                <th rowspan="2">Simbol</th>
                <th rowspan="2">Nama Titik Lokasi</th>
                <th colspan="2" class="bg-secondary">Koordinat Derajat (Input)</th>
                <th colspan="2" class="bg-success">Koordinat Radian (Output)</th>
              </tr>
              <tr>
                <th class="bg-secondary">Latitude</th>
                <th class="bg-secondary">Longitude</th>
                <th class="bg-success">Latitude ($\phi$)</th>
                <th class="bg-success">Longitude ($\lambda$)</th>
              </tr></thead><tbody>';
        
        // Baris Titik Asal
        echo '<tr class="table-warning">';
        echo '<td><strong>Asal (Titik 1)</strong></td>';
        echo '<td class="text-start fw-bold">'.$user_lokasi.'</td>';
        echo '<td>'.$user_lat.'</td>';
        echo '<td>'.$user_lng.'</td>';
        echo '<td class="fw-bold text-danger">'.round($lat1_rad, 6).'</td>';
        echo '<td class="fw-bold text-danger">'.round($lng1_rad, 6).'</td>';
        echo '</tr>';

        // Baris Sampel Wisata (Sudah Terurut Terdekat)
        foreach ($calculations as $simbol => $data) {
            echo '<tr>';
            echo '<td><strong>'.$simbol.' (Titik 2)</strong></td>';
            echo '<td class="text-start">'.$data['nama'].'</td>';
            echo '<td>'.$data['lat_deg'].'</td>';
            echo '<td>'.$data['lng_deg'].'</td>';
            echo '<td>'.round($data['lat_rad'], 6).'</td>';
            echo '<td>'.round($data['lng_rad'], 6).'</td>';
            echo '</tr>';
        }
        echo '</tbody></table></div>';

        // TAHAP 2: PERHITUNGAN KOMPONEN A DAN C
        echo '<div class="table-container">';
        echo '<h5 class="fw-bold">Tahap 2: Perhitungan Komponen Delta, $a$, dan $c$</h5>';
        echo '<table class="table table-bordered table-striped">';
        echo '<thead><tr>
                <th>Pasangan Titik</th>
                <th>$\Delta\phi$ (Selisih Lat)</th>
                <th>$\Delta\lambda$ (Selisih Lng)</th>
                <th>Komponen ($a$)</th>
                <th>Komponen ($c$)</th>
              </tr></thead><tbody>';
        foreach ($calculations as $simbol => $data) {
            echo '<tr>';
            echo '<td><strong>Asal vs '.$simbol.'</strong></td>';
            echo '<td>'.round($data['dlat'], 6).'</td>';
            echo '<td>'.round($data['dlng'], 6).'</td>';
            echo '<td class="font-monospace text-primary">'.round($data['a'], 8).'</td>';
            echo '<td class="font-monospace text-primary">'.round($data['c'], 8).'</td>';
            echo '</tr>';
        }
        echo '</tbody></table></div>';

        // TAHAP 3: HASIL AKHIR JARAK
        echo '<div class="table-container">';
        echo '<h5 class="fw-bold text-success">Tahap 3: Hasil Akhir Jarak (Distance)</h5>';
        echo '<p class="small text-muted mb-3">Hasil dari $d = 6371 \times c$. Kelima data di bawah ini adalah destinasi paling dekat dengan posisi pengguna.</p>';
        echo '<table class="table table-bordered table-hover align-middle">';
        echo '<thead><tr class="table-success">
                <th>Ranking Terdekat</th>
                <th>Simbol</th>
                <th>Nama Tempat Wisata Tujuan</th>
                <th>Perhitungan ($6371 \times c$)</th>
                <th>Jarak Akhir (Kilometer)</th>
              </tr></thead><tbody>';

        $rank = 1;
        foreach ($calculations as $simbol => $data) {
            echo '<tr>';
            echo '<td><span class="badge bg-primary rounded-circle p-2">#'.$rank.'</span></td>';
            echo '<td><strong>'.$simbol.'</strong></td>';
            echo '<td class="text-start fw-bold">'.$data['nama'].'</td>';
            echo '<td class="font-monospace text-muted">6371 &times; '.round($data['c'], 8).'</td>';
            echo '<td class="fw-bold text-danger fs-6">'.round($data['d'], 2).' km</td>';
            echo '</tr>';
            $rank++;
        }
        echo '</tbody></table></div>';

        echo '</div></body></html>';
    }
}
<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Sinkronisasi data wisata dari OpenStreetMap (Overpass API).
 *
 * Sumber hanya menyediakan nama, koordinat, kategori/tag, dan kadang gambar.
 * Rating, jumlah ulasan, HTM, serta deskripsi milik data lama/admin tetap
 * dipertahankan agar tidak merusak hasil Content Based Filtering.
 */
class Osm_sync_lib {

    protected $CI;

    protected $endpoints = array(
        'https://overpass-api.de/api/interpreter',
        'https://overpass.kumi.systems/api/interpreter',
        'https://overpass.private.coffee/api/interpreter'
    );

    // south,west,north,east (Daerah Istimewa Yogyakarta)
    protected $bbox = '-8.25,109.95,-7.45,110.90';

    protected $match_radius_km = 2.0;

    protected $default_htm_weekday = 0;
    protected $default_htm_weekend = 0;

    protected $by_osm = array();
    protected $all_rows = array();
    protected $used_ids = array();

    public function __construct() {
        $this->CI =& get_instance();
        $this->CI->load->model('Wisata_model');
        $this->CI->load->library('Nlp_lib');
    }

    /**
     * Jalankan sinkronisasi.
     *
     * @param array $options limit (int) untuk membatasi jumlah objek yang diproses
     * @return array ringkasan hasil
     */
    public function sync($options = array()) {
        $limit = isset($options['limit']) ? (int) $options['limit'] : 0;
        $started = microtime(true);

        $stats = array(
            'fetched' => 0,
            'inserted' => 0,
            'updated' => 0,
            'skipped' => 0,
            'failed' => 0,
            'categories' => array(),
            'errors' => array(),
            'started_at' => date('Y-m-d H:i:s'),
            'finished_at' => null,
            'duration' => 0
        );

        $elements = $this->fetch_elements();
        $stats['fetched'] = count($elements);

        $kategori_list = $this->CI->Wisata_model->get_all_kategori();
        $kategori_map = array();
        $default_kategori = null;

        foreach ($kategori_list as $kategori) {
            $kategori_map[strtolower($kategori['slug'])] = $kategori;
            $kategori_map[strtolower($kategori['nama_kategori'])] = $kategori;
            $kategori_map[strtolower(str_replace(' ', '-', $kategori['nama_kategori']))] = $kategori;

            if ($default_kategori === null) {
                $default_kategori = $kategori;
            }
        }

        if (isset($kategori_map['buatan'])) {
            $default_kategori = $kategori_map['buatan'];
        }

        $this->load_existing_rows();
        $processed = 0;

        foreach ($elements as $element) {
            if ($limit > 0 && $processed >= $limit) {
                break;
            }

            $processed++;

            try {
                $status = $this->process_element($element, $kategori_map, $default_kategori, $stats);
                $stats[$status]++;
            } catch (Exception $e) {
                $stats['failed']++;
                if (count($stats['errors']) < 20) {
                    $stats['errors'][] = $e->getMessage();
                }
            }
        }

        $stats['finished_at'] = date('Y-m-d H:i:s');
        $stats['duration'] = round(microtime(true) - $started, 2);

        return $stats;
    }

    protected function load_existing_rows() {
        $rows = $this->CI->Wisata_model->get_all_wisata_for_sync();

        foreach ($rows as $row) {
            $row['_norm'] = $this->normalize_name($row['nama']);
            $this->all_rows[] = $row;
            $this->used_ids[$row['id']] = false;

            if (!empty($row['osm_type']) && !empty($row['osm_id'])) {
                $this->by_osm[$row['osm_type'] . '/' . $row['osm_id']] = $row;
            }
        }
    }

    protected function process_element($element, $kategori_map, $default_kategori, &$stats) {
        $tags = (isset($element['tags']) && is_array($element['tags'])) ? $element['tags'] : array();
        $name = isset($tags['name']) ? trim($tags['name']) : '';

        if ($name === '') {
            return 'skipped';
        }

        $osm_type = isset($element['type']) ? $element['type'] : '';
        $osm_id = isset($element['id']) ? $element['id'] : '';

        if ($osm_type === '' || $osm_id === '') {
            return 'skipped';
        }

        $normalized_name = $this->normalize_name($name);
        $generic_names = array(
            'candi', 'taman', 'lapangan', 'lapangan voli', 'lapangan upacara',
            'makam', 'masjid', 'gereja', 'pura', 'museum', 'monumen', 'situs',
            'gunung', 'pantai', 'gua', 'bukit', 'air terjun', 'hutan', 'desa',
            'kampung', 'jembatan', 'tugu', 'benteng', 'istana', 'kolam',
            'waduk', 'embung', 'telaga', 'pasar', 'gedung', 'balai'
        );

        if ($normalized_name === '' || in_array($normalized_name, $generic_names, true)) {
            return 'skipped';
        }

        $coords = $this->extract_coords($element);

        if ($coords === null) {
            return 'skipped';
        }

        list($lat, $lon) = $coords;
        $key = $osm_type . '/' . $osm_id;

        $category_slug = $this->map_category($tags, $name);
        $kategori = isset($kategori_map[$category_slug]) ? $kategori_map[$category_slug] : $default_kategori;

        if ($kategori === null) {
            return 'skipped';
        }

        $payload = array(
            'nama' => $this->limit_text($name, 83),
            'latitude' => round($lat, 9),
            'longitude' => round($lon, 7),
            'id_kategori' => (int) $kategori['id'],
            'type' => $this->limit_text($kategori['nama_kategori'], 18),
            'osm_type' => $osm_type,
            'osm_id' => (string) $osm_id,
            'source' => 'osm',
            'last_synced_at' => date('Y-m-d H:i:s')
        );

        // 1. Sudah pernah disinkronkan sebelumnya (cocok berdasarkan ID OSM)
        if (isset($this->by_osm[$key])) {
            $existing = $this->by_osm[$key];

            if (!empty($existing['source'])) {
                $payload['source'] = $existing['source'];
            }

            // Hanya data yang sepenuhnya berasal dari OSM yang deskripsinya diperbarui.
            // Data hasil migrasi dari Kaggle/admin tetap mempertahankan deskripsi lama.
            if ($payload['source'] === 'osm') {
                $payload['description'] = $this->build_description($tags, $name, $kategori);

                if (empty($existing['image'])) {
                    $payload['image'] = $this->extract_image($tags);
                }
            }

            $update = $this->with_recalculated_clean_text($existing, $payload);
            $this->CI->Wisata_model->update_wisata($existing['id'], $update);
            $this->register_updated_row($existing, $update, $key);
            $this->bump_category($stats, $kategori);
            return 'updated';
        }

        // 2. Coba cocokkan dengan data lama (Kaggle/admin) berdasarkan nama + jarak
        $match = $this->find_match($name, $lat, $lon);

        if ($match !== null) {
            if (!empty($this->used_ids[$match['id']])) {
                return 'skipped';
            }

            if (!empty($match['osm_type']) && !empty($match['osm_id']) &&
                $match['osm_type'] . '/' . $match['osm_id'] !== $key) {
                return 'skipped';
            }

            // Data lama yang berhasil dipasangkan dengan OSM: pertahankan
            // rating, foto, HTM, dan deskripsi asli milik admin/Kaggle.
            $payload['source'] = 'osm-migrated';
            $update = $this->with_recalculated_clean_text($match, $payload);
            $this->CI->Wisata_model->update_wisata($match['id'], $update);
            $this->register_updated_row($match, $update, $key);
            $this->bump_category($stats, $kategori);
            return 'updated';
        }

        // 3. Objek baru: isi field yang tidak disediakan OSM dengan nilai default
        $description = $this->build_description($tags, $name, $kategori);
        $clean_description = $this->CI->nlp_lib->preprocess($name . ' ' . $description);

        $insert = $payload + array(
            'vote_average' => 0.0,
            'vote_count' => 0,
            'htm_weekday' => $this->default_htm_weekday,
            'htm_weekend' => $this->default_htm_weekend,
            'image' => $this->extract_image($tags),
            'description' => $description,
            'clean_description' => $clean_description
        );

        $this->CI->Wisata_model->insert_wisata($insert);
        $insert['id'] = $this->CI->db->insert_id();
        $insert['_norm'] = $this->normalize_name($insert['nama']);

        $this->by_osm[$key] = $insert;
        $this->all_rows[] = $insert;
        $this->used_ids[$insert['id']] = true;

        $this->bump_category($stats, $kategori);
        return 'inserted';
    }

    protected function with_recalculated_clean_text($existing, $payload) {
        if (isset($payload['description'])) {
            $description = $payload['description'];
        } else {
            $description = isset($existing['description']) ? $existing['description'] : '';
        }

        $payload['clean_description'] = $this->CI->nlp_lib->preprocess($payload['nama'] . ' ' . $description);

        return $payload;
    }

    protected function register_updated_row($existing, $payload, $key) {
        $updated = array_merge($existing, $payload);
        $updated['_norm'] = $this->normalize_name($updated['nama']);

        $this->by_osm[$key] = $updated;
        $this->all_rows[] = $updated;
        $this->used_ids[$existing['id']] = true;
    }

    protected function find_match($name, $lat, $lon) {
        $norm = $this->normalize_name($name);

        if ($norm === '') {
            return null;
        }

        $best = null;
        $best_distance = null;
        $best_similarity = 0.0;

        foreach ($this->all_rows as $row) {
            if (!isset($row['latitude'], $row['longitude'])) {
                continue;
            }

            $row_lat = (float) $row['latitude'];
            $row_lon = (float) $row['longitude'];

            if ($row_lat == 0 && $row_lon == 0) {
                continue;
            }

            // Filter kasar supaya tidak perlu menghitung jarak untuk semua data
            if (abs($row_lat - $lat) > 0.05 || abs($row_lon - $lon) > 0.05) {
                continue;
            }

            $distance = $this->haversine($lat, $lon, $row_lat, $row_lon);

            if ($distance > $this->match_radius_km) {
                continue;
            }

            $row_norm = isset($row['_norm']) ? $row['_norm'] : $this->normalize_name($row['nama']);
            $similarity = 0.0;

            if ($row_norm === $norm) {
                $similarity = 100.0;
            } else {
                similar_text($norm, $row_norm, $percent);
                $similarity = (float) $percent;

                if ($similarity < 85.0) {
                    continue;
                }
            }

            if ($similarity > $best_similarity ||
                ($similarity == $best_similarity && ($best_distance === null || $distance < $best_distance))) {
                $best = $row;
                $best_distance = $distance;
                $best_similarity = $similarity;
            }
        }

        return $best;
    }

    protected function bump_category(&$stats, $kategori) {
        $label = $kategori['nama_kategori'];

        if (!isset($stats['categories'][$label])) {
            $stats['categories'][$label] = 0;
        }

        $stats['categories'][$label]++;
    }

    protected function extract_coords($element) {
        $lat = null;
        $lon = null;

        if (isset($element['lat'], $element['lon'])) {
            $lat = $element['lat'];
            $lon = $element['lon'];
        } elseif (isset($element['center']['lat'], $element['center']['lon'])) {
            $lat = $element['center']['lat'];
            $lon = $element['center']['lon'];
        }

        if (!is_numeric($lat) || !is_numeric($lon)) {
            return null;
        }

        $lat = (float) $lat;
        $lon = (float) $lon;

        // Pastikan masih berada di area DIY
        if ($lat < -8.30 || $lat > -7.40 || $lon < 109.90 || $lon > 110.95) {
            return null;
        }

        return array($lat, $lon);
    }

    protected function map_category($tags, $name) {
        $tourism = isset($tags['tourism']) ? strtolower($tags['tourism']) : '';
        $historic = isset($tags['historic']) ? strtolower($tags['historic']) : '';
        $natural = isset($tags['natural']) ? strtolower($tags['natural']) : '';
        $leisure = isset($tags['leisure']) ? strtolower($tags['leisure']) : '';
        $amenity = isset($tags['amenity']) ? strtolower($tags['amenity']) : '';
        $lower_name = function_exists('mb_strtolower') ? mb_strtolower($name, 'UTF-8') : strtolower($name);

        if ($tourism === 'museum' || strpos($lower_name, 'museum') !== false) {
            return 'museum';
        }

        if ($natural === 'beach' || strpos($lower_name, 'pantai') !== false) {
            return 'pantai';
        }

        if ($amenity === 'place_of_worship' ||
            preg_match('/masjid|gereja|pura|vihara|klenteng|kapel/', $lower_name)) {
            return 'religi';
        }

        if ($historic !== '' ||
            preg_match('/candi|keraton|istana|monumen|makam|benteng|situs|prasasti/', $lower_name)) {
            return 'budaya-dan-sejarah';
        }

        if ($natural !== '' || $leisure === 'nature_reserve' || $tourism === 'viewpoint' ||
            preg_match('/bukit|gunung|hutan|air terjun|goa|gua|pantai|lembah|sungai|puncak/', $lower_name)) {
            return 'alam';
        }

        if (preg_match('/agro|kebun|buah|petik|perkebunan/', $lower_name)) {
            return 'agrowisata';
        }

        if (strpos($lower_name, 'desa ') !== false) {
            return 'desa-wisata';
        }

        if (preg_match('/kuliner|resto|cafe|kafe|warung|pasar/', $lower_name)) {
            return 'kuliner';
        }

        if (preg_match('/kolam|renang|water|embung|waduk|telaga/', $lower_name)) {
            return 'wisata-air';
        }

        if ($tourism === 'information' || preg_match('/pendidikan|edukasi/', $lower_name)) {
            return 'pendidikan';
        }

        if ($tourism === 'artwork' || $tourism === 'gallery' ||
            preg_match('/galeri|seni|tari|wayang|batik/', $lower_name)) {
            return 'seni';
        }

        return 'buatan';
    }

    protected function build_description($tags, $name, $kategori) {
        $description = '';

        foreach (array('description', 'note') as $tag_key) {
            if (!empty($tags[$tag_key])) {
                $candidate = trim(strip_tags($tags[$tag_key]));

                // Lewati deskripsi hasil impor databasis yang tidak informatif
                if (strlen($candidate) < 15 || preg_match('/gns database|geonames|database update/i', $candidate)) {
                    continue;
                }

                $description = $candidate;
                break;
            }
        }

        if ($description === '') {
            $label = ucwords(str_replace(array('_', '-'), ' ', $kategori['nama_kategori']));
            $description = $name . ' merupakan destinasi wisata ' . $label .
                ' yang berada di Daerah Istimewa Yogyakarta.';
        }

        return $this->limit_text($description, 2339);
    }

    protected function extract_image($tags) {
        $image = '';

        if (!empty($tags['image']) && preg_match('#^https?://#i', $tags['image'])) {
            $image = trim($tags['image']);
        } elseif (!empty($tags['wikimedia_commons'])) {
            $file = trim($tags['wikimedia_commons']);

            if (stripos($file, 'File:') === 0) {
                $file = trim(substr($file, 5));
            }

            if ($file !== '') {
                $file = str_replace(' ', '_', $file);
                $image = 'https://commons.wikimedia.org/wiki/Special:FilePath/' .
                    rawurlencode($file) . '?width=800';
            }
        }

        if ($image === '' || strlen($image) > 166) {
            return '';
        }

        return $image;
    }

    protected function normalize_name($name) {
        $name = strtolower(trim($name));
        $name = str_replace('goa', 'gua', $name);
        $name = preg_replace('/\b(objek|destinasi|wisata)\b/', ' ', $name);
        $name = preg_replace('/[^a-z0-9]+/', ' ', $name);
        $name = preg_replace('/\s+/', ' ', $name);

        return trim($name);
    }

    protected function limit_text($text, $max_length) {
        $text = trim($text);

        if (function_exists('mb_substr')) {
            return mb_substr($text, 0, $max_length, 'UTF-8');
        }

        return substr($text, 0, $max_length);
    }

    protected function haversine($lat1, $lon1, $lat2, $lon2) {
        $earth_radius = 6371;

        $d_lat = deg2rad($lat2 - $lat1);
        $d_lon = deg2rad($lon2 - $lon1);

        $a = sin($d_lat / 2) * sin($d_lat / 2) +
            cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
            sin($d_lon / 2) * sin($d_lon / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $earth_radius * $c;
    }

    protected function fetch_elements() {
        $query = $this->build_query();
        $last_error = '';

        foreach ($this->endpoints as $endpoint) {
            $curl = curl_init($endpoint);

            curl_setopt_array($curl, array(
                CURLOPT_POST => true,
                CURLOPT_POSTFIELDS => http_build_query(array('data' => $query)),
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_CONNECTTIMEOUT => 20,
                CURLOPT_TIMEOUT => 180,
                CURLOPT_USERAGENT => 'wisata-cbf/1.0 (Sistem Rekomendasi Wisata Yogyakarta)',
                CURLOPT_HTTPHEADER => array('Accept: application/json')
            ));

            $response = curl_exec($curl);
            $http_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
            $curl_error = curl_error($curl);
            curl_close($curl);

            if ($response !== false && $http_code === 200) {
                $json = json_decode($response, true);

                if (is_array($json) && isset($json['elements']) && is_array($json['elements'])) {
                    return $json['elements'];
                }

                $last_error = 'Respons Overpass bukan JSON yang valid.';
            } else {
                $last_error = 'HTTP ' . $http_code . ' ' . $curl_error;
            }
        }

        throw new Exception('Gagal mengambil data dari Overpass API: ' . $last_error);
    }

    protected function build_query() {
        $bbox = $this->bbox;
        $tourism = 'attraction|museum|viewpoint|zoo|theme_park|gallery';
        $historic = 'monument|memorial|castle|fort|ruins|archaeological_site|palace|temple|shrine|tomb|city_gate|citywalls';
        $natural = 'beach|cave_entrance|peak|waterfall|spring';

        $filters = array();

        foreach (array('node', 'way', 'relation') as $type) {
            $filters[] = $type . '["tourism"~"^(' . $tourism . ')$"]["name"](' . $bbox . ');';
            $filters[] = $type . '["historic"~"^(' . $historic . ')$"]["name"](' . $bbox . ');';
            $filters[] = $type . '["natural"~"^(' . $natural . ')$"]["name"](' . $bbox . ');';
            $filters[] = $type . '["leisure"="nature_reserve"]["name"](' . $bbox . ');';
        }

        return "[out:json][timeout:180];\n(\n" . implode("\n", $filters) . "\n);\nout center tags;";
    }
}

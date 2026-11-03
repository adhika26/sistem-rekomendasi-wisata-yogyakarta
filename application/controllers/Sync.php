<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Perintah sinkronisasi via CLI (untuk cron / Task Scheduler).
 *
 * Contoh:
 *   php index.php sync run
 *   php index.php sync run 50
 */
class Sync extends CI_Controller {

    public function __construct() {
        parent::__construct();

        if (!is_cli()) {
            show_404();
        }
    }

    public function run($limit = 0) {
        set_time_limit(0);

        $this->load->library('Osm_sync_lib');

        try {
            $stats = $this->osm_sync_lib->sync(array('limit' => (int) $limit));

            echo "Sinkronisasi selesai." . PHP_EOL;
            echo json_encode($stats, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . PHP_EOL;
        } catch (Exception $e) {
            echo "Sinkronisasi gagal: " . $e->getMessage() . PHP_EOL;
            exit(1);
        }
    }
}

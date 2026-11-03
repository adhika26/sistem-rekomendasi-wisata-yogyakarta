<?php
defined('BASEPATH') OR exit('No direct script access allowed');

require_once APPPATH . '../vendor/autoload.php';

use Sastrawi\Stemmer\StemmerFactory;
use Sastrawi\StopWordRemover\StopWordRemoverFactory;

class Nlp_lib {

    protected $stemmer;
    protected $stopwordRemover;

    public function __construct() {

        // Inisialisasi Sastrawi
        $stemmerFactory = new StemmerFactory();
        $this->stemmer  = $stemmerFactory->createStemmer();

        $stopwordFactory = new StopWordRemoverFactory();
        $this->stopwordRemover = $stopwordFactory->createStopWordRemover();
    }

    public function preprocess($text) {

        // 1. Case Folding
        $text = strtolower($text);

        // 2. Cleaning
        $text = preg_replace('/[^a-z\s]/', ' ', $text);
        $text = preg_replace('/\s+/', ' ', $text);
        $text = trim($text);

        // 3. Stopword Removal
        $text = $this->stopwordRemover->remove($text);

        // 4. Stemming
        $text = $this->stemmer->stem($text);

        return $text;
    }
    public function casefolding($text) {

        // 1. Case Folding
        $text = strtolower($text);
        return $text;
    }

    public function cleaning($text) {

        // 1. Case Folding
        $text = strtolower($text);

        // 2. Cleaning
        $text = preg_replace('/[^a-z\s]/', ' ', $text);
        $text = preg_replace('/\s+/', ' ', $text);
        $text = trim($text);
        return $text;
    }

    public function stopwordremoval($text) {

        // 1. Case Folding
        $text = strtolower($text);

        // 2. Cleaning
        $text = preg_replace('/[^a-z\s]/', ' ', $text);
        $text = preg_replace('/\s+/', ' ', $text);
        $text = trim($text);

        // 3. Stopword Removal
        $text = $this->stopwordRemover->remove($text);
        return $text;
    }
}
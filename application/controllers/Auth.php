<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Auth extends CI_Controller {

    public function __construct() {
        parent::__construct();
        
        // Ambil nama fungsi yang sedang diakses
        $method = $this->router->fetch_method();
        
        // Jika admin sudah login, lempar ke halaman admin, 
        // KECUALI jika dia sedang mencoba mengakses fungsi 'logout'
        if ($this->session->userdata('logged_in') == TRUE && $method != 'logout') {
            redirect('index.php/admin');
        }
    }

    // Menampilkan halaman login
    public function index() {
        $this->load->view('login_view');
    }

    // Memproses form login
    public function proses_login() {
        $username = $this->input->post('username', TRUE);
        $password = $this->input->post('password', TRUE);

        // Cari admin berdasarkan username
        $admin = $this->db->get_where('admin', ['username' => $username])->row_array();

        if ($admin) {
            // Verifikasi password hash BCRYPT
            if (password_verify($password, $admin['password'])) {
                // Set data ke session
                $session_data = [
                    'admin_id'  => $admin['id'],
                    'username'  => $admin['username'],
                    'nama'      => $admin['nama'],
                    'logged_in' => TRUE
                ];
                $this->session->set_userdata($session_data);
                redirect('index.php/admin');
            } else {
                $this->session->set_flashdata('error', 'Password salah!');
                redirect('index.php/auth');
            }
        } else {
            $this->session->set_flashdata('error', 'Username tidak ditemukan!');
            redirect('index.php/auth');
        }
    }

    // Proses Logout
    public function logout() {
        $this->session->sess_destroy();
        
        // Tambahkan index.php/ di dalam fungsi redirect
        redirect('index.php/auth'); 
    }

}
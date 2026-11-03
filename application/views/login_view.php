<!DOCTYPE html>
<html lang="id">
<head>
    <title>Login Admin - Sistem Wisata</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    
    <link rel="stylesheet" href="<?= base_url('assets/css/admin_style.css') ?>">
</head>
<body class="login-body">

    <div class="card-login-wrapper">
        <div class="card login-card p-2">
            <div class="card-body">
                
                <div class="text-center mb-4">
                    <div class="d-inline-flex align-items-center justify-content-center bg-primary-subtle text-primary rounded-circle mb-3" style="width: 55px; height: 55px;">
                        <i class="bi bi-shield-lock-fill fs-3"></i>
                    </div>
                    <h4 class="fw-bold mb-0 text-dark">Admin Login</h4>
                </div>

                <?php if($this->session->flashdata('error')): ?>
                    <div class="alert alert-danger py-2 text-center" style="font-size: 0.85rem;" role="alert">
                        <i class="bi bi-exclamation-triangle-fill me-1"></i>
                        <?= $this->session->flashdata('error'); ?>
                    </div>
                <?php endif; ?>

                <form action="<?= base_url('index.php/auth/proses_login') ?>" method="POST">
                    
                    <div class="mb-3">
                        <label class="form-label text-secondary fw-medium small mb-1">Username</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-person text-muted"></i></span>
                            <input type="text" name="username" class="form-control px-2" placeholder="Masukkan username" required autofocus>
                        </div>
                    </div>
                    
                    <div class="mb-4">
                        <label class="form-label text-secondary fw-medium small mb-1">Password</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-key text-muted"></i></span>
                            <input type="password" name="password" class="form-control px-2" placeholder="Masukkan password" required>
                        </div>
                    </div>
                    
                    <button type="submit" class="btn btn-primary w-100 py-2 fw-semibold rounded-3 shadow-sm">
                        Masuk
                    </button>
                    
                </form>
            </div>
        </div>
        
        <div class="text-center mt-4">
            <a href="<?= base_url() ?>" class="text-decoration-none back-link fw-medium small">
                &larr; Kembali ke Web Wisata
            </a>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
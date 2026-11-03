<!DOCTYPE html>
<?php
    $wisata = isset($wisata) ? $wisata : null;
    $is_edit = !empty($wisata);
    $form_action = $is_edit
        ? base_url('index.php/admin/update/' . $wisata['id'])
        : base_url('index.php/admin/simpan');
    $nilai = function ($key, $default = '') use ($wisata) {
        return ($wisata && isset($wisata[$key]) && $wisata[$key] !== '') ? $wisata[$key] : $default;
    };
?>
<html lang="id">
<head>
    <title><?= $is_edit ? 'Edit Dataset Wisata' : 'Tambah Dataset Wisata' ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    
    <link rel="stylesheet" href="<?= base_url('assets/css/admin_style.css') ?>">
</head>
<body>

    <div class="container mt-5 mb-5" style="max-width: 900px;">
        
        <a href="<?= base_url('index.php/admin') ?>" class="btn btn-outline-secondary mb-4 rounded-pill px-4 shadow-sm bg-white">
            <i class="bi bi-arrow-left me-2"></i>Kembali ke Panel
        </a>
        
        <div class="card card-custom">
            <div class="card-header-custom">
                <h5 class="mb-0 fw-bold text-primary">
                    <i class="bi <?= $is_edit ? 'bi-pencil-square' : 'bi-file-earmark-plus-fill' ?> me-2"></i>
                    <?= $is_edit ? 'Edit Data Wisata' : 'Input Data Wisata Baru' ?>
                </h5>
            </div>
            
            <div class="card-body p-4">
                <?php $admin_error = $this->session->flashdata('admin_error'); ?>
                <?php if ($admin_error): ?>
                    <div class="alert alert-danger" role="alert">
                        <i class="bi bi-exclamation-triangle-fill me-2"></i><?= html_escape($admin_error) ?>
                    </div>
                <?php endif; ?>

                <form action="<?= $form_action ?>" method="post" enctype="multipart/form-data">
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label"><i class="bi bi-signpost-split text-muted me-1"></i> Nama Wisata</label>
                            <input type="text" name="nama" class="form-control" placeholder="Contoh: Pantai Kuta" value="<?= html_escape($nilai('nama')) ?>" required>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label class="form-label"><i class="bi bi-tags text-muted me-1"></i> Kategori Wisata</label>
                            <select name="id_kategori" class="form-select" required>
                                <option value="">-- Pilih Kategori --</option>
                                <?php $selected_kategori = $nilai('id_kategori'); ?>
                                <?php foreach($kategori as $kat): ?>
                                    <option value="<?= $kat['id'] ?>" <?= ($selected_kategori == $kat['id']) ? 'selected' : '' ?>>
                                        <?= ucwords(str_replace('-', ' ', $kat['nama_kategori'])) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-3 mb-3">
                            <label class="form-label"><i class="bi bi-star-fill text-warning me-1"></i> Rating (1-5)</label>
                            <input type="number" step="0.1" name="vote_average" class="form-control" placeholder="4.5" value="<?= html_escape($nilai('vote_average', '0')) ?>" required>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label"><i class="bi bi-chat-left-text text-muted me-1"></i> Ulasan</label>
                            <input type="number" name="vote_count" class="form-control" placeholder="150" value="<?= html_escape($nilai('vote_count', '0')) ?>" required>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label"><i class="bi bi-cash text-muted me-1"></i> HTM Weekday</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light">Rp</span>
                                <input type="number" name="htm_weekday" class="form-control" placeholder="0" value="<?= html_escape($nilai('htm_weekday', '0')) ?>" required>
                            </div>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label"><i class="bi bi-cash-stack text-muted me-1"></i> HTM Weekend</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light">Rp</span>
                                <input type="number" name="htm_weekend" class="form-control" placeholder="0" value="<?= html_escape($nilai('htm_weekend', '0')) ?>" required>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label"><i class="bi bi-geo-alt text-danger me-1"></i> Latitude</label>
                            <input type="text" name="latitude" class="form-control" placeholder="-8.7185" value="<?= html_escape($nilai('latitude')) ?>" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label"><i class="bi bi-geo-alt-fill text-danger me-1"></i> Longitude</label>
                            <input type="text" name="longitude" class="form-control" placeholder="115.1686" value="<?= html_escape($nilai('longitude')) ?>" required>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label"><i class="bi bi-image text-muted me-1"></i> Foto Wisata</label>
                        <input type="file" name="image_file" id="image_file" class="form-control" accept=".jpg,.jpeg,.png,.webp,image/*">
                        <div class="form-text">
                            Format JPG, JPEG, PNG, atau WEBP. Maksimal 2 MB.
                            <?= $is_edit ? 'Biarkan kosong jika tidak ingin mengganti foto.' : 'Opsional.' ?>
                        </div>
                        <img id="image_preview" src="<?= html_escape($nilai('image')) ?>" alt="Preview foto"
                             class="mt-3 rounded border shadow-sm"
                             style="max-height: 200px; max-width: 100%; object-fit: cover; display: <?= $nilai('image') ? 'block' : 'none' ?>;">
                    </div>

                    <div class="mb-4">
                        <label class="form-label"><i class="bi bi-justify-left text-muted me-1"></i> Deskripsi</label>
                        <textarea name="description" class="form-control" rows="5" placeholder="Tuliskan deskripsi tempat wisata di sini..." required><?= html_escape($nilai('description')) ?></textarea>
                        
                        <div class="d-flex align-items-center mt-2 p-2 bg-primary-subtle text-primary-emphasis rounded border border-primary-subtle" style="font-size: 0.85rem;">
                            <i class="bi bi-info-circle-fill me-2 fs-5"></i>
                            <span><strong>Info:</strong> Proses simpan mungkin memakan waktu beberapa detik karena sistem melakukan <em>stemming</em> teks bahasa Indonesia (NLP).</span>
                        </div>
                    </div>

                    <hr class="text-muted mb-4">

                    <button type="submit" class="btn btn-submit w-100 shadow-sm">
                        <i class="bi <?= $is_edit ? 'bi-check-circle-fill' : 'bi-cloud-arrow-up-fill' ?> me-2"></i>
                        <?= $is_edit ? 'Simpan Perubahan' : 'Simpan Data Wisata' ?>
                    </button>
                </form>
            </div>
        </div>
    </div>

    <script>
        document.getElementById('image_file').addEventListener('change', function(e) {
            var preview = document.getElementById('image_preview');
            var file = e.target.files && e.target.files[0];

            if (file) {
                preview.src = URL.createObjectURL(file);
                preview.style.display = 'block';
            } else {
                preview.style.display = 'none';
            }
        });
    </script>
</body>
</html>
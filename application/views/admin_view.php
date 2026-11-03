<!DOCTYPE html>
<html lang="id">
<head>
    <title>Panel Admin - Dataset Wisata</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    
    <link rel="stylesheet" href="<?= base_url('assets/css/admin_style.css') ?>">
</head>
<body>

    <nav class="navbar navbar-dark navbar-custom mb-4 sticky-top">
        <div class="container">
            <a class="navbar-brand fw-bold" href="<?= base_url('index.php/admin') ?>">
                <i class="bi bi-grid-1x2-fill me-2"></i>Panel Admin
            </a>
            <div>
                <a href="<?= base_url() ?>" class="btn btn-outline-light btn-sm me-2 rounded-pill px-3" target="_blank">
                    <i class="bi bi-globe me-1"></i> Lihat Web User
                </a>
                <a href="<?= base_url('index.php/auth/logout') ?>" class="btn btn-danger btn-sm rounded-pill px-3 shadow-sm" onclick="return confirm('Apakah Anda yakin ingin keluar dari panel admin?')">
                    <i class="bi bi-box-arrow-right me-1"></i> Keluar
                </a>
            </div>
        </div>
    </nav>
    
    <div class="container pb-5">

        <?php $sync_result = $this->session->flashdata('sync_result'); ?>
        <?php $sync_error = $this->session->flashdata('sync_error'); ?>
        <?php $admin_success = $this->session->flashdata('admin_success'); ?>

        <?php if ($admin_success): ?>
            <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
                <i class="bi bi-check-circle-fill me-2"></i><?= html_escape($admin_success) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Tutup"></button>
            </div>
        <?php endif; ?>

        <?php if ($sync_result): ?>
            <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
                <h6 class="fw-bold mb-2"><i class="bi bi-check-circle-fill me-2"></i>Sinkronisasi OSM selesai</h6>
                <div class="small">
                    <span class="me-3"><strong><?= (int) $sync_result['fetched'] ?></strong> objek diunduh</span>
                    <span class="me-3 text-success"><strong><?= (int) $sync_result['inserted'] ?></strong> baru</span>
                    <span class="me-3 text-primary"><strong><?= (int) $sync_result['updated'] ?></strong> diperbarui</span>
                    <span class="me-3 text-secondary"><strong><?= (int) $sync_result['skipped'] ?></strong> dilewati</span>
                    <span class="me-3 text-danger"><strong><?= (int) $sync_result['failed'] ?></strong> gagal</span>
                    <span>Durasi: <strong><?= (int) $sync_result['duration'] ?> detik</strong></span>
                </div>
                <?php if (!empty($sync_result['categories'])): ?>
                    <div class="small mt-2">
                        Kategori:
                        <?php foreach ($sync_result['categories'] as $nama_kategori => $jumlah): ?>
                            <span class="badge bg-light text-dark border me-1"><?= html_escape($nama_kategori) ?>: <?= (int) $jumlah ?></span>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
                <?php if (!empty($sync_result['errors'])): ?>
                    <details class="small mt-2">
                        <summary class="text-danger">Lihat error (<?= count($sync_result['errors']) ?>)</summary>
                        <ul class="mb-0 mt-1">
                            <?php foreach ($sync_result['errors'] as $error): ?>
                                <li><?= html_escape($error) ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </details>
                <?php endif; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Tutup"></button>
            </div>
        <?php endif; ?>

        <?php if ($sync_error): ?>
            <div class="alert alert-danger alert-dismissible fade show shadow-sm" role="alert">
                <h6 class="fw-bold mb-1"><i class="bi bi-exclamation-triangle-fill me-2"></i>Sinkronisasi gagal</h6>
                <div class="small"><?= html_escape($sync_error) ?></div>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Tutup"></button>
            </div>
        <?php endif; ?>
        
        <div class="card card-custom">
            <div class="card-header-custom d-flex justify-content-between align-items-center flex-wrap gap-2">
                <h5 class="mb-0 fw-bold text-primary">
                    <i class="bi bi-database-check me-2"></i>Kelola Dataset Wisata
                </h5>
                <div class="d-flex gap-2">
                    <form action="<?= base_url('index.php/admin/sync_osm') ?>" method="post"
                          onsubmit="return confirm('Sinkronisasi data dari OpenStreetMap? Nama, koordinat, dan kategori akan diperbarui. Proses ini bisa memakan waktu beberapa menit.');">
                        <button type="submit" class="btn btn-success shadow-sm px-3">
                            <i class="bi bi-cloud-arrow-down me-1"></i> Sinkronisasi OSM
                        </button>
                    </form>
                    <a href="<?= base_url('index.php/admin/tambah') ?>" class="btn btn-add shadow-sm px-3">
                        <i class="bi bi-plus-circle me-1"></i> Tambah Dataset
                    </a>
                </div>
            </div>

            <div class="card-body p-0 table-responsive table-container">
                <table class="table table-hover table-striped mb-0">
                    <thead>
                        <tr>
                            <th width="5%" class="text-center">No</th>
                            <th width="25%">Nama Wisata</th>
                            <th width="15%">Kategori</th>
                            <th width="25%">Lokasi (Lat, Lng)</th>
                            <th width="15%">Status NLP</th>
                            <th width="15%" class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $no = 1; foreach($wisata as $w): ?>
                        <tr>
                            <td class="text-center fw-semibold"><?= $no++ ?></td>
                            <td class="fw-medium text-dark"><?= $w['nama'] ?></td>
                            <td>
                                <span class="text-secondary">
                                    <i class="bi bi-tags me-1"></i>
                                    <?php if(!empty($w['nama_kategori'])): ?>
                                        <?= ucwords(str_replace('-', ' ', $w['nama_kategori'])) ?>
                                    <?php else: ?>
                                        <?= str_replace('_', ' ', $w['type']) ?>
                                    <?php endif; ?>
                                </span>
                            </td>
                            <td>
                                <small class="text-muted font-monospace bg-light p-1 rounded border">
                                    <i class="bi bi-geo-alt-fill text-danger me-1"></i>
                                    <?= $w['latitude'] ?>, <?= $w['longitude'] ?>
                                </small>
                            </td>
                            <td>
                                <?php if(!empty($w['clean_description'])): ?>
                                    <span class="badge rounded-pill bg-success-subtle text-success border border-success">
                                        <i class="bi bi-check-circle me-1"></i>Sudah Diolah
                                    </span>
                                <?php else: ?>
                                    <span class="badge rounded-pill bg-warning-subtle text-warning border border-warning">
                                        <i class="bi bi-hourglass-split text-dark me-1"></i><span class="text-dark">Belum Diolah</span>
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td class="text-center">
                                <div class="d-flex justify-content-center gap-1">
                                    <a href="<?= base_url('index.php/admin/edit/'.$w['id']) ?>" class="btn btn-sm btn-outline-primary shadow-sm rounded-3">
                                        <i class="bi bi-pencil-square me-1"></i>Edit
                                    </a>
                                    <a href="<?= base_url('index.php/admin/hapus/'.$w['id']) ?>" class="btn btn-sm btn-outline-danger shadow-sm rounded-3" onclick="return confirm('Yakin hapus data ini?')">
                                        <i class="bi bi-trash3 me-1"></i>Hapus
                                    </a>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <button onclick="window.scrollTo({top: 0, behavior: 'smooth'});" class="btn btn-primary rounded-circle shadow-lg btn-scroll-top" title="Kembali ke atas">
        <i class="bi bi-arrow-up"></i>
    </button>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
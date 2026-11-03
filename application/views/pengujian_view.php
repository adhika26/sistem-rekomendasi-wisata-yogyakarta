<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Pengujian Spesifik - Precision@K</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; background-color: #f4f7f6; }
        .card-custom { border: none; border-radius: 16px; box-shadow: 0 8px 30px rgba(0,0,0,0.05); }
    </style>
</head>
<body>

    <?php $this->load->view('navbar'); ?>

    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-lg-10">
                
                <div class="card card-custom mb-4 bg-white">
                    <div class="card-body p-4 p-lg-5">
                        <h3 class="fw-bold text-dark mb-1"><i class="bi bi-crosshair text-primary me-2"></i>Pengujian Akurasi</h3>
                        <p class="text-muted mb-4">Pilih salah satu objek wisata untuk menguji seberapa akurat sistem memberikan <?= isset($hasil_uji) ? $hasil_uji['k_value'] : 10 ?> rekomendasi Content-Based Filtering.</p>
                        
                        <form action="<?= base_url('index.php/pengujian') ?>" method="POST" class="row g-3 align-items-end">
                            <div class="col-md-9">
                                <label class="form-label fw-semibold">Pilih Objek Wisata Target:</label>
                                <select name="id_wisata" class="form-select border-secondary" required>
                                    <option value="">-- Silakan Pilih Wisata --</option>
                                    <?php foreach($list_wisata as $w): ?>
                                        <option value="<?= $w['id'] ?>" <?= (isset($id_terpilih) && $id_terpilih == $w['id']) ? 'selected' : '' ?>>
                                            <?= $w['nama'] ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-3 text-end">
                                <button type="submit" class="btn btn-primary w-100 fw-bold rounded-pill" style="padding: 10px 0;">
                                    <i class="bi bi-play-fill me-1"></i> Mulai Uji
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <?php if(isset($hasil_uji)): ?>
                <div class="card card-custom bg-white">
                    <div class="card-body p-4 p-lg-5">
                        <div class="d-flex flex-wrap justify-content-between align-items-center border-bottom pb-4 mb-4">
                            <div>
                                <span class="text-muted small fw-medium text-uppercase tracking-wide">Target Pengujian</span>
                                <h4 class="fw-bold text-dark mt-1 mb-0"><?= $hasil_uji['target_nama'] ?></h4>
                                <span class="badge bg-secondary mt-2"><?= ucwords(str_replace('_', ' ', $hasil_uji['target_kategori'])) ?></span>
                            </div>
                            <div class="text-end mt-3 mt-md-0">
                                <div class="bg-primary bg-opacity-10 text-primary p-3 rounded-4">
                                    <span class="small fw-semibold d-block">Skor P@<?= $hasil_uji['k_value'] ?></span>
                                    <h2 class="fw-bold mb-0"><?= $hasil_uji['precision_score'] * 100 ?>%</h2>
                                </div>
                            </div>
                        </div>

                        <h6 class="fw-bold mb-3">Hasil Top <?= $hasil_uji['k_value'] ?> Rekomendasi Sistem:</h6>
                        <div class="table-responsive">
                            <table class="table table-hover align-middle border">
                                <thead class="table-light text-muted small">
                                    <tr>
                                        <th class="text-center" width="5%">Rank</th>
                                        <th width="40%">Nama Wisata Rekomendasi</th>
                                        <th width="20%">Kategori Asli</th>
                                        <th width="15%" class="text-center">Kemiripan</th>
                                        <th width="20%" class="text-center">Status Relevansi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php $rank = 1; foreach($hasil_uji['detail_rekomendasi'] as $rek): ?>
                                    <tr>
                                        <td class="text-center fw-bold text-secondary"><?= $rank++ ?></td>
                                        <td class="fw-semibold text-dark"><?= $rek['nama'] ?></td>
                                        <td><span class="badge bg-light text-dark border"><?= ucwords(str_replace('_', ' ', $rek['kategori'])) ?></span></td>
                                        <td class="text-center fw-medium"><?= $rek['score'] ?>%</td>
                                        <td class="text-center">
                                            <?php if($rek['is_relevant']): ?>
                                                <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 w-100 py-2"><i class="bi bi-check-circle-fill me-1"></i> Relevan</span>
                                            <?php else: ?>
                                                <span class="badge bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25 w-100 py-2"><i class="bi bi-x-circle-fill me-1"></i> Tidak Relevan</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>

                        <div class="alert alert-info mt-3 border-0 rounded-3">
                            <i class="bi bi-info-circle-fill me-2"></i> Total Item Relevan: <strong><?= $hasil_uji['jumlah_relevan'] ?> dari <?= $hasil_uji['k_value'] ?></strong> rekomendasi teratas.
                        </div>

                    </div>
                </div>
                <?php endif; ?>

            </div>
        </div>
    </div>
    
    <?php $this->load->view('footer'); ?>
</body>
</html>
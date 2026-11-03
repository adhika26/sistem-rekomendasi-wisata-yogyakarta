<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail: <?= $wisata['nama'] ?></title>
    
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">

    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <link rel="stylesheet" href="https://unpkg.com/leaflet-routing-machine@latest/dist/leaflet-routing-machine.css" />

    <link rel="stylesheet" href="<?= base_url('assets/css/home_style.css?v=8') ?>">
</head>
<body>

    <?php $this->load->view('navbar'); ?>

    <div class="container main-container pb-5 pt-4">

        <a href="<?= base_url() ?>" class="btn btn-back rounded-pill px-4 mb-4 shadow-sm">
            <i class="bi bi-arrow-left me-2"></i>Kembali ke Beranda
        </a>

        <div class="card-custom detail-card mb-5">
            <div class="row g-0">
                <div class="col-md-5">
                    <img src="<?= !empty($wisata['image']) ? $wisata['image'] : 'https://images.unsplash.com/photo-1501785888041-af3ef285b470?auto=format&fit=crop&w=600&q=80' ?>" class="img-fluid detail-img w-100" alt="<?= $wisata['nama'] ?>">
                </div>
                <div class="col-md-7">
                    <div class="card-body p-4 p-lg-5">
                        <div class="mb-3">
                            <span class="kategori-badge">
                                <i class="bi bi-tag-fill me-1"></i>
                                <?= ucwords(str_replace('_', ' ', $wisata['type'])) ?>
                            </span>
                        </div>
                        
                        <h2 class="mb-3" style="font-size: clamp(2rem, 3.5vw, 3rem);"><?= $wisata['nama'] ?></h2>
                        
                        <div class="row g-2 mb-4">
                            <div class="col-sm-4">
                                <div class="stat-box h-100">
                                    <div class="text-muted small mb-1">Rating & Ulasan</div>
                                    <div class="fw-bold text-dark d-flex align-items-center">
                                        <i class="bi bi-star-fill text-warning me-2"></i>
                                        <?= $wisata['vote_average'] ?> <span class="text-muted fw-normal ms-1" style="font-size: 0.8rem;">(<?= $wisata['vote_count'] ?>)</span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-sm-4">
                                <div class="stat-box h-100">
                                    <div class="text-muted small mb-1">HTM Weekday</div>
                                    <div class="fw-bold text-dark text-truncate">
                                        <i class="bi bi-ticket-perforated text-success me-2"></i>
                                        Rp <?= number_format($wisata['htm_weekday'], 0, ',', '.') ?>
                                    </div>
                                </div>
                            </div>
                            <div class="col-sm-4">
                                <div class="stat-box h-100">
                                    <div class="text-muted small mb-1">HTM Weekend</div>
                                    <div class="fw-bold text-dark text-truncate">
                                        <i class="bi bi-ticket-detailed text-danger me-2"></i>
                                        Rp <?= number_format($wisata['htm_weekend'], 0, ',', '.') ?>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <h5 class="fw-bold text-dark mb-2">Deskripsi</h5>
                        <p class="text-secondary lh-lg" style="text-align: justify;">
                            <?= nl2br($wisata['description']) ?>
                        </p>
                        
                    </div>
                </div>
            </div>
        </div>

        <?php
            $has_coords = is_numeric($wisata['latitude']) && is_numeric($wisata['longitude'])
                && (float) $wisata['latitude'] != 0 && (float) $wisata['longitude'] != 0;
        ?>

        <div class="map-shell mb-5">
            <div class="card-header-custom d-flex justify-content-between align-items-center flex-wrap gap-2">
                <h5 class="mb-0 fw-bold text-dark">
                    <i class="bi bi-geo-alt-fill text-danger me-2"></i>Lokasi Wisata
                </h5>
                <?php if ($has_coords): ?>
                    <button type="button" id="btn-route-detail" class="btn btn-route rounded-pill px-3 shadow-sm">
                        <i class="bi bi-sign-turn-right-fill me-1"></i>Rute dari Lokasi Saya
                    </button>
                <?php endif; ?>
            </div>
            <div class="card-body p-0">
                <?php if ($has_coords): ?>
                    <div id="detail-map"></div>
                <?php else: ?>
                    <div class="p-4 text-center text-muted">
                        <i class="bi bi-geo-slash fs-1"></i>
                        <p class="mt-2 mb-0">Lokasi wisata ini belum tersedia.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <hr class="text-muted my-5">
        <div class="section-head">
            <div>
                <h4 class="section-title mb-0"><i class="bi bi-stars text-warning me-2"></i>Wisata Serupa untuk Anda</h4>
                <p class="section-sub">Rekomendasi berdasarkan kemiripan karakteristik wisata.</p>
            </div>
        </div>

        <div class="row g-4">
            <?php if (!empty($rekomendasi)): ?>
                <?php foreach ($rekomendasi as $rek): ?>
                    <?php $w = $rek['wisata']; ?>
                    <div class="col-xl-3 col-lg-4 col-md-6">
                        <div class="card rek-card bg-white">
                            <img src="<?= !empty($w['image']) ? $w['image'] : 'https://images.unsplash.com/photo-1584853660359-5980a1334c9f?w=400' ?>" class="rek-img w-100" alt="<?= $w['nama'] ?>" loading="lazy">

                            <div class="card-body p-3 d-flex flex-column">
                                <h6 class="dest-title mb-2 text-truncate" title="<?= $w['nama'] ?>" style="font-size: 1.1rem;">
                                    <?= $w['nama'] ?>
                                </h6>
                                <div class="mt-auto">
                                    <div class="similarity-badge mb-3 d-inline-block">
                                        <i class="bi bi-bar-chart-fill me-1"></i> Mirip: <?= round($rek['similarity_score'] * 100, 2) ?>%
                                    </div>
                                    <a href="<?= base_url('index.php/wisata/detail/'.$w['id']) ?>" class="btn btn-rek-detail btn-sm w-100 rounded-3">
                                        Lihat Detail
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="col-12 text-center py-4">
                    <p class="text-muted">Tidak ada rekomendasi wisata serupa yang ditemukan.</p>
                </div>
            <?php endif; ?>
        </div>

    </div> <?php $this->load->view('footer'); ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <?php if ($has_coords): ?>
        <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
        <script src="https://unpkg.com/leaflet-routing-machine@latest/dist/leaflet-routing-machine.js"></script>
        <script>
            (function() {
                var destLat = <?= json_encode((float) $wisata['latitude']) ?>;
                var destLng = <?= json_encode((float) $wisata['longitude']) ?>;
                var wisataNama = <?= json_encode($wisata['nama']) ?>;
                var wisataKategori = <?= json_encode(ucwords(str_replace(['_', '-'], ' ', $wisata['type']))) ?>;
                var routingControl = null;

                var map = L.map('detail-map').setView([destLat, destLng], 15);

                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    maxZoom: 19,
                    attribution: '&copy; OpenStreetMap contributors'
                }).addTo(map);

                var popup = document.createElement('div');
                popup.className = 'text-center';
                var popupNama = document.createElement('b');
                popupNama.style.color = '#1f6fb2';
                popupNama.textContent = wisataNama;
                var popupKategori = document.createElement('div');
                popupKategori.className = 'text-muted small';
                popupKategori.textContent = wisataKategori;
                popup.appendChild(popupNama);
                popup.appendChild(popupKategori);

                L.marker([destLat, destLng]).addTo(map).bindPopup(popup).openPopup();

                // Pastikan ukuran peta dihitung ulang setelah CSS dan layout selesai dimuat
                setTimeout(function() {
                    map.invalidateSize();
                }, 200);

                var routeButton = document.getElementById('btn-route-detail');

                routeButton.addEventListener('click', function() {
                    if (!navigator.geolocation) {
                        alert('Browser Anda tidak mendukung deteksi lokasi.');
                        return;
                    }

                    var originalHtml = routeButton.innerHTML;
                    routeButton.disabled = true;
                    routeButton.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Mencari lokasi...';

                    navigator.geolocation.getCurrentPosition(function(position) {
                        routeButton.disabled = false;
                        routeButton.innerHTML = originalHtml;

                        if (routingControl !== null) {
                            map.removeControl(routingControl);
                        }

                        routingControl = L.Routing.control({
                            waypoints: [
                                L.latLng(position.coords.latitude, position.coords.longitude),
                                L.latLng(destLat, destLng)
                            ],
                            routeWhileDragging: false,
                            fitSelectedRoutes: true,
                            show: false,
                            lineOptions: {
                                styles: [{ color: '#0ea5e9', opacity: 0.8, weight: 6 }]
                            }
                        }).addTo(map);
                    }, function() {
                        routeButton.disabled = false;
                        routeButton.innerHTML = originalHtml;
                        alert('Gagal mendeteksi lokasi. Pastikan GPS aktif dan izin lokasi diizinkan.');
                    }, {
                        enableHighAccuracy: true,
                        timeout: 10000
                    });
                });
            })();
        </script>
    <?php endif; ?>
</body>
</html>
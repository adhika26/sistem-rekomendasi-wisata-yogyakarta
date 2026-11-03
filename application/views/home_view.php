<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EksplorJogja - Rekomendasi Wisata Yogyakarta</title>

    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">

    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <link rel="stylesheet" href="https://unpkg.com/leaflet-routing-machine@latest/dist/leaflet-routing-machine.css" />

    <link rel="stylesheet" href="<?= base_url('assets/css/home_style.css?v=8') ?>">
</head>
<body>

    <?php $this->load->view('navbar'); ?>

    <section class="hero">
        <div class="container">
            <p class="hero-eyebrow">Jelajahi. Rasakan. Kenangi.</p>
            <h1 class="hero-title">Temukan Wisata<br>Luar Biasa di Yogyakarta</h1>
            <p class="hero-sub">
                Rekomendasi destinasi terbaik berdasarkan kedekatan lokasi dan kemiripan karakteristik wisata.
            </p>

            <div class="hero-search search-autocomplete">
                <i class="bi bi-search search-icon"></i>
                <input type="text" id="hero-search-input" class="form-control"
                       placeholder="Cari nama wisata di Yogyakarta..." autocomplete="off"
                       aria-label="Cari nama wisata">
                <button type="button" id="hero-search-button" class="btn">Cari</button>
                <div id="hero-search-suggestions" class="search-suggestions list-group shadow"></div>
            </div>
            <p class="hero-search-note">Mulai dari Candi Prambanan, Pantai Parangtritis, hingga Goa Jomblang.</p>
        </div>
    </section>

    <section class="section">
        <div class="container">
            <div class="section-head">
                <div>
                    <h2 class="section-title">Peta &amp; Wisata Terdekat</h2>
                    <p class="section-sub">Temukan destinasi di sekitar lokasi Anda dan lihat rute perjalanannya langsung dari peta.</p>
                </div>
            </div>

            <div class="row g-4">
                <div class="col-lg-8">
                    <div class="map-shell">
                        <div class="card-header-custom d-flex justify-content-between align-items-center">
                            <h5 class="mb-0 fw-bold text-dark"><i class="bi bi-map text-primary me-2"></i>Peta Lokasi Wisata</h5>
                        </div>
                        <div class="card-body p-0">
                            <div id="map"></div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4">
                    <div class="map-shell">
                        <div class="card-header-custom">
                            <h5 class="mb-0 fw-bold" style="color: var(--accent);">
                                <i class="bi bi-geo-fill me-2"></i>5 Wisata Terdekat
                            </h5>
                            <p class="text-muted small mb-0 mt-1">Berdasarkan lokasi Anda saat ini</p>
                        </div>
                        <div class="card-body p-0">
                            <div id="list-wisata" class="list-group list-group-custom">
                                <div class="p-5 text-center text-muted">
                                    <div class="spinner-border text-primary mb-3" role="status" style="width: 3rem; height: 3rem;"></div>
                                    <br>
                                    <span class="fw-medium">Mendeteksi koordinat GPS...</span>
                                    <p class="small mt-2">Mohon izinkan akses lokasi pada browser Anda.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="section pt-0">
        <div class="container">
            <div class="section-head">
                <div>
                    <h2 class="section-title">Jelajahi Berdasarkan Kategori</h2>
                    <p class="section-sub">Pilih jenis wisata yang paling sesuai dengan gaya perjalanan Anda.</p>
                </div>
            </div>

            <div class="category-grid">
                <a class="category-card" href="<?= base_url('index.php/home/semua_wisata') ?>">
                    <i class="bi bi-tree"></i>
                    <h6>Alam</h6>
                    <p>Pemandangan hijau dan udara segar</p>
                </a>
                <a class="category-card" href="<?= base_url('index.php/home/semua_wisata') ?>">
                    <i class="bi bi-bank"></i>
                    <h6>Budaya &amp; Sejarah</h6>
                    <p>Candi, keraton, dan situs bersejarah</p>
                </a>
                <a class="category-card" href="<?= base_url('index.php/home/semua_wisata') ?>">
                    <i class="bi bi-water"></i>
                    <h6>Pantai</h6>
                    <p>Garis pantai selatan Yogyakarta</p>
                </a>
                <a class="category-card" href="<?= base_url('index.php/home/semua_wisata') ?>">
                    <i class="bi bi-building"></i>
                    <h6>Museum</h6>
                    <p>Koleksi dan cerita di baliknya</p>
                </a>
                <a class="category-card" href="<?= base_url('index.php/home/semua_wisata') ?>">
                    <i class="bi bi-egg-fried"></i>
                    <h6>Kuliner</h6>
                    <p>Cita rasa khas Jogja</p>
                </a>
                <a class="category-card" href="<?= base_url('index.php/home/semua_wisata') ?>">
                    <i class="bi bi-flower1"></i>
                    <h6>Agrowisata</h6>
                    <p>Kebun, desa wisata, dan petualangan</p>
                </a>
            </div>
        </div>
    </section>

    <section class="section pt-0">
        <div class="container">
            <div class="newsletter">
                <div>
                    <h3>Dapatkan inspirasi perjalanan</h3>
                    <p>Rekomendasi wisata terbaru dan tips menjelajah Yogyakarta, langsung ke inbox Anda.</p>
                    <p id="newsletter-message" class="newsletter-note mb-0"></p>
                </div>
                <form id="newsletter-form" class="newsletter-form" action="#" method="post">
                    <input type="email" class="form-control" placeholder="Alamat email Anda" aria-label="Alamat email" required>
                    <button type="submit" class="btn">Berlangganan</button>
                </form>
            </div>
        </div>
    </section>

    <?php $this->load->view('footer'); ?>

    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script src="https://unpkg.com/leaflet-routing-machine@latest/dist/leaflet-routing-machine.js"></script>

    <script>
        // 1. Inisialisasi Peta
        var map = L.map('map').setView([-7.7956, 110.3695], 11);

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19,
            attribution: '&copy; OpenStreetMap contributors'
        }).addTo(map);

        var userLat, userLng;
        var routingControl = null;

        var userIcon = L.divIcon({
            html: '<i class="bi bi-person-standing text-danger custom-user-icon"></i>',
            iconSize: [30, 42],
            iconAnchor: [15, 42],
            className: 'custom-user-icon-container'
        });

        // 2. Minta Izin Lokasi
        if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(function(position) {
                userLat = position.coords.latitude;
                userLng = position.coords.longitude;

                L.marker([userLat, userLng], {icon: userIcon})
                 .addTo(map)
                 .bindPopup("<div class='text-center fw-bold'>Lokasi Anda Saat Ini</div>")
                 .openPopup();

                map.setView([userLat, userLng], 12);

                // 3. AJAX Fetch API
                let formData = new FormData();
                formData.append('latitude', userLat);
                formData.append('longitude', userLng);

                fetch('<?= base_url("index.php/home/get_nearest") ?>', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if(data.status === 'success') {
                        renderWisata(data.data);
                    } else {
                        document.getElementById('list-wisata').innerHTML = `
                            <div class="p-4 text-center text-danger">
                                <i class="bi bi-exclamation-triangle fs-1"></i>
                                <p class="mt-2">Gagal memuat data wisata.</p>
                            </div>
                        `;
                    }
                })
                .catch(err => {
                    console.error('Error fetching nearest wisata:', err);
                });

            }, function(error) {
                document.getElementById('list-wisata').innerHTML = `
                    <div class="p-4 text-center">
                        <i class="bi bi-geo-slash text-danger fs-1"></i>
                        <p class="mt-3 fw-medium">Gagal mendeteksi lokasi.</p>
                        <small class="text-muted">Pastikan GPS aktif dan Anda mengizinkan akses lokasi di browser.</small>
                    </div>
                `;
            });
        } else {
            alert("Browser Anda tidak mendukung Geolocation.");
        }

        // 4. Render hasil wisata
        function renderWisata(wisataData) {
            const listContainer = document.getElementById('list-wisata');
            listContainer.innerHTML = '';

            if(wisataData.length === 0) {
                listContainer.innerHTML = '<div class="p-4 text-center text-muted">Belum ada data wisata yang tersedia.</div>';
                return;
            }

            wisataData.forEach(wisata => {
                const item = document.createElement('div');
                item.className = 'list-group-item list-item-custom';

                let distanceVal = parseFloat(wisata.distance);
                let distanceStr = isNaN(distanceVal) ? 'N/A' : distanceVal.toFixed(2);

                item.innerHTML = `
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div>
                            <h6 class="wisata-title">${wisata.nama}</h6>
                            <span class="jarak-badge"><i class="bi bi-geo-alt-fill me-1"></i>${distanceStr} km</span>
                        </div>
                    </div>
                    <div class="d-flex gap-2">
                        <button class="btn btn-sm btn-route flex-grow-1" data-lat="${wisata.latitude}" data-lng="${wisata.longitude}">
                            <i class="bi bi-sign-turn-right-fill me-1"></i>Lihat Rute
                        </button>
                        <a href="<?= base_url('index.php/wisata/detail/') ?>${wisata.id}" class="btn btn-sm btn-detail px-3 shadow-sm">
                            Detail <i class="bi bi-arrow-right ms-1"></i>
                        </a>
                    </div>
                `;
                listContainer.appendChild(item);

                L.marker([wisata.latitude, wisata.longitude]).addTo(map)
                    .bindPopup(`
                        <div class="text-center">
                            <b style="color: #1f6fb2;">${wisata.nama}</b><br>
                            <span class="text-muted small">Jarak: ${distanceStr} km</span>
                        </div>
                    `);
            });

            document.querySelectorAll('.btn-route').forEach(button => {
                button.addEventListener('click', function(e) {
                    var destLat = this.getAttribute('data-lat');
                    var destLng = this.getAttribute('data-lng');
                    drawRoute(destLat, destLng);
                });
            });
        }

        // 5. Fungsi Menggambar Rute
        function drawRoute(destLat, destLng) {
            if (routingControl != null) {
                map.removeControl(routingControl);
            }
            routingControl = L.Routing.control({
                waypoints: [
                    L.latLng(userLat, userLng),
                    L.latLng(destLat, destLng)
                ],
                routeWhileDragging: false,
                fitSelectedRoutes: true,
                show: false,
                lineOptions: {
                    styles: [{color: '#1f6fb2', opacity: 0.8, weight: 6}]
                }
            }).addTo(map);
        }

        // 6. Pencarian pada hero
        (function() {
            var input = document.getElementById('hero-search-input');
            var box = document.getElementById('hero-search-suggestions');
            var button = document.getElementById('hero-search-button');
            var searchUrl = '<?= base_url("index.php/home/search_autocomplete") ?>';
            var detailBase = '<?= base_url("index.php/wisata/detail/") ?>';
            var semuaUrl = '<?= base_url("index.php/home/semua_wisata") ?>';
            var timer = null;
            var request = null;
            var results = [];
            var activeIndex = -1;

            function escapeHtml(str) {
                return String(str).replace(/[&<>"']/g, function(c) {
                    return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c];
                });
            }

            function formatKategori(str) {
                return String(str).replace(/[_-]/g, ' ').replace(/\b\w/g, function(c) {
                    return c.toUpperCase();
                });
            }

            function closeBox() {
                box.innerHTML = '';
                box.style.display = 'none';
                results = [];
                activeIndex = -1;
            }

            function render() {
                if (!results.length) {
                    closeBox();
                    return;
                }

                box.innerHTML = results.map(function(w, i) {
                    var kategori = w.nama_kategori || w.type || '';
                    var kategoriHtml = kategori
                        ? '<span class="suggestion-category text-muted d-block">' + escapeHtml(formatKategori(kategori)) + '</span>'
                        : '';
                    var active = (i === activeIndex) ? ' active' : '';

                    return '<button type="button" class="list-group-item list-group-item-action' + active + '" data-index="' + i + '">' +
                        '<span class="fw-semibold d-block">' + escapeHtml(w.nama) + '</span>' +
                        kategoriHtml +
                    '</button>';
                }).join('');

                box.style.display = 'block';
            }

            function goTo(index) {
                var wisata = results[index];
                if (wisata) {
                    window.location.href = detailBase + wisata.id;
                }
            }

            function submitSearch() {
                if (results.length) {
                    goTo(activeIndex >= 0 ? activeIndex : 0);
                } else if (input.value.trim() !== '') {
                    window.location.href = semuaUrl;
                }
            }

            input.addEventListener('input', function() {
                var keyword = input.value.trim();
                clearTimeout(timer);

                if (keyword === '') {
                    closeBox();
                    return;
                }

                timer = setTimeout(function() {
                    if (request) {
                        request.abort();
                    }
                    request = new AbortController();

                    fetch(searchUrl + '?q=' + encodeURIComponent(keyword), { signal: request.signal })
                        .then(function(response) {
                            return response.json();
                        })
                        .then(function(data) {
                            results = Array.isArray(data) ? data : [];
                            activeIndex = -1;
                            render();
                        })
                        .catch(function(err) {
                            if (err.name !== 'AbortError') {
                                console.error('Gagal memuat saran wisata:', err);
                            }
                        });
                }, 250);
            });

            input.addEventListener('keydown', function(e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    submitSearch();
                    return;
                }

                if (!results.length) {
                    return;
                }

                if (e.key === 'ArrowDown') {
                    e.preventDefault();
                    activeIndex = (activeIndex + 1) % results.length;
                    render();
                } else if (e.key === 'ArrowUp') {
                    e.preventDefault();
                    activeIndex = (activeIndex - 1 + results.length) % results.length;
                    render();
                } else if (e.key === 'Escape') {
                    closeBox();
                }
            });

            button.addEventListener('click', submitSearch);

            box.addEventListener('mousedown', function(e) {
                var item = e.target.closest('[data-index]');
                if (item) {
                    e.preventDefault();
                    goTo(parseInt(item.getAttribute('data-index'), 10));
                }
            });

            document.addEventListener('click', function(e) {
                if (!e.target.closest('.hero-search')) {
                    closeBox();
                }
            });
        })();

        // 7. Newsletter (tampilan saja)
        (function() {
            var form = document.getElementById('newsletter-form');
            var message = document.getElementById('newsletter-message');

            if (form) {
                form.addEventListener('submit', function(e) {
                    e.preventDefault();
                    message.textContent = 'Terima kasih! Inspirasi perjalanan akan dikirim ke email Anda.';
                    form.reset();
                });
            }
        })();
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

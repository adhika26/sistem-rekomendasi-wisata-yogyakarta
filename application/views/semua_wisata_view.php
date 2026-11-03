<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Semua Wisata - EksplorJogja</title>

    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">

    <link rel="stylesheet" href="<?= base_url('assets/css/home_style.css?v=8') ?>">
</head>
<body>

    <?php $this->load->view('navbar'); ?>

    <section class="page-hero">
        <div class="container">
            <p class="hero-eyebrow">Jelajahi Yogyakarta</p>
            <h1 class="page-title">Destinasi Wisata Yogyakarta</h1>
            <p class="page-sub">Temukan tempat wisata terbaik di Yogyakarta, dari keindahan alam hingga cerita budaya yang tak terlupakan.</p>

            <div class="row justify-content-center">
                <div class="col-lg-6">
                    <div class="search-autocomplete">
                        <i class="bi bi-search search-icon"></i>
                        <input type="text" id="search-wisata" class="form-control"
                               placeholder="Cari nama wisata..." autocomplete="off" aria-label="Cari nama wisata">
                        <div id="search-suggestions" class="search-suggestions list-group shadow"></div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="section pt-0">
        <div class="container">

            <?php
                $kategori_meta = array(
                    'alam' => array('bi-tree', 'Pemandangan alam dan udara segar'),
                    'budaya dan sejarah' => array('bi-bank', 'Candi, keraton, dan situs bersejarah'),
                    'pantai' => array('bi-water', 'Garis pantai selatan Yogyakarta'),
                    'buatan' => array('bi-buildings', 'Wahana dan rekreasi buatan'),
                    'museum' => array('bi-building', 'Koleksi dan cerita di baliknya'),
                    'minat khusus' => array('bi-compass', 'Wisata sesuai minat tertentu'),
                    'agrowisata' => array('bi-flower1', 'Kebun dan wisata petik'),
                    'religi' => array('bi-house-heart', 'Tempat ibadah dan wisata rohani'),
                    'kuliner' => array('bi-egg-fried', 'Cita rasa khas Jogja'),
                    'pendidikan' => array('bi-mortarboard', 'Wisata edukatif untuk belajar'),
                    'wisata air' => array('bi-droplet', 'Kolam, embung, dan wahana air'),
                    'desa wisata' => array('bi-houses', 'Kehidupan dan budaya desa'),
                    'seni' => array('bi-palette', 'Galeri dan pertunjukan seni')
                );
            ?>

            <div class="category-grid mb-4" id="kategori-filter">
                <button type="button" class="category-card filter-btn active" onclick="filterKategori('semua', this)">
                    <i class="bi bi-grid-fill"></i>
                    <h6>Semua</h6>
                    <p>Seluruh destinasi wisata</p>
                </button>

                <?php foreach($kategori as $kode => $nama): ?>
                    <?php
                        $meta_key = strtolower(str_replace(array('_', '-'), ' ', $kode));
                        $meta = isset($kategori_meta[$meta_key]) ? $kategori_meta[$meta_key] : array('bi-tag', 'Jelajahi kategori ini');
                    ?>
                    <button type="button" class="category-card filter-btn" onclick="filterKategori('<?= $kode ?>', this)">
                        <i class="bi <?= $meta[0] ?>"></i>
                        <h6><?= $nama ?></h6>
                        <p><?= $meta[1] ?></p>
                    </button>
                <?php endforeach; ?>
            </div>

            <div class="row g-4" id="wisata-container">
                <?php if(!empty($wisata)): ?>
                    <?php foreach($wisata as $w): ?>
                        <?php $kat_kode = !empty($w['nama_kategori']) ? $w['nama_kategori'] : $w['type']; ?>

                        <div class="col-xl-3 col-lg-4 col-md-6 wisata-item" data-kategori="<?= $kat_kode ?>">
                            <div class="card card-custom dest-card h-100 border-0">
                                <div class="dest-img-wrap">
                                    <img src="<?= !empty($w['image']) ? $w['image'] : 'https://images.unsplash.com/photo-1584853660359-5980a1334c9f?w=400' ?>"
                                         class="card-img-top wisata-card-img" alt="<?= $w['nama'] ?>" loading="lazy">
                                    <span class="dest-cat"><?= ucwords(str_replace(['_', '-'], ' ', $kat_kode)) ?></span>
                                </div>

                                <div class="card-body p-3 d-flex flex-column">
                                    <h6 class="dest-title mb-1 text-truncate" title="<?= $w['nama'] ?>"><?= $w['nama'] ?></h6>

                                    <div class="text-warning small mb-3">
                                        <i class="bi bi-star-fill"></i> <?= $w['vote_average'] ?>
                                        <span class="text-muted">(<?= $w['vote_count'] ?> ulasan)</span>
                                    </div>

                                    <div class="mt-auto">
                                        <a href="<?= base_url('index.php/wisata/detail/'.$w['id']) ?>" class="btn btn-primary btn-sm w-100 rounded-3 fw-medium">
                                            Lihat Detail <i class="bi bi-arrow-right ms-1"></i>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="col-12 text-center py-5">
                        <i class="bi bi-inbox text-muted fs-1"></i>
                        <p class="mt-3 text-muted">Belum ada data wisata yang tersedia.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <?php $this->load->view('footer'); ?>

    <script>
        (function() {
            var input = document.getElementById('search-wisata');
            var box = document.getElementById('search-suggestions');
            var searchUrl = '<?= base_url("index.php/home/search_autocomplete") ?>';
            var detailBase = '<?= base_url("index.php/wisata/detail/") ?>';
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
                } else if (e.key === 'Enter') {
                    if (activeIndex >= 0) {
                        e.preventDefault();
                        goTo(activeIndex);
                    }
                } else if (e.key === 'Escape') {
                    closeBox();
                }
            });

            box.addEventListener('mousedown', function(e) {
                var item = e.target.closest('[data-index]');
                if (item) {
                    e.preventDefault();
                    goTo(parseInt(item.getAttribute('data-index'), 10));
                }
            });

            document.addEventListener('click', function(e) {
                if (!e.target.closest('.search-autocomplete')) {
                    closeBox();
                }
            });
        })();

        function filterKategori(kategori, btnElement) {
            document.querySelectorAll('.filter-btn').forEach(btn => {
                btn.classList.remove('active');
            });
            btnElement.classList.add('active');

            let items = document.querySelectorAll('.wisata-item');
            items.forEach(item => {
                if (kategori === 'semua' || item.getAttribute('data-kategori') === kategori) {
                    item.style.display = 'block';
                    item.style.opacity = '0';
                    setTimeout(() => item.style.opacity = '1', 50);
                } else {
                    item.style.display = 'none';
                }
            });
        }
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

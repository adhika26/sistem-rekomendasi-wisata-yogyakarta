<footer class="custom-footer">
    <div class="container">
        <div class="row g-4 g-lg-5">

            <div class="col-lg-5">
                <a class="footer-brand" href="<?= base_url() ?>">
                    <i class="bi bi-compass"></i>EksplorJogja
                </a>
                <p class="footer-desc">
                    Sistem rekomendasi destinasi wisata di Yogyakarta berbasis kecerdasan buatan dengan
                    mengintegrasikan <strong>Haversine Formula</strong> untuk jarak terdekat dan
                    <strong>Content-Based Filtering</strong> untuk kemiripan karakteristik wisata.
                </p>
                <div class="footer-social">
                    <a href="#" aria-label="Instagram"><i class="bi bi-instagram"></i></a>
                    <a href="#" aria-label="Facebook"><i class="bi bi-facebook"></i></a>
                    <a href="#" aria-label="TikTok"><i class="bi bi-tiktok"></i></a>
                    <a href="#" aria-label="YouTube"><i class="bi bi-youtube"></i></a>
                </div>
            </div>

            <div class="col-6 col-lg-2">
                <h6 class="footer-title">Jelajahi</h6>
                <ul class="footer-links list-unstyled mb-0">
                    <li class="mb-2">
                        <a href="<?= base_url() ?>">Beranda</a>
                    </li>
                    <li class="mb-2">
                        <a href="<?= base_url('index.php/home/semua_wisata') ?>">Semua Wisata</a>
                    </li>
                    <li class="mb-2">
                        <a href="<?= base_url('index.php/home/semua_wisata') ?>">Rekomendasi</a>
                    </li>
                </ul>
            </div>

            <div class="col-6 col-lg-2">
                <h6 class="footer-title">Metode</h6>
                <ul class="footer-links list-unstyled mb-0">
                    <li class="mb-2"><span>Haversine Distance</span></li>
                    <li class="mb-2"><span>Content-Based Filtering</span></li>
                    <li class="mb-2"><span>OpenStreetMap</span></li>
                </ul>
            </div>

            <div class="col-lg-3">
                <h6 class="footer-title">Tentang</h6>
                <p class="footer-desc mt-0">
                    Menemukan tempat wisata Yogyakarta yang paling relevan dan terdekat dari lokasi Anda.
                </p>
            </div>

        </div>

        <hr class="footer-divider">

        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2">
            <p class="footer-copy mb-0">&copy; <?= date('Y') ?> EksplorJogja. All Rights Reserved.</p>
            <div class="footer-legal">
                <a href="#">Kebijakan Privasi</a>
                <a href="#">Syarat Layanan</a>
            </div>
        </div>
    </div>
</footer>

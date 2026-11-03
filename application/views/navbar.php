<?php
    $segment2 = $this->uri->segment(2);
    $is_home = ($segment2 == '');
    $is_semua = ($segment2 == 'semua_wisata');
?>

<nav class="navbar navbar-expand-lg navbar-light navbar-custom sticky-top">
    <div class="container">

        <a class="navbar-brand" href="<?= base_url() ?>">
            <i class="bi bi-compass"></i>EksplorJogja
        </a>

        <button class="navbar-toggler border-0 shadow-none" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Buka menu">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto align-items-lg-center">
                <li class="nav-item">
                    <a class="nav-link <?= $is_home ? 'active' : '' ?>" href="<?= base_url() ?>">
                        Beranda
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= $is_semua ? 'active' : '' ?>" href="<?= base_url('index.php/home/semua_wisata') ?>">
                        Semua Wisata
                    </a>
                </li>
                <?php if (!$is_semua): ?>
                    <li class="nav-item ms-lg-3 mt-2 mt-lg-0">
                        <a class="btn-nav-cta" href="<?= base_url('index.php/home/semua_wisata') ?>">
                            <i class="bi bi-arrow-up-right"></i> Jelajahi
                        </a>
                    </li>
                <?php endif; ?>
            </ul>
        </div>

    </div>
</nav>

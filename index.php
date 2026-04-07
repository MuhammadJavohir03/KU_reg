<?php session_start(); ?>
<?php $title = "Bosh Sahifa"; ?>
<?php require "Includes/header.php"; ?>
<?php require "atmosphere.php"; ?>

<style>
    /* Maxsus Zamonaviy Dizayn */
    :root {
        --ku-blue-dark: #1E3A8A;
        /* Kokand University asosiy rangi */
        --ku-blue-light: #3B82F6;
        --accent-teal: #10B981;
        --accent-orange: #F97316;
        --accent-red: #EF4444;
        --bg-glass: rgba(255, 255, 255, 0.9);
        --border-glass: rgba(255, 255, 255, 0.2);
    }

    body {
        background-color: #f3f4f6;
        font-family: 'Inter', sans-serif;
    }

    /* Asosiy Container: Glassmorphism effekti */
    .glass-container {
        background: var(--bg-glass);
        backdrop-filter: blur(10px);
        border-radius: 24px;
        border: 1px solid var(--border-glass);
        box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.1);
    }

    /* Karusel dizaynini yaxshilash */
    .carousel {
        border-radius: 16px;
        overflow: hidden;
        box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
    }

    .carousel-caption {
        background: rgba(0, 0, 0, 0.5);
        backdrop-filter: blur(5px);
        border-radius: 12px;
        bottom: 20px;
        padding: 15px;
    }

    /* Bo'lim Sarlavhalari */
    .section-title {
        color: var(--ku-blue-dark);
        font-weight: 800;
        position: relative;
        display: inline-block;
        padding-bottom: 10px;
    }

    .section-title::after {
        content: '';
        position: absolute;
        width: 60px;
        height: 4px;
        background: var(--ku-blue-light);
        bottom: 0;
        left: 0;
        border-radius: 2px;
    }

    /* Yangi Gibrid Dizayn: Accordion + Tabs */
    .service-accordion .accordion-item {
        border: none;
        background: transparent;
        margin-bottom: 15px;
    }

    .service-accordion .accordion-button {
        background: #fff;
        border-radius: 16px !important;
        padding: 20px;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
        transition: all 0.3s ease;
        border: 1px solid #e5e7eb;
    }

    .service-accordion .accordion-button:not(.collapsed) {
        background: #fff;
        color: var(--ku-blue-dark);
        box-shadow: 0 10px 15px -3px rgba(59, 130, 246, 0.1);
        border-color: rgba(59, 130, 246, 0.2);
    }

    .service-accordion .accordion-button::after {
        background-color: #f3f4f6;
        border-radius: 50%;
        padding: 15px;
        background-size: 15px;
    }

    /* Ikonkalar uchun konteyner */
    .icon-box {
        width: 50px;
        height: 50px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-right: 15px;
        font-size: 1.3rem;
        color: #fff;
    }

    /* Ro'yxat elementlari */
    .data-list-item {
        background: #fff;
        border-radius: 12px;
        margin-bottom: 8px;
        padding: 12px 20px;
        border: 1px solid #f3f4f6;
        transition: 0.2s;
        display: flex;
        align-items: center;
    }

    .data-list-item:hover {
        background: #f8faff;
        transform: translateX(5px);
        border-color: rgba(59, 130, 246, 0.1);
    }

    .data-list-item b {
        color: var(--ku-blue-light);
        margin-right: 10px;
        font-family: monospace;
    }

    /* Mobil moslashuvchanlik */
    @media (max-width: 768px) {
        .glass-container {
            border-radius: 0;
            padding: 15px !important;
            margin: 0 !important;
        }

        .carousel-item img {
            height: 250px !important;
        }

        .icon-box {
            width: 40px;
            height: 40px;
            font-size: 1.1rem;
            margin-right: 10px;
        }

        .data-list-item {
            font-size: 0.85rem;
            padding: 10px 15px;
        }
    }


    /* Zamonaviy Karusel Stili */
    .custom-carousel {
        border-radius: 24px;
        overflow: hidden;
        box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
        border: 1px solid rgba(255, 255, 255, 0.2);
    }

    .carousel-item {
        transition: transform 0.8s cubic-bezier(0.4, 0, 0.2, 1);
    }

    .carousel-item img {
        height: 450px;
        object-fit: cover;
        filter: brightness(0.85);
        /* Matn yaxshi ko'rinishi uchun rasmni biroz qorong'ulashtirish */
        transition: transform 1.5s ease;
    }

    .carousel-item.active img {
        transform: scale(1.05);
        /* Rasm sekin kattalashish effekti */
    }

    /* Glassmorphism Caption */
    .modern-caption {
        background: rgba(255, 255, 255, 0.15);
        backdrop-filter: blur(15px);
        -webkit-backdrop-filter: blur(15px);
        border: 1px solid rgba(255, 255, 255, 0.2);
        border-radius: 20px;
        padding: 25px;
        bottom: 40px;
        left: 10%;
        right: 10%;
        text-align: left;
        max-width: 500px;
    }

    .modern-caption h5 {
        font-size: 1.6rem;
        text-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
        margin-bottom: 10px;
    }

    .modern-btn {
        background: #fff;
        color: #000;
        padding: 8px 25px;
        border-radius: 50px;
        text-decoration: none;
        font-weight: 600;
        font-size: 0.9rem;
        display: inline-block;
        margin-top: 15px;
        transition: 0.3s;
    }

    .modern-btn:hover {
        background: #3b82f6;
        color: #fff;
        transform: translateY(-3px);
    }

    /* Mobil moslashuv */
    @media (max-width: 768px) {
        .carousel-item img {
            height: 300px;
        }

        .modern-caption {
            bottom: 20px;
            padding: 15px;
        }

        .modern-caption h5 {
            font-size: 1.1rem;
        }
    }
</style>

<body class="gradient-custom">
    <?php require "Includes/yuklash.php"; ?>
    <?php require "Includes/navbar.php"; ?>

    <canvas class="z-n1 position-fixed top-0 start-0 w-100 h-100" id="bg"></canvas>

    <div class="container glass-container p-3 p-md-5 my-md-5 shadow-2xl">

        <div id="modernCarousel" class="carousel slide carousel-fade custom-carousel mb-5" data-bs-ride="carousel">
            <div class="carousel-indicators">
                <button type="button" data-bs-target="#modernCarousel" data-bs-slide-to="0" class="active" aria-current="true"></button>
                <button type="button" data-bs-target="#modernCarousel" data-bs-slide-to="1"></button>
            </div>

            <div class="carousel-inner">
                <div class="carousel-item active">
                    <img src="Logos/bepul.jpg" class="d-block w-100" alt="Qayta topshiruv">
                    <div class="carousel-caption modern-caption animate__animated animate__fadeInLeft">
                        <span class="badge bg-primary mb-2">Qabul ochiq</span>
                        <h5 class="fw-bold">Qayta topshiruv (bahorgi) maktabiga arizalar qabuli</h5>
                        <p class="small d-none d-sm-block">Arizalar 21-mart 2026-yilgacha qabul qilinadi. Kechikmang!</p>
                        <a href="elon1.php" class="modern-btn shadow-sm">Batafsil ma'lumot <i class="fas fa-arrow-right ms-2"></i></a>
                    </div>
                </div>

                <div class="carousel-item">
                    <img src="Logos/2.jpg" class="d-block w-100" alt="Seminar">
                    <div class="carousel-caption modern-caption animate__animated animate__fadeInLeft">
                        <span class="badge bg-success mb-2">Seminar</span>
                        <h5 class="fw-bold">Ijtimoiy hamkorlik loyihalari seminari</h5>
                        <p class="small d-none d-sm-block">Qoʻqon universitetida zamonaviy taʼlim va ijtimoiy integratsiya muhokamasi.</p>
                        <a href="elon2.php" class="modern-btn shadow-sm">Ro'yxatdan o'tish <i class="fas fa-arrow-right ms-2"></i></a>
                    </div>
                </div>
            </div>

            <button class="carousel-control-prev" type="button" data-bs-target="#modernCarousel" data-bs-slide="prev">
                <span class="carousel-control-prev-icon p-3 bg-dark rounded-circle" aria-hidden="true"></span>
            </button>
            <button class="carousel-control-next" type="button" data-bs-target="#modernCarousel" data-bs-slide="next">
                <span class="carousel-control-next-icon p-3 bg-dark rounded-circle" aria-hidden="true"></span>
            </button>
        </div>

        <div class="row mb-5">
            <div class="col-12 text-center mb-4">
                <h2 class="section-title">Talabalar uchun xizmatlar portali</h2>
            </div>
            <div class="col-12">
                <div class="accordion service-accordion" id="serviceAccordion">

                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseAkademik">
                                <div class="icon-box bg-primary shadow">
                                    <i class="fas fa-graduation-cap"></i>
                                </div>
                                <div class="d-flex flex-column">
                                    <span class="fw-bold text-dark">Akademik (o'quv) faoliyat xizmatlari</span>
                                    <small class="text-muted d-none d-sm-block">Ma'lumotnomalar, arizalar, transkriptlar (29 xizmat)</small>
                                </div>
                            </button>
                        </h2>
                        <div id="collapseAkademik" class="accordion-collapse collapse" data-bs-parent="#serviceAccordion">
                            <div class="accordion-body px-2 px-md-4 pt-0">
                                <div class="p-3 bg-light rounded-3 shadow-inner">
                                    <?php
                                    $akademik_raw = [
                                        "A1" => "Akademik (o'quv) faoliyat yuzasidan axborot-maslahat berish",
                                        "A2" => "Akademik ma'lumotnoma va transkript berish",
                                        "A3" => "Talabalarga turli xil ma'lumotnomalar berish",
                                        "A4" => "Talabalarni o'zlashtirish ko'rsatkichlari haqida ma'lumot",
                                        "A5" => "Talabalarning shaxsiy hamda o'quv ma'lumotlarini tahrirlash",
                                        "A6" => "Talabaning familiyasini o'zgartirish to'g'risida ariza",
                                        "A7" => "O'qishni ko'chirish, tiklash va chetlashtirish to'g'risida ariza",
                                        "A8" => "Akademik ta'til olish to'g'risida murojaat",
                                        "A9" => "Darsdan ozod etish to'g'risida arizalar",
                                        "A10" => "Sababli qoldirilgan dars soatlarini belgilash",
                                        "A11" => "Sirtqi va masofaviy ta'lim shakllari uchun chaqiruv xati",
                                        "A12" => "Fanlar farqini aniqlash (o'qishni ko'chirganlar uchun)",
                                        "A13" => "Talaba guvohnomasini (ID karta) yaratish va berish",
                                        "A14" => "Ta'lim shaklini o'zgartirish to'g'risida ariza",
                                        "A15" => "Talabni bir guruhdan ikkinchi guruhga ko'chirish",
                                        "A16" => "Imtihonlar yuzasidan konsultatsiya tashkil etish",
                                        "A17" => "Imtihonlar ro'yxatini shakllantirish va talabaga taqdim etish",
                                        "A18" => "Imtihon natijalari bo'yicha appelyatsiyalarni qabul qilish",
                                        "A19" => "Talabalarga imtihon natijalarini taqdim etish",
                                        "A20" => "Akademik qarzdorliklarni aniqlash",
                                        "A21" => "Qayta o'qishga ariza berish",
                                        "A22" => "Yozgi va qishki maktablarda o'qish uchun ariza berish",
                                        "A23" => "Talabalarga fan resurslari bo'yicha ma'lumot taqdim etish",
                                        "A24" => "Talabalar uchun aylanma varaqa yaratish",
                                        "A25" => "Bitiruvchilarni ishga taqsimlash va joylashishiga oid hujjatlar",
                                        "A26" => "Talabalarga ish o'rni to'g'risida kengroq axborot berish",
                                        "A27" => "To'garaklarga talabalarni ro'yxatga olish",
                                        "A28" => "Arxiv ma'lumotlarini taqdim etish",
                                        "A29" => "QR kodli diplom shakllantirish va berish"
                                    ];
                                    foreach ($akademik_raw as $key => $val) {
                                        echo "<div class='data-list-item'><b>$key.</b> $val</div>";
                                    }
                                    ?>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseIT">
                                <div class="icon-box shadow" style="background-color: var(--accent-teal);">
                                    <i class="fas fa-desktop"></i>
                                </div>
                                <div class="d-flex flex-column">
                                    <span class="fw-bold text-dark">Axborot tizimlari bo'yicha xizmatlar</span>
                                    <small class="text-muted d-none d-sm-block">HEMIS, Wi-Fi, ZOOM va ELMS platformalari</small>
                                </div>
                            </button>
                        </h2>
                        <div id="collapseIT" class="accordion-collapse collapse" data-bs-parent="#serviceAccordion">
                            <div class="accordion-body px-2 px-md-4 pt-0">
                                <div class="p-3 bg-light rounded-3 shadow-inner">
                                    <?php
                                    $it_raw = [
                                        "B1" => "HEMIS va masofaviy ta'lim platformasi tizimi bo'yicha konsultatsiya",
                                        "B2" => "HEMIS tizimida shaxsiy ma'lumotlarni tahrirlash",
                                        "B3" => "HEMIS tizimida login parolni o'zgartirish",
                                        "B4" => "Masofaviy ta'lim platformasida shaxsiy ma'lumotlarni tahrirlash",
                                        "B5" => "Masofaviy ta'lim shakli talabalari uchun ELMS platformasida login",
                                        "B6" => "O'quv jarayoniga oid dasturiy ta'minotlardan foydalanish bo'yicha",
                                        "B7" => "ZOOM dasturiga ulanishga texnik yordam ko'rsatish",
                                        "B8" => "Talabalarni Wi-Fi tarmog'iga ulash"
                                    ];
                                    foreach ($it_raw as $key => $val) {
                                        echo "<div class='data-list-item'><b>$key.</b> $val</div>";
                                    }
                                    ?>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseMoliya">
                                <div class="icon-box shadow" style="background-color: var(--accent-orange);">
                                    <i class="fas fa-coins"></i>
                                </div>
                                <div class="d-flex flex-column">
                                    <span class="fw-bold text-dark">Moliyaviy masalalar bo'yicha xizmatlar</span>
                                    <small class="text-muted d-none d-sm-block">Kontraktlar, stipendiyalar va yotoqxona to'lovlari</small>
                                </div>
                            </button>
                        </h2>
                        <div id="collapseMoliya" class="accordion-collapse collapse" data-bs-parent="#serviceAccordion">
                            <div class="accordion-body px-2 px-md-4 pt-0">
                                <div class="p-3 bg-light rounded-3 shadow-inner">
                                    <?php
                                    $moliya_raw = [
                                        "C1" => "Talabalarga to'lov-shartnomasi olish uchun ariza",
                                        "C2" => "Talabaning to'lov-shartnomasi bo'yicha qarzdorligi",
                                        "C3" => "Qayta o'qishga shartnoma berish",
                                        "C4" => "Talabaning qayta o'qish shartnomasi bo'yicha qarzdorligi",
                                        "C5" => "Hisob varag'ini shakllantirib berish",
                                        "C6" => "Yotoqxonalarga joylashishga shartnoma va yo'llanma berish",
                                        "C7" => "Talabaning yotoqxonalarga joylashish shartnomasi bo'yicha",
                                        "C8" => "Ijtimoiy himoya reyestri va ayollar daftarida turuvchi talabalar"
                                    ];
                                    foreach ($moliya_raw as $key => $val) {
                                        echo "<div class='data-list-item'><b>$key.</b> $val</div>";
                                    }
                                    ?>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>

    </div>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="add.js"></script>
</body>
<?php require "Includes/footer.php"; ?>
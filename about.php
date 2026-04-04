<?php session_start(); ?>
<?php $title = "Registrator Ofisi | Kokand University"; ?>
<?php require "Includes/header.php"; ?>

<style>
    :root {
        --ku-red: #b91c1c;
        --ku-dark: #1e293b;
    }

    body {
        background-color: #f1f5f9;
        font-family: 'Inter', sans-serif;
    }

    /* Hero Section */
    .hero-card {
        background: white;
        border-radius: 20px;
        border-top: 5px solid var(--ku-red);
        overflow: hidden;
    }

    /* Xodimlar kartochkasi */
    .staff-card {
        background: white;
        border-radius: 15px;
        transition: transform 0.3s ease, box-shadow 0.3s ease;
        height: 100%;
        border: 1px solid #e2e8f0;
    }

    .staff-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 20px rgba(0,0,0,0.1) !important;
    }

    .profile-img-container {
        position: relative;
        width: 150px;
        height: 150px;
        margin: 20px auto;
    }

    .profile-img-main {
        width: 150px;
        height: 150px;
        object-fit: cover;
        border: 4px solid white;
        box-shadow: 0 4px 10px rgba(0,0,0,0.1);
    }

    .profile-img-small {
        width: 120px;
        height: 120px;
        object-fit: cover;
        border: 3px solid white;
        box-shadow: 0 4px 10px rgba(0,0,0,0.1);
    }

    .section-title {
        position: relative;
        display: inline-block;
        padding-bottom: 10px;
        margin-bottom: 30px;
        font-weight: 800;
        color: var(--ku-dark);
    }

    .section-title::after {
        content: '';
        position: absolute;
        bottom: 0;
        left: 50%;
        transform: translateX(-50%);
        width: 60px;
        height: 4px;
        background: var(--ku-red);
        border-radius: 2px;
    }

    .contact-info {
        background: #f8fafc;
        border-radius: 12px;
        padding: 15px;
    }

    .badge-role {
        font-size: 0.8rem;
        padding: 5px 12px;
        border-radius: 20px;
        background: #fee2e2;
        color: var(--ku-red);
        font-weight: 600;
    }
</style>

<body>
    <?php require "Includes/yuklash.php"; ?>
    <?php require "Includes/navbar.php"; ?>
    <canvas class="z-n1" id="bg" style="position:fixed; top:0; left:0; width:100%; height:100%;"></canvas>

    <div class="container py-5">
        <div class="hero-card shadow-sm p-4 p-md-5 mb-5 text-center">
            <h1 class="display-5 fw-bold text-danger mb-3">Registrator Ofisi</h1>
            <p class="lead text-muted mx-auto mb-4" style="max-width: 800px;">
                Talabalarning o'quv jarayonida vujudga kelgan savollar, yordam va ko'rsatmalar bo'yicha ko'mak beruvchi asosiy bo'lim.
            </p>
            
            <div class="row g-3 justify-content-center">
                <div class="col-md-4">
                    <div class="contact-info border shadow-sm h-100">
                        <i class="bi bi-calendar-check text-danger h3"></i>
                        <h6 class="fw-bold mt-2">Qabul kunlari</h6>
                        <p class="small mb-0 text-secondary">Dushanba - Juma<br>08:30 - 17:30</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="contact-info border shadow-sm h-100">
                        <i class="bi bi-telephone-outbound text-danger h3"></i>
                        <h6 class="fw-bold mt-2">Aloqa markazi</h6>
                        <p class="small mb-0 text-secondary">+(998) 73 545-33-33<br>info@kokanduniversity.uz</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="text-center mb-5">
            <h2 class="section-title">Ofis Rahbariyati</h2>
            <div class="row justify-content-center">
                <div class="col-lg-5">
                    <div class="staff-card shadow-sm p-4">
                        <div class="profile-img-container">
                            <img class="rounded-circle profile-img-main" src="Reg_office/Ikramov_MuhammadYusuf.jpg" alt="Ikramov MuhammadYusuf">
                        </div>
                        <h4 class="fw-bold mb-1">Ikramov MuhammadYusuf</h4>
                        <p class="text-danger fw-semibold mb-3">Registrator ofisi boshlig'i</p>
                        
                        <button class="btn btn-outline-danger btn-sm rounded-pill px-4" type="button" data-bs-toggle="collapse" data-bs-target="#bioMain">
                            Batafsil ma'lumot
                        </button>
                        
                        <div class="collapse mt-3" id="bioMain">
                            <div class="card card-body border-0 bg-light small text-start">
                                Muhammad Yusuf Ikramov shu kunga qadar Qo‘qon universitetining Iqtisodiyot va ta'lim fakulteti ish yurituvchisi, fakultet dekani muovini mas'ul vazifalarida ishlagan.
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="text-center mb-5">
            <h2 class="section-title">Registrator Hero Bo'limi</h2>
            <div class="row g-4">
                <?php
                $heroes = [
                    ["name" => "Mulaydinov Farxod", "role" => "Hero Mutaxassis", "img" => "Mulaydinov Farxod Murotovich.jpg"],
                    ["name" => "Xo’jamurodov Diyorbek", "role" => "Hero Mutaxassis", "img" => "Diyorbek_Hero.jpg"],
                    ["name" => "Toxirjonov Doniyor", "role" => "Hero Mutaxassis", "img" => "Toxirjonov_Donyor.jpg"],
                    ["name" => "Baxtiyorjonov M.Javoxir", "role" => "Hero Mutaxassis", "img" => "Mulaydinov Farxod Murotovich.jpg"],
                    ["name" => "Saydaliyev Abdulazizxon", "role" => "Hero Mutaxassis", "img" => "Diyorbek_Hero.jpg"],
                    ["name" => "Begzodbek", "role" => "Hero Mutaxassis", "img" => "Toxirjonov_Donyor.jpg"]
                ];
                foreach ($heroes as $hero): ?>
                <div class="col-6 col-md-4 col-lg-2">
                    <div class="staff-card shadow-sm p-3">
                        <img class="rounded-circle profile-img-small mb-3" src="Reg_office/<?= $hero['img'] ?>" alt="<?= $hero['name'] ?>">
                        <h6 class="fw-bold mb-1 small"><?= $hero['name'] ?></h6>
                        <span class="badge-role">Hero</span>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="text-center">
            <h2 class="section-title">Asosiy Shtab</h2>
            <div class="row g-4 justify-content-center">
                <?php
                $shtab = [
                    ["name" => "Xatamqulov Munavvarjon", "img" => "Munavvar.jpg"],
                    ["name" => "Zaylobiddinov Diyorbek", "img" => "Diyor.jpg"],
                    ["name" => "Oxunov Lazizxon", "img" => "laziz.jpg"],
                    ["name" => "Sodiqjonov Yaxyobek", "img" => "yaxyo.jpg"],
                    ["name" => "Aliyev Alibek", "img" => "ali.jpg"],
                    ["name" => "Xasanova Mohimbegim", "img" => "mohim.jpg"],
                    ["name" => "Ermuhammedov Abdullajon", "img" => "placeholder.jpg"]
                ];
                foreach ($shtab as $member): ?>
                <div class="col-6 col-md-4 col-lg-3">
                    <div class="staff-card shadow-sm p-3">
                        <img class="rounded-circle profile-img-small mb-3" src="Reg_office/<?= $member['img'] ?>" alt="<?= $member['name'] ?>" onerror="this.src='https://via.placeholder.com/150'">
                        <h6 class="fw-bold mb-1 small"><?= $member['name'] ?></h6>
                        <p class="text-muted extra-small mb-0">Asosiy shtab a'zosi</p>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="add.js"></script>
    <?php require "Includes/footer.php"; ?>
</body>
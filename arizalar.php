<?php session_start(); ?>
<?php require "Includes/header.php"; ?>
<?php require "atmosphere.php"; 
$title = "Arizalar"; ?>


<style>
    :root {
        --bg-dark: #0f172a;
        --card-bg: rgba(255, 255, 255, 0.05);
        --accent-free: #2ecc71; /* Yashil - bepul uchun */
        --accent-paid: #3498db; /* Moviy - pullik uchun */
    }

    body {
        margin: 0;
        background: var(--bg-dark);
        font-family: 'Inter', sans-serif;
        min-height: 100vh;
        display: flex;
        flex-direction: column;
    }

    #bg {
        position: fixed;
        top: 0; left: 0; width: 100%; height: 100%;
        z-index: -1;
    }

    .selection-wrapper {
        flex: 1;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 20px;
    }

    .selection-container {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 25px;
        max-width: 800px;
        width: 100%;
    }

    .option-card {
        background: rgba(30, 41, 59, 0.7);
        backdrop-filter: blur(10px);
        border: 1px solid rgba(255, 255, 255, 0.1);
        border-radius: 20px;
        padding: 40px 30px;
        text-align: center;
        transition: all 0.3s ease;
        text-decoration: none;
        display: flex;
        flex-direction: column;
        align-items: center;
    }

    .option-card:hover {
        transform: translateY(-10px);
        background: rgba(30, 41, 59, 0.9);
        border-color: rgba(255, 255, 255, 0.2);
        box-shadow: 0 20px 40px rgba(0,0,0,0.3);
    }

    .icon-box {
        width: 70px;
        height: 70px;
        border-radius: 18px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-bottom: 20px;
        font-size: 30px;
    }

    .free-icon { background: rgba(46, 204, 113, 0.2); color: var(--accent-free); }
    .paid-icon { background: rgba(52, 152, 219, 0.2); color: var(--accent-paid); }

    .option-card h3 {
        color: white;
        margin-bottom: 15px;
        font-size: 1.25rem;
        font-weight: 600;
    }

    .option-card p {
        color: #94a3b8;
        font-size: 0.9rem;
        line-height: 1.5;
        margin-bottom: 25px;
    }

    .btn-action {
        padding: 10px 25px;
        border-radius: 10px;
        font-weight: 600;
        font-size: 0.9rem;
        transition: 0.3s;
        border: none;
    }

    .btn-free { background: var(--accent-free); color: #fff; }
    .btn-paid { background: var(--accent-paid); color: #fff; }

    .option-card:hover .btn-free { background: #27ae60; box-shadow: 0 0 15px rgba(46, 204, 113, 0.4); }
    .option-card:hover .btn-paid { background: #2980b9; box-shadow: 0 0 15px rgba(52, 152, 219, 0.4); }

    /* Mobil uchun */
    @media (max-width: 650px) {
        .selection-container {
            grid-template-columns: 1fr;
        }
        .option-card {
            padding: 30px 20px;
        }
    }
</style>

<body>
    <?php require "Includes/yuklash.php"; ?>
    <?php require "Includes/navbar.php"; ?>
    <canvas class="z-n1" id="bg"></canvas>

    <div class="selection-wrapper">
        <div class="selection-container">
            
            <a href="ariza_bepul.php" class="option-card">
                <div class="icon-box free-icon">
                    <i class="bi bi-gift"></i> ✨
                </div>
                <h3>Bepul imkoniyatga ariza topshirish</h3>
                <p>Belgilangan kvota doirasida imtihonni bepul qayta topshirish uchun ariza yuboring.</p>
                <span class="btn-action btn-free">Tanlash</span>
            </a>

            <a href="ariza_pullik.php" class="option-card">
                <div class="icon-box paid-icon">
                    <i class="bi bi-credit-card"></i> 💳
                </div>
                <h3>Mini semestrga ro'yxatdan o'tish</h3>
                <p>Kvota tugagan yoki qo'shimcha imkoniyat uchun pullik qayta topshirishga ariza bering.</p>
                <span class="btn-action btn-paid">Tanlash</span>
            </a>

        </div>
    </div>

    <?php require "Includes/footer.php"; ?>
    <script src="add.js"></script>
</body>
</html>
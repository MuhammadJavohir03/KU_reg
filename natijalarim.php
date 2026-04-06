<?php
require "database.php";
session_start();
$title = "Natijalarim";

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// 1-QADAM: Tizimga kirgan foydalanuvchi ma'lumotlarini olish
$user_id = $_SESSION['user_id'];
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

// Foydalanuvchining talaba_id raqamini olamiz
$t_id = $user['talaba_id'];

// 2 va 3-QADAM: talabalar jadvalidan talaba_id orqali fanlarni va natijalarni olish, 
// so'ngra fanlar jadvalidan ularning semestrini aniqlash
$sql = "
    SELECT 
        f.id as f_id,
        f.nomi as fan_nomi,
        f.semestr as fan_semestr,
        t.joriy_nazorat,
        t.oraliq_nazorat,
        t.reyting,
        t.yakuniy_nazorat,
        t.qayta_topshirish,
        t.umumiy,
        t.davomat
    FROM talabalar t
    INNER JOIN fanlar f ON t.fan_id = f.id
    WHERE t.talaba_id = ?
    ORDER BY f.semestr ASC, f.nomi ASC
";

$stmt = $pdo->prepare($sql);
$stmt->execute([$t_id]);
$results = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Natijalarni semestrlar bo'yicha guruhlash
$semesters = [];
foreach ($results as $res) {
    $semesters[$res['fan_semestr']][] = $res;
}
?>

<?php require "Includes/header.php"; ?>
<?php require "atmosphere.php"; ?>

<style>
    html, body {
        overflow-y: auto !important;
        background: <?= $is_night ? '#0f172a' : '#f8fafc' ?>;
        font-family: 'Plus Jakarta Sans', sans-serif;
    }

    .user-banner {
        background: <?= $is_night ? 'rgba(30, 41, 59, 0.7)' : 'linear-gradient(135deg, #1e3a8a 0%, #3b82f6 100%)' ?>;
        backdrop-filter: blur(15px);
        border-radius: 24px;
        padding: 35px;
        color: white;
        margin-bottom: 30px;
        box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
    }

    .sem-heading {
        font-size: 1.5rem;
        font-weight: 800;
        color: <?= $is_night ? '#f8fafc' : '#1e293b' ?>;
        margin: 40px 0 20px;
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .sem-container-active {
        background: <?= $is_night ? 'rgba(255, 255, 255, 0.03)' : 'rgba(59, 130, 246, 0.03)' ?>;
        padding: 20px;
        border-radius: 28px;
        border: 1px solid <?= $is_night ? 'rgba(255,255,255,0.05)' : 'rgba(59, 130, 246, 0.1)' ?>;
        margin-bottom: 20px;
    }

    .subject-card {
        background: <?= $is_night ? 'rgba(30, 41, 59, 0.8)' : '#ffffff' ?>;
        border-radius: 20px;
        padding: 25px;
        border: 1px solid <?= $is_night ? 'rgba(255, 255, 255, 0.1)' : '#eef2f6' ?>;
        transition: all 0.3s ease;
        height: 100%;
        cursor: pointer;
        color: <?= $is_night ? '#ffffff' : '#1e293b' ?>; /* Umumiy matn rangi */
        position: relative;
    }

    .subject-card:hover {
        transform: translateY(-5px);
        border-color: #3b82f6;
        box-shadow: 0 15px 30px rgba(0,0,0,0.1);
    }

    .ball-badge {
        position: absolute;
        top: 20px;
        right: 20px;
        width: 48px;
        height: 48px;
        background: #3b82f6;
        color: white;
        border-radius: 12px;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        font-weight: 800;
    }

    .stat-label { font-size: 0.6rem; color: #94a3b8; font-weight: 700; text-transform: uppercase; }
    .stat-value { font-size: 0.9rem; font-weight: 700; color: color: <?= $is_night ? '#ffffff' : '#1e293b' ?>; }
</style>

<body>
    <?php require "Includes/navbar.php"; ?>

    <div class="container py-5">
        <div class="user-banner">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h1 class="fw-bold mb-1"><?= htmlspecialchars($user['fio']) ?></h1>
                    <p class="mb-0 opacity-75">
                        <i class="bi bi-mortarboard-fill"></i> <?= htmlspecialchars($user['guruh'] ?? 'Guruh topilmadi') ?> | ID: <?= htmlspecialchars($t_id) ?>
                    </p>
                </div>
            </div>
        </div>

        <?php for ($s = 1; $s <= 8; $s++): 
            if (!isset($semesters[$s])) continue; 
        ?>
            <div class="sem-heading">
                <i class="bi bi-bookmarks-fill text-primary"></i> <?= $s ?>-Semestr
            </div>

            <div class="sem-container-active">
                <div class="row g-4">
                    <?php foreach ($semesters[$s] as $res): 
                        $json = htmlspecialchars(json_encode($res, JSON_UNESCAPED_UNICODE), ENT_QUOTES, 'UTF-8');
                    ?>
                        <div class="col-xl-3 col-lg-4 col-md-6">
                            <div class="subject-card" onclick='openModal(<?= $json ?>)'>
                                <div class="ball-badge">
                                    <small style="font-size: 0.5rem; opacity: 0.8;">BALL</small>
                                    <?= $res['umumiy'] ?? 0 ?>
                                </div>
                                <h5 class="fw-bold mb-3 pe-5" style="font-size: 1rem; min-height: 45px;">
                                    <?= htmlspecialchars($res['fan_nomi']) ?>
                                </h5>

                                <div class="row g-2 border-top border-secondary border-opacity-10 pt-3">
                                    <div class="col-12 mb-2">
                                        <div class="stat-label">DAVOMAT</div>
                                        <div class="stat-value text-primary fw-bold"><?= $res['davomat'] ?? 0 ?>%</div>
                                    </div>
                                    <div class="col-6">
                                        <div class="stat-label">JORIY</div>
                                        <div class="stat-value"><?= $res['joriy_nazorat'] ?? 0 ?></div>
                                    </div>
                                    <div class="col-6 text-end">
                                        <div class="stat-label">ORALIQ</div>
                                        <div class="stat-value"><?= $res['oraliq_nazorat'] ?? 0 ?></div>
                                    </div>
                                    <div class="col-6">
                                        <div class="stat-label">YAKUNIY</div>
                                        <div class="stat-value"><?= $res['yakuniy_nazorat'] ?? 0 ?></div>
                                    </div>
                                    <div class="col-6 text-end">
                                        <div class="stat-label">UMUMIY</div>
                                        <div class="stat-value fw-bold text-primary"><?= $res['umumiy'] ?? 0 ?></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endfor; ?>
    </div>

    <div class="modal fade" id="infoModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg" style="border-radius: 28px;">
                <div class="modal-body p-4">
                    <div class="text-center mb-4">
                        <div class="display-4 fw-bold text-primary" id="v-umumiy">0</div>
                        <div class="text-muted fw-bold small">YAKUNIY NATIJA</div>
                    </div>
                    <div class="bg-light rounded-4 p-4 mb-4">
                        <h6 class="fw-bold text-center border-bottom pb-2 mb-3 text-dark" id="v-title">Fan nomi</h6>
                        <div class="row g-3 text-center">
                            <div class="col-4">
                                <div class="stat-label small">Joriy</div>
                                <div class="fw-bold text-dark" id="v-joriy">0</div>
                            </div>
                            <div class="col-4">
                                <div class="stat-label small">Oraliq</div>
                                <div class="fw-bold text-dark" id="v-oraliq">0</div>
                            </div>
                            <div class="col-4">
                                <div class="stat-label small">Reyting</div>
                                <div class="fw-bold text-dark" id="v-reyting">0</div>
                            </div>
                        </div>
                    </div>
                    <button class="btn btn-primary w-100 py-3 rounded-4 fw-bold shadow-sm" data-bs-dismiss="modal">Yopish</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function openModal(data) {
            document.getElementById('v-title').innerText = data.fan_nomi;
            document.getElementById('v-joriy').innerText = data.joriy_nazorat || 0;
            document.getElementById('v-oraliq').innerText = data.oraliq_nazorat || 0;
            document.getElementById('v-reyting').innerText = data.reyting || 0;
            document.getElementById('v-umumiy').innerText = data.umumiy || 0;
            new bootstrap.Modal(document.getElementById('infoModal')).show();
        }
    </script>
</body>
</html>
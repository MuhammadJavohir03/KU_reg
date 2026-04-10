<?php
require "database.php";
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;

}

require "ajax_helper.php";

$id = $_SESSION['user_id'];
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$user) exit("Foydalanuvchi topilmadi");

$fio = $user['fio'];
$talaba_id = $user['talaba_id'];
$u_id = $user['id'];

// --- 1. MAVJUD ARIZANI OLISH (Topshirilganlarni aniqlash) ---
$current_stmt = $pdo->prepare("SELECT fan1, fan2, fan3, fan4 FROM bepul WHERE talaba_id = ?");
$current_stmt->execute([$talaba_id]);
$current_ariza = $current_stmt->fetch(PDO::FETCH_ASSOC);
$submitted_fans = $current_ariza ? array_filter([$current_ariza['fan1'], $current_ariza['fan2'], $current_ariza['fan3'], $current_ariza['fan4']]) : [];

// --- 2. FILTRLANGAN FANLAR RO'YXATI ---
$fanlar_stmt = $pdo->prepare("
    SELECT f.id, f.nomi, f.semestr FROM talabalar t 
    JOIN fanlar f ON t.fan_id = f.id 
    WHERE t.user_id = ? AND t.reyting >= 20 AND t.davomat < 33 AND t.umumiy <= 60
    ORDER BY f.semestr ASC
");
$fanlar_stmt->execute([$fio]);
$fanlar_list = $fanlar_stmt->fetchAll(PDO::FETCH_ASSOC);

// --- 3. ARIZA TOPSHIRISH MANTIQI ---
if (isset($_POST['submit'])) {
    $selected = isset($_POST['fanlar']) ? array_filter(array_unique($_POST['fanlar'])) : [];
    $new_valid = [];
    foreach ($selected as $f_id) {
        if (!in_array($f_id, $submitted_fans)) $new_valid[] = $f_id;
    }

    if (!empty($new_valid)) {
        $total = array_merge($submitted_fans, $new_valid);
        if (count($total) <= 4) {
            $f = array_pad($total, 4, null);
            $sql = "INSERT INTO bepul (user_id, talaba_id, fan1, fan2, fan3, fan4, created_at) 
                    VALUES (?, ?, ?, ?, ?, ?, NOW()) 
                    ON DUPLICATE KEY UPDATE fan1=VALUES(fan1), fan2=VALUES(fan2), fan3=VALUES(fan3), fan4=VALUES(fan4)";
            $pdo->prepare($sql)->execute([$u_id, $talaba_id, $f[0], $f[1], $f[2], $f[3]]);
            echo "<script>alert('✅ Arizangiz qabul qilindi!'); window.location.href='ariza_bepul.php';</script>";
        } else {
            echo "<script>alert('❌ Jami fanlar 4 tadan oshmasligi kerak!');</script>";
        }
    }
}
?>

<?php require "Includes/header.php"; ?>
<style>
    body {
        background: #0f172a;
        color: white;
        font-family: 'Inter', sans-serif;
        overflow-y: auto !important;
    }

    .glass-card {
        background: rgba(30, 41, 59, 0.7);
        backdrop-filter: blur(12px);
        border: 1px solid rgba(255, 255, 255, 0.1);
        border-radius: 20px;
        padding: 30px;
    }

    .form-control-static {
        background: rgba(255, 255, 255, 0.05);
        border: 1px solid rgba(255, 255, 255, 0.1);
        color: #e2e8f0;
        border-radius: 10px;
        padding: 10px;
        margin-bottom: 15px;
    }

    .form-input {
        background: #fff;
        color: #0f172a;
        border-radius: 10px;
        padding: 12px;
    }

    .btn-add {
        background: rgba(46, 204, 113, 0.1);
        color: #2ecc71;
        border: 1px dashed #2ecc71;
        width: 100%;
        padding: 10px;
        border-radius: 10px;
    }

    .btn-submit {
        background: #2ecc71;
        color: white;
        font-weight: 700;
        border: none;
        border-radius: 12px;
        padding: 15px;
        width: 100%;
        margin-top: 20px;
    }

    .submitted-list {
        background: rgba(46, 204, 113, 0.05);
        border-left: 4px solid #2ecc71;
        padding: 15px;
        border-radius: 8px;
    }
</style>

<body>
    <?php require "Includes/navbar.php"; ?>
    <?php require "atmosphere.php"; ?>
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <div class="glass-card shadow-lg">
                    <h2 class="text-white mb-4">✨ Bepul ariza topshirish</h2>
                    <form method="POST">
                        <div class="row">
                            <div class="col-md-5 border-end border-secondary">
                                <label class="small text-white opacity-75">Talaba ma'lumotlari</label>
                                <div class="form-control-static"><?= $user['talaba_id'] ?> | <?= $user['fio'] ?></div>
                                <div class="form-control-static"><?= $user['guruh'] ?></div>

                                <?php if (!empty($submitted_fans)): ?>
                                    <div class="submitted-list mt-3">
                                        <h6 class="small text-success fw-bold">Topshirilgan fanlar:</h6>
                                        <?php foreach ($submitted_fans as $sid):
                                            // SQL so'rovida nomi va semstr ustunlarini tanlaymiz
                                            $fn = $pdo->prepare("SELECT nomi, semestr FROM fanlar WHERE id = ?");
                                            $fn->execute([$sid]);

                                            // fetchColumn() o'rniga fetch() ishlatamiz, u massiv qaytaradi
                                            $fan = $fn->fetch();

                                            if ($fan): ?>
                                                <div class="small text-white opacity-75 border-bottom border-secondary py-1">
                                                    ✅ <?= htmlspecialchars($fan['nomi']) ?>
                                                    <span class="ms-2 badge rounded-pill" style="background-color: rgba(13, 202, 240, 0.15); color: #0dcaf0; border: 1px solid rgba(13, 202, 240, 0.3); font-weight: 500; font-size: 0.75rem;">
                                                        <?= htmlspecialchars($fan['semestr']) ?>-semestr
                                                    </span>
                                                </div>
                                            <?php endif; ?>
                                        <?php endforeach; ?>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <div class="col-md-7 ps-md-4">
                                <h5 class="text-white mb-3">Fanlarni tanlang</h5>
                                <div id="fan-container">
                                    <div class="fan-row mb-2">
                                        <select name="fanlar[]" class="form-control form-input" required>
                                            <option value="">Fan tanlang...</option>
                                            <?php foreach ($fanlar_list as $f):
                                                $is_done = in_array($f['id'], $submitted_fans); ?>
                                                <option value="<?= $f['id'] ?>" <?= $is_done ? 'disabled style="color:gray"' : '' ?>>
                                                    <?= htmlspecialchars($f['nomi']) ?> <?= $is_done ? '— (✅ TOPSHIRILGAN)' : '' ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>
                                <button type="button" id="addFan" class="btn btn-add mt-2"><i class="bi bi-plus-circle"></i> Fan qo'shish</button>
                                <button name="submit" type="submit" class="btn bg-success text-white btn-submit shadow">ARIZANI YUBORISH</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        let maxFans = 4 - <?= count($submitted_fans) ?>;
        let current = 1;
        document.getElementById("addFan").onclick = function() {
            if (current >= maxFans) {
                alert("Maksimal 4 ta fan bo'lishi mumkin!");
                return;
            }
            let div = document.createElement("div");
            div.className = "fan-row mb-2 d-flex gap-2";
            div.innerHTML = `<select name="fanlar[]" class="form-control form-input"><option value="">Fan tanlang...</option><?php foreach ($fanlar_list as $f): $is_done = in_array($f['id'], $submitted_fans); ?><option value="<?= $f['id'] ?>" <?= $is_done ? 'disabled' : '' ?>><?= htmlspecialchars($f['nomi']) ?></option><?php endforeach; ?></select><button type="button" class="btn btn-danger" onclick="this.parentElement.remove(); current--;">✕</button>`;
            document.getElementById("fan-container").appendChild(div);
            current++;
        };
    </script>
</body>

</html>
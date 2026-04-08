<?php
require "database.php";
session_start();
$title = "Pullik Ariza Topshirish";

// 1. Sessiyani tekshirish
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$id = $_SESSION['user_id'];
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) exit("Foydalanuvchi topilmadi");

$fio = $user['fio'];
$talaba_id = $user['talaba_id'];
$u_id = $user['id'];

// --- 2. MAVJUD ARIZANI OLISH (Topshirilgan fanlarni aniqlash) ---
$current_stmt = $pdo->prepare("SELECT fan1, fan2, fan3, fan4 FROM pullik WHERE talaba_id = ?");
$current_stmt->execute([$talaba_id]);
$current_ariza = $current_stmt->fetch(PDO::FETCH_ASSOC);
$submitted_fans = $current_ariza ? array_filter([$current_ariza['fan1'], $current_ariza['fan2'], $current_ariza['fan3'], $current_ariza['fan4']]) : [];

// --- 3. FILTRLANGAN FANLAR RO'YXATINI OLISH ---
$fanlar_stmt = $pdo->prepare("
    SELECT f.id, f.nomi, f.semestr, t.umumiy 
    FROM talabalar t 
    JOIN fanlar f ON t.fan_id = f.id 
    WHERE t.user_id = ? 
      AND t.umumiy <= 60
    ORDER BY f.semestr ASC, f.nomi ASC
");
$fanlar_stmt->execute([$fio]);
$fanlar_list = $fanlar_stmt->fetchAll(PDO::FETCH_ASSOC);

// --- 4. ARIZA YUBORILGANDA (Submit) ---
if (isset($_POST['submit'])) {
    $selected_fanlar = isset($_POST['fanlar']) ? array_filter(array_unique($_POST['fanlar'])) : [];
    $hemis_parol = trim($_POST['hemis_parol'] ?? '');

    if (empty($selected_fanlar) && empty($submitted_fans)) {
        echo "<script>alert('❌ Kamida bitta fan tanlang');</script>";
    } else {
        $new_valid_fans = [];
        foreach ($selected_fanlar as $f_id) {
            if (!in_array($f_id, $submitted_fans)) {
                $new_valid_fans[] = $f_id;
            }
        }

        $total_fans = array_merge($submitted_fans, $new_valid_fans);

        if (count($total_fans) > 4) {
            echo "<script>alert('❌ Umumiy fanlar soni 4 tadan oshib ketmasligi kerak!');</script>";
        } else {
            $f = array_pad($total_fans, 4, null);

            $sql = "INSERT INTO pullik (user_id, talaba_id, fan1, fan2, fan3, fan4, hemis_parol, created_at) 
                    VALUES (:u_id, :t_id, :f1, :f2, :f3, :f4, :h_parol, NOW()) 
                    ON DUPLICATE KEY UPDATE 
                    fan1 = VALUES(fan1), fan2 = VALUES(fan2), fan3 = VALUES(fan3), fan4 = VALUES(fan4),
                    hemis_parol = VALUES(hemis_parol)";

            $insert = $pdo->prepare($sql);
            $insert->execute([
                ':u_id' => $u_id,
                ':t_id' => $talaba_id,
                ':f1' => $f[0],
                ':f2' => $f[1],
                ':f3' => $f[2],
                ':f4' => $f[3],
                ':h_parol' => $hemis_parol
            ]);

            echo "<script>alert('✅ Pullik arizangiz muvaffaqiyatli saqlandi!'); window.location.href='arizalar.php';</script>";
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
    }

    .glass-card {
        background: rgba(30, 41, 59, 0.7);
        backdrop-filter: blur(12px);
        border: 1px solid rgba(255, 255, 255, 0.1);
        border-radius: 20px;
        padding: 35px;
        box-shadow: 0 20px 50px rgba(0, 0, 0, 0.4);
    }

    .form-control-static {
        background: rgba(255, 255, 255, 0.05);
        border: 1px solid rgba(255, 255, 255, 0.1);
        color: #e2e8f0;
        border-radius: 10px;
        padding: 10px 15px;
        margin-bottom: 12px;
        font-size: 0.9rem;
    }

    .form-input {
        background: #fff;
        color: #0f172a;
        border-radius: 10px;
        padding: 12px;
        border: none;
    }

    .btn-add {
        background: rgba(52, 152, 219, 0.1);
        color: #3498db;
        border: 1px dashed #3498db;
        width: 100%;
        padding: 10px;
        border-radius: 10px;
        transition: 0.3s;
    }

    .btn-add:hover {
        background: rgba(52, 152, 219, 0.2);
    }

    .btn-submit {
        background: #3498db;
        color: white;
        font-weight: 700;
        border: none;
        border-radius: 12px;
        padding: 15px;
        width: 100%;
        margin-top: 20px;
        box-shadow: 0 4px 15px rgba(52, 152, 219, 0.3);
    }

    .submitted-box {
        background: rgba(52, 152, 219, 0.1);
        border-radius: 12px;
        padding: 15px;
        margin-top: 15px;
        border: 1px solid rgba(52, 152, 219, 0.2);
    }
</style>

<body>
    <?php require "Includes/navbar.php"; ?>
    <?php require "atmosphere.php"; ?>
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-lg-11">
                <div class="glass-card">
                    <div class="mb-4 text-center">
                        <h2 class="text-white mb-1">💳 Mini semestrga ro'yxatdan o'tish</h2>
                        <span class="badge bg-info text-dark">Pullik qayta topshirish</span>
                    </div>

                    <form method="POST">
                        <div class="row">
                            <div class="col-md-5 border-end border-secondary pe-md-4">
                                <label class="small opacity-75">Talaba ma'lumotlari</label>
                                <div class="form-control-static">ID: <?= $user['talaba_id'] ?></div>
                                <div class="form-control-static"><?= $user['fio'] ?></div>
                                <div class="form-control-static"><?= $user['guruh'] ?></div>
                                <?php if (!empty($submitted_fans)): ?>
                                    <div class="submitted-box">
                                        <h6 class="text-info small fw-bold mb-2"><i class="bi bi-check-circle-fill"></i> Topshirilgan fanlar:</h6>
                                        <?php foreach ($submitted_fans as $sid):
                                            $fn = $pdo->prepare("SELECT nomi FROM fanlar WHERE id = ?");
                                            $fn->execute([$sid]); ?>
                                            <div class="small mb-1 text-white opacity-90 border-bottom border-secondary pb-1">
                                                ✅ <?= htmlspecialchars($fn->fetchColumn()) ?>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <div class="col-md-7 ps-md-4">
                                <h5 class="mb-3">Qayta o'qiladigan fanlar (Maks 4 ta)</h5>
                                <div id="fan-container">
                                    <div class="fan-row mb-2">
                                        <select name="fanlar[]" class="form-control form-input" required>
                                            <option value="">Fan tanlang...</option>
                                            <?php foreach ($fanlar_list as $f):
                                                $done = in_array($f['id'], $submitted_fans); ?>
                                                <option value="<?= $f['id'] ?>" <?= $done ? 'disabled style="color:#999"' : '' ?>>
                                                    <?= htmlspecialchars($f['nomi']) ?> <?= $done ? '— (TOPShIRILGAN)' : '' ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>

                                <button type="button" id="addFan" class="btn btn-add mt-2">
                                    <i class="bi bi-plus-circle"></i> Fan qo'shish
                                </button>

                                <button name="submit" type="submit" class="btn bg-success text-white btn-submit">
                                    ARIZANI SAQLASH VA YUBORISH
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        let maxAllowed = 4 - <?= count($submitted_fans) ?>;
        let currentCount = 1;

        document.getElementById("addFan").onclick = function() {
            if (currentCount >= maxAllowed) {
                alert("Siz jami 4 ta fan tanlab bo'ldingiz yoki limitga yetdingiz!");
                return;
            }
            let div = document.createElement("div");
            div.className = "fan-row mb-2 d-flex gap-2";
            div.innerHTML = `
                <select name="fanlar[]" class="form-control form-input">
                    <option value="">Fan tanlang...</option>
                    <?php foreach ($fanlar_list as $f): $done = in_array($f['id'], $submitted_fans); ?>
                        <option value="<?= $f['id'] ?>" <?= $done ? 'disabled' : '' ?>>
                            <?= htmlspecialchars($f['nomi']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <button type="button" class="btn btn-danger" onclick="this.parentElement.remove(); currentCount--;">✕</button>
            `;
            document.getElementById("fan-container").appendChild(div);
            currentCount++;
        };
    </script>
</body>

</html>
<?php
require "database.php";
session_start();

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

// 2. Ariza yuborilganda (Submit)
if (isset($_POST['submit'])) {
    $fanlar = isset($_POST['fanlar']) ? array_filter($_POST['fanlar']) : [];
    $u_id = $user['id'];             // users table raqamli ID
    $fio = $user['fio'];             // users table FIO (varcharda saqlangan)
    $talaba_id = $user['talaba_id'];
    $hemis_parol = trim($_POST['hemis_parol']);

    // 3. Takroriy ariza tekshiruvi
    $check = $pdo->prepare("SELECT id FROM bepul WHERE user_id = ?");
    $check->execute([$u_id]);
    if ($check->fetch()) {
        echo "<script>alert('❌ Siz allaqachon ariza topshirgansiz'); window.location.href='arizalar.php';</script>";
        exit;
    }

    if (empty($fanlar)) {
        echo "<script>alert('❌ Kamida bitta fan tanlang');</script>";
    } else {
        $validFans = [];

        foreach ($fanlar as $fan_id) {
            if (!$fan_id) continue;

            // 4. FIO bo'yicha talabalar jadvalidan tekshirish
            $stmt = $pdo->prepare("SELECT reyting, davomat, umumiy FROM talabalar WHERE user_id = ? AND fan_id = ?");
            $stmt->execute([$fio, $fan_id]);
            $res = $stmt->fetch();

            if (!$res) {
                echo "<script>alert('❌ Siz ($fio) ushbu fanga birikmagansiz.');</script>";
                continue;
            }

            // 5. TAQIQLANGAN HOLATLAR (Siz aytgan shartlar)
            $reyting = (float)$res['reyting'];
            $davomat = (float)$res['davomat'];
            $umumiy = (float)$res['umumiy'];

            // a) Reyting 20 dan kichik bo'lsa
            if ($reyting < 20) {
                echo "<script>alert('❌ Reyting ballingiz 20 dan kam. Ariza topshira olmaysiz.');</script>";
                continue;
            }

            // b) Davomat (NB) 33 dan katta yoki teng bo'lsa
            if ($davomat >= 33) {
                echo "<script>alert('❌ NBlar soni 33 tadan ko\'p. Ariza topshira olmaysiz.');</script>";
                continue;
            }

            // c) Umumiy ball 60 dan katta bo'lsa
            if ($umumiy > 60) {
                echo "<script>alert('❌ Umumiy ballingiz 60 dan yuqori. Ariza topshira olmaysiz.');</script>";
                continue;
            }

            // AGAR YUQORIDAGI TO'SIQLARDAN O'TSA - Fan qabul qilinadi
            $validFans[] = $fan_id;
        }

        // 6. Bazaga yozish (Agar barcha tanlangan fanlar filtrdan o'tgan bo'lsa)
        // 6. Bazaga yozish (created_at ustuni bilan)
        if (!empty($validFans)) {
            // Dublikatlarni o'chirib, faqat 4 tagacha fanni olamiz
            $validFans = array_unique(array_slice($validFans, 0, 4));

            $f1 = $validFans[0] ?? null;
            $f2 = $validFans[1] ?? null;
            $f3 = $validFans[2] ?? null;
            $f4 = $validFans[3] ?? null;

            // "sana" o'rniga "created_at" deb yozildi:
            $insert = $pdo->prepare("INSERT INTO bepul (user_id, talaba_id, fan1, fan2, fan3, fan4, hemis_parol, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, NOW())");

            // Ma'lumotlarni yuboramiz
            $insert->execute([$u_id, $talaba_id, $f1, $f2, $f3, $f4, $hemis_parol]);

            echo "<script>alert('✅ Arizangiz muvaffaqiyatli qabul qilindi!'); window.location.href='arizalar.php';</script>";
        }
    }
}

// Fanlar ro'yxati
$fanlar_list = $pdo->query("SELECT id, nomi FROM fanlar ORDER BY nomi ASC")->fetchAll();
?>

<?php require "Includes/header.php"; ?>

<style>
    body {
        background: #0f172a;
        color: white;
        font-family: 'Inter', sans-serif;
    }

    #bg {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        z-index: -1;
    }

    .glass-card {
        background: rgba(30, 41, 59, 0.7);
        backdrop-filter: blur(12px);
        border: 1px solid rgba(255, 255, 255, 0.1);
        border-radius: 20px;
        padding: 35px;
        box-shadow: 0 20px 50px rgba(0, 0, 0, 0.3);
    }

    .form-label {
        color: #94a3b8;
        font-size: 0.85rem;
        margin-bottom: 5px;
    }

    .form-control-static {
        background: rgba(255, 255, 255, 0.05);
        border: 1px solid rgba(255, 255, 255, 0.1);
        color: #e2e8f0;
        border-radius: 10px;
        padding: 10px 15px;
        margin-bottom: 15px;
    }

    .form-input {
        background: #fff;
        color: #0f172a;
        border-radius: 10px;
        border: 2px solid transparent;
        padding: 12px;
        transition: 0.3s;
    }

    .form-input:focus {
        border-color: #2ecc71;
        box-shadow: 0 0 15px rgba(46, 204, 113, 0.2);
        outline: none;
    }

    .fan-select-row {
        display: flex;
        gap: 10px;
        margin-bottom: 10px;
        animation: slideIn 0.3s ease-out;
    }

    @keyframes slideIn {
        from {
            opacity: 0;
            transform: translateX(10px);
        }

        to {
            opacity: 1;
            transform: translateX(0);
        }
    }

    .btn-add {
        background: rgba(46, 204, 113, 0.15);
        color: #2ecc71;
        border: 1px dashed #2ecc71;
        width: 100%;
        padding: 10px;
        border-radius: 10px;
        transition: 0.3s;
    }

    .btn-add:hover {
        background: rgba(46, 204, 113, 0.25);
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
</style>

<body>
    <?php require "Includes/yuklash.php"; ?>
    <?php require "Includes/navbar.php"; ?>
    <canvas id="bg"></canvas>

    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <div class="glass-card">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h2 class="mb-0">✨ Bepul imkoniyatga ariza topshirish</h2>
                        <a href="arizalar.php" class="btn btn-sm btn-outline-light">Orqaga qaytish</a>
                    </div>

                    <form method="POST">
                        <div class="row">
                            <div class="col-md-5 border-end border-secondary pe-md-4">
                                <p class="text-info small mb-4"><i class="bi bi-info-circle"></i> Shaxsiy ma'lumotlaringiz avtomatik yuklangan.</p>

                                <label class="form-label">Talaba ID</label>
                                <div class="form-control-static"><?= $user['talaba_id'] ?></div>

                                <label class="form-label">F.I.O</label>
                                <div class="form-control-static"><?= $user['fio'] ?></div>

                                <label class="form-label">Guruh</label>
                                <div class="form-control-static"><?= $user['guruh'] ?></div>

                                <label class="form-label">Hemis Parol (Tasdiqlash uchun)</label>
                                <input type="password" name="hemis_parol" class="form-control form-input" placeholder="Parolni kiriting" required>
                            </div>

                            <div class="col-md-7 ps-md-4 mt-4 mt-md-0">
                                <h5 class="mb-3">Qayta topshiriladigan fanlar</h5>
                                <p class="text-muted small">Maksimal 4 ta fan tanlashingiz mumkin.</p>

                                <div id="fan-container">
                                    <div class="fan-select-row">
                                        <select name="fanlar[]" class="form-control form-input" required>
                                            <option value="">Fan tanlang...</option>
                                            <?php foreach ($fanlar_list as $f): ?>
                                                <option value="<?= $f['id'] ?>"><?= $f['nomi'] ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>

                                <button type="button" id="addFan" class="btn btn-add mt-2">
                                    <i class="bi bi-plus-circle"></i> + Fan qo‘shish
                                </button>

                                <button name="submit" type="submit" class="btn btn-submit">
                                    Arizani jo'natish <i class="bi bi-send-fill ms-2"></i>
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        let maxFans = 4;
        let count = 1;

        document.getElementById("addFan").onclick = function() {
            if (count >= maxFans) {
                alert("Maksimal 4 ta fan tanlash mumkin!");
                return;
            }

            let div = document.createElement("div");
            div.className = "fan-select-row mt-2";
            div.innerHTML = `
                <select name="fanlar[]" class="form-control form-input">
                    <option value="">Fan tanlang...</option>
                    <?php foreach ($fanlar_list as $f): ?>
                        <option value="<?= $f['id'] ?>"><?= $f['nomi'] ?></option>
                    <?php endforeach; ?>
                </select>
                <button type="button" class="btn btn-outline-danger btn-sm" onclick="this.parentElement.remove(); count--;">✕</button>
            `;
            document.getElementById("fan-container").appendChild(div);
            count++;
        };
    </script>

    <?php require "Includes/footer.php"; ?>
    <script src="add.js"></script>
</body>

</html>
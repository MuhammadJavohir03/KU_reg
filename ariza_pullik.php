<?php
require "database.php";
session_start();

// 🔐 login check
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$id = $_SESSION['user_id'];

// 👤 user olish
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    exit("User topilmadi");
}

if (isset($_POST['submit'])) {

    $fanlar = $_POST['fanlar'] ?? [];

    $user_id = $user['id'];
    $talaba_id = $user['talaba_id'];

    $validFans = [];

    foreach ($fanlar as $fan_id) {

        if (!$fan_id) continue;

        // 🔍 talabalar jadvalidan tekshirish
        $stmt = $pdo->prepare("
            SELECT reyting, davomat
            FROM talabalar
            WHERE user_id=? AND fan_id=?
        ");
        $stmt->execute([$user_id, $fan_id]);
        $res = $stmt->fetch();

        // ❌ umuman yo‘q
        if (!$res) {
            echo "<script>alert('❌ Siz bu fanga birikmagansiz, kuting');</script>";
            continue;
        }

        $reyting = (float)$res['reyting'];
        $davomat = (float)$res['davomat'];

        // ❌ yiqilgan
        // if ($reyting < 20 || $davomat >= 33) {
        //     echo "<script>alert('❌ Sizda pullik imkoniyatga ball yetarli emas');</script>";
        //     continue;
        // }

        // ✅ o‘tgan
        $validFans[] = $fan_id;
    }

    if (empty($validFans)) {
        die("❌ Hech qaysi fan qo‘shilmadi");
    }

    // max 4 ta
    $validFans = array_unique($validFans);
    $validFans = array_slice($validFans, 0, 4);

    $fan1 = $validFans[0] ?? null;
    $fan2 = $validFans[1] ?? null;
    $fan3 = $validFans[2] ?? null;
    $fan4 = $validFans[3] ?? null;

    echo "<script>alert('✅ Ariza topshirildi');</script>";

    $user_id = $user['id'];
    $talaba_id = $user['talaba_id'];
    $hemis_parol = trim($_POST['hemis_parol']);

    // ❌ tekshirish: 2 marta topshirmasin
    $check = $pdo->prepare("SELECT id FROM pullik WHERE user_id=?");
    $check->execute([$user_id]);

    if ($check->fetch()) {
        die("❌ Siz allaqachon ariza topshirgansiz");
    }

    // ✅ insert
    $pdo->prepare("
    INSERT INTO pullik
    (user_id, talaba_id, fan1, fan2, fan3, fan4, hemis_parol)
    VALUES (?, ?, ?, ?, ?, ?, ?)
")->execute([
        $user_id,
        $talaba_id,
        $fan1,
        $fan2,
        $fan3,
        $fan4,
        $hemis_parol
    ]);
}
$stmt = $pdo->query("SELECT id, nomi FROM fanlar");
$fanlar = $stmt->fetchAll();
?>

<?php require "Includes/header.php"; ?>

<body>

    <div class="container bg-white p-5 mt-5 shadow rounded-1">
        <a href="arizalar.php" class="btn mb-5 btn-danger"><-Orqaga
                </a>

                <h1 class="text-danger mb-3">Pullik Ariza Topshirish</h1>
                <form method="POST" action="">
                    <label class="text-dark">Talaba ID:</label>
                    <input class="form-control" type="text"
                        value="<?= $user['talaba_id'] ?>" disabled><br>

                    <label class="text-dark">FIO</label>
                    <input class="form-control" type="text"
                        value="<?= $user['fio'] ?>" disabled><br>

                    <label class="text-dark">Email</label>
                    <input class="form-control" type="text"
                        value="<?= $user['email'] ?>" disabled><br>

                    <label class="text-dark">Guruh</label>
                    <input class="form-control" type="text"
                        value="<?= $user['guruh'] ?>" disabled><br>

                    <label class="text-dark">Hemis Prol Kiriting</label>
                    <input
                        class="mb-3 form-control"
                        type="text"
                        name="hemis_parol"
                        required>

                    <div id="fan-container">

                        <div class="mb-2">
                            <select name="fanlar[]" class="form-control">
                                <option value="">Fan tanlang</option>
                                <?php foreach ($fanlar as $f): ?>
                                    <option value="<?= $f['id'] ?>">
                                        <?= $f['nomi'] ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                    </div>

                    <button type="button" id="addFan" class="btn btn-success m-2">
                        + Fan qo‘shish
                    </button>

                    <button name="submit" type="submit" class="m-2 btn btn-success">
                        Saqlash va Jo'natish
                    </button>
                </form>

                <script>
                    let maxFans = 4;
                    let count = 1;

                    document.getElementById("addFan").onclick = function() {

                        if (count >= maxFans) {
                            alert("Maximum 4 ta fan!");
                            return;
                        }

                        let div = document.createElement("div");
                        div.className = "mb-2";

                        div.innerHTML = `
        <select name="fanlar[]" class="form-control">
            <option value="">Fan tanlang</option>
            <?php foreach ($fanlar as $f): ?>
                <option value="<?= $f['id'] ?>">
                    <?= $f['nomi'] ?>
                </option>
            <?php endforeach; ?>
        </select>
    `;

                        document.getElementById("fan-container").appendChild(div);
                        count++;
                    };
                </script>
    </div>


</body>
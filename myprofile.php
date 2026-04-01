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

// ✏️ UPDATE (faqat password)
if (isset($_POST['update'])) {

    $password = trim($_POST['password']);

    if (!empty($password)) {

        // 🔐 HASH
        $hash = password_hash($password, PASSWORD_DEFAULT);

        $update = $pdo->prepare("
            UPDATE users 
            SET password = ? 
            WHERE id = ?
        ");

        $update->execute([$hash, $id]);
    }

    header("Location: myprofile.php");
    exit;
}
?>

<?php require "Includes/header.php"; ?>
<body>
    <canvas class="z-n1" id="bg"></canvas>

    <div class="container p-5 mt-5 shadow rounded-1"
        style="background-color: rgba(255, 255, 255); border:1px solid rgb(100, 13, 20);">

        <a href="index.php" class="btn border btn-danger mb-3">← Orqaga</a>

        <h2 class="text-dark">My Profile</h2>

        <!-- INFO -->
        <h4 class="text-dark">Email: <?= $user['email'] ?></h4>

        <h4 class="text-dark">
            Role:
            <?= ($user['role'] == 'user') ? 'Talaba' : 'Admin' ?>
        </h4>

        <hr class="text-dark">

        <!-- FORM -->
        <form method="POST">

            <label class="text-dark">Talaba ID:</label>
            <input class="form-control" type="text"
                value="<?= $user['talaba_id'] ?>" disabled><br>

            <label class="text-dark">FIO:</label>
            <input class="form-control" type="text"
                value="<?= $user['fio'] ?>" disabled><br>

            <label class="text-dark">Kurs:</label>
            <input class="form-control" type="text"
                value="<?= $user['kurs'] ?>" disabled><br>

            <label class="text-dark">Yangi password:</label>
            <input class="form-control" type="password"
                name="password" placeholder="Yangi parol"><br>

            <button class="border btn btn-success" name="update">
                Saqlash
            </button>

        </form>

        <br>

        <a href="logout.php" class="border btn btn-danger">Chiqish</a>

    </div>

</body>

</html>
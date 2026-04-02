<?php
session_start();
require "database.php";

// ================= ADD FAN =================
if (isset($_POST['add_fan'])) {

    $nomi = trim($_POST['nomi']);

    if (!empty($nomi)) {

        $stmt = $pdo->prepare("INSERT INTO fanlar (nomi) VALUES (?)");
        $stmt->execute([$nomi]);

        header("Location: fanlar.php");
        exit;
    } else {
        $error = "Fan nomi bo‘sh bo‘lmasligi kerak!";
    }
}

// ================= DELETE FAN =================
if (isset($_GET['delete'])) {

    $id = (int)$_GET['delete'];

    $stmt = $pdo->prepare("DELETE FROM fanlar WHERE id=?");
    $stmt->execute([$id]);

    header("Location: fanlar.php");
    exit;
}

// ================= SEARCH =================
$q = $_GET['q'] ?? '';

if (!empty($q)) {
    $stmt = $pdo->prepare("SELECT * FROM fanlar WHERE nomi LIKE ? ORDER BY id DESC");
    $stmt->execute(["%$q%"]);
} else {
    $stmt = $pdo->query("SELECT * FROM fanlar ORDER BY id DESC");
}

$fanlar = $stmt->fetchAll();
?>

<?php include "Includes/header.php"; ?>

<body>
    <?php include "Includes/navbar.php"; ?>

    <div class="container bg-light shadow p-4" style="min-height: 100vh;">

        <h3 class="mb-4">📖 Fanlar</h3>

        <!-- ================= ADD FAN ================= -->
        <div class="card mb-4 shadow-sm">
            <div class="card-body">

                <?php if (!empty($error)): ?>
                    <div class="alert alert-danger"><?= $error ?></div>
                <?php endif; ?>

                <form method="POST" class="row g-2">
                    <div class="col-md-10">
                        <input type="text" name="nomi" class="form-control" placeholder="Fan nomi">
                    </div>

                    <div class="col-md-2 d-grid">
                        <button class="btn btn-primary" name="add_fan">Qo‘shish</button>
                    </div>
                </form>

            </div>
        </div>

        <!-- ================= SEARCH ================= -->
        <form method="GET" class="mb-3">
            <input type="text" name="q" class="form-control"
                placeholder="🔍 Fan qidirish..."
                value="<?= htmlspecialchars($_GET['q'] ?? '') ?>">
        </form>

        <!-- ================= FAN LIST ================= -->
        <div class="row g-3">

            <?php foreach ($fanlar as $fan): ?>

                <div class="col-md-4">

                    <div class="card shadow-sm p-3">

                        <h5>📘 <?= htmlspecialchars($fan['nomi']) ?></h5>

                        <div class="d-flex justify-content-between mt-2">

                            <!-- TALABALAR -->
                            <a href="talabalar.php?fan_id=<?= $fan['id'] ?>"
                                class="btn btn-sm btn-info text-white">
                                Ochish
                            </a>

                            <!-- DELETE -->
                            <a href="?delete=<?= $fan['id'] ?>"
                                onclick="return confirm('Rostdan ham o‘chirmoqchimisiz?')"
                                class="btn btn-sm btn-danger">
                                O‘chirish
                            </a>

                        </div>

                    </div>

                </div>

            <?php endforeach; ?>

        </div>

    </div>

</body>
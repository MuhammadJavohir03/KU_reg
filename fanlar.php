<?php
session_start();
require "database.php";

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

if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];

    $stmt = $pdo->prepare("DELETE FROM fanlar WHERE id=?");
    $stmt->execute([$id]);

    header("Location: fanlar.php");
    exit;
}

$stmt = $pdo->query("SELECT * FROM fanlar ORDER BY id DESC");
$fanlar = $stmt->fetchAll();
?>

<?php include "Includes/header.php"; ?>

<body>
    <?php include "Includes/navbar.php"; ?>

    <div class="container bg-light shadow p-4">

        <h3 class="mb-4 border-dark">📚 Fanlar</h3>

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

        <div class="row g-3">

            <?php foreach ($fanlar as $fan): ?>

                <div class="col-md-4">

                    <a href="talabalar.php?fan_id=<?= $fan['id'] ?>"
                        class="text-decoration-none">

                        <div class="card shadow-sm p-3">
                            <h5 class="mb-0">📘 <?= htmlspecialchars($fan['nomi']) ?></h5>
                        </div>

                    </a>

                </div>

            <?php endforeach; ?>

        </div>

    </div>

</body>
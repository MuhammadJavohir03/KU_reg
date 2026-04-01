<?php
session_start();
require "database.php";

$fan_id = $_GET['id'] ?? $_GET['fan_id'] ?? null;

if (!$fan_id) {
    die("Fan tanlanmagan");
}

/* ================= FAN OLISH ================= */
$stmt = $pdo->prepare("SELECT * FROM fanlar WHERE id=?");
$stmt->execute([$fan_id]);
$fan = $stmt->fetch();

if (!$fan) {
    die("Fan topilmadi");
}

/* ================= DELETE ================= */
if (isset($_GET['delete'])) {

    $id = (int) $_GET['delete'];

    $stmt = $pdo->prepare("DELETE FROM talabalar WHERE id = ?");
    $stmt->execute([$id]);

    header("Location: " . $_SERVER['HTTP_REFERER']);
    exit;
}

/* ================= ADD ================= */
if (isset($_POST['add'])) {

    $fio = trim($_POST['fio']);

    $stmt = $pdo->prepare("SELECT id FROM users WHERE fio=?");
    $stmt->execute([$fio]);
    $user_id = $stmt->fetchColumn();

    if ($user_id) {

        $pdo->prepare("
            INSERT INTO talabalar
            (user_id, fan_id, joriy_nazorat, oraliq_nazorat, reyting,
             yakuniy_nazorat, qayta_topshirish, umumiy, davomat)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
        ")->execute([
            $user_id,
            $fan_id,
            $_POST['joriy_nazorat'],
            $_POST['oraliq_nazorat'],
            $_POST['reyting'],
            $_POST['yakuniy_nazorat'],
            $_POST['qayta_topshirish'],
            $_POST['umumiy'],
            $_POST['davomat']
        ]);
    }

    header("Location: talabalar.php?fan_id=$fan_id");
    exit;
}

/* ================= UPDATE ================= */
if (isset($_POST['update'])) {

    $pdo->prepare("
        UPDATE talabalar SET
            joriy_nazorat=?,
            oraliq_nazorat=?,
            reyting=?,
            yakuniy_nazorat=?,
            qayta_topshirish=?,
            umumiy=?,
            davomat=?
        WHERE id=? AND fan_id=?
    ")->execute([
        $_POST['joriy_nazorat'],
        $_POST['oraliq_nazorat'],
        $_POST['reyting'],
        $_POST['yakuniy_nazorat'],
        $_POST['qayta_topshirish'],
        $_POST['umumiy'],
        $_POST['davomat'],
        $_POST['id'],
        $fan_id
    ]);

    header("Location: talabalar.php?fan_id=$fan_id");
    exit;
}

/* ================= FILTER ================= */
$filter = $_GET['filter'] ?? 'all';

/* ================= IMPORT ================= */
if (isset($_POST['import'])) {

    $file = $_FILES['file']['tmp_name'];
    $h = fopen($file, "r");

    fgetcsv($h, 1000, ";");

    while (($d = fgetcsv($h, 1000, ";")) !== FALSE) {

        if (count($d) < 8) continue;

        $fio = trim($d[0]);

        $q = $pdo->prepare("SELECT id FROM users WHERE fio=?");
        $q->execute([$fio]);
        $user_id = $q->fetchColumn();

        if (!$user_id) continue;

        $pdo->prepare("
            INSERT INTO talabalar
            (user_id, fan_id, joriy_nazorat, oraliq_nazorat, reyting,
             yakuniy_nazorat, qayta_topshirish, umumiy, davomat)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
        ")->execute([
            $user_id,
            $fan_id,
            (int)$d[1],
            (int)$d[2],
            (int)$d[3],
            (int)$d[4],
            (int)$d[5],
            (int)$d[6],
            (float)$d[7]
        ]);
    }

    fclose($h);

    header("Location: talabalar.php?fan_id=$fan_id");
    exit;
}

/* ================= EXPORT ================= */
if (isset($_GET['export'])) {

    header("Content-Type: text/csv");
    header("Content-Disposition: attachment; filename=talabalar.csv");

    $out = fopen("php://output", "w");

    fputcsv($out, [
        "FIO",
        "Joriy",
        "Oraliq",
        "Reyting",
        "Yakuniy",
        "Qayta",
        "Umumiy",
        "Davomat"
    ], ";");

    $stmt = $pdo->prepare("
        SELECT u.fio, t.*
        FROM talabalar t
        JOIN users u ON u.id=t.user_id
        WHERE t.fan_id=?
    ");
    $stmt->execute([$fan_id]);

    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($data as $r) {
        fputcsv($out, [
            $r['fio'],
            $r['joriy_nazorat'],
            $r['oraliq_nazorat'],
            $r['reyting'],
            $r['yakuniy_nazorat'],
            $r['qayta_topshirish'],
            $r['umumiy'],
            $r['davomat']
        ], ";");
    }

    fclose($out);
    exit;
}

/* ================= DATA ================= */
$stmt = $pdo->prepare("
    SELECT t.*, u.fio
    FROM talabalar t
    JOIN users u ON u.id=t.user_id
    WHERE t.fan_id=?
");
$stmt->execute([$fan_id]);

$rows = $stmt->fetchAll();

/* ================= FILTER LOGIC ================= */
$rows = array_filter($rows, function ($r) use ($filter) {

    $davomat = (float)$r['davomat'];
    $reyting = (float)$r['reyting'];

    $isFail = ($davomat >= 33 || $reyting < 20);

    if ($filter == 'fail') return $isFail;
    if ($filter == 'pass') return !$isFail;

    return true;
});

if (isset($_POST['save'])) {

    $fio = trim($_POST['fio']);

    // usersdan topish
    $stmt = $pdo->prepare("SELECT id FROM users WHERE fio=?");
    $stmt->execute([$fio]);
    $user_id = $stmt->fetchColumn();

    if (!$user_id) {
        die("User topilmadi!");
    }

    // fan ichiga qo'shish
    $pdo->prepare("
        INSERT INTO talabalar
        (fan_id, user_id, joriy_nazorat, oraliq_nazorat, reyting, yakuniy_nazorat, qayta_topshirish, umumiy, davomat)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
    ")->execute([
        $fan_id,
        $user_id,
        $_POST['joriy_nazorat'],
        $_POST['oraliq_nazorat'],
        $_POST['reyting'],
        $_POST['yakuniy_nazorat'],
        $_POST['qayta_topshirish'],
        $_POST['umumiy'],
        $_POST['davomat']
    ]);

    header("Location: talabalar.php?id=" . $fan_id);
    exit;
}
?>

<?php include "Includes/header.php"; ?>

<body>
    <?php include "Includes/navbar.php"; ?>

    <div class="p-4 bg-white">


        <a class="btn btn-danger mb-3" href="fanlar.php"><-Orqaga</a>

                <h3>📘 <?= htmlspecialchars($fan['nomi']) ?> - Fanidan Talabalar</h3>

                <!-- FILTER -->
                <div class="mb-3">
                    <a href="?fan_id=<?= $fan_id ?>" class="btn btn-secondary">Hamma</a>
                    <a href="?fan_id=<?= $fan_id ?>&filter=fail" class="btn btn-danger">O‘tolmagan</a>
                    <a href="?fan_id=<?= $fan_id ?>&filter=pass" class="btn btn-success">O‘tgan</a>
                </div>

                <!-- IMPORT / EXPORT -->
                <form method="POST" enctype="multipart/form-data" class="mb-3">
                    <input type="file" name="file" required>
                    <button name="import" class="btn btn-primary">Import</button>

                    <a href="?fan_id=<?= $fan_id ?>&export=1" class="btn btn-danger">
                        Export
                    </a>
                </form>

                <div class="mb-4">

                    <h3>📚 Fan ID: <?= $fan_id ?></h3>

                    <!-- ================= ADD FORM ================= -->
                    <form method="POST" class="border p-3 mb-4">

                        <input name="fio" class="form-control mb-2" placeholder="FIO" required>

                        <div class="row">
                            <div class="col"><input name="joriy_nazorat" class="form-control" placeholder="Joriy"></div>
                            <div class="col"><input name="oraliq_nazorat" class="form-control" placeholder="Oraliq"></div>
                            <div class="col"><input name="reyting" class="form-control" placeholder="Reyting"></div>
                        </div>

                        <div class="row mt-2">
                            <div class="col"><input name="yakuniy_nazorat" class="form-control" placeholder="Yakuniy"></div>
                            <div class="col"><input name="qayta_topshirish" class="form-control" placeholder="Qayta"></div>
                            <div class="col"><input name="umumiy" class="form-control" placeholder="Umumiy"></div>
                        </div>

                        <input name="davomat" class="form-control mt-2" placeholder="Davomat">

                        <button name="save" class="btn btn-success mt-3">
                            💾 Saqlash
                        </button>

                    </form>

                </div>

                <!-- TABLE -->
                <table class="table table-bordered table-striped">

                    <thead class="table-dark">
                        <tr>
                            <th>No</th>
                            <th>FIO</th>
                            <th>Joriy</th>
                            <th>Oraliq</th>
                            <th>Reyting</th>
                            <th>Yakuniy</th>
                            <th>Qayta</th>
                            <th>Umumiy</th>
                            <th>Davomat</th>
                            <th>Action</th>
                        </tr>
                    </thead>

                    <tbody class="table-group-divider shadow">

                        <?php $i = 1;
                        foreach ($rows as $r): ?>

                            <?php
                            $isFail = ($r['davomat'] >= 33 || $r['reyting'] < 20);
                            ?>

                            <form method="POST">
                                <tr class="<?= $isFail ? 'table-danger' : '' ?>">

                                    <td><?= $i++ ?></td>
                                    <td><?= $r['fio'] ?></td>

                                    <td><input name="joriy_nazorat" value="<?= $r['joriy_nazorat'] ?>"></td>
                                    <td><input name="oraliq_nazorat" value="<?= $r['oraliq_nazorat'] ?>"></td>
                                    <td><input name="reyting" value="<?= $r['reyting'] ?>"></td>
                                    <td><input name="yakuniy_nazorat" value="<?= $r['yakuniy_nazorat'] ?>"></td>
                                    <td><input name="qayta_topshirish" value="<?= $r['qayta_topshirish'] ?>"></td>
                                    <td><input name="umumiy" value="<?= $r['umumiy'] ?>"></td>
                                    <td><input name="davomat" value="<?= $r['davomat'] ?>"></td>

                                    <td>
                                        <input type="hidden" name="id" value="<?= $r['id'] ?>">
                                        <button name="update" class="btn mb-2 btn-success btn-sm">Saqlash</button>
                                        <a href="?delete=<?= $r['id'] ?>&fan_id=<?= $fan_id ?>"
                                            class="btn mb-2 btn-danger btn-sm">
                                            O'chirish
                                        </a>
                                    </td>

                                </tr>
                            </form>

                        <?php endforeach; ?>

                    </tbody>
                </table>

    </div>

</body>
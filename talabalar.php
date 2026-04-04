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
    header("Location: talabalar.php?fan_id=$fan_id");
    exit;
}

/* ================= ADD (Manual) ================= */
if (isset($_POST['save'])) {
    $fio = trim($_POST['fio']);

    // usersdan topish (Talaba_id va guruhni avtomat olish uchun)
    $stmt = $pdo->prepare("SELECT fio, talaba_id, guruh FROM users WHERE fio=?");
    $stmt->execute([$fio]);
    $user = $stmt->fetch();

    if (!$user) {
        die("Bunday FIOga ega foydalanuvchi users jadvalida topilmadi!");
    }

    $db_fio    = $user['fio']; // user_id o'rniga FIO ketadi
    $talaba_id = $user['talaba_id'];
    $guruh     = $user['guruh'];

    $pdo->prepare("
        INSERT INTO talabalar
        (fan_id, user_id, talaba_id, guruh, joriy_nazorat, oraliq_nazorat, reyting, yakuniy_nazorat, qayta_topshirish, umumiy, davomat)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ")->execute([
        $fan_id,
        $db_fio,
        $talaba_id,
        $guruh,
        $_POST['joriy_nazorat'],
        $_POST['oraliq_nazorat'],
        $_POST['reyting'],
        $_POST['yakuniy_nazorat'],
        $_POST['qayta_topshirish'],
        $_POST['umumiy'],
        $_POST['davomat']
    ]);

    header("Location: talabalar.php?fan_id=" . $fan_id);
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
    fgetcsv($h, 1000, ";"); // Header skip

    while (($d = fgetcsv($h, 1000, ";")) !== FALSE) {
        if (count($d) < 8) continue;
        $csv_fio = trim($d[0]);

        $q = $pdo->prepare("SELECT fio, talaba_id, guruh FROM users WHERE fio = ?");
        $q->execute([$csv_fio]);
        $user = $q->fetch(PDO::FETCH_ASSOC);

        if (!$user) continue;

        $stmt = $pdo->prepare("
            INSERT INTO talabalar 
            (user_id, fan_id, talaba_id, guruh, 
             joriy_nazorat, oraliq_nazorat, reyting, 
             yakuniy_nazorat, qayta_topshirish, umumiy, davomat) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $user['fio'], // VARCHAR user_id
            $fan_id,
            $user['talaba_id'],
            $user['guruh'],
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
    header("Content-Type: text/csv; charset=utf-8");
    header("Content-Disposition: attachment; filename=talabalar.csv");
    $out = fopen("php://output", "w");
    fputs($out, $bom = chr(0xEF) . chr(0xBB) . chr(0xBF)); // Excelda o'zbekcha harflar uchun

    fputcsv($out, ["FIO", "Talaba ID", "Guruh", "Joriy", "Oraliq", "Reyting", "Yakuniy", "Qayta", "Umumiy", "Davomat"], ";");

    // user_id endi FIO bo'lgani uchun JOIN o'zgardi
    $sql = "SELECT * FROM talabalar WHERE fan_id=?";
    if ($filter == 'fail') {
        $sql .= " AND (davomat >= 33 OR reyting < 20)";
    } elseif ($filter == 'pass') {
        $sql .= " AND (davomat < 33 AND reyting >= 20)";
    }

    $stmt = $pdo->prepare($sql);
    $stmt->execute([$fan_id]);
    while ($r = $stmt->fetch(PDO::FETCH_ASSOC)) {
        fputcsv($out, [
            $r['user_id'], // Chunki user_id ichida FIO yozilgan
            $r['talaba_id'],
            $r['guruh'],
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

/* ================= DATA GETTING ================= */
// Talabalar jadvalidagi user_id ustunining o'zida FIO bor
$stmt = $pdo->prepare("SELECT * FROM talabalar WHERE fan_id=?");
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
?>

<?php include "Includes/header.php"; ?>

<body>
    <?php require "Includes/yuklash.php"; ?>
    <?php include "Includes/navbar.php"; ?>

    <div class="p-4 bg-white">
        <a class="btn btn-danger mb-3" href="fanlar.php"><- Orqaga</a>
                <h3>📘 <?= htmlspecialchars($fan['nomi']) ?> - Fanidan Talabalar</h3>

                <div class="mb-3">
                    <a href="?fan_id=<?= $fan_id ?>" class="btn btn-secondary">Hamma</a>
                    <a href="?fan_id=<?= $fan_id ?>&filter=fail" class="btn btn-danger">O‘tolmagan</a>
                    <a href="?fan_id=<?= $fan_id ?>&filter=pass" class="btn btn-success">O‘tgan</a>
                </div>

                <form method="POST" enctype="multipart/form-data" class="mb-3 border p-2">
                    <input type="file" name="file" required>
                    <button name="import" class="btn btn-primary">Import (CSV)</button>
                    <a href="?fan_id=<?= $fan_id ?>&filter=<?= $filter ?>&export=1" class="btn btn-warning">Export (CSV)</a>
                </form>

                <form method="POST" class="border p-3 mb-4 bg-light">
                    <h5>Yangi talaba qo'shish</h5>
                    <input name="fio" class="form-control mb-2" placeholder="FIO (Users jadvalida bo'lishi shart)" required>
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
                    <button name="save" class="btn btn-success mt-3 w-100">💾 Saqlash</button>
                </form>

                <table class="table table-bordered table-striped">
                    <thead class="table-dark">
                        <tr>
                            <th>No</th>
                            <th>FIO</th>
                            <th>Talaba ID</th>
                            <th>Guruh</th>
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
                    <tbody>
                        <?php $i = 1;
                        foreach ($rows as $r): ?>
                            <?php $isFail = ($r['davomat'] >= 33 || $r['reyting'] < 20); ?>
                            <form method="POST">
                                <tr class="<?= $isFail ? 'table-danger' : '' ?>">
                                    <td><?= $i++ ?></td>
                                    <td><?= htmlspecialchars($r['user_id']) ?></td>
                                    <td><?= $r['talaba_id'] ?></td>
                                    <td><?= $r['guruh'] ?></td>
                                    <td><input name="joriy_nazorat" class="form-control form-control-sm" value="<?= $r['joriy_nazorat'] ?>"></td>
                                    <td><input name="oraliq_nazorat" class="form-control form-control-sm" value="<?= $r['oraliq_nazorat'] ?>"></td>
                                    <td><input name="reyting" class="form-control form-control-sm" value="<?= $r['reyting'] ?>"></td>
                                    <td><input name="yakuniy_nazorat" class="form-control form-control-sm" value="<?= $r['yakuniy_nazorat'] ?>"></td>
                                    <td><input name="qayta_topshirish" class="form-control form-control-sm" value="<?= $r['qayta_topshirish'] ?>"></td>
                                    <td><input name="umumiy" class="form-control form-control-sm" value="<?= $r['umumiy'] ?>"></td>
                                    <td><input name="davomat" class="form-control form-control-sm" value="<?= $r['davomat'] ?>"></td>
                                    <td>
                                        <input type="hidden" name="id" value="<?= $r['id'] ?>">
                                        <button name="update" class="btn btn-success btn-sm">Saqlash</button>
                                        <a href="?delete=<?= $r['id'] ?>&fan_id=<?= $fan_id ?>" class="btn btn-danger btn-sm" onclick="return confirm('Ochiqmi?')">O'chirish</a>
                                    </td>
                                </tr>
                            </form>
                        <?php endforeach; ?>
                    </tbody>
                </table>
    </div>
</body>
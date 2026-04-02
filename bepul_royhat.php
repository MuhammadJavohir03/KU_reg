<?php
require "database.php";
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$q = $_GET['q'] ?? '';

$where = "";
$params = [];

if (!empty($q)) {
    $where = "
    WHERE 
        u.fio LIKE ? OR
        u.talaba_id LIKE ? OR
        u.email LIKE ? OR
        u.guruh LIKE ? OR
        b.hemis_parol LIKE ? OR
        b.fan1 LIKE ? OR
        b.fan2 LIKE ? OR
        b.fan3 LIKE ? OR
        b.fan4 LIKE ?
    ";

    $qParam = "%$q%";
    $params = array_fill(0, 9, $qParam);
}

$stmt = $pdo->prepare("
    SELECT 
        b.*,
        u.fio,
        u.talaba_id,
        u.email,
        u.guruh,

        CONCAT(b.fan1, ' ', f1.nomi) AS fan1_full,
        CONCAT(b.fan2, ' ', f2.nomi) AS fan2_full,
        CONCAT(b.fan3, ' ', f3.nomi) AS fan3_full,
        CONCAT(b.fan4, ' ', f4.nomi) AS fan4_full

    FROM bepul b
    JOIN users u ON u.id = b.user_id

    LEFT JOIN fanlar f1 ON f1.id = b.fan1
    LEFT JOIN fanlar f2 ON f2.id = b.fan2
    LEFT JOIN fanlar f3 ON f3.id = b.fan3
    LEFT JOIN fanlar f4 ON f4.id = b.fan4

    $where
    ORDER BY b.id DESC
");

$stmt->execute($params);
$data = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<?php require "Includes/header.php"; ?>
<?php require "Includes/navbar.php"; ?>
<div class="container bg-white p-3 mt-5">



    <h2 class="mb-4 text-primary">📋 Bepul Ro‘yxat</h2>

    <a class="btn btn-danger mb-5" href="arizaroyhati.php"><-Orqaga
            </a>

            <form method="GET" class="mb-3">
                <input type="text" name="q" class="form-control"
                    placeholder="🔍 Qidirish: FIO, ID, email, fan..."
                    value="<?= $_GET['q'] ?? '' ?>">
                <button class="btn btn-primary mt-2">Qidirish</button>

                <a href="export_bepul.php?q=<?= $_GET['q'] ?? '' ?>"
                    class="btn btn-success mt-2">
                    Excel Export
                </a>
            </form>

            <table class="table table-bordered table-striped">

                <thead class="table-dark">
                    <tr>
                        <th>No</th>
                        <th>FIO</th>
                        <th>Talaba ID</th>
                        <th>Email</th>
                        <th>Guruh</th>
                        <th>HEMIS Parol</th>
                        <th>Fan 1</th>
                        <th>Fan 2</th>
                        <th>Fan 3</th>
                        <th>Fan 4</th>
                    </tr>
                </thead>

                <tbody>
                    <?php $i = 1; ?>
                    <?php foreach ($data as $row): ?>
                        <tr>
                            <td><?= $i++ ?></td>

                            <td><?= htmlspecialchars($row['fio']) ?></td>
                            <td><?= htmlspecialchars($row['talaba_id']) ?></td>
                            <td><?= htmlspecialchars($row['email']) ?></td>
                            <td><?= htmlspecialchars($row['guruh']) ?></td>

                            <td><?= htmlspecialchars($row['hemis_parol']) ?></td>

                            <td><?= htmlspecialchars($row['fan1_full'] ?? ' ') ?></td>
                            <td><?= htmlspecialchars($row['fan2_full'] ?? ' ') ?></td>
                            <td><?= htmlspecialchars($row['fan3_full'] ?? ' ') ?></td>
                            <td><?= htmlspecialchars($row['fan4_full'] ?? ' ') ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>

            </table>

</div>

<?php require "Includes/footer.php"; ?>
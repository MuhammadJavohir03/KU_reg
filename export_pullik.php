<?php
require "database.php";
session_start();

if (!isset($_SESSION['user_id'])) {
    exit("Ruxsat berilmagan");
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
        f1.nomi LIKE ? OR
        f2.nomi LIKE ? OR
        f3.nomi LIKE ? OR
        f4.nomi LIKE ?
    ";
    $qParam = "%$q%";
    $params = array_fill(0, 9, $qParam);
}

// Ma'lumotlarni olish (Nomi va Semestr bilan birga)
$sql = "
    SELECT 
        u.fio, u.talaba_id, u.guruh,
        f1.nomi AS fan1_nomi, f1.semestr AS s1,
        f2.nomi AS fan2_nomi, f2.semestr AS s2,
        f3.nomi AS fan3_nomi, f3.semestr AS s3,
        f4.nomi AS fan4_nomi, f4.semestr AS s4
    FROM pullik b
    JOIN users u ON u.id = b.user_id
    LEFT JOIN fanlar f1 ON f1.id = b.fan1
    LEFT JOIN fanlar f2 ON f2.id = b.fan2
    LEFT JOIN fanlar f3 ON f3.id = b.fan3
    LEFT JOIN fanlar f4 ON f4.id = b.fan4
    $where
    ORDER BY b.id DESC
";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

$filename = "pullik_royhat_" . date('Y-m-d_H-i') . ".xls";

header("Content-Type: application/vnd.ms-excel; charset=utf-8");
header("Content-Disposition: attachment; filename=\"$filename\"");
header("Expires: 0");
header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
header("Cache-Control: private", false);

echo "\xEF\xBB\xBF"; // UTF-8 BOM
?>
<table border="1">
    <thead>
        <tr style="background-color: #f2f2f2; font-weight: bold;">
            <th>FIO</th>
            <th>Talaba ID</th>
            <th>Guruh</th>
            <th>Fan Nomi</th>
            <th>Semestr</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($rows as $row): ?>
            <?php
            for ($i = 1; $i <= 4; $i++):
                $fan_key = "fan{$i}_nomi";
                $sem_key = "s{$i}";

                if (!empty($row[$fan_key])):
            ?>
                    <tr>
                        <td><?= htmlspecialchars($row['fio']) ?></td>
                        <td><?= htmlspecialchars($row['talaba_id']) ?></td>
                        <td><?= htmlspecialchars($row['guruh']) ?></td>
                        <td><?= htmlspecialchars($row[$fan_key]) ?></td>
                        <td align="center"><?= !empty($row[$sem_key]) ? htmlspecialchars($row[$sem_key]) . "-semestr" : "-" ?></td>
                    </tr>
            <?php
                endif;
            endfor;
            ?>
        <?php endforeach; ?>
    </tbody>
</table>
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

// SQL: Fanlar bilan birga talabalar jadvalidan reyting ballarini ham olamiz
$sql = "
    SELECT 
        u.fio, u.talaba_id, u.guruh,
        f1.nomi AS fan1_nomi, f1.semestr AS s1, t1.reyting AS r1,
        f2.nomi AS fan2_nomi, f2.semestr AS s2, t2.reyting AS r2,
        f3.nomi AS fan3_nomi, f3.semestr AS s3, t3.reyting AS r3,
        f4.nomi AS fan4_nomi, f4.semestr AS s4, t4.reyting AS r4
    FROM bepul b
    JOIN users u ON u.id = b.user_id
    LEFT JOIN fanlar f1 ON f1.id = b.fan1
    LEFT JOIN talabalar t1 ON t1.user_id = u.fio AND t1.fan_id = b.fan1
    LEFT JOIN fanlar f2 ON f2.id = b.fan2
    LEFT JOIN talabalar t2 ON t2.user_id = u.fio AND t2.fan_id = b.fan2
    LEFT JOIN fanlar f3 ON f3.id = b.fan3
    LEFT JOIN talabalar t3 ON t3.user_id = u.fio AND t3.fan_id = b.fan3
    LEFT JOIN fanlar f4 ON f4.id = b.fan4
    LEFT JOIN talabalar t4 ON t4.user_id = u.fio AND t4.fan_id = b.fan4
    $where
    ORDER BY b.id DESC
";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

$filename = "bepul_royhat_" . date('Y-m-d_H-i') . ".xls";

header("Content-Type: application/vnd.ms-excel; charset=utf-8");
header("Content-Disposition: attachment; filename=\"$filename\"");

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
            <th style="background-color: #e2efda;">Reyting Ball</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($rows as $row): ?>
            <?php
            for ($i = 1; $i <= 4; $i++):
                $fan_key = "fan{$i}_nomi";
                $sem_key = "s{$i}";
                $ball_key = "r{$i}";

                if (!empty($row[$fan_key])):
            ?>
                    <tr>
                        <td><?= htmlspecialchars($row['fio']) ?></td>
                        <td><?= htmlspecialchars($row['talaba_id']) ?></td>
                        <td><?= htmlspecialchars($row['guruh']) ?></td>
                        <td><?= htmlspecialchars($row[$fan_key]) ?></td>
                        <td align="center"><?= !empty($row[$sem_key]) ? htmlspecialchars($row[$sem_key]) . "-semestr" : "-" ?></td>
                        <td align="center" style="font-weight: bold;"><?= !empty($row[$ball_key]) ? htmlspecialchars($row[$ball_key]) : "0" ?></td>
                    </tr>
            <?php
                endif;
            endfor;
            ?>
        <?php endforeach; ?>
    </tbody>
</table>
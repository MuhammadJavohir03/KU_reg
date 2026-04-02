<?php
require "database.php";

$q = $_GET['q'] ?? '';

header("Content-Type: application/vnd.ms-excel");
header("Content-Disposition: attachment; filename=bepul_royhat.xls");

$where = "";
$params = [];

if (!empty($q)) {
    $where = "
    WHERE 
        u.fio LIKE ? OR
        u.talaba_id LIKE ? OR
        u.email LIKE ? OR
        u.guruh LIKE ?
    ";

    $qParam = "%$q%";
    $params = array_fill(0, 4, $qParam);
}

$stmt = $pdo->prepare("
    SELECT 
        u.fio,
        u.talaba_id,
        u.email,
        u.guruh,
        b.hemis_parol,
        b.fan1,
        b.fan2,
        b.fan3,
        b.fan4

    FROM bepul b
    JOIN users u ON u.id = b.user_id

    $where
");

$stmt->execute($params);
$data = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "FIO\tTalaba ID\tEmail\tGuruh\tHEMIS Parol\tFan1\tFan2\tFan3\tFan4\n";

foreach ($data as $row) {
    echo $row['fio'] . "\t";
    echo $row['talaba_id'] . "\t";
    echo $row['email'] . "\t";
    echo $row['guruh'] . "\t";
    echo $row['hemis_parol'] . "\t";
    echo $row['fan1'] . "\t";
    echo $row['fan2'] . "\t";
    echo $row['fan3'] . "\t";
    echo $row['fan4'] . "\n";
}

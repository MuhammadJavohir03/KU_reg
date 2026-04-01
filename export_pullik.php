<?php
require "database.php";

$filters = [
    'talaba_id' => $_GET['talaba_id'] ?? '',
    'familiya'  => $_GET['familiya'] ?? '',
    'ism'       => $_GET['ism'] ?? '',
    'otasi'     => $_GET['otasi'] ?? '',
    'guruh'     => $_GET['guruh'] ?? '',
    'yonalish'  => $_GET['yonalish'] ?? '',
    'kurs'      => $_GET['kurs'] ?? '',
];

$sql = "SELECT * FROM pullik WHERE 1";
$params = [];

foreach ($filters as $key => $value) {
    if (!empty($value)) {
        $sql .= " AND $key LIKE :$key";
        $params[$key] = "%$value%";
    }
}

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$data = $stmt->fetchAll();

header("Content-Type: text/csv");
header("Content-Disposition: attachment; filename=talabalar.csv");

$output = fopen("php://output", "w");

if (!empty($data)) {
    fputcsv($output, array_keys($data[0]));
}

foreach ($data as $row) {
    fputcsv($output, $row);
}

fclose($output);
exit;
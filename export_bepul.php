<?php
require "database.php";

// Excel headerlar
header("Content-Type: application/vnd.ms-excel");
header("Content-Disposition: attachment; filename=bepul_students.xls");

// Jadval sarlavhalari
echo "Talaba ID\tFamiliya\tIsm\tOtasi\tGuruh\tYonalish\tKurs\tFan1\tFan2\tFan3\n";

// Ma'lumotlarni olish
$stmt = $pdo->prepare("SELECT * FROM bepul");
$stmt->execute();

while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    echo $row['talaba_id'] . "\t";
    echo $row['familiya'] . "\t";
    echo $row['ism'] . "\t";
    echo $row['otasi'] . "\t";
    echo $row['guruh'] . "\t";
    echo $row['yonalish'] . "\t";
    echo $row['kurs'] . "\t";
    echo $row['fan1'] . "\t";
    echo $row['fan2'] . "\t";
    echo $row['fan3'] . "\n";
}

<?php
session_start();
require "database.php";
?>

<?php require "Includes/header.php"; ?>

<body>
    <div class="container">
        <h1 class="bg-white text-danger p-2 mb-3 text-center shadow">Pullik A'rizaga topshirgan talabalar ro'yxati</h1>
        <a href="export_pullik.php" class="btn border-white shadow btn-success mb-3">
            Excelga export qilish
        </a>
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Talaba ID</th>
                    <th>Familiya</th>
                    <th>Ism</th>
                    <th>Otasi</th>
                    <th>Guruh</th>
                    <th>Yo‘nalish</th>
                    <th>Kurs</th>
                    <th>Fan 1</th>
                    <th>Fan 2</th>
                    <th>Fan 3</th>
                </tr>
            </thead>
            <tbody>

                <?php
                $stmt = $pdo->prepare("SELECT * FROM pullik");
                $stmt->execute();
                $rows = $stmt->fetchAll();

                if ($rows) {
                    $i = 1;
                    foreach ($rows as $row) {
                        echo "<tr>
                        <td>{$i}</td>
                        <td>{$row['talaba_id']}</td>
                        <td>{$row['familiya']}</td>
                        <td>{$row['ism']}</td>
                        <td>{$row['otasi']}</td>
                        <td>{$row['guruh']}</td>
                        <td>{$row['yonalish']}</td>
                        <td>{$row['kurs']}</td>
                        <td>{$row['fan1']}</td>
                        <td>{$row['fan2']}</td>
                        <td>{$row['fan3']}</td>
                    </tr>";
                        $i++;
                    }
                } else {
                    echo "<tr><td colspan='11' class='text-center'>Ma'lumot topilmadi</td></tr>";
                }
                ?>

            </tbody>
        </table>
    </div>

</body>

<?php require "Includes/footer.php"; ?>
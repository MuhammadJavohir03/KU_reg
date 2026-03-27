<?php
session_start();
require "database.php";
?>

<?php require "Includes/header.php"; ?>

<body>
    <div class="container">

        <h1 class="bg-white text-danger p-2 mb-3 text-center shadow">
            Pullik A'riza Ro'yxati
        </h1>

        <form method="GET" class="row g-2 mb-3">

            <input name="talaba_id" class="form-control col" placeholder="Talaba ID" value="<?= $_GET['talaba_id'] ?? '' ?>">
            <input name="familiya" class="form-control col" placeholder="Familiya" value="<?= $_GET['familiya'] ?? '' ?>">
            <input name="ism" class="form-control col" placeholder="Ism" value="<?= $_GET['ism'] ?? '' ?>">
            <input name="otasi" class="form-control col" placeholder="Otasi" value="<?= $_GET['otasi'] ?? '' ?>">
            <input name="guruh" class="form-control col" placeholder="Guruh" value="<?= $_GET['guruh'] ?? '' ?>">
            <input name="yonalish" class="form-control col" placeholder="Yo'nalish" value="<?= $_GET['yonalish'] ?? '' ?>">
            <input name="kurs" class="form-control col" placeholder="Kurs" value="<?= $_GET['kurs'] ?? '' ?>">

            <button type="submit" class="btn btn-success col-md-2">Filter</button>

        </form>

        <div class="mb-3">
            <a href="export_pullik.php?<?= http_build_query($_GET) ?>"
                class="btn btn-success w-100">
                Excelga export
            </a>
        </div>

        <?php

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
        $rows = $stmt->fetchAll();
        ?>

        <table class="table table-bordered shadow">
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
                    <th>Fan1</th>
                    <th>Fan2</th>
                    <th>Fan3</th>
                </tr>
            </thead>

            <tbody>
                <?php if ($rows): ?>
                    <?php $i = 1;
                    foreach ($rows as $row): ?>
                        <tr>
                            <td><?= $i++ ?></td>
                            <td><?= htmlspecialchars($row['talaba_id']) ?></td>
                            <td><?= htmlspecialchars($row['familiya']) ?></td>
                            <td><?= htmlspecialchars($row['ism']) ?></td>
                            <td><?= htmlspecialchars($row['otasi']) ?></td>
                            <td><?= htmlspecialchars($row['guruh']) ?></td>
                            <td><?= htmlspecialchars($row['yonalish']) ?></td>
                            <td><?= htmlspecialchars($row['kurs']) ?></td>
                            <td><?= htmlspecialchars($row['fan1']) ?></td>
                            <td><?= htmlspecialchars($row['fan2']) ?></td>
                            <td><?= htmlspecialchars($row['fan3']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="11" class="text-center">Ma'lumot topilmadi</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
        <a class="bg-danger text-decoration-none text-white p-2 mb-5" href="arizaroyhati.php">Orqaga</a>
    </div>
</body>

<?php require "Includes/footer.php"; ?>
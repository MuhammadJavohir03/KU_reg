<?php
session_start();
require "database.php";
?>

<?php require "Includes/header.php"; ?>

<body>
    <div class="container">

        <h1 class="bg-white text-danger p-2 mb-3 text-center shadow">
            Bepul A'rizaga topshirgan talabalar ro'yxati
        </h1>
        <div class="mb-2">
            <a class="p-2 btn border-white shadow-sm btn-danger" href="arizaroyhati.php">Orqaga</a>
        </div>
        <form method="GET" class="row g-2 mb-3">



            <div class="col-md-4">
                <input
                    list="guruhlar"
                    name="guruh"
                    class="form-control"
                    placeholder="Guruh yozing yoki tanlang"
                    value="<?= isset($_GET['guruh']) ? htmlspecialchars($_GET['guruh']) : '' ?>">

                <datalist id="guruhlar">
                    <?php
                    $groups = $pdo->query("SELECT DISTINCT guruh FROM bepul")->fetchAll();
                    foreach ($groups as $g) {
                        echo "<option value='{$g['guruh']}'>";
                    }
                    ?>
                </datalist>
            </div>

            <div class="col-md-2">
                <button type="submit" class="btn btn-success border-white shadow-sm w-100">
                    Filter
                </button>
            </div>

            <div class="col-md-3">
                <a href="export_bepul.php?guruh=<?= isset($_GET['guruh']) ? urlencode($_GET['guruh']) : '' ?>"
                    class="border-white shadow-sm btn btn-success w-100">
                    Excelga export
                </a>
            </div>

        </form>

        <table class="shadow table table-bordered">
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
                $guruh = $_GET['guruh'] ?? '';

                if (!empty($guruh)) {
                    $stmt = $pdo->prepare("SELECT * FROM bepul WHERE guruh LIKE :guruh");
                    $stmt->execute([
                        'guruh' => '%' . $guruh . '%'
                    ]);
                } else {
                    $stmt = $pdo->prepare("SELECT * FROM bepul");
                    $stmt->execute();
                }

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
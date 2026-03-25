<?php
set_time_limit(0);
ini_set('memory_limit', '1024M');
?>

<?php
require "database.php";

$response = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['file'])) {

    $file = $_FILES['file']['tmp_name'];

    if (($handle = fopen($file, "r")) !== FALSE) {

        $pdo->exec("DELETE FROM users WHERE role != 'super_admin'");

        $count = 0;

        $header = fgetcsv($handle, 1000, ";");
        if (!$header) $header = fgetcsv($handle, 1000, "\t");
        if (!$header) $header = fgetcsv($handle, 1000, ",");

        while (($data = fgetcsv($handle, 1000, ";")) !== FALSE
            || ($data = fgetcsv($handle, 1000, "\t")) !== FALSE
            || ($data = fgetcsv($handle, 1000, ",")) !== FALSE
        ) {

            $email      = isset($data[3]) ? trim($data[3]) : '';
            $talaba_id  = isset($data[0]) ? trim($data[0]) : '';
            $fio        = isset($data[1]) ? trim($data[1]) : '';
            $kurs       = isset($data[2]) ? trim($data[2]) : '';
            $guruh      = isset($data[2]) ? trim($data[2]) : '';

            if ($email === '') continue;

            if (stripos($talaba_id, 'E') !== false) {
                $talaba_id = number_format(floatval($talaba_id), 0, '', '');
            }

            $password = password_hash("a1234567", PASSWORD_DEFAULT);
            $role = 'user';
            $status = 'active';

            try {
                $stmt = $pdo->prepare("
                    INSERT INTO users (email, password, talaba_id, role, status, fio, kurs, guruh)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?)
                ");
                $stmt->execute([$email, $password, $talaba_id, $role, $status, $fio, $kurs, $guruh]);
                $count++;
            } catch (Exception $e) {
                continue;
            }
        }

        fclose($handle);

        $response = [
            'success' => true,
            'message' => "✅ $count ta foydalanuvchi muvaffaqiyatli import qilindi!"
        ];
    } else {
        $response = ['success' => false, 'message' => "❌ Fayl ochilmadi!"];
    }
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $response = ['success' => false, 'message' => "❌ Fayl tanlanmadi!"];
}
?>

<!DOCTYPE html>
<html lang="uz">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CSV Import</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>

    <div class="container">
        <a class="back-btn m-3" href="index.php">
            <span class="arrow">←</span>
            <span class="text">Orqaga</span>
        </a>

        <style>
            .back-btn {
                display: inline-flex;
                align-items: center;
                gap: 8px;
                padding: 8px 16px;
                border: 2px solid #dc3545;
                color: #dc3545;
                text-decoration: none;
                border-radius: 8px;
                font-weight: 500;
                transition: all 0.3s ease;
            }

            .back-btn:hover {
                background-color: #dc3545;
                color: white;
            }

            .back-btn .arrow {
                font-size: 20px;
                transition: transform 0.3s ease;
            }

            .back-btn:hover .arrow {
                transform: translateX(-5px);
            }

            .back-btn .text {
                transition: transform 0.3s ease;
            }

            .back-btn:hover .text {
                transform: translateX(-3px);
            }
        </style>
    </div>

    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card shadow-sm">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0">Foydalanuvchilarni CSV dan import qilish</h5>
                    </div>
                    <div class="card-body">
                        <form method="post" enctype="multipart/form-data">
                            <div class="mb-3">
                                <label for="file" class="form-label">CSV faylni tanlang</label>
                                <input class="form-control" type="file" id="file" name="file" accept=".csv" required>
                            </div>
                            <div class="d-grid">
                                <button type="submit" class="btn btn-success">Import qilish</button>
                            </div>
                        </form>

                        <?php if ($response): ?>
                            <div class="mt-3">
                                <?php if ($response['success']): ?>
                                    <div class="alert alert-success"><?= $response['message'] ?></div>
                                <?php else: ?>
                                    <div class="alert alert-danger"><?= $response['message'] ?></div>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>

                        <div class="mt-3">
                            <small class="text-muted">
                                CSV format: IDU; To‘liq ism; O‘quv kursi; Student guruhi; Elektron pochta<br>
                                **IDU ustuni Text formatida bo‘lishi kerak** (katta raqamlar scientific notation bo‘lmasligi uchun)
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
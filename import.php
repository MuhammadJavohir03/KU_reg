<?php
require "database.php"; // PDO bilan DB ulanish

$response = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['file'])) {

    $file = $_FILES['file']['tmp_name'];

    if (($handle = fopen($file, "r")) !== FALSE) {

        // eski foydalanuvchilarni o'chirish, super admin saqlansin
        $pdo->exec("DELETE FROM users WHERE role != 'super_admin'");

        $count = 0;

        // Headerni o‘tkazib yuborish
        $header = fgetcsv($handle, 1000, ";"); // Semicolon delimiter
        if (!$header) $header = fgetcsv($handle, 1000, "\t"); // Tab delimiter
        if (!$header) $header = fgetcsv($handle, 1000, ","); // Comma delimiter

        while (($data = fgetcsv($handle, 1000, ";")) !== FALSE
            || ($data = fgetcsv($handle, 1000, "\t")) !== FALSE
            || ($data = fgetcsv($handle, 1000, ",")) !== FALSE
        ) {

            // CSV ustunlarini tekshirish va trim
            $email      = isset($data[3]) ? trim($data[3]) : '';
            $talaba_id  = isset($data[0]) ? trim($data[0]) : '';
            $fio        = isset($data[1]) ? trim($data[1]) : '';
            $kurs       = isset($data[2]) ? trim($data[2]) : '';
            $guruh      = isset($data[2]) ? trim($data[2]) : ''; // agar guruh 2-ustunda bo‘lsa

            // Email bo‘lmasa, qatorni o‘tkazib yuborish
            if ($email === '') continue;

            // IDU scientific notation bo‘lsa stringga o‘tkazish
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
                continue; // xato bo‘lsa, qatorni o‘tkazib yuborish
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
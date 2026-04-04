<?php
require "database.php";
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
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

$sql = "
    SELECT 
        b.*,
        u.fio,
        u.talaba_id,
        u.email,
        u.guruh,
        CONCAT(f1.nomi, ' (', b.fan1, ')') AS fan1_full,
        CONCAT(f2.nomi, ' (', b.fan2, ')') AS fan2_full,
        CONCAT(f3.nomi, ' (', b.fan3, ')') AS fan3_full,
        CONCAT(f4.nomi, ' (', b.fan4, ')') AS fan4_full
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
$data = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<?php require "Includes/header.php"; ?>

<style>
    body { background: #f4f7fe; font-family: 'Plus Jakarta Sans', sans-serif; color: #2d3748; }
    .dashboard-card { background: #fff; border-radius: 20px; box-shadow: 0 15px 35px rgba(0,0,0,0.05); padding: 30px; margin-top: 30px; border: 1px solid rgba(0,0,0,0.02); }
    .search-section { background: #f8fafc; border-radius: 15px; padding: 20px; margin-bottom: 25px; border: 1px solid #eef2f7; }
    .table-responsive { border-radius: 12px; overflow: hidden; }
    .table { border-spacing: 0 8px; border-collapse: separate; }
    .table thead th { background: transparent; color: #718096; text-transform: uppercase; font-size: 0.75rem; font-weight: 700; border: none; padding: 15px; }
    .table tbody tr { background: #fff; transition: all 0.2s; box-shadow: 0 2px 4px rgba(0,0,0,0.01); }
    .table tbody tr:hover { transform: translateY(-2px); box-shadow: 0 8px 20px rgba(0,0,0,0.06); background-color: #fcfdff; }
    .table td { padding: 16px 15px; border-top: 1px solid #f1f4f8; border-bottom: 1px solid #f1f4f8; vertical-align: middle; }
    .table td:first-child { border-left: 1px solid #f1f4f8; border-radius: 12px 0 0 12px; }
    .table td:last-child { border-right: 1px solid #f1f4f8; border-radius: 0 12px 12px 0; }
    .fan-badge { display: inline-block; background: linear-gradient(135deg, #fff5f5 0%, #fed7d7 100%); color: #c53030; padding: 5px 12px; border-radius: 8px; font-size: 0.75rem; font-weight: 600; margin: 2px; border: 1px solid #feb2b2; }
    .btn-custom { border-radius: 12px; padding: 10px 22px; font-weight: 600; transition: 0.3s; display: inline-flex; align-items: center; gap: 8px; }
    .btn-search { background: #4361ee; color: white; border: none; }
    .btn-search:hover { background: #3a0ca3; color: white; transform: translateY(-1px); }
    .btn-export { background: #2dce89; color: white; border: none; }
    .btn-export:hover { background: #26af74; color: white; }
    .btn-back { background: #f7fafc; color: #4a5568; border: 1px solid #e2e8f0; }
    code { background: #ebf4ff; color: #3182ce; padding: 4px 10px; border-radius: 6px; font-weight: 600; font-family: 'Monaco', monospace; }
</style>

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">

<body>
    <?php require "Includes/yuklash.php"; ?>
    <?php require "Includes/navbar.php"; ?>

    <div class="container-fluid px-4 mb-5">
        <div class="dashboard-card">
            
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-3">
                <div>
                    <h2 class="fw-bold text-dark mb-1">
                        <i class="bi bi-wallet2 text-danger me-2"></i> Pullik Ro‘yxat
                    </h2>
                    <p class="text-muted small mb-0">Pullik asosda ariza topshirgan talabalar ma'lumotnomasi</p>
                </div>
                <a class="btn btn-back btn-custom shadow-sm" href="arizaroyhati.php">
                    <i class="bi bi-chevron-left"></i> Orqaga qaytish
                </a>
            </div>

            <div class="search-section shadow-sm">
                <form method="GET" class="row g-3">
                    <div class="col-md-7">
                        <div class="input-group">
                            <span class="input-group-text bg-white border-end-0" style="border-radius: 10px 0 0 10px;">
                                <i class="bi bi-search text-muted"></i>
                            </span>
                            <input type="text" name="q" class="form-control border-start-0 ps-0" 
                                   placeholder="FIO, ID, email yoki fan nomini yozing..." 
                                   value="<?= htmlspecialchars($q) ?>" style="border-radius: 0 10px 10px 0;">
                        </div>
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-search btn-custom w-100 shadow-sm">
                            <i class="bi bi-funnel"></i> Saralash
                        </button>
                    </div>
                    <div class="col-md-3">
                        <a href="export_pullik.php?q=<?= urlencode($q) ?>" class="btn btn-export btn-custom w-100 shadow-sm">
                            <i class="bi bi-file-earmark-spreadsheet"></i> Excelga chiqarish
                        </a>
                    </div>
                </form>
            </div>

            <div class="table-responsive">
                <table class="table align-middle">
                    <thead>
                        <tr>
                            <th class="text-center" width="60">No</th>
                            <th>Talaba va ID</th>
                            <th>Aloqa ma'lumotlari</th>
                            <th class="text-center">Guruh</th>
                            <th>HEMIS Parol</th>
                            <th>Tanlangan fanlar</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($data)): ?>
                            <tr>
                                <td colspan="6" class="text-center py-5">
                                    <i class="bi bi-inbox text-light display-1"></i>
                                    <p class="text-muted mt-3">Ma'lumotlar topilmadi</p>
                                </td>
                            </tr>
                        <?php else: $i = 1; foreach ($data as $row): ?>
                            <tr>
                                <td class="text-center fw-bold text-muted"><?= $i++ ?></td>
                                <td>
                                    <div class="fw-bold text-dark"><?= htmlspecialchars($row['fio']) ?></div>
                                    <div class="text-danger small fw-semibold">ID: <?= htmlspecialchars($row['talaba_id']) ?></div>
                                </td>
                                <td>
                                    <div class="small"><i class="bi bi-envelope-fill me-1 text-muted"></i> <?= htmlspecialchars($row['email']) ?></div>
                                </td>
                                <td class="text-center">
                                    <span class="badge rounded-pill bg-light text-dark border px-3 py-2">
                                        <?= htmlspecialchars($row['guruh']) ?>
                                    </span>
                                </td>
                                <td><code><?= htmlspecialchars($row['hemis_parol']) ?></code></td>
                                <td>
                                    <div class="d-flex flex-wrap gap-1" style="max-width: 350px;">
                                        <?php for($j=1; $j<=4; $j++): 
                                            $fname = "fan{$j}_full";
                                            if(!empty(trim($row[$fname] ?? ''))): ?>
                                                <span class="fan-badge"><?= htmlspecialchars($row[$fname]) ?></span>
                                            <?php endif; 
                                        endfor; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; endif; ?>
                    </tbody>
                </table>
            </div>
            
            <div class="mt-4 text-end">
                <span class="text-muted small">
                    Jami topilgan yozuvlar: <strong><?= count($data) ?></strong> ta
                </span>
            </div>

        </div>
    </div>

    <?php require "Includes/footer.php"; ?>
</body>
</html>
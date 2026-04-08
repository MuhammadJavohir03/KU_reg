<?php
require "database.php";
session_start();
$title = "Bepul Ro'yhat";

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// --- 1. O'CHIRISH MANTIQI ---
if (isset($_GET['delete_id']) && isset($_GET['column'])) {
    $del_id = (int)$_GET['delete_id'];
    $column = $_GET['column'];

    // Xavfsizlik uchun faqat ruxsat berilgan ustunlarni tekshiramiz
    $allowed_columns = ['fan1', 'fan2', 'fan3', 'fan4'];
    if (in_array($column, $allowed_columns)) {
        // Ustunni NULL (bo'sh) qilib qo'yamiz
        $del_stmt = $pdo->prepare("UPDATE bepul SET $column = NULL WHERE id = ?");
        $del_stmt->execute([$del_id]);

        // Agar hamma fanlar NULL bo'lib qolsa, qatorni butunlay o'chirib yuborsangiz ham bo'ladi (ixtiyoriy)
        $check_stmt = $pdo->prepare("SELECT fan1, fan2, fan3, fan4 FROM bepul WHERE id = ?");
        $check_stmt->execute([$del_id]);
        $res = $check_stmt->fetch(PDO::FETCH_ASSOC);
        if (!$res['fan1'] && !$res['fan2'] && !$res['fan3'] && !$res['fan4']) {
            $pdo->prepare("DELETE FROM bepul WHERE id = ?")->execute([$del_id]);
        }

        header("Location: bepul_royhat.php?msg=deleted");
        exit;
    }
}

$q = $_GET['q'] ?? '';
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if ($page < 1) $page = 1;
$limit = 100; // Har sahifada 100 ta yozuv
$offset = ($page - 1) * $limit;

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

// --- 2. UMUMIY SONINI ANIQLASH (Pagination uchun) ---
$countSql = "SELECT COUNT(*) FROM bepul b JOIN users u ON u.id = b.user_id 
             LEFT JOIN fanlar f1 ON f1.id = b.fan1
             LEFT JOIN fanlar f2 ON f2.id = b.fan2
             LEFT JOIN fanlar f3 ON f3.id = b.fan3
             LEFT JOIN fanlar f4 ON f4.id = b.fan4 $where";
$countStmt = $pdo->prepare($countSql);
$countStmt->execute($params);
$total_rows = $countStmt->fetchColumn();
$total_pages = ceil($total_rows / $limit);

// --- 3. MA'LUMOTLARNI OLISH (LIMIT bilan) ---
$sql = "
    SELECT 
        b.*,
        u.fio, u.talaba_id, u.email, u.guruh,
        t1.reyting AS r1, t1.davomat AS d1,
        t2.reyting AS r2, t2.davomat AS d2,
        t3.reyting AS r3, t3.davomat AS d3,
        t4.reyting AS r4, t4.davomat AS d4,
        f1.semestr AS s1, f2.semestr AS s2, f3.semestr AS s3, f4.semestr AS s4,
        CONCAT(f1.nomi, ' (', b.fan1, ')') AS fan1_full,
        CONCAT(f2.nomi, ' (', b.fan2, ')') AS fan2_full,
        CONCAT(f3.nomi, ' (', b.fan3, ')') AS fan3_full,
        CONCAT(f4.nomi, ' (', b.fan4, ')') AS fan4_full
    FROM bepul b
    JOIN users u ON u.id = b.user_id
    LEFT JOIN talabalar t1 ON TRIM(t1.talaba_id) = TRIM(u.talaba_id) AND t1.fan_id = b.fan1
    LEFT JOIN talabalar t2 ON TRIM(t2.talaba_id) = TRIM(u.talaba_id) AND t2.fan_id = b.fan2
    LEFT JOIN talabalar t3 ON TRIM(t3.talaba_id) = TRIM(u.talaba_id) AND t3.fan_id = b.fan3
    LEFT JOIN talabalar t4 ON TRIM(t4.talaba_id) = TRIM(u.talaba_id) AND t4.fan_id = b.fan4
    LEFT JOIN fanlar f1 ON f1.id = b.fan1
    LEFT JOIN fanlar f2 ON f2.id = b.fan2
    LEFT JOIN fanlar f3 ON f3.id = b.fan3
    LEFT JOIN fanlar f4 ON f4.id = b.fan4
    $where
    ORDER BY b.id DESC
    LIMIT $limit OFFSET $offset
";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$data = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<?php require "Includes/header.php"; ?>

<style>
    body {
        background: #f4f7fe;
        font-family: 'Plus Jakarta Sans', sans-serif;
        color: #2d3748;
    }

    .dashboard-card {
        background: #fff;
        border-radius: 20px;
        box-shadow: 0 15px 35px rgba(0, 0, 0, 0.05);
        padding: 30px;
        margin-top: 30px;
    }

    .search-section {
        background: #f8faff;
        border-radius: 15px;
        padding: 20px;
        margin-bottom: 25px;
        border: 1px solid #eef2f7;
    }

    .table-responsive {
        border-radius: 12px;
        overflow: hidden;
    }

    .table {
        border-spacing: 0 8px;
        border-collapse: separate;
    }

    .table thead th {
        background: #f8faff;
        color: #718096;
        text-transform: uppercase;
        font-size: 0.75rem;
        font-weight: 700;
        border: none;
        padding: 15px;
    }

    .table tbody tr {
        background: #fff;
        transition: all 0.2s;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.02);
    }

    .table tbody tr:hover {
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
    }

    .table td {
        padding: 15px;
        border-top: 1px solid #f1f4f8;
        border-bottom: 1px solid #f1f4f8;
        vertical-align: middle;
    }

    .fan-badge {
        display: inline-block;
        background: #e3f2fd;
        color: #1976d2;
        padding: 4px 10px;
        border-radius: 6px;
        font-size: 0.75rem;
        font-weight: 600;
        margin: 2px;
    }

    .btn-custom {
        border-radius: 10px;
        padding: 10px 20px;
        font-weight: 600;
        transition: 0.3s;
    }

    .rating-badge {
        background: #f0fdf4;
        color: #166534;
        padding: 3px 10px;
        border-radius: 6px;
        font-weight: 700;
        font-size: 0.85rem;
        border: 1px solid #bbf7d0;
        display: block;
        width: 80px;
    }

    .attendance-badge {
        background: #fff1f2;
        color: #9f1239;
        padding: 3px 10px;
        border-radius: 6px;
        font-weight: 700;
        font-size: 0.85rem;
        border: 1px solid #fecdd3;
        display: block;
        width: 80px;
    }

    .pagination .page-link {
        border-radius: 8px;
        margin: 0 3px;
        border: none;
        color: #4a5568;
        font-weight: 600;
    }

    .pagination .page-item.active .page-link {
        background-color: #0d6efd;
        color: white;
    }
</style>

<?php require "atmosphere.php"; ?>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">

<body>
    <?php require "Includes/yuklash.php"; ?>
    <?php require "Includes/navbar.php"; ?>

    <div class="container-fluid px-4 mb-5">
        <div class="dashboard-card">

            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h2 class="fw-bold text-dark mb-1"><i class="bi bi-clipboard-check text-primary me-2"></i> Bepul Ro‘yxat</h2>
                    <p class="text-muted small">Jami yozuvlar: <b><?= $total_rows ?></b> ta</p>
                </div>
            </div>

            <div class="search-section">
                <form method="GET" class="row g-3">
                    <div class="col-md-7">
                        <div class="input-group shadow-sm">
                            <span class="input-group-text bg-white border-end-0"><i class="bi bi-search text-muted"></i></span>
                            <input type="text" name="q" class="form-control border-start-0 ps-0"
                                placeholder="FIO, ID yoki fan nomini yozing..." value="<?= htmlspecialchars($q) ?>">
                        </div>
                    </div>
                    <div class="col-md-2">
                        <button class="btn btn-primary btn-custom w-100">Qidirish</button>
                    </div>
                    <div class="col-md-3">
                        <a href="export_bepul.php?q=<?= urlencode($q) ?>" class="btn btn-success btn-custom w-100">
                            <i class="bi bi-file-earmark-excel"></i> Excel Export
                        </a>
                    </div>
                </form>
            </div>

            <div class="table-responsive">
                <table class="table align-middle">
                    <thead>
                        <tr>
                            <th class="text-center" style="width: 50px;">No</th>
                            <th>Talaba ma'lumotlari</th>
                            <th>Aloqa & Guruh</th>
                            <th class="text-center">Natijalar</th>
                            <th>Semestr</th>
                            <th>Tanlangan fan</th>
                            <th class="text-center">Amallar</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($data)): ?>
                            <tr>
                                <td colspan="7" class="text-center py-5 text-muted">Ma'lumot topilmadi</td>
                            </tr>
                            <?php else:
                            $counter = $offset + 1;
                            foreach ($data as $row):
                                for ($j = 1; $j <= 4; $j++):
                                    $fname = "fan{$j}_full";
                                    $r_alias = "r{$j}";
                                    $d_alias = "d{$j}";
                                    $s_alias = "s{$j}";

                                    if (!empty(trim($row[$fname] ?? ''))): ?>
                                        <tr>
                                            <td class="text-center fw-bold text-muted"><?= $counter++ ?></td>
                                            <td>
                                                <div class="fw-bold"><?= htmlspecialchars($row['fio']) ?></div>
                                                <div class="small text-primary">ID: <?= htmlspecialchars($row['talaba_id']) ?></div>
                                            </td>
                                            <td>
                                                <div class="small"><i class="bi bi-envelope me-1"></i><?= htmlspecialchars($row['email']) ?></div>
                                                <span class="badge bg-light text-dark border mt-1"><?= htmlspecialchars($row['guruh']) ?></span>
                                            </td>
                                            <td class="text-center">
                                                <div class="d-flex flex-column align-items-center gap-1">
                                                    <span class="rating-badge" title="Reyting balli">
                                                        <i class="bi bi-graph-up-arrow me-1"></i>
                                                        <?= (isset($row[$r_alias]) && $row[$r_alias] !== null) ? htmlspecialchars($row[$r_alias]) : '0' ?>
                                                    </span>
                                                    <span class="attendance-badge" title="Davomat">
                                                        <i class="bi bi-clock-history me-1"></i>
                                                        <?= (isset($row[$d_alias]) && $row[$d_alias] !== null) ? htmlspecialchars($row[$d_alias]) : '0' ?>
                                                    </span>
                                                </div>
                                            </td>
                                            <td class="text-center">
                                                <span class="badge bg-info text-dark">
                                                    <?= (isset($row[$s_alias]) && $row[$s_alias] !== null) ? htmlspecialchars($row[$s_alias]) . "-semestr" : "Noma'lum" ?>
                                                </span>
                                            </td>
                                            <td>
                                                <span class="fan-badge">
                                                    <i class="bi bi-book me-1"></i> <?= htmlspecialchars($row[$fname]) ?>
                                                </span>
                                            </td>
                                            <td class="text-center">
                                                <a href="?delete_id=<?= $row['id'] ?>&column=fan<?= $j ?>"
                                                    onclick="return confirm('Haqiqatan ham ushbu fanni ro‘yxatdan o‘chirmoqchimisiz?')"
                                                    class="btn btn-outline-danger btn-sm border-0" title="Fanni o'chirish">
                                                    <i class="bi bi-trash3-fill"></i>
                                                </a>
                                            </td>
                                        </tr>
                        <?php endif;
                                endfor;
                            endforeach;
                        endif; ?>
                    </tbody>
                </table>
            </div>

            <?php if ($total_pages > 1): ?>
                <nav aria-label="Page navigation" class="mt-4">
                    <ul class="pagination justify-content-center">
                        <li class="page-item <?= ($page <= 1) ? 'disabled' : '' ?>">
                            <a class="page-link" href="?page=<?= $page - 1 ?>&q=<?= urlencode($q) ?>"><i class="bi bi-chevron-left"></i></a>
                        </li>

                        <?php
                        $start = max(1, $page - 2);
                        $end = min($total_pages, $page + 2);

                        for ($i = $start; $i <= $end; $i++): ?>
                            <li class="page-item <?= ($i == $page) ? 'active' : '' ?>">
                                <a class="page-link" href="?page=<?= $i ?>&q=<?= urlencode($q) ?>"><?= $i ?></a>
                            </li>
                        <?php endfor; ?>

                        <li class="page-item <?= ($page >= $total_pages) ? 'disabled' : '' ?>">
                            <a class="page-link" href="?page=<?= $page + 1 ?>&q=<?= urlencode($q) ?>"><i class="bi bi-chevron-right"></i></a>
                        </li>
                    </ul>
                </nav>
            <?php endif; ?>

        </div>
    </div>

    <?php require "Includes/footer.php"; ?>
</body>

</html>
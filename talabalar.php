<?php
session_start();
require "database.php";
$title = "Talabalar";

if (isset($_GET['check_id'])) {
    $id = trim($_GET['check_id']);
    $stmt = $pdo->prepare("SELECT fio FROM users WHERE talaba_id = ?");
    $stmt->execute([$id]);
    $user = $stmt->fetch();

    // Faqat ismni qaytaramiz va dasturni to'xtatamiz
    echo $user ? htmlspecialchars($user['fio']) : '';
    exit;
}

// 1. Kutubxonalarni avtomatik yuklovchi faylni ulaymiz
require_once __DIR__ . '/vendor/autoload.php';

// 2. Excel bilan ishlovchi klassni chaqiramiz
use PhpOffice\PhpSpreadsheet\IOFactory;

$fan_id = $_GET['id'] ?? $_GET['fan_id'] ?? null;
if (!$fan_id) die("Fan tanlanmagan");
/* ================= MA'LUMOTLAR VA PAGINATSIYA ================= */

$limit = 50;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if ($page < 1) $page = 1;
$offset = ($page - 1) * $limit;

$filter = $_GET['filter'] ?? 'all';

// 1. Jami talabalar sonini aniqlash (Paginatsiya uchun)
$count_query = "SELECT COUNT(*) FROM talabalar WHERE fan_id = ?";
$count_stmt = $pdo->prepare($count_query);
$count_stmt->execute([$fan_id]);
$total_items = $count_stmt->fetchColumn();
$total_pages = ceil($total_items / $limit);

// 2. Ma'lumotlarni LIMIT bilan olish
$sql = "SELECT * FROM talabalar WHERE fan_id = ? ORDER BY id DESC LIMIT $limit OFFSET $offset";
$stmt = $pdo->prepare($sql);
$stmt->execute([$fan_id]);
$rows = $stmt->fetchAll();

$rows_data = [];
foreach ($rows as $r) {
    $davomat_fail = ($r['davomat'] >= 33);
    $reyting_fail = ($r['reyting'] < 20);
    $umumiy_fail = ($r['umumiy'] < 60);
    $isFail = ($davomat_fail || $reyting_fail || $umumiy_fail);

    // Filtrni qo'llash
    if ($filter == 'fail' && !$isFail) continue;
    if ($filter == 'pass' && $isFail) continue;

    $r['davomat_fail'] = $davomat_fail;
    $r['reyting_fail'] = $reyting_fail;
    $r['umumiy_fail'] = $umumiy_fail;
    $r['row_fail'] = $isFail;
    $rows_data[] = $r;
}
// Endi sizning eski kodlaringiz davom etadi...

// 1. AJAX uchun Talaba ID tekshirish qismi (Eng tepada bo'lishi shart)
if (isset($_POST['save'])) {
    $talaba_id = trim($_POST['talaba_id']);

    // --- TEKSHIRUV QISMI BOSHLANDI ---
    // Ushbu fan_id ga ushbu talaba_id avval biriktirilganmi?
    $check_sql = "SELECT COUNT(*) FROM talabalar WHERE fan_id = ? AND talaba_id = ?";
    $check_stmt = $pdo->prepare($check_sql);
    $check_stmt->execute([$fan_id, $talaba_id]);
    $exists = $check_stmt->fetchColumn();

    if ($exists > 0) {
        // Agar talaba allaqachon bo'lsa, to'xtatamiz va xabar beramiz
        header("Location: talabalar.php?fan_id=$fan_id&error=duplicate");
        exit;
    }
    // --- TEKSHIRUV QISMI TUGADI ---

    $stmt = $pdo->prepare("SELECT fio, talaba_id, guruh FROM users WHERE talaba_id=?");
    $stmt->execute([$talaba_id]);
    $user = $stmt->fetch();

    if ($user) {
        $pdo->prepare("INSERT INTO talabalar (fan_id, user_id, talaba_id, guruh, joriy_nazorat, oraliq_nazorat, reyting, yakuniy_nazorat, qayta_topshirish, umumiy, davomat) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)")
            ->execute([
                $fan_id,
                $user['fio'],
                $user['talaba_id'],
                $user['guruh'],
                $_POST['joriy_nazorat'] ?: 0,
                $_POST['oraliq_nazorat'] ?: 0,
                $_POST['reyting'] ?: 0,
                $_POST['yakuniy_nazorat'] ?: 0,
                $_POST['qayta_topshirish'] ?: 0,
                $_POST['umumiy'] ?: 0,
                $_POST['davomat'] ?: 0
            ]);
    }
    header("Location: talabalar.php?fan_id=$fan_id");
    exit;
}

// Rollarni aniqlash
$user_role = $_SESSION['role'] ?? 'user';
$is_super = ($user_role === 'super_admin');
$is_admin = ($user_role === 'admin' || $user_role === 'super_admin');



/* ================= FAN MA'LUMOTI ================= */
$stmt = $pdo->prepare("SELECT * FROM fanlar WHERE id=?");
$stmt->execute([$fan_id]);
$fan = $stmt->fetch();
if (!$fan) die("Fan topilmadi");

/* ================= AMALLAR (SUPER ADMIN) ================= */
if ($is_super) {
    if (isset($_GET['delete'])) {
        $id = (int) $_GET['delete'];
        $pdo->prepare("DELETE FROM talabalar WHERE id = ?")->execute([$id]);
        header("Location: talabalar.php?fan_id=$fan_id");
        exit;
    }

    if (isset($_POST['save'])) {
        $talaba_id = trim($_POST['talaba_id']);
        $stmt = $pdo->prepare("SELECT fio, talaba_id, guruh FROM users WHERE talaba_id=?");
        $stmt->execute([$talaba_id]);
        $user = $stmt->fetch();

        if ($user) {
            $pdo->prepare("INSERT INTO talabalar (fan_id, user_id, talaba_id, guruh, joriy_nazorat, oraliq_nazorat, reyting, yakuniy_nazorat, qayta_topshirish, umumiy, davomat) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)")
                ->execute([
                    $fan_id,
                    $user['fio'],
                    $user['talaba_id'],
                    $user['guruh'],
                    $_POST['joriy_nazorat'] ?: 0,
                    $_POST['oraliq_nazorat'] ?: 0,
                    $_POST['reyting'] ?: 0,
                    $_POST['yakuniy_nazorat'] ?: 0,
                    $_POST['qayta_topshirish'] ?: 0,
                    $_POST['umumiy'] ?: 0,
                    $_POST['davomat'] ?: 0
                ]);
        }
        header("Location: talabalar.php?fan_id=$fan_id");
        exit;
    }

    if (isset($_POST['update'])) {
        $pdo->prepare("UPDATE talabalar SET joriy_nazorat=?, oraliq_nazorat=?, reyting=?, yakuniy_nazorat=?, qayta_topshirish=?, umumiy=?, davomat=? WHERE id=? AND fan_id=?")
            ->execute([$_POST['joriy_nazorat'], $_POST['oraliq_nazorat'], $_POST['reyting'], $_POST['yakuniy_nazorat'], $_POST['qayta_topshirish'], $_POST['umumiy'], $_POST['davomat'], $_POST['id'], $fan_id]);
        header("Location: talabalar.php?fan_id=$fan_id");
        exit;
    }
}

/* ================= MULTIPLE IMPORT (ADMIN & SUPER) ================= */
if ($is_admin && isset($_POST['import_multiple'])) {
    $total_inserted = 0;
    $total_skipped = 0;
    $file_count = count($_FILES['files']['name']);

    try {
        for ($i = 0; $i < $file_count; $i++) {
            if ($_FILES['files']['error'][$i] !== UPLOAD_ERR_OK) continue;

            $file_tmp = $_FILES['files']['tmp_name'][$i];

            // Faylni yuklash
            $spreadsheet = IOFactory::load($file_tmp);
            $worksheet = $spreadsheet->getActiveSheet();
            $rows = $worksheet->toArray();

            // Sarlavhani olib tashlash
            array_shift($rows);

            foreach ($rows as $d) {
                if (empty($d[1])) continue; // FIO bo'sh bo'lsa o'tkazish

                $csv_fio = trim($d[1]);

                // 1. Userni qidirish
                $q = $pdo->prepare("SELECT fio, talaba_id, guruh FROM users WHERE fio = ?");
                $q->execute([$csv_fio]);
                $user = $q->fetch(PDO::FETCH_ASSOC);

                if ($user) {
                    $t_id = $user['talaba_id'];

                    // 2. Dublikatni tekshirish (shu fanda bor-yo'qligi)
                    $check = $pdo->prepare("SELECT id FROM talabalar WHERE fan_id = ? AND talaba_id = ?");
                    $check->execute([$fan_id, $t_id]);

                    if ($check->fetch()) {
                        $total_skipped++;
                        continue;
                    }

                    // 3. Bazaga kiritish
                    $stmt = $pdo->prepare("INSERT INTO talabalar 
                        (user_id, fan_id, talaba_id, guruh, joriy_nazorat, oraliq_nazorat, reyting, yakuniy_nazorat, qayta_topshirish, umumiy, davomat) 
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

                    $stmt->execute([
                        $user['fio'],
                        $fan_id,
                        $t_id,
                        $user['guruh'],
                        (float)($d[2] ?? 0),
                        (float)($d[3] ?? 0),
                        (float)($d[4] ?? 0),
                        (float)($d[5] ?? 0),
                        (($d[6] == "'-'" || $d[6] == "-" || empty($d[6])) ? 0 : (float)$d[6]),
                        (float)($d[7] ?? 0),
                        (float)($d[8] ?? 0)
                    ]);
                    $total_inserted++;
                }
            }
        }

        header("Location: talabalar.php?fan_id=$fan_id&success=1&inserted=$total_inserted&skipped=$total_skipped");
        exit;
    } catch (Exception $e) {
        die("Fayllarni o'qishda xatolik: " . $e->getMessage());
    }
}

if (isset($_GET['export'])) {
    header("Content-Type: text/csv; charset=utf-8");
    header("Content-Disposition: attachment; filename=talabalar.csv");
    $out = fopen("php://output", "w");
    fputs($out, chr(0xEF) . chr(0xBB) . chr(0xBF));
    fputcsv($out, ["FIO", "ID", "Guruh", "Joriy", "Oraliq", "Reyting", "Yakuniy", "Qayta", "Umumiy", "Davomat"], ";");
    foreach ($rows_data as $r) {
        fputcsv($out, [$r['user_id'], $r['talaba_id'], $r['guruh'], $r['joriy_nazorat'], $r['oraliq_nazorat'], $r['reyting'], $r['yakuniy_nazorat'], $r['qayta_topshirish'], $r['umumiy'], $r['davomat']], ";");
    }
    fclose($out);
    exit;
}
?>

<?php include "Includes/header.php"; ?>
<?php require "atmosphere.php"; ?>
<style>
    body {
        background: #f8fafc;
        font-family: 'Inter', sans-serif;
    }

    .glass-card {
        background: white;
        border-radius: 15px;
        box-shadow: 0 10px 25px rgba(0, 0, 0, 0.05);
        padding: 25px;
        border: none;
    }

    .score-input {
        width: 60px;
        text-align: center;
        border: 1px solid #e2e8f0;
        border-radius: 6px;
        padding: 4px;
        font-size: 14px;
    }

    .score-input[readonly] {
        background: transparent;
        border-color: transparent;
        font-weight: 700;
        pointer-events: none;
    }

    .row-fail {
        background-color: #fff1f2 !important;
    }

    .cell-fail {
        background-color: #fecaca !important;
        border-color: #ef4444 !important;
        color: #b91c1c !important;
        font-weight: bold;
    }

    .table thead th {
        background: #1e293b;
        color: white;
        font-size: 11px;
        text-transform: uppercase;
    }

    .btn-modern {
        border-radius: 8px;
        font-weight: 600;
        transition: 0.3s;
    }

    .filter-group {
        background: #ffffff;
        border-radius: 10px;
        padding: 4px;
        border: 1px solid #f1f5f9;
        box-shadow: 0 2px 5px rgba(0, 0, 0, 0.03);
    }

    .btn-filter {
        border: none;
        padding: 6px 16px;
        border-radius: 8px;
        font-size: 14px;
        font-weight: 600;
        text-decoration: none;
        transition: 0.2s;
    }

    .btn-filter.active {
        background: #1e293b !important;
        color: white !important;
    }

    /* Yangi qo'shish qatori uchun stil */
    .new-row-style {
        background-color: #f0fdf4 !important;
        border-left: 4px solid #22c55e !important;
    }

    .id-search-input {
        border: 2px solid #0d6efd !important;
        font-weight: bold;
        background: #fff;
    }

    .icon-box {
        background: linear-gradient(135deg, #1e293b, #3b82f6);
        color: white;
        width: 45px;
        height: 45px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 12px;
        box-shadow: 0 4px 15px rgba(59, 130, 246, 0.3);
        font-size: 20px;
    }
</style>

<body>
    <?php require "Includes/yuklash.php"; ?>
    <?php include "Includes/navbar.php"; ?>

    <div class="container-fluid py-4 px-4">
        <div class="glass-card">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <a href="fanlar.php" class="btn btn-sm btn-outline-secondary mb-2">← Orqaga</a>
                    <h3 class="fw-bold text-dark m-0 d-flex align-items-center">
                        <span class="icon-box me-3">
                            <i class="fas fa-graduation-cap"></i>
                        </span>
                        <?= htmlspecialchars($fan['nomi']) ?>
                    </h3>
                </div>
                <div class="filter-group d-flex shadow-sm">
                    <a href="?fan_id=<?= $fan_id ?>" class="btn-filter <?= $filter == 'all' ? 'active' : 'text-dark' ?>">Hamma</a>
                    <a href="?fan_id=<?= $fan_id ?>&filter=fail" class="btn-filter text-danger <?= $filter == 'fail' ? 'active' : '' ?>">O'tolmagan</a>
                    <a href="?fan_id=<?= $fan_id ?>&filter=pass" class="btn-filter text-success <?= $filter == 'pass' ? 'active' : '' ?>">O'tgan</a>
                </div>
            </div>

            <?php if ($is_admin): ?>
                <div class="row g-3 mb-4 align-items-end">
                    <div class="col-md-5">
                        <a href="?fan_id=<?= $fan_id ?>&export=1" class="btn btn-sm btn-outline-success shadow-sm">📤 Natijalarni CSV ga eksport qilish</a>
                    </div>

                    <form method="POST" enctype="multipart/form-data" class="p-3 border rounded-3 bg-light">
                        <label class="small fw-bold d-block mb-2">KO'P FAYLLI IMPORT (Excel/CSV)</label>
                        <div class="input-group input-group-sm">
                            <input type="file" name="files[]" class="form-control" multiple required>
                            <button name="import_multiple" class="btn btn-primary">Natijalarni yuklash (ko'p)</button>
                        </div>
                        <small class="text-muted">Bir nechta faylni tanlashingiz mumkin (Ctrl tugmasini bosib turing)</small>
                    </form>
                    <div class="col-md-12">
                        <?php if (isset($_GET['success'])): ?>
                            <div class="alert alert-success border-0 shadow-sm mb-0" role="alert" style="border-radius: 12px;">
                                <i class="fas fa-check-circle me-2"></i>
                                <strong>Muvaffaqiyat!</strong> <?= (int)$_GET['inserted'] ?> ta talaba muvaffaqiyatli qo'shildi, <?= (int)$_GET['skipped'] ?> ta talaba dublikat bo'lgani uchun o'tkazib yuborildi.
                            </div>
                        <?php endif; ?>
                    </div>
                    <div class="col-md-7 text-end">
                        talaba qo'shish uchun <button id="toggleAddBtn" class="btn btn-sm btn-outline-primary shadow-sm">+ Yangi Talaba</button>
                    </div>
                </div>
            <?php endif; ?>
            <?php if (isset($_GET['error']) && $_GET['error'] == 'duplicate'): ?>
                <div class="alert alert-warning border-0 shadow-sm mb-4" role="alert" style="border-radius: 12px;">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    <strong>Diqqat!</strong> Ushbu talaba ushbu fan ro'yxatida allaqachon mavjud.
                </div>
            <?php endif; ?>
            <div class="table-responsive rounded-3 border">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th class="ps-3">No</th>
                            <th style="min-width: 200px;">Talaba Ma'lumoti</th>
                            <th class="text-center">Joriy</th>
                            <th class="text-center">Oraliq</th>
                            <th class="text-center">Reyting</th>
                            <th class="text-center">Yakuniy</th>
                            <th class="text-center">Qayta</th>
                            <th class="text-center">Umumiy</th>
                            <th class="text-center">Davomat</th>
                            <?php if ($is_super): ?><th class="text-end pe-3">Amallar</th><?php endif; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($is_super): ?>
                            <tr id="addRow" style="display: none;" class="new-row-style">
                                <form method="POST" id="newStudentForm">
                                    <td class="ps-3 text-success fw-bold">Yangi</td>
                                    <td>
                                        <input type="text" name="talaba_id" id="id_checker" class="form-control form-control-sm id-search-input mb-1" placeholder="Talaba ID yozing..." required>
                                        <div id="fio_res" class="small fw-bold text-primary"></div>
                                    </td>
                                    <td class="text-center"><input name="joriy_nazorat" class="score-input add-field" value="0" disabled></td>
                                    <td class="text-center"><input name="oraliq_nazorat" class="score-input add-field" value="0" disabled></td>
                                    <td class="text-center"><input name="reyting" class="score-input add-field" value="0" disabled></td>
                                    <td class="text-center"><input name="yakuniy_nazorat" class="score-input add-field" value="0" disabled></td>
                                    <td class="text-center"><input name="qayta_topshirish" class="score-input add-field" value="0" disabled></td>
                                    <td class="text-center"><input name="umumiy" class="score-input add-field" value="0" disabled></td>
                                    <td class="text-center"><input name="davomat" class="score-input add-field" value="0" disabled></td>
                                    <td class="text-end pe-3">
                                        <button name="save" class="btn btn-sm btn-primary add-field" disabled>SAQLASH</button>
                                    </td>
                                </form>
                            </tr>
                        <?php endif; ?>

                        <?php
                        $i = $offset + 1;
                        foreach ($rows_data as $r): ?>
                            <tr class="<?= $r['row_fail'] ? 'row-fail' : '' ?>">
                                <td class="ps-3 text-muted small"><?= $i++ ?></td>
                                <td>
                                    <div class="fw-bold d-block"><?= htmlspecialchars($r['user_id']) ?></div>
                                    <span class="small text-muted">ID: <?= $r['talaba_id'] ?> | <?= $r['guruh'] ?></span>
                                </td>
                                <form method="POST">
                                    <input type="hidden" name="id" value="<?= $r['id'] ?>">
                                    <td class="text-center"><input name="joriy_nazorat" class="score-input" value="<?= $r['joriy_nazorat'] ?>" <?= !$is_super ? 'readonly' : '' ?>></td>
                                    <td class="text-center"><input name="oraliq_nazorat" class="score-input" value="<?= $r['oraliq_nazorat'] ?>" <?= !$is_super ? 'readonly' : '' ?>></td>
                                    <td class="text-center">
                                        <input name="reyting" class="score-input <?= $r['reyting_fail'] ? 'cell-fail' : '' ?>" value="<?= $r['reyting'] ?>" <?= !$is_super ? 'readonly' : '' ?>>
                                    </td>
                                    <td class="text-center"><input name="yakuniy_nazorat" class="score-input" value="<?= $r['yakuniy_nazorat'] ?>" <?= !$is_super ? 'readonly' : '' ?>></td>
                                    <td class="text-center"><input name="qayta_topshirish" class="score-input" value="<?= $r['qayta_topshirish'] ?>" <?= !$is_super ? 'readonly' : '' ?>></td>
                                    <td class="text-center">
                                        <input name="umumiy" class="score-input <?= $r['umumiy_fail'] ? 'cell-fail' : '' ?>" value="<?= $r['umumiy'] ?>" <?= !$is_super ? 'readonly' : '' ?>>
                                    </td>
                                    <td class="text-center">
                                        <input name="davomat"
                                            class="score-input <?= $r['davomat_fail'] ? 'cell-fail' : '' ?>"
                                            value="<?= round($r['davomat'], 2) ?>"
                                            <?= !$is_super ? 'readonly' : '' ?>>
                                    </td>

                                    <?php if ($is_super): ?>
                                        <td class="text-end pe-3">
                                            <div class="d-flex gap-1 justify-content-end">
                                                <button name="update" class="btn btn-sm btn-success">OK</button>
                                                <a href="?delete=<?= $r['id'] ?>&fan_id=<?= $fan_id ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('O\'chirilsinmi?')">🗑️</a>
                                            </div>
                                        </td>
                                    <?php endif; ?>
                                </form>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <?php if ($total_pages > 1): ?>
        <nav aria-label="Page navigation" class="mt-4">
            <ul class="pagination justify-content-center">
                <li class="page-item <?= ($page <= 1) ? 'disabled' : '' ?>">
                    <a class="page-link shadow-sm" href="?fan_id=<?= $fan_id ?>&filter=<?= $filter ?>&page=<?= $page - 1 ?>">Oldingi</a>
                </li>

                <?php
                // Sahifalar juda ko'p bo'lsa, faqat ma'lum bir qismini ko'rsatish
                $start = max(1, $page - 2);
                $end = min($total_pages, $page + 2);

                for ($p = $start; $p <= $end; $p++): ?>
                    <li class="page-item <?= ($p == $page) ? 'active' : '' ?>">
                        <a class="page-link shadow-sm" href="?fan_id=<?= $fan_id ?>&filter=<?= $filter ?>&page=<?= $p ?>"><?= $p ?></a>
                    </li>
                <?php endfor; ?>

                <li class="page-item <?= ($page >= $total_pages) ? 'disabled' : '' ?>">
                    <a class="page-link shadow-sm" href="?fan_id=<?= $fan_id ?>&filter=<?= $filter ?>&page=<?= $page + 1 ?>">Keyingi</a>
                </li>
            </ul>
        </nav>
        <div class="text-center text-muted small mt-2">
            Jami: <?= $total_items ?> ta talabadan <?= $offset + 1 ?>-<?= min($offset + $limit, $total_items) ?> oraliqdagi ma'lumotlar.
        </div>
    <?php endif; ?>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const toggleBtn = document.getElementById('toggleAddBtn');
            const addRow = document.getElementById('addRow');
            const idInput = document.getElementById('id_checker');
            const fioRes = document.getElementById('fio_res');
            const scoreFields = document.querySelectorAll('.add-field');

            // 1. Qatorni ko'rsatish/yashirish
            if (toggleBtn) {
                toggleBtn.addEventListener('click', function() {
                    addRow.style.display = (addRow.style.display === 'none') ? 'table-row' : 'none';
                    idInput.focus();
                });
            }

            // 2. ID bo'yicha talabani qidirish (Real-time AJAX)
            if (idInput) {
                idInput.addEventListener('input', function() {
                    const val = this.value.trim();
                    if (val.length >= 3) {
                        // Xuddi shu faylga so'rov yuboramiz
                        fetch(`talabalar.php?fan_id=<?= $fan_id ?>&check_id=${val}`)
                            .then(res => res.text())
                            .then(name => {
                                if (name !== '') {
                                    fioRes.textContent = "✅ " + name;
                                    fioRes.style.color = "blue";
                                    // Baholarni ochish
                                    scoreFields.forEach(el => el.disabled = false);
                                } else {
                                    fioRes.textContent = "❌ Talaba topilmadi";
                                    fioRes.style.color = "red";
                                    scoreFields.forEach(el => el.disabled = true);
                                }
                            });
                    } else {
                        fioRes.textContent = "";
                        scoreFields.forEach(el => el.disabled = true);
                    }
                });
            }
        });
    </script>
</body>

</html>
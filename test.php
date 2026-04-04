<?php
session_start();
require "database.php";

$yonalish = $_GET['yonalish'] ?? '';
$guruh    = $_GET['guruh'] ?? '';
$semestr  = $_GET['semestr'] ?? '';
$search   = $_GET['search'] ?? '';
$status   = $_GET['status'] ?? 'all';

$active_subjects = [];
$students_data = [];
$total_count = 0;
$failed_count = 0;

// Yangi statistika uchun o'zgaruvchilar
$r_fail_count = 0; 
$u_fail_count = 0; 
$d_fail_count = 0;

if ($yonalish && $guruh && $semestr) {
    try {
        $sub_stmt = $pdo->prepare("SELECT DISTINCT f.id, f.nomi FROM fanlar f JOIN talabalar t ON f.id = t.fan_id WHERE f.yonalish = ? AND t.guruh = ? AND f.semestr = ?");
        $sub_stmt->execute([$yonalish, $guruh, $semestr]);
        $active_subjects = $sub_stmt->fetchAll(PDO::FETCH_ASSOC);

        if (!empty($active_subjects)) {
            $sql = "SELECT t.user_id as fio ";
            foreach ($active_subjects as $subject) {
                $id = $subject['id'];
                $sql .= ", MAX(CASE WHEN t.fan_id = $id THEN t.reyting END) as r_$id ";
                $sql .= ", MAX(CASE WHEN t.fan_id = $id THEN t.umumiy END) as u_$id ";
                $sql .= ", MAX(CASE WHEN t.fan_id = $id THEN t.davomat END) as d_$id ";
            }
            $sql .= " FROM talabalar t JOIN fanlar f ON t.fan_id = f.id WHERE f.yonalish = ? AND t.guruh = ? AND f.semestr = ?";
            $params = [$yonalish, $guruh, $semestr];

            if ($search) {
                $sql .= " AND t.user_id LIKE ?";
                $params[] = "%$search%";
            }
            $sql .= " GROUP BY t.user_id";

            $main_stmt = $pdo->prepare($sql);
            $main_stmt->execute($params);
            $raw_data = $main_stmt->fetchAll(PDO::FETCH_ASSOC);

            foreach ($raw_data as $row) {
                $is_failed = false;
                $r_bad = false; $u_bad = false; $d_bad = false;

                foreach ($active_subjects as $sub) {
                    $id = $sub['id'];
                    
                    // Reyting tekshiruvi
                    if ($row["r_$id"] < 20 && $row["r_$id"] !== null) {
                        $r_bad = true;
                        $is_failed = true;
                    }
                    // Umumiy ball tekshiruvi
                    if ($row["u_$id"] < 60 && $row["u_$id"] !== null) {
                        $u_bad = true;
                        $is_failed = true;
                    }
                    // Davomat tekshiruvi
                    if ($row["d_$id"] >= 33) {
                        $d_bad = true;
                        $is_failed = true;
                    }
                }

                // Statistika uchun sanash
                if ($r_bad) $r_fail_count++;
                if ($u_bad) $u_fail_count++;
                if ($d_bad) $d_fail_count++;
                if ($is_failed) $failed_count++;

                if ($status == 'fail' && !$is_failed) continue;
                if ($status == 'pass' && $is_failed) continue;

                $row['is_failed'] = $is_failed;
                $students_data[] = $row;
            }
            $total_count = count($raw_data);
        }
    } catch (PDOException $e) {
        die("Xatolik: " . $e->getMessage());
    }
}

// Export CSV mantiqi
if (isset($_GET['export_csv']) && !empty($students_data)) {
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename=hisobot.csv');
    $output = fopen('php://output', 'w');
    fputs($output, chr(0xEF) . chr(0xBB) . chr(0xBF));
    $header = ['Talaba F.I.O'];
    foreach ($active_subjects as $f) {
        $header[] = $f['nomi'] . " (R)";
        $header[] = $f['nomi'] . " (U)";
        $header[] = $f['nomi'] . " (D)";
    }
    fputcsv($output, $header, ";");
    foreach ($students_data as $s) {
        $line = [$s['fio']];
        foreach ($active_subjects as $f) {
            $id = $f['id'];
            $line[] = $s["r_$id"] ?? 0;
            $line[] = $s["u_$id"] ?? 0;
            $line[] = ($s["d_$id"] ?? 0) . "%";
        }
        fputcsv($output, $line, ";");
    }
    fclose($output);
    exit;
}

$pass_count = $total_count - $failed_count;
$pass_percent = $total_count > 0 ? round(($pass_count / $total_count) * 100) : 0;
$fail_percent = $total_count > 0 ? round(($failed_count / $total_count) * 100) : 0;

// Yangi foizlar
$r_fail_percent = $total_count > 0 ? round(($r_fail_count / $total_count) * 100) : 0;
$u_fail_percent = $total_count > 0 ? round(($u_fail_count / $total_count) * 100) : 0;
$d_fail_percent = $total_count > 0 ? round(($d_fail_count / $total_count) * 100) : 0;

$res_yonalish = $pdo->query("SELECT DISTINCT yonalish FROM fanlar WHERE yonalish IS NOT NULL")->fetchAll();
$res_guruh = $pdo->query("SELECT DISTINCT guruh FROM talabalar WHERE guruh IS NOT NULL")->fetchAll();
$res_semestr = $pdo->query("SELECT DISTINCT semestr FROM fanlar WHERE semestr IS NOT NULL ORDER BY semestr ASC")->fetchAll();
?>

<?php include "Includes/header.php"; ?>
<style>
    :root {
        --soft-border: #eef2f7;
        --text-muted: #6c757d;
    }
    body { background-color: #f4f7f9; }
    .stat-card { border-radius: 15px; border: none; transition: 0.3s; background: #fff; }
    .stat-card:hover { transform: translateY(-3px); box-shadow: 0 10px 20px rgba(0, 0, 0, 0.05); }
    .circle-stat { width: 50px; height: 50px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: bold; border: 3px solid; font-size: 0.85rem; }
    .custom-table-card { border-radius: 15px; overflow: hidden; border: 1px solid var(--soft-border); background: #fff; box-shadow: 0 5px 15px rgba(0, 0, 0, 0.02); }
    .table thead th { background-color: #f8f9fb; border-bottom: 1px solid var(--soft-border); color: #495057; font-weight: 600; font-size: 0.75rem; text-transform: uppercase; border-right: 1px solid #8a8a8a; }
    .table tbody td { border-bottom: 1px solid #afafaf; border-right: 1px solid #9c9c9c; color: #555; font-size: 0.88rem; padding: 12px 10px; }
    .fail-text { color: #e74c3c !important; font-weight: 600; background-color: #ff00001f !important; }
    .fio-col { text-align: left; padding-left: 20px !important; width: 300px; color: #2c3e50 !important; }
    .progress { height: 6px; border-radius: 10px; background-color: #f0f0f0; }
</style>

<body>
    <?php require "Includes/yuklash.php"; ?>
    <?php include "Includes/navbar.php"; ?>

    <div class="container-fluid p-4">
        <form method="GET" id="filterForm" class="stat-card shadow-sm p-4 mb-4">
            <div class="row g-3 align-items-end">
                <div class="col-md-2">
                    <label class="small fw-bold text-muted mb-1">Yo'nalish</label>
                    <input list="yonalish_list" name="yonalish" class="form-control select-auto shadow-none" placeholder="Yozing" value="<?= htmlspecialchars($yonalish) ?>">
                    <datalist id="yonalish_list">
                        <?php foreach ($res_yonalish as $r): ?><option value="<?= $r['yonalish'] ?>"><?php endforeach; ?>
                    </datalist>
                </div>
                <div class="col-md-2">
                    <label class="small fw-bold text-muted mb-1">Guruh</label>
                    <input list="guruh_list" name="guruh" class="form-control select-auto shadow-none" placeholder="Yozing" value="<?= htmlspecialchars($guruh) ?>">
                    <datalist id="guruh_list">
                        <?php foreach ($res_guruh as $r): ?><option value="<?= $r['guruh'] ?>"><?php endforeach; ?>
                    </datalist>
                </div>
                <div class="col-md-2">
                    <label class="small fw-bold text-muted mb-1">Semestr</label>
                    <input list="semestr_list" name="semestr" class="form-control select-auto shadow-none" placeholder="Tanlang" value="<?= htmlspecialchars($semestr) ?>">
                    <datalist id="semestr_list">
                        <?php foreach ($res_semestr as $r): ?><option value="<?= $r['semestr'] ?>"><?php endforeach; ?>
                    </datalist>
                </div>
                <div class="col-md-2">
                    <label class="small fw-bold text-muted mb-1">Holat</label>
                    <select name="status" class="form-select select-auto shadow-none">
                        <option value="all" <?= $status == 'all' ? 'selected' : '' ?>>Hamma talabalar</option>
                        <option value="pass" <?= $status == 'pass' ? 'selected' : '' ?>>O'tganlar</option>
                        <option value="fail" <?= $status == 'fail' ? 'selected' : '' ?>>Yiqilganlar</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="small fw-bold text-muted mb-1">Qidirish</label>
                    <div class="input-group">
                        <input type="text" name="search" class="form-control shadow-none" placeholder="Ism bo'yicha..." value="<?= htmlspecialchars($search) ?>">
                        <button class="btn btn-primary px-3" type="submit">🔍</button>
                    </div>
                </div>
            </div>
        </form>

        <?php if ($yonalish && $guruh && $semestr): ?>
            <div class="row g-3 mb-4">
                <div class="col-md-4">
                    <div class="stat-card p-3 d-flex align-items-center justify-content-between shadow-sm">
                        <div>
                            <span class="text-muted small fw-bold">MUVAFFAQIYATLI</span>
                            <h2 class="mb-0 text-success fw-bold"><?= $pass_count ?></h2>
                        </div>
                        <div class="circle-stat border-success text-success"><?= $pass_percent ?>%</div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="stat-card p-3 d-flex align-items-center justify-content-between shadow-sm">
                        <div>
                            <span class="text-muted small fw-bold">QARZDORLAR</span>
                            <h2 class="mb-0 text-danger fw-bold"><?= $failed_count ?></h2>
                        </div>
                        <div class="circle-stat border-danger text-danger"><?= $fail_percent ?>%</div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="stat-card p-3 shadow-sm bg-primary text-white">
                        <span class="text-white-50 small fw-bold">GURUHDA JAMI</span>
                        <h2 class="mb-0 fw-bold"><?= $total_count ?></h2>
                    </div>
                </div>
            </div>

            <div class="row g-3 mb-4">
                <div class="col-md-4">
                    <div class="stat-card p-3 shadow-sm border-start border-4 border-warning">
                        <span class="text-muted small fw-bold">R - REYTING (< 20)</span>
                        <div class="d-flex align-items-center justify-content-between mt-2">
                            <div>
                                <h4 class="mb-0 fw-bold text-dark"><?= $r_fail_count ?> <small class="text-muted" style="font-size:12px;">ta</small></h4>
                                <p class="text-danger small mb-0">Qizil: <?= $r_fail_percent ?>%</p>
                            </div>
                            <div class="text-success small text-end">
                                O'tgan: <?= 100 - $r_fail_percent ?>%
                                <div class="progress mt-1" style="width: 80px;"><div class="progress-bar bg-success" style="width: <?= 100 - $r_fail_percent ?>%"></div></div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="stat-card p-3 shadow-sm border-start border-4 border-info">
                        <span class="text-muted small fw-bold">U - UMUMIY (< 60)</span>
                        <div class="d-flex align-items-center justify-content-between mt-2">
                            <div>
                                <h4 class="mb-0 fw-bold text-dark"><?= $u_fail_count ?> <small class="text-muted" style="font-size:12px;">ta</small></h4>
                                <p class="text-danger small mb-0">Qizil: <?= $u_fail_percent ?>%</p>
                            </div>
                            <div class="text-success small text-end">
                                O'tgan: <?= 100 - $u_fail_percent ?>%
                                <div class="progress mt-1" style="width: 80px;"><div class="progress-bar bg-success" style="width: <?= 100 - $u_fail_percent ?>%"></div></div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="stat-card p-3 shadow-sm border-start border-4 border-danger">
                        <span class="text-muted small fw-bold">D - DAVOMAT (>= 33%)</span>
                        <div class="d-flex align-items-center justify-content-between mt-2">
                            <div>
                                <h4 class="mb-0 fw-bold text-dark"><?= $d_fail_count ?> <small class="text-muted" style="font-size:12px;">ta</small></h4>
                                <p class="text-danger small mb-0">Qizil: <?= $d_fail_percent ?>%</p>
                            </div>
                            <div class="text-success small text-end">
                                Yaxshi: <?= 100 - $d_fail_percent ?>%
                                <div class="progress mt-1" style="width: 80px;"><div class="progress-bar bg-success" style="width: <?= 100 - $d_fail_percent ?>%"></div></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="fw-bold text-dark mb-0">Natijalar Jadvali</h5>
                <a href="<?= $_SERVER['REQUEST_URI'] ?>&export_csv=1" class="btn btn-sm btn-success rounded-pill px-3 shadow-sm">📥 CSV Export</a>
            </div>

            <div class="custom-table-card shadow-sm">
                <div class="table-responsive">
                    <table class="table text-center align-middle mb-0">
                        <thead>
                            <tr>
                                <th rowspan="2" class="fio-col align-middle">Talaba Ismi</th>
                                <?php foreach ($active_subjects as $subject): ?>
                                    <th colspan="3"><?= htmlspecialchars($subject['nomi']) ?></th>
                                <?php endforeach; ?>
                            </tr>
                            <tr>
                                <?php foreach ($active_subjects as $subject): ?>
                                    <th>R</th><th>U</th><th>D</th>
                                <?php endforeach; ?>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($students_data as $s): ?>
                                <tr class="<?= $s['is_failed'] ? 'table-danger-soft' : '' ?>">
                                    <td class="fio-col"><div class="fw-bold"><?= htmlspecialchars($s['fio']) ?></div></td>
                                    <?php foreach ($active_subjects as $sub): $id = $sub['id']; ?>
                                        <td class="<?= ($s["r_$id"] < 20 && $s["r_$id"] !== null) ? 'fail-text' : '' ?>"><?= $s["r_$id"] ?? '-' ?></td>
                                        <td class="<?= ($s["u_$id"] < 60 && $s["u_$id"] !== null) ? 'fail-text' : '' ?>"><?= $s["u_$id"] ?? '-' ?></td>
                                        <td class="<?= ($s["d_$id"] >= 33) ? 'fail-text' : '' ?>"><?= isset($s["d_$id"]) ? $s["d_$id"] . '%' : '-' ?></td>
                                    <?php endforeach; ?>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php else: ?>
            <div class="alert bg-white border shadow-sm rounded-3 py-4 text-center">
                <h5 class="text-muted mb-0">Ma'lumotlarni ko'rish uchun filtrlarni tanlang.</h5>
            </div>
        <?php endif; ?>
    </div>

    <script>
        document.querySelectorAll('.select-auto').forEach(el => {
            el.addEventListener('input', (e) => {
                const listId = el.getAttribute('list');
                if (listId) {
                    const options = document.getElementById(listId).options;
                    for (let i = 0; i < options.length; i++) {
                        if (options[i].value === el.value) {
                            document.getElementById('filterForm').submit();
                            break;
                        }
                    }
                }
            });
            if (el.tagName === 'SELECT') {
                el.addEventListener('change', () => document.getElementById('filterForm').submit());
            }
        });
    </script>
</body>
</html>
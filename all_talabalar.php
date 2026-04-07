<?php
session_start();
require "database.php"; // Ma'lumotlar bazasiga ulanish

$current_role = $_SESSION['role'] ?? 'user';

// 1. QIDIRUV VA PAGINATION PARAMETRLARI
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$limit = 100;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if ($page < 1) $page = 1;
$offset = ($page - 1) * $limit;

// 2. GLOBAL QIDIRUV UCHUN SQL SHARTI
$where = "role = 'user'";
$params = [];

if (!empty($search)) {
    $where .= " AND (fio LIKE ? OR talaba_id LIKE ? OR guruh LIKE ? OR email LIKE ?)";
    $search_param = "%$search%";
    $params = [$search_param, $search_param, $search_param, $search_param];
}

// 3. JAMI TOPILGANLAR SONI (Pagination hisoblash uchun)
$count_stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE $where");
$count_stmt->execute($params);
$total_results = $count_stmt->fetchColumn();
$total_pages = ceil($total_results / $limit);

// 4. MA'LUMOTLARNI OLISH
$stmt = $pdo->prepare("SELECT * FROM users WHERE $where ORDER BY fio ASC LIMIT $limit OFFSET $offset");
$stmt->execute($params);
$talabalar = $stmt->fetchAll();

// 5. ADMINLAR (Faqat qidiruv bo'lmaganda va 1-sahifada ko'rinsin)
$adminlar = [];
if (empty($search) && $page == 1) {
    $admin_stmt = $pdo->query("SELECT * FROM users WHERE role IN ('admin', 'super_admin') ORDER BY fio ASC");
    $adminlar = $admin_stmt->fetchAll();
}

// Initsiallar funksiyasi
function getInitials($fio)
{
    $parts = explode(" ", trim($fio));
    $i = mb_strtoupper(mb_substr($parts[0], 0, 1, 'UTF-8'));
    if (isset($parts[1])) $i .= mb_strtoupper(mb_substr($parts[1], 0, 1, 'UTF-8'));
    return $i;
}
?>

<?php include "Includes/header.php"; ?>
<?php require "atmosphere.php"; ?>
<style>
    body {
        font-family: 'Inter', sans-serif;
    }

    .student-card {
        background: #fff;
        border-radius: 20px;
        transition: 0.3s;
        border: none;
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
        text-decoration: none !important;
        color: #333;
        display: block;
        height: 100%;
    }

    .student-card:hover {
        transform: translateY(-7px);
        box-shadow: 0 15px 30px rgba(0, 0, 0, 0.1);
    }

    .avatar-box {
        width: 80px;
        height: 80px;
        border-radius: 50%;
        margin: 0 auto 15px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: linear-gradient(45deg, #4e73df, #224abe);
        color: #fff;
        font-size: 22px;
        font-weight: bold;
        border: 4px solid #fff;
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        overflow: hidden;
    }

    .avatar-box img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .search-container {
        position: relative;
        max-width: 600px;
        margin: 0 auto 40px;
    }

    .search-input {
        border-radius: 50px;
        border: 2px solid #fff;
        padding: 15px 30px;
        box-shadow: 0 10px 25px rgba(0, 0, 0, 0.05);
        font-size: 16px;
        width: 100%;
        outline: none;
        transition: 0.3s;
    }

    .search-input:focus {
        border-color: #4e73df;
    }

    .btn-custom {
        background: #fff;
        color: #4e73df;
        border: 2px solid #4e73df;
        padding: 10px 30px;
        border-radius: 12px;
        font-weight: 700;
        transition: 0.3s;
        text-decoration: none;
        display: inline-block;
    }

    .btn-custom:hover {
        background: #4e73df;
        color: #fff;
    }

    .page-info {
        background: #4e73df;
        color: white;
        padding: 10px 25px;
        border-radius: 12px;
        font-weight: 800;
    }
</style>

<body>
    <?php include "Includes/yuklash.php"; ?>
    <?php include "Includes/navbar.php"; ?>


    <div class="container py-5">

        <div class="search-container">
            <form method="GET" action="">
                <input type="text" name="search" class="search-input"
                    placeholder="FIO, ID, Guruh yoki Email bo'yicha qidirish..."
                    value="<?= htmlspecialchars($search) ?>" autocomplete="off">
                <button type="submit" style="display:none;">Qidirish</button>
            </form>
        </div>

        <?php if (!empty($adminlar)): ?>
            <div class="p-4 mb-5" style="background: #1a202c; border-radius: 25px;">
                <h6 class="text-info fw-bold mb-4">🛡️ Mas'ullar (Admin)</h6>
                <div class="row g-3">
                    <?php foreach ($adminlar as $a): ?>
                        <div class="col-md-3 col-6">
                            <a href="myprofile.php?auto_id=<?= $a['id'] ?>" class="student-card p-3 bg-dark text-white border-0">
                                <div class="d-flex align-items-center gap-2">
                                    <div class="avatar-box m-0" style="width: 40px; height: 40px; font-size: 12px;">
                                        <?php if (!empty($a['image'])): ?>
                                            <img src="uploads/<?= $a['image'] ?>" onerror="this.parentElement.innerHTML='<?= getInitials($a['fio']) ?>'">
                                        <?php else: ?>
                                            <?= getInitials($a['fio']) ?>
                                        <?php endif; ?>
                                    </div>
                                    <div class="small fw-bold"><?= htmlspecialchars($a['fio']) ?></div>
                                </div>
                            </a>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>

        <div class="row g-4">
            <?php if (empty($talabalar)): ?>
                <div class="col-12 text-center py-5">
                    <h5 class="text-muted">Ma'lumot topilmadi...</h5>
                </div>
            <?php endif; ?>

            <?php foreach ($talabalar as $u): ?>
                <div class="col-xl-2 col-lg-3 col-md-4 col-6">
                    <a href="myprofile.php?auto_id=<?= $u['id'] ?>" class="student-card p-4 text-center">
                        <div class="avatar-box">
                            <?php if (!empty($u['image'])): ?>
                                <img src="uploads/<?= $u['image'] ?>" onerror="this.style.display='none'; this.parentElement.innerHTML='<?= getInitials($u['fio']) ?>'">
                            <?php else: ?>
                                <?= getInitials($u['fio']) ?>
                            <?php endif; ?>
                        </div>

                        <h6 class="fw-bold mb-1" style="font-size: 14px; line-height: 1.3;"><?= htmlspecialchars($u['fio']) ?></h6>
                        <p class="text-muted mb-1" style="font-size: 10px;">ID: <?= $u['talaba_id'] ?></p>
                        <p class="text-primary mb-2" style="font-size: 10px; font-weight:600;"><?= htmlspecialchars($u['guruh'] ?: 'Guruhsiz') ?></p>

                        <?php
                        $g_year = (int)substr($u['guruh'], -2);
                        $kurs = ($g_year > 0) ? (26 - $g_year) : 0;
                        ?>
                        <span class="badge bg-light text-primary border px-2 py-2" style="border-radius: 8px; font-size: 10px;">
                            <?= ($kurs > 4 ? ($kurs - 4) . '-kurs Magistr' : ($kurs > 0 ? $kurs . '-kurs' : 'Noma\'lum')) ?>
                        </span>
                    </a>
                </div>
            <?php endforeach; ?>
        </div>

        <?php if ($total_pages > 1): ?>
            <div class="d-flex justify-content-center align-items-center mt-5 gap-4">
                <?php if ($page > 1): ?>
                    <a href="?page=<?= $page - 1 ?>&search=<?= urlencode($search) ?>" class="btn-custom shadow-sm">⬅ Oldingi</a>
                <?php endif; ?>

                <div class="page-info shadow-sm">
                    <?= $page ?> / <?= $total_pages ?>
                </div>

                <?php if ($page < $total_pages): ?>
                    <a href="?page=<?= $page + 1 ?>&search=<?= urlencode($search) ?>" class="btn-custom shadow-sm">Keyingi ➡</a>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>

</body>

</html>
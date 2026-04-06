<?php
session_start();
require "database.php";
$title = "Fanlar"; // Sahifa sarlavhasi

// ================= ADD FAN =================
if (isset($_POST['add_fan'])) {
    $nomi = trim($_POST['nomi']);
    $yonalish = trim($_POST['yonalish']);
    $semestr = (int)$_POST['semestr'];

    if (!empty($nomi) && !empty($yonalish) && $semestr > 0) {
        $stmt = $pdo->prepare("INSERT INTO fanlar (nomi, yonalish, semestr) VALUES (?, ?, ?)");
        $stmt->execute([$nomi, $yonalish, $semestr]);
        header("Location: fanlar.php");
        exit;
    } else {
        $error = "Barcha maydonlarni to‘ldiring!";
    }
}

// ================= DELETE FAN =================
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $stmt = $pdo->prepare("DELETE FROM fanlar WHERE id=?");
    $stmt->execute([$id]);
    header("Location: fanlar.php");
    exit;
}

// ================= SEARCH & FILTER =================
$q = $_GET['q'] ?? '';
$f_yonalish = $_GET['f_yonalish'] ?? '';
$f_semestr = $_GET['f_semestr'] ?? '';

$sql = "SELECT * FROM fanlar WHERE 1=1";
$params = [];

if (!empty($q)) {
    $sql .= " AND (nomi LIKE ? OR yonalish LIKE ?)";
    $params[] = "%$q%";
    $params[] = "%$q%";
}
if (!empty($f_yonalish)) {
    $sql .= " AND yonalish = ?";
    $params[] = $f_yonalish;
}
if (!empty($f_semestr)) {
    $sql .= " AND semestr = ?";
    $params[] = $f_semestr;
}

$sql .= " ORDER BY id DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$fanlar = $stmt->fetchAll();

// Filtrlar uchun ro'yxatlarni olish
$yonalishlar = $pdo->query("SELECT DISTINCT yonalish FROM fanlar")->fetchAll(PDO::FETCH_COLUMN);
?>

<?php include "Includes/header.php"; ?>
<?php require "atmosphere.php"; ?>

<style>
    :root {
        --primary-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        --glass-bg: rgba(255, 255, 255, 0.95);
    }

    body {
        background-color: #f8f9fa;
        font-family: 'Inter', sans-serif;
    }

    .page-title {
        font-weight: 800;
        color: #ffffff;
        letter-spacing: -1px;
    }

    /* Card Styles */
    .fan-card {
        border: none;
        border-radius: 16px;
        transition: all 0.3s ease;
        background: var(--glass-bg);
    }

    .fan-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 12px 20px rgba(0, 0, 0, 0.08) !important;
    }

    .semester-badge {
        background: #edf2f7;
        color: #4a5568;
        font-weight: 600;
        padding: 4px 12px;
        border-radius: 8px;
        font-size: 0.85rem;
    }

    /* Form Styles */
    .custom-input {
        border-radius: 10px;
        border: 1px solid #e2e8f0;
        padding: 10px 15px;
    }

    .custom-input:focus {
        box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        border-color: #667eea;
    }

    .btn-add {
        background: var(--primary-gradient);
        border: none;
        border-radius: 10px;
        font-weight: 600;
        transition: opacity 0.3s;
    }

    .btn-add:hover {
        opacity: 0.9;
        color: white;
    }

    /* Filter Bar */
    .filter-bar {
        background: white;
        padding: 20px;
        border-radius: 16px;
        margin-bottom: 30px;
        border: 1px solid #edf2f7;
    }
</style>

<body>
    <?php require "Includes/yuklash.php"; ?>
    <?php include "Includes/navbar.php"; ?>

    <div class="container py-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="page-title">📚 Fanlar katalogi</h2>
        </div>

        <div class="card fan-card shadow-sm mb-5">
            <div class="card-body p-4">
                <h6 class="text-muted mb-3">Yangi fan qo'shish</h6>
                <?php if (!empty($error)): ?>
                    <div class="alert alert-danger py-2"><?= $error ?></div>
                <?php endif; ?>

                <form method="POST" class="row g-3">
                    <div class="col-md-4">
                        <input type="text" name="nomi" class="form-control custom-input" placeholder="Fan nomi (masalan: Matematika)" required>
                    </div>
                    <div class="col-md-4">
                        <input type="text" name="yonalish" class="form-control custom-input" placeholder="Yo‘nalish (masalan: Kompyuter injiniringi)" required>
                    </div>
                    <div class="col-md-2">
                        <select name="semestr" class="form-select custom-input" required>
                            <option value="">Semestr</option>
                            <?php for ($i = 1; $i <= 8; $i++): ?>
                                <option value="<?= $i ?>"><?= $i ?></option>
                            <?php endfor; ?>
                        </select>
                    </div>
                    <div class="col-md-2 d-grid">
                        <button class="btn btn-primary btn-add" name="add_fan">
                            <i class="bi bi-plus-lg"></i> Qo‘shish
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <div class="filter-bar shadow-sm">
            <form method="GET" class="row g-3">
                <div class="col-md-5">
                    <div class="input-group">
                        <span class="input-group-text bg-white border-end-0"><i class="bi bi-search text-muted"></i></span>
                        <input type="text" name="q" class="form-control custom-input border-start-0"
                            placeholder="Fan nomi orqali qidirish..." value="<?= htmlspecialchars($q) ?>">
                    </div>
                </div>
                <div class="col-md-3">
                    <select name="f_yonalish" class="form-select custom-input" onchange="this.form.submit()">
                        <option value="">Barcha yo'nalishlar</option>
                        <?php foreach ($yonalishlar as $y): ?>
                            <option value="<?= $y ?>" <?= $f_yonalish == $y ? 'selected' : '' ?>><?= $y ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <select name="f_semestr" class="form-select custom-input" onchange="this.form.submit()">
                        <option value="">Barcha semestrlar</option>
                        <?php for ($i = 1; $i <= 8; $i++): ?>
                            <option value="<?= $i ?>" <?= $f_semestr == $i ? 'selected' : '' ?>><?= $i ?>-semestr</option>
                        <?php endfor; ?>
                    </select>
                </div>
                <div class="col-md-2 d-grid">
                    <a href="fanlar.php" class="btn btn-outline-secondary" style="border-radius:10px;">Tozalash</a>
                </div>
            </form>
        </div>

        <div class="row g-4">
            <?php if (empty($fanlar)): ?>
                <div class="col-12 text-center py-5">
                    <img src="https://cdn-icons-png.flaticon.com/512/6134/6134065.png" width="100" class="mb-3 opacity-50">
                    <h5 class="text-muted">Hech qanday fan topilmadi</h5>
                </div>
            <?php endif; ?>

            <?php foreach ($fanlar as $fan): ?>
                <div class="col-md-6 col-lg-4">
                    <div class="card fan-card shadow-sm h-100">
                        <div class="card-body p-4 d-flex flex-column">
                            <div class="d-flex justify-content-between align-items-start mb-3">
                                <span class="semester-badge"><?= $fan['semestr'] ?>-semestr</span>

                                <div class="dropdown">
                                    <button class="btn btn-link text-muted p-0" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                        <i class="bi bi-three-dots-vertical"></i>
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end shadow border-0">
                                        <li>
                                            <a class="dropdown-item text-danger" href="?delete=<?= $fan['id'] ?>"
                                                onclick="return confirm('Ushbu fanni o‘chirishni tasdiqlaysizmi?')">
                                                <i class="bi bi-trash me-2"></i> O'chirish
                                            </a>
                                        </li>
                                    </ul>
                                </div>
                            </div>

                            <h5 class="card-title fw-bold mb-2"><?= htmlspecialchars($fan['nomi']) ?></h5>
                            <p class="text-muted small mb-4">
                                <i class="bi bi-geo-alt me-1"></i> <?= htmlspecialchars($fan['yonalish']) ?>
                            </p>

                            <div class="mt-auto">
                                <a href="talabalar.php?fan_id=<?= $fan['id'] ?>" class="btn btn-light w-100 fw-bold" style="border-radius:10px; background: #f1f5f9;">
                                    Batafsil ko'rish <i class="bi bi-arrow-right ms-2"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;800&display=swap" rel="stylesheet">
</body>

</html>
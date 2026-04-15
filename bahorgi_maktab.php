<?php
session_start();
require "database.php";

// --- MA'LUMOTLAR BAZASINI TAYYORLASH ---
$pdo->exec("CREATE TABLE IF NOT EXISTS bepul_ruxsatlar (
    id INT AUTO_INCREMENT PRIMARY KEY,
    bepul_id INT,
    fan_id INT,
    maktab_id INT,
    status TINYINT DEFAULT 1
)");

// --- YORDAMCHI FUNKSIYALAR ---

// 1. Word (.docx) faylini o'qish funksiyasi
function read_docx($filename)
{
    $striped_content = '';
    $content = '';
    if (!$filename || !file_exists($filename)) return false;

    $zip = new ZipArchive;
    if ($zip->open($filename) === true) {
        $xml_content = $zip->getFromName('word/document.xml');
        $zip->close();

        if ($xml_content) {
            // XML teglarini matnga aylantirish va tozalash
            $content = str_replace(['</w:r></w:p>', '<w:p>', '</w:r>'], ["\n", "\n", ""], $xml_content);
            $striped_content = strip_tags($content);
            return html_entity_decode($striped_content, ENT_QUOTES, 'UTF-8');
        }
    }
    return false;
}

// 2. Savollarni format bo'yicha ajratib olish (Parsing)
function parse_savollar($text)
{
    $savollar_massivi = [];
    // Savol matni ?{ ... javoblar ... } formatini qidiradi
    preg_match_all('/(.*?)\?\{\s*([^}]*)\s*\}/s', $text, $matches, PREG_SET_ORDER);

    foreach ($matches as $val) {
        $savol_matni = trim($val[1]);
        $ichki_qism = trim($val[2]);

        $variantlar = explode("\n", $ichki_qism);
        $togri = "";
        $notogri = [];

        foreach ($variantlar as $v) {
            $v = trim($v);
            if (empty($v)) continue;

            if (strpos($v, '~') === 0) {
                $togri = substr($v, 1);
            } elseif (strpos($v, '#') === 0) {
                $notogri[] = substr($v, 1);
            }
        }

        if (!empty($savol_matni)) {
            $savollar_massivi[] = [
                'savol' => $savol_matni,
                'togri' => $togri,
                'notogri' => $notogri
            ];
        }
    }
    return $savollar_massivi;
}

// --- MANTIQIY AMALLAR (POST/GET) ---

// 1. Mavsum qo'shish
if (isset($_POST['add_maktab'])) {
    $nomi = filter_input(INPUT_POST, 'maktab_nomi', FILTER_SANITIZE_STRING);
    if ($nomi) {
        $stmt = $pdo->prepare("INSERT INTO bepul_maktab (nomi) VALUES (?)");
        $stmt->execute([$nomi]);
    }
}

// 2. Test yuklash (Ham .txt, ham .docx uchun yagona mantiq)
if (isset($_POST['upload_test'])) {
    $f_id = (int)$_POST['fan_id'];
    $m_id = (int)$_POST['maktab_id'];
    $ball = (float)$_POST['test_ball'];
    $urunish = (float)$_POST['Urunishlar'];
    $vaqt = (float)$_POST['vaqt'];

    if (isset($_FILES['test_file']) && $_FILES['test_file']['error'] == 0) {
        $file_ext = strtolower(pathinfo($_FILES['test_file']['name'], PATHINFO_EXTENSION));
        $tmp_path = $_FILES['test_file']['tmp_name'];

        // Fayl turiga qarab o'qish
        if ($file_ext == 'docx') {
            $content = read_docx($tmp_path);
        } else {
            $content = file_get_contents($tmp_path);
        }

        // Binary va tushunarsiz belgilardan tozalash
        if ($content) {
            $content = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/', '', $content);
            $testlar = parse_savollar($content);

            if (!empty($testlar)) {
                try {
                    $pdo->beginTransaction();
                    $sql = "INSERT INTO testlar (fan_id, maktab_id, savol, variant_t, variant_1, variant_2, variant_3, ball, test_vaqti, limit_urunish) 
                            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
                    $stmt = $pdo->prepare($sql);

                    foreach ($testlar as $t) {
                        $stmt->execute([
                            $f_id,
                            $m_id,
                            $t['savol'],
                            $t['togri'],
                            $t['notogri'][0] ?? '',
                            $t['notogri'][1] ?? '',
                            $t['notogri'][2] ?? '',
                            $ball,
                            $vaqt,
                            $urunish
                        ]);
                    }
                    $pdo->commit();

                    // Sessiyaga muvaffaqiyat xabarini saqlaymiz
                    $_SESSION['msg'] = "Muvaffaqiyatli: " . count($testlar) . " ta savol bazaga yuklandi!";
                    $_SESSION['msg_type'] = "success";

                    // Sahifani qayta yuklaymiz (Redirect)
                    header("Location: bahorgi_maktab.php?maktab_id=$m_id&fan_id=$f_id");
                    exit;
                } catch (Exception $e) {
                    $pdo->rollBack();
                    $_SESSION['msg'] = "Baza xatosi: " . $e->getMessage();
                    $_SESSION['msg_type'] = "danger";

                    header("Location: bahorgi_maktab.php?maktab_id=$m_id&fan_id=$f_id");
                    exit;
                }
            } else {
                echo "<div class='alert alert-danger mt-3'>Xato: Fayl ichidan savollar topilmadi. Formatni (?{...}) tekshiring!</div>";
            }
        } else {
            echo "<div class='alert alert-danger mt-3'>Faylni o'qishda xatolik yuz berdi!</div>";
        }
    }
}

// 3. Ruxsatni o'zgartirish (Toggle)
if (isset($_GET['toggle_ruxsat'])) {
    $b_id = $_GET['b_id'];
    $f_id = $_GET['f_id'];
    $m_id = $_GET['m_id'];
    $st = (int)$_GET['st'];

    $check = $pdo->prepare("SELECT id FROM bepul_ruxsatlar WHERE bepul_id = ? AND fan_id = ? AND maktab_id = ?");
    $check->execute([$b_id, $f_id, $m_id]);
    $exists = $check->fetch();

    if ($exists) {
        $stmt = $pdo->prepare("UPDATE bepul_ruxsatlar SET status = ? WHERE id = ?");
        $stmt->execute([$st, $exists['id']]);
    } else {
        $stmt = $pdo->prepare("INSERT INTO bepul_ruxsatlar (bepul_id, fan_id, maktab_id, status) VALUES (?, ?, ?, ?)");
        $stmt->execute([$b_id, $f_id, $m_id, $st]);
    }
    header("Location: bahorgi_maktab.php?maktab_id=$m_id&fan_id=$f_id");
    exit;
}
// PHP kodning tepa qismiga qo'shing
if (isset($_GET['delete_test'])) {
    $del_id = (int)$_GET['delete_test'];
    $f_id = (int)$_GET['f_id'];
    $m_id = (int)$_GET['m_id'];

    // Bazadan o'chirish so'rovi
    $stmt = $pdo->prepare("DELETE FROM testlar WHERE id = ?");
    $result = $stmt->execute([$del_id]);

    if ($result) {
        // O'chgandan keyin parametrlar bilan qayta yuklash (url toza bo'lishi uchun)
        header("Location: ?f_id=$f_id&m_id=$m_id&msg=deleted");
        exit;
    } else {
        echo "Xatolik yuz berdi, o'chirishning iloji bo'lmadi.";
    }
}
require 'Includes/header.php';
?>

<style>
    :root {
        --primary: #4361ee;
        --secondary: #3f37c9;
    }

    body {
        font-family: 'Inter', sans-serif;
        background: #f4f7fe;
        overflow: auto !important;
    }

    .main-card {
        background: #fff;
        border-radius: 20px;
        box-shadow: 0 10px 25px rgba(0, 0, 0, 0.05);
        padding: 30px;
        margin-top: 30px;
        border: none;
    }

    .nav-breadcrumb {
        background: #edf2f7;
        padding: 10px 20px;
        border-radius: 10px;
        margin-bottom: 20px;
    }

    .btn-custom {
        border-radius: 10px;
        font-weight: 600;
        transition: 0.3s;
    }

    .list-group-item {
        border: 1px solid #f0f0f0;
        margin-bottom: 10px;
        border-radius: 12px !important;
        transition: 0.2s;
    }

    .list-group-item:hover {
        background: #f8faff;
        transform: scale(1.01);
        border-color: var(--primary);
    }
</style>
<?php require 'Includes/navbar.php';
require 'atmosphere.php' ?>
<div class="container pb-5">
    <div class="main-card">

        <h3 class="fw-bold text-dark mb-4"><i class="fas fa-university text-primary me-2"></i> Bepul Maktab Boshqaruvi</h3>

        <div class="nav-breadcrumb">
            <a href="bahorgi_maktab.php" class="text-decoration-none text-muted small">Bosh sahifa</a>
            <?php if (isset($_GET['maktab_id'])): ?>
                <span class="mx-2 text-muted">/</span>
                <a href="?maktab_id=<?= $_GET['maktab_id'] ?>" class="text-decoration-none text-muted small">Fanlar</a>
            <?php endif; ?>
            <?php if (isset($_GET['fan_id'])): ?>
                <span class="mx-2 text-muted">/</span>
                <span class="small text-primary fw-bold">Talabalar va Test</span>
            <?php endif; ?>
        </div>

        <?php if (!isset($_GET['maktab_id'])): ?>
            <div class="row">
                <div class="col-md-5">
                    <div class="card card-body border-0 bg-light mb-4">
                        <h6 class="fw-bold">Yangi mavsum ochish</h6>
                        <form method="POST">
                            <input type="text" name="maktab_nomi" class="form-control mb-2" placeholder="Masalan: Bahorgi 2026" required>
                            <button type="submit" name="add_maktab" class="btn btn-primary w-100 btn-custom">Saqlash</button>
                        </form>
                    </div>
                </div>
                <div class="col-md-7">
                    <div class="list-group">
                        <?php
                        $maktablar = $pdo->query("SELECT * FROM bepul_maktab ORDER BY id DESC")->fetchAll();
                        foreach ($maktablar as $m): ?>
                            <a href="?maktab_id=<?= $m['id'] ?>" class="list-group-item d-flex justify-content-between align-items-center p-3 shadow-sm">
                                <div><i class="fas fa-folder-open text-warning me-2"></i> <strong><?= htmlspecialchars($m['nomi']) ?></strong></div>
                                <i class="fas fa-chevron-right text-muted"></i>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

        <?php elseif (isset($_GET['maktab_id']) && !isset($_GET['fan_id'])): ?>
            <div class="row g-3">
                <?php
                $stmt = $pdo->query("SELECT * FROM fanlar ORDER BY nomi ASC");
                foreach ($stmt->fetchAll() as $f): ?>
                    <div class="col-md-4">
                        <a href="?maktab_id=<?= $_GET['maktab_id'] ?>&fan_id=<?= $f['id'] ?>" class="text-decoration-none">
                            <div class="card card-body text-center list-group-item h-100 d-flex justify-content-center">
                                <h6 class="text-dark fw-bold mb-0"><?= htmlspecialchars($f['nomi']) ?></h6>
                            </div>
                        </a>
                    </div>
                <?php endforeach; ?>
            </div>

        <?php elseif (isset($_GET['fan_id'])): ?>
            <div class="row">
                <div class="col-md-12 mb-4">
                    <div class="card card-body border-primary shadow-sm" style="border-left: 5px solid var(--primary);">
                        <h6 class="fw-bold"><i class="fas fa-file-import me-2"></i> Test sozlamalari</h6>
                        <form method="POST" enctype="multipart/form-data" class="row align-items-end">
                            <input type="hidden" name="fan_id" value="<?= $_GET['fan_id'] ?>">
                            <input type="hidden" name="maktab_id" value="<?= $_GET['maktab_id'] ?>">

                            <div class="col-md-3">
                                <label class="small text-muted">Test fayli (Docx)</label>
                                <input type="file" name="test_file" class="form-control form-control-sm" required>
                            </div>

                            <div class="col-md-2">
                                <label class="small text-muted">Test balli</label>
                                <input type="number" name="test_ball" class="form-control form-control-sm" value="2" step="0.1">
                            </div>

                            <div class="col-md-2">
                                <label class="small text-muted">Urunishlar soni</label>
                                <input type="number" name="Urunishlar" class="form-control form-control-sm" value="1" min="1">
                            </div>

                            <div class="col-md-2">
                                <label class="small text-muted">Vaqt (daqiqa)</label>
                                <input type="number" name="vaqt" class="form-control form-control-sm" value="30" min="1">
                            </div>

                            <div class="col-md-3">
                                <button type="submit" name="upload_test" class="btn btn-primary btn-sm w-100 btn-custom">Saqlash</button>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="col-md-12">
                    <div class="table-responsive">
                        <div class="row mt-4">
                            <div class="col-md-12">
                                <div class="card border-0 shadow-sm" style="border-radius: 15px;">
                                    <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                                        <h5 class="mb-0 fw-bold text-dark"><i class="fas fa-file-alt text-info me-2"></i> Yuklangan testlar ro'yxati</h5>
                                        <?php
                                        $f_id = (int)$_GET['fan_id'];
                                        $m_id = (int)$_GET['maktab_id'];

                                        // Statistika olish
                                        $stat = $pdo->prepare("SELECT COUNT(*) as jami, SUM(ball) as jami_ball FROM testlar WHERE fan_id = ? AND maktab_id = ?");
                                        $stat->execute([$f_id, $m_id]);
                                        $info = $stat->fetch();
                                        ?>
                                        <div>
                                            <span class="badge bg-primary-subtle text-primary px-3 py-2 rounded-pill">Savollar: <?= $info['jami'] ?? 0 ?> ta</span>
                                            <span class="badge bg-success-subtle text-success px-3 py-2 rounded-pill ms-2">Jami ball: <?= $info['jami_ball'] ?? 0 ?></span>
                                        </div>
                                    </div>
                                    <div class="card-body">
                                        <div class="accordion shadow-sm" id="testAccordion">
                                            <?php
                                            $test_stmt = $pdo->prepare("SELECT * FROM testlar WHERE fan_id = ? AND maktab_id = ? ORDER BY id ASC");
                                            $test_stmt->execute([$f_id, $m_id]);
                                            $questions = $test_stmt->fetchAll();

                                            if ($questions):
                                                foreach ($questions as $index => $q):
                                            ?>
                                                    <div class="accordion-item border-0 mb-2 shadow-sm" style="border-radius: 10px !important; overflow: hidden;">
                                                        <h2 class="accordion-header" id="heading<?= $q['id'] ?>">
                                                            <button class="accordion-button collapsed bg-white text-dark py-3" type="button" data-bs-toggle="collapse" data-bs-target="#collapse<?= $q['id'] ?>">
                                                                <span class="badge bg-secondary me-3"><?= $index + 1 ?></span>
                                                                <?= htmlspecialchars(mb_strimwidth($q['savol'], 0, 100, "...")) ?>
                                                                <span class="ms-auto badge border text-muted fw-normal me-3"><?= $q['ball'] ?> ball</span>
                                                            </button>
                                                        </h2>
                                                        <div id="collapse<?= $q['id'] ?>" class="accordion-collapse collapse" data-bs-parent="#testAccordion">
                                                            <div class="accordion-body bg-light-subtle">
                                                                <p class="fw-bold mb-3"><?= htmlspecialchars($q['savol']) ?></p>
                                                                <div class="list-group">
                                                                    <div class="list-group-item list-group-item-success border-0 mb-1 rounded">
                                                                        <i class="fas fa-check-circle me-2"></i> <strong>To'g'ri javob:</strong> <?= htmlspecialchars($q['variant_t']) ?>
                                                                    </div>
                                                                    <div class="list-group-item border-0 mb-1 rounded text-muted small">
                                                                        <i class="fas fa-times-circle me-2"></i> <?= htmlspecialchars($q['variant_1']) ?>
                                                                    </div>
                                                                    <div class="list-group-item border-0 mb-1 rounded text-muted small">
                                                                        <i class="fas fa-times-circle me-2"></i> <?= htmlspecialchars($q['variant_2']) ?>
                                                                    </div>
                                                                    <div class="list-group-item border-0 mb-1 rounded text-muted small">
                                                                        <i class="fas fa-times-circle me-2"></i> <?= htmlspecialchars($q['variant_3']) ?>
                                                                    </div>
                                                                </div>
                                                                <div class="text-end mt-3">
                                                                    <a href="?delete_test=<?= $q['id'] ?>&f_id=<?= $f_id ?>&m_id=<?= $m_id ?>"
                                                                        class="btn btn-sm btn-outline-danger border-0"
                                                                        onclick="return confirm('Ushbu savolni o\'chirmoqchimisiz?')">
                                                                        <i class="fas fa-trash"></i> O'chirish
                                                                    </a>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                <?php
                                                endforeach;
                                            else:
                                                ?>
                                                <div class="text-center py-5">
                                                    <i class="fas fa-vials fa-3x text-muted mb-3"></i>
                                                    <p class="text-muted">Hozircha testlar yuklanmagan.</p>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <?php
                        // Savolni o'chirish logikasi
                        if (isset($_GET['delete_test'])) {
                            $del_id = (int)$_GET['delete_test'];
                            $f_id = (int)$_GET['f_id'];
                            $m_id = (int)$_GET['m_id'];

                            $del_stmt = $pdo->prepare("DELETE FROM testlar WHERE id = ?");
                            $del_stmt->execute([$del_id]);

                            echo "<script>window.location.href='bahorgi_maktab.php?maktab_id=$m_id&fan_id=$f_id';</script>";
                        }
                        ?>
                        <table class="table table-hover bg-white border">
                            <thead class="table-dark">
                                <tr>
                                    <th>F.I.SH (Talaba)</th>
                                    <th>Talaba ID</th>
                                    <th>Guruh</th>
                                    <th>Tanlangan fanlar</th>
                                    <th class="text-center">Ushbu fanga ruxsat</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $f_id = (int)$_GET['fan_id'];
                                $m_id = (int)$_GET['maktab_id'];

                                // Xatoni tuzatish uchun GROUP BY qismiga t.user_id va r.status ham qo'shildi
                                $stmt = $pdo->prepare("
                                    SELECT 
                                        b.id as b_id, 
                                        t.user_id as fio, 
                                        t.guruh,
                                        t.talaba_id,
                                        r.status
                                    FROM bepul b
                                    JOIN talabalar t ON b.talaba_id = t.talaba_id
                                    LEFT JOIN bepul_ruxsatlar r ON b.id = r.bepul_id 
                                        AND r.fan_id = ? 
                                        AND r.maktab_id = ?
                                    WHERE (b.fan1 = ? OR b.fan2 = ? OR b.fan3 = ? OR b.fan4 = ?)
                                    GROUP BY b.id, t.user_id, t.guruh, r.status -- GROUP BY ga ham qo'shish shart
                                ");

                                $stmt->execute([$f_id, $m_id, $f_id, $f_id, $f_id, $f_id]);
                                $rows = $stmt->fetchAll();

                                foreach ($rows as $row):
                                    $is_allowed = ($row['status'] == 1) ? 1 : 0;
                                ?>
                                    <tr class="align-middle">
                                        <td class="fw-bold">
                                            <i class="fas fa-user-graduate text-muted me-2"></i>
                                            <?= htmlspecialchars($row['fio']) ?>
                                        </td>
                                        <td class="text-center"><?= htmlspecialchars($row['talaba_id']) ?></td>
                                        <td class="text-center"><?= htmlspecialchars($row['guruh']) ?></td>
                                        <td class="text-center">
                                            <?php if ($is_allowed): ?>
                                                <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-3">
                                                    <i class="fas fa-check-circle me-1"></i> Ochiq
                                                </span>
                                            <?php else: ?>
                                                <span class="badge bg-danger-subtle text-danger border border-danger-subtle rounded-pill px-3">
                                                    <i class="fas fa-times-circle me-1"></i> Yopiq
                                                </span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-center">
                                            <a href="?toggle_ruxsat=1&b_id=<?= $row['b_id'] ?>&f_id=<?= $f_id ?>&m_id=<?= $m_id ?>&st=<?= $is_allowed ? 0 : 1 ?>"
                                                class="btn btn-sm <?= $is_allowed ? 'btn-outline-danger' : 'btn-outline-success' ?> rounded-pill px-4">
                                                <?= $is_allowed ? 'Bloklash' : 'Ruxsat berish' ?>
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<?php require 'Includes/footer.php'; ?>
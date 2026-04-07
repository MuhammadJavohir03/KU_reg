<?php
session_start();
require "database.php";

// 🔐 Super Admin tekshiruvi
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'super_admin') {
    header("Location: login.php");
    exit;
}

$message = "";

// admin_panel.php ning yuqori qismida $message o'zgaruvchisidan keyin qo'shing:
if (isset($_GET['msg'])) {
    if ($_GET['msg'] == 'admin_deleted') $message = "✅ Admin va unga tegishli barcha xabarlar o'chirildi!";
    if ($_GET['msg'] == 'error_self') $message = "❌ O'zingizni o'chira olmaysiz!";
    if ($_GET['msg'] == 'error_delete') $message = "❌ Adminni o'chirishda bazada xatolik yuz berdi!";
    if ($_GET['msg'] == 'deleted') $message = "✅ Bo'lim muvaffaqiyatli o'chirildi!";
}

// 1️⃣ YANGI ADMIN QO'SHISH LOGIKASI
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_admin'])) {
    $email = trim($_POST['email']);
    $password_plain = trim($_POST['password']);
    $talaba_id = trim($_POST['talaba_id']);

    if (!preg_match('/@kokanduni\.uz$/', $email)) {
        $message = "❌ Email @kokanduni.uz bo'lishi shart!";
    } elseif (!preg_match('/^\d{12}$/', $talaba_id)) {
        $message = "❌ ID 12 ta raqam bo'lishi shart!";
    } else {
        $password_hashed = password_hash($password_plain, PASSWORD_DEFAULT);
        try {
            $stmt = $pdo->prepare("INSERT INTO users (email, password, talaba_id, role) VALUES (?, ?, ?, 'admin')");
            $stmt->execute([$email, $password_hashed, $talaba_id]);
            $message = "✅ Admin qo'shildi!";
        } catch (PDOException $e) {
            $message = "❌ Xatolik: Email yoki ID band!";
        }
    }
}

// 2️⃣ ADMINNI BO'LIMGA BIRIKTIRISH LOGIKASI
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['assign_section'])) {
    $admin_id = (int)$_POST['admin_id'];
    $section_id = (int)$_POST['section_id'];

    $stmt = $pdo->prepare("INSERT IGNORE INTO admin_sections (admin_id, section_id) VALUES (?, ?)");
    if ($stmt->execute([$admin_id, $section_id])) {
        $message = "✅ Bo'lim muvaffaqiyatli biriktirildi!";
    }
}

// 3️⃣ BO'LIM QO'SHISH VA TAHRIRLASH LOGIKASI
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // QO'SHISH
    if (isset($_POST['add_section'])) {
        $name = trim($_POST['section_name']);
        if (!empty($name)) {
            try {
                $stmt = $pdo->prepare("INSERT INTO sections (name) VALUES (?)");
                $stmt->execute([$name]);
                $message = "✅ Yangi bo'lim qo'shildi!";
            } catch (PDOException $e) {
                $message = ($e->getCode() == 23000) ? "❌ Bunday nomli bo'lim bor!" : "❌ Xatolik yuz berdi!";
            }
        }
    }

    // TAHRIRLASH (Qalamcha bosilganda ishlaydi)
    if (isset($_POST['edit_section'])) {
        $id = (int)$_POST['section_id'];
        $name = trim($_POST['section_name']); // Mana shu joyda inputdan nom keladi
        if (!empty($name)) {
            try {
                $stmt = $pdo->prepare("UPDATE sections SET name = ? WHERE id = ?");
                $stmt->execute([$name, $id]);
                $message = "✅ Bo'lim nomi yangilandi!";
            } catch (PDOException $e) {
                $message = "❌ Tahrirlashda xatolik yuz berdi!";
            }
        }
    }
}

// 4️⃣ BO'LIMNI O'CHIRISH (PHP ORQALI TO'LIQ TOZALASH)
if (isset($_GET['delete_section'])) {
    $id = (int)$_GET['delete_section'];

    try {
        // Tranzaksiyani boshlaymiz (Xavfsizlik uchun)
        $pdo->beginTransaction();

        // A) Avval ushbu bo'limga tegishli barcha xabarlarni o'chiramiz
        $stmt1 = $pdo->prepare("DELETE FROM messages WHERE section_id = ?");
        $stmt1->execute([$id]);

        // B) Agar ushbu bo'limga biriktirilgan adminlar bo'lsa, o'sha bog'lanishni ham uzamiz
        // (admin_sections jadvali bo'lsa)
        $stmt2 = $pdo->prepare("DELETE FROM admin_sections WHERE section_id = ?");
        $stmt2->execute([$id]);

        // C) Va nihoyat, bo'limning o'zini o'chiramiz
        $stmt3 = $pdo->prepare("DELETE FROM sections WHERE id = ?");
        $stmt3->execute([$id]);

        // Hamma amal muvaffaqiyatli bo'lsa, bazaga saqlaymiz
        $pdo->commit();

        header("Location: " . $_SERVER['PHP_SELF'] . "?msg=deleted");
        exit;
    } catch (Exception $e) {
        // Agar birorta joyda xato bersa, barcha amallarni bekor qilamiz
        $pdo->rollBack();
        $message = "❌ O'chirishda xatolik: " . $e->getMessage();
    }
}

// Ma'lumotlarni olish
$admins = $pdo->query("SELECT id, email, fio, image, role FROM users WHERE role IN ('admin', 'super_admin') ORDER BY id DESC")->fetchAll();
$sections = $pdo->query("SELECT id, name FROM sections ORDER BY id DESC")->fetchAll();
?>

<?php require "Includes/header.php"; ?>
<?php require "atmosphere.php"; ?>

<style>
    body {
        font-family: 'Inter', sans-serif;
    }

    .glass-card {
        background: white;
        border-radius: 20px;
        border: none;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.04);
        padding: 25px;
        height: 100%;
    }

    .form-label {
        font-size: 11px;
        font-weight: 700;
        color: #64748b;
        text-transform: uppercase;
        margin-bottom: 5px;
        display: block;
    }

    .form-control-modern {
        border-radius: 12px;
        padding: 10px 15px;
        border: 1px solid #e2e8f0;
        margin-bottom: 15px;
        font-size: 14px;
    }

    .btn-indigo {
        background: #6366f1;
        color: white;
        border-radius: 12px;
        font-weight: 600;
        transition: 0.3s;
        border: none;
    }

    .btn-indigo:hover {
        background: #4f46e5;
        transform: translateY(-2px);
        color: white;
    }

    .admin-profile-img {
        width: 35px;
        height: 35px;
        border-radius: 10px;
        object-fit: cover;
    }

    .admin-initials {
        width: 35px;
        height: 35px;
        border-radius: 10px;
        background: #6366f1;
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: bold;
        font-size: 12px;
    }

    .section-item {
        border-bottom: 1px solid #f1f5f9;
        padding: 8px 0;
        transition: 0.2s;
    }

    .section-item:hover {
        background: #f8fafc;
    }

    .scroll-area {
        max-height: 400px;
        overflow-y: auto;
        scrollbar-width: thin;
    }
</style>

<?php require "Includes/yuklash.php"; ?>
<?php require "Includes/navbar.php"; ?>

<div class="container-fluid py-4 px-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold text-dark m-0">Boshqaruv Paneli</h2>
        <?php if ($message): ?>
            <div class="alert alert-light border shadow-sm py-2 px-4 m-0" style="border-radius: 12px; font-size: 14px;"><?= $message ?></div>
        <?php endif; ?>
    </div>

    <div class="row g-4">
        <div class="col-xl-3 col-lg-6">
            <div class="glass-card">
                <h6 class="fw-bold mb-4">➕ Yangi Admin</h6>
                <form method="POST">
                    <label class="form-label">Elektron pochta</label>
                    <input type="email" name="email" class="form-control form-control-modern w-100" placeholder="admin@kokanduni.uz" required>
                    <label class="form-label">Talaba ID</label>
                    <input type="text" name="talaba_id" class="form-control form-control-modern w-100" placeholder="12 ta raqam" required>
                    <label class="form-label">Parol</label>
                    <input type="password" name="password" class="form-control form-control-modern w-100" required>
                    <button type="submit" name="add_admin" class="btn btn-indigo w-100 py-2">Saqlash</button>
                </form>
            </div>
        </div>

        <div class="col-xl-3 col-lg-6">
            <div class="glass-card">
                <h6 class="fw-bold mb-3">📂 Bo'limlar</h6>
                <form method="POST" class="mb-3">
                    <div class="input-group">
                        <input type="text" name="section_name" class="form-control form-control-modern mb-0" placeholder="Yangi..." required style="border-radius: 12px 0 0 12px;">
                        <button type="submit" name="add_section" class="btn btn-indigo mb-0 px-3" style="border-radius: 0 12px 12px 0;">➕</button>
                    </div>
                </form>
                <div class="scroll-area px-1">
                    <?php foreach ($sections as $s): ?>
                        <div class="section-item d-flex justify-content-between align-items-center">
                            <span class="small fw-semibold"><?= htmlspecialchars($s['name']) ?></span>
                            <div>
                                <button class="btn btn-sm text-primary p-1" onclick="openEditModal(<?= $s['id'] ?>, '<?= htmlspecialchars($s['name']) ?>')">✏️</button>
                                <a href="?delete_section=<?= $s['id'] ?>" class="btn btn-sm text-danger p-1" onclick="return confirm('O\'chirilsinmi?')">🗑️</a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-lg-6">
            <div class="glass-card">
                <h6 class="fw-bold mb-4">⚙️ Biriktirish</h6>
                <form method="POST">
                    <label class="form-label">Adminni tanlang</label>
                    <select name="admin_id" class="form-select form-control-modern" required>
                        <option value="" disabled selected>Tanlang...</option>
                        <?php foreach ($admins as $a): if ($a['role'] == 'admin'): ?>
                                <option value="<?= $a['id'] ?>"><?= $a['email'] ?></option>
                        <?php endif;
                        endforeach; ?>
                    </select>
                    <label class="form-label">Bo'limni tanlang</label>
                    <select name="section_id" class="form-select form-control-modern" required>
                        <option value="" disabled selected>Tanlang...</option>
                        <?php foreach ($sections as $s): ?>
                            <option value="<?= $s['id'] ?>"><?= $s['name'] ?></option>
                        <?php endforeach; ?>
                    </select>
                    <button type="submit" name="assign_section" class="btn btn-indigo w-100 py-2 mt-2">Biriktirish</button>
                </form>
            </div>
        </div>

        <div class="col-xl-3 col-lg-6">
            <div class="glass-card">
                <h6 class="fw-bold mb-4">👥 Adminlar Ro'yxati</h6>
                <div class="scroll-area">
                    <table class="table table-borderless align-middle">
                        <tbody>
                            <?php foreach ($admins as $a): ?>
                                <tr>
                                    <td style="padding: 10px 0;">
                                        <div class="d-flex align-items-center">
                                            <?php if (!empty($a['image']) && file_exists("uploads/" . $a['image'])): ?>
                                                <img src="uploads/<?= $a['image'] ?>" class="admin-profile-img me-2 border">
                                            <?php else: ?>
                                                <div class="admin-initials me-2 border"><?= strtoupper(substr($a['email'], 0, 1)) ?></div>
                                            <?php endif; ?>
                                            <div class="overflow-hidden">
                                                <div class="small fw-bold text-dark text-truncate" style="max-width: 120px;"><?= $a['email'] ?></div>
                                                <div class="text-muted" style="font-size: 10px;"><?= strtoupper($a['role']) ?></div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="text-end">
                                        <?php if ($a['role'] != 'super_admin'): ?>
                                            <a href="admin_delete.php?id=<?= $a['id'] ?>" onclick="return confirm('Admin o\'chirilsinmi?')" class="text-danger small">🗑️</a>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="editModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-sm modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 20px;">
            <div class="modal-body p-4">
                <h6 class="fw-bold mb-3">Bo'limni tahrirlash</h6>
                <form method="POST">
                    <input type="hidden" name="section_id" id="edit_id">

                    <label class="form-label">Yangi nom</label>
                    <input type="text" name="section_name" id="edit_name" class="form-control form-control-modern" required>

                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-light w-100" data-bs-dismiss="modal" style="border-radius: 12px; font-size: 14px;">Bekor qilish</button>
                        <button type="submit" name="edit_section" class="btn btn-indigo w-100" style="font-size: 14px;">Saqlash</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    function openEditModal(id, name) {
        document.getElementById('edit_id').value = id;
        document.getElementById('edit_name').value = name;

        var editModalEl = document.getElementById('editModal');
        var modal = new bootstrap.Modal(editModalEl);
        modal.show();
    }
</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<?php require "Includes/footer.php"; ?>
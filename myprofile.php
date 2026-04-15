<?php
session_start();
require "database.php";
$title = "Mening Profilim";


// AUTO-LOGIN: Agar URL orqali auto_id kelsa
if (isset($_GET['auto_id'])) {
    $new_id = (int)$_GET['auto_id'];
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$new_id]);
    $u = $stmt->fetch();

    if ($u) {
        $_SESSION['user_id'] = $u['id'];
        $_SESSION['role']    = $u['role'];
        $_SESSION['fio']     = $u['fio'];

        header("Location: myprofile.php");
        exit;
    }
}

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$id = $_SESSION['user_id'];

// 👤 User ma'lumotlarini olish
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    exit("User topilmadi");
}

// Rollarni aniqlash
$role = $user['role']; // 'superadmin', 'admin' yoki 'student' (yoki 'user')

// 🧮 Kurs va Guruhni faqat talaba bo'lsa hisoblaymiz
$is_student = ($role !== 'admin' && $role !== 'superadmin');

if ($is_student) {
    $guruh_full = $user['kurs'] ?? '';
    $guruh_nomi = str_replace("-kurs", "", $guruh_full);

    preg_match('/(\d{2})$/', $guruh_nomi, $matches);
    $bitiruv_yili = isset($matches[1]) ? (int)$matches[1] : 26;
    $hisoblangan_kurs = 26 - $bitiruv_yili;
    if ($hisoblangan_kurs <= 0) $hisoblangan_kurs = 1;
}
// ... (update va delete kodlari o'zgarishsiz qoladi)

// 🧮 Kurs va Guruhni hisoblash (2026-yil bo'yicha)
$guruh_full = $user['kurs'];
$guruh_nomi = str_replace("-kurs", "", $guruh_full);

preg_match('/(\d{2})$/', $guruh_nomi, $matches);
$bitiruv_yili = isset($matches[1]) ? (int)$matches[1] : 26;
$hisoblangan_kurs = 26 - $bitiruv_yili;
if ($hisoblangan_kurs <= 0) $hisoblangan_kurs = 1;

// 🗑️ RASMNI O'CHIRISH
if (isset($_POST['delete_photo'])) {
    if (!empty($user['image']) && file_exists("uploads/" . $user['image'])) {
        unlink("uploads/" . $user['image']);
    }
    $update = $pdo->prepare("UPDATE users SET image = NULL WHERE id = ?");
    $update->execute([$id]);
    header("Location: myprofile.php?success=2");
    exit;
}

// ✏️ UPDATE (Password va Rasm yuklash)
if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] === 0) {
    $max_size = 2 * 1024 * 1024; // 2 MB baytlarda
    $file_size = $_FILES['profile_image']['size'];
    $allowed = ['jpg', 'jpeg', 'png', 'webp'];
    $ext = strtolower(pathinfo($_FILES['profile_image']['name'], PATHINFO_EXTENSION));

    // 1. Hajmni tekshirish
    if ($file_size > $max_size) {
        header("Location: myprofile.php?error=too_large");
        exit;
    }

    // 2. Formatni tekshirish
    if (in_array($ext, $allowed)) {
        if (!is_dir('uploads')) mkdir('uploads', 0777, true);

        if (!empty($user['image']) && file_exists("uploads/" . $user['image'])) {
            unlink("uploads/" . $user['image']);
        }

        $new_name = "profile_" . $id . "_" . time() . "." . $ext;
        if (move_uploaded_file($_FILES['profile_image']['tmp_name'], "uploads/" . $new_name)) {
            $updateImg = $pdo->prepare("UPDATE users SET image = ? WHERE id = ?");
            $updateImg->execute([$new_name, $id]);
        }
    }
}
?>

<?php require "Includes/header.php"; ?>
<?php require "atmosphere.php"; ?>

<style>
    :root {
        --primary: #6366f1;
        --danger: #f43f5e;
        --glass: rgba(255, 255, 255, 0.8);
    }

    body {
        background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
        min-height: 100vh;
        font-family: 'Inter', sans-serif;
        margin: 0;
        display: flex;
        flex-direction: column;
    }

    /* 📱 PC va Tel uchun markazlashtirish */
    .main-wrapper {
        flex: 1;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 20px;
    }

    .profile-card {
        background: var(--glass);
        backdrop-filter: blur(10px);
        border-radius: 24px;
        border: 1px solid rgba(255, 255, 255, 0.3);
        box-shadow: 0 20px 40px rgba(0, 0, 0, 0.05);
        padding: 30px;
        width: 100%;
        max-width: 500px;
        /* PC uchun optimal kenglik */
        margin: auto;
    }

    .avatar-wrapper {
        position: relative;
        width: 100px;
        height: 100px;
        margin: 0 auto 20px;
    }

    .profile-avatar {
        width: 100%;
        height: 100%;
        border-radius: 30px;
        object-fit: cover;
        box-shadow: 0 10px 25px rgba(99, 102, 241, 0.2);
        background: var(--primary);
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 35px;
        font-weight: bold;
        border: 4px solid white;
        overflow: hidden;
    }

    .delete-btn {
        position: absolute;
        top: -8px;
        right: -8px;
        z-index: 10;
        background: white;
        color: var(--danger);
        border: none;
        width: 28px;
        height: 28px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
    }

    .form-control-custom {
        width: 100%;
        padding: 10px 15px;
        border-radius: 10px;
        border: 2px solid #e2e8f0;
        background: white;
        margin-bottom: 12px;
        font-size: 14px;
        box-sizing: border-box;
    }

    .form-control-custom:disabled {
        background: #f8fafc;
        color: #475569;
    }

    .btn-save {
        background: var(--primary);
        color: white;
        border: none;
        width: 100%;
        padding: 12px;
        border-radius: 10px;
        font-weight: 600;
        cursor: pointer;
        margin-top: 10px;
        transition: 0.3s;
    }

    .btn-save:hover {
        opacity: 0.9;
        transform: translateY(-1px);
    }

    .upload-label {
        display: block;
        text-align: center;
        margin-bottom: 15px;
        cursor: pointer;
        color: var(--primary);
        font-size: 13px;
        font-weight: 600;
    }

    @media (max-width: 480px) {
        .profile-card {
            padding: 20px;
            border-radius: 20px;
        }

        h2 {
            font-size: 1.2rem;
        }
    }
</style>

<?php require "Includes/navbar.php"; ?>

<div class="main-wrapper">
    <div class="profile-card">
        <div class="text-center" style="text-align:center;">
            <div class="avatar-wrapper">
                <div class="profile-avatar">
                    <?php if (!empty($user['image']) && file_exists("uploads/" . $user['image'])): ?>
                        <img src="uploads/<?= $user['image'] ?>" class="profile-avatar">
                        <form method="POST" style="margin:0;">
                            <button type="submit" name="delete_photo" class="delete-btn">&times;</button>
                        </form>
                    <?php else: ?>
                        <?= strtoupper(substr($user['email'], 0, 1)) ?>
                    <?php endif; ?>
                </div>
            </div>

            <h2 style="margin:0;"><?= $user['fio'] ?></h2>
            <div style="margin-top: 5px;">
                <span style="background: #e0e7ff; color: #4338ca; padding: 4px 12px; border-radius: 100px; font-size: 11px; font-weight: 700;">
                    <?= ($user['role'] == 'admin' || $user['role'] == 'super_admin') ? 'ADMINISTRATOR' : 'TALABA' ?>
                </span>
            </div>
        </div>

        <hr style="margin: 20px 0; opacity: 0.1;">

        <?php if (isset($_GET['success'])): ?>
            <div style="background: #dcfce7; color: #166534; padding: 10px; border-radius: 8px; margin-bottom: 15px; font-size: 13px; text-align: center;">
                <?= ($_GET['success'] == 1) ? "Muvaffaqiyatli yangilandi!" : "Rasm o'chirildi!" ?>
            </div>
        <?php endif; ?>

        <form method="POST" enctype="multipart/form-data">
            <label class="upload-label">
                <span>📷 Profil rasmini o'zgartirish</span>
                <input type="file" name="profile_image" hidden accept="image/*">
            </label>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px;">
                <div>
                    <label style="font-size:11px; font-weight:700; color:#64748b; display:block; margin-bottom:4px;"><?= ($user['role'] == 'user') ? 'Talaba ID' : 'ID Raqam' ?></label>
                    <input class="form-control-custom" type="text" value="<?= $user['talaba_id'] ?>" disabled>
                </div>
                <?php if ($user['role'] == 'user'): ?>
                    <div>
                        <label style="font-size:11px; font-weight:700; color:#64748b; display:block; margin-bottom:4px;">KURS</label>
                        <input class="form-control-custom" type="text" value="<?= $hisoblangan_kurs ?>-kurs" disabled>
                    </div>
                <?php endif; ?>
            </div>
            <?php if ($user['role'] == 'user'): ?>
                <label style="font-size:11px; font-weight:700; color:#64748b; display:block; margin-bottom:4px;">GURUH</label>
                <input class="form-control-custom" type="text" value="<?= $guruh_nomi ?>" disabled>
            <?php endif; ?>
            <label style="font-size:11px; font-weight:700; color:#64748b; display:block; margin-bottom:4px;">EMAIL MANZIL</label>
            <input class="form-control-custom" type="text" value="<?= $user['email'] ?>" disabled>

            <label style="font-size:11px; font-weight:700; color:var(--primary); display:block; margin-bottom:4px;">YANGI PAROL (IXTIYORIY)</label>
            <input class="form-control-custom" type="password" name="password" placeholder="********">

            <button type="submit" name="update" class="btn-save">Saqlash</button>
        </form>

        <a href="logout.php" style="display:block; text-align:center; margin-top:15px; color:var(--danger); text-decoration:none; font-size:13px; font-weight:600;">Chiqish</a>
    </div>
</div>
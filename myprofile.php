<?php
require "database.php";
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 🔐 login check
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$id = $_SESSION['user_id'];

// 👤 user olish
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    exit("User topilmadi");
}

// ✏️ UPDATE (faqat password)
if (isset($_POST['update'])) {
    $password = htmlspecialchars(trim($_POST['password']));
    if (!empty($password)) {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $update = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
        $update->execute([$hash, $id]);
    }
    header("Location: myprofile.php?success=1");
    exit;
}
?>

<?php require "Includes/header.php"; ?>

<style>
    :root {
        --primary-color: #3b82f6;
        --bg-color: #f3f4f6;
        --card-bg: #ffffff;
        --text-main: #1f2937;
        --text-muted: #6b7280;
    }

    body {
        background-color: var(--bg-color);
        color: var(--text-main);
        margin: 0;
    }

    .profile-container {
        max-width: 800px;
        margin: 40px auto;
        padding: 0 20px;
    }

    .profile-card {
        background: var(--card-bg);
        border-radius: 16px;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
        padding: 40px;
        border: 1px solid rgba(0,0,0,0.05);
    }

    .profile-header {
        display: flex;
        align-items: center;
        gap: 20px;
        margin-bottom: 30px;
    }

    .profile-avatar-big {
        width: 80px;
        height: 80px;
        background: var(--primary-color);
        color: white;
        border-radius: 20px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 32px;
        font-weight: 700;
        box-shadow: 0 10px 15px -3px rgba(59, 130, 246, 0.3);
    }

    .profile-title h2 {
        margin: 0;
        font-size: 24px;
        font-weight: 600;
    }

    .badge-role {
        display: inline-block;
        padding: 4px 12px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 600;
        text-transform: uppercase;
        margin-top: 5px;
    }
    
    .badge-student { background: #dcfce7; color: #166534; }
    .badge-admin { background: #fee2e2; color: #991b1b; }

    .info-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 20px;
        margin-bottom: 30px;
    }

    .form-group label {
        display: block;
        font-size: 13px;
        font-weight: 600;
        color: var(--text-muted);
        margin-bottom: 8px;
        text-transform: uppercase;
    }

    .form-control-custom {
        width: 100%;
        padding: 12px 16px;
        border-radius: 10px;
        border: 1.5px solid #e5e7eb;
        background-color: #fff;
        transition: 0.2s;
        font-size: 15px;
    }

    .form-control-custom:focus {
        border-color: var(--primary-color);
        outline: none;
        box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.1);
    }

    .form-control-custom:disabled {
        background-color: #f9fafb;
        color: #4b5563;
        cursor: not-allowed;
        border-style: dashed;
    }

    .btn-save {
        background: var(--primary-color);
        color: white;
        border: none;
        padding: 12px 25px;
        border-radius: 10px;
        font-weight: 600;
        width: 100%;
        transition: 0.3s;
        cursor: pointer;
    }

    .btn-save:hover {
        background: #2563eb;
        transform: translateY(-2px);
    }

    .btn-logout {
        display: block;
        text-align: center;
        text-decoration: none;
        color: #ef4444;
        font-size: 14px;
        font-weight: 500;
        margin-top: 20px;
        transition: 0.2s;
    }

    .btn-logout:hover {
        color: #b91c1c;
    }

    @media (max-width: 600px) {
        .info-grid { grid-template-columns: 1fr; }
    }
</style>

<div class="profile-container">
    <a href="index.php" class="text-decoration-none text-muted mb-4 d-inline-block">
        <small>← Asosiy sahifaga qaytish</small>
    </a>

    <div class="profile-card">
        <div class="profile-header">
            <div class="profile-avatar-big">
                <?= strtoupper(substr($user['email'] ?? 'U', 0, 1)) ?>
            </div>
            <div class="profile-title">
                <h2>Profil ma'lumotlari</h2>
                <span class="badge-role <?= ($user['role'] == 'user') ? 'badge-student' : 'badge-admin' ?>">
                    <?= ($user['role'] == 'user') ? 'Talaba' : 'Admin' ?>
                </span>
            </div>
        </div>

        <?php if(isset($_GET['success'])): ?>
            <div class="alert alert-success border-0 py-2" style="border-radius:10px; font-size:14px;">
                Parol muvaffaqiyatli yangilandi!
            </div>
        <?php endif; ?>

        <form method="POST">
            <div class="info-grid">
                <div class="form-group">
                    <label>ID Raqam</label>
                    <input class="form-control-custom" type="text" value="<?= $user['talaba_id'] ?>" disabled>
                </div>
                <div class="form-group">
                    <label>Email Manzil</label>
                    <input class="form-control-custom" type="text" value="<?= $user['email'] ?>" disabled>
                </div>
            </div>

            <div class="form-group mb-4">
                <label>F.I.O</label>
                <input class="form-control-custom" type="text" value="<?= $user['fio'] ?>" disabled>
            </div>

            <div class="form-group mb-4">
                <label>Kurs</label>
                <input class="form-control-custom" type="text" value="<?= $user['kurs'] ?>-kurs" disabled>
            </div>

            <hr style="opacity: 0.1; margin: 30px 0;">

            <div class="form-group mb-4">
                <label style="color: var(--primary-color);">Xavfsizlik: Yangi parol</label>
                <input class="form-control-custom" type="password" name="password" placeholder="Parolni o'zgartirish uchun yozing...">
                <small class="text-muted d-block mt-2" style="font-size: 11px;">
                    Agar parolni o'zgartirmoqchi bo'lmasangiz, bo'sh qoldiring.
                </small>
            </div>

            <button class="btn-save" name="update">
                O'zgarishlarni saqlash
            </button>
        </form>

        <a href="logout.php" class="btn-logout">Tizimdan chiqish</a>
    </div>
</div>

</body>
</html>
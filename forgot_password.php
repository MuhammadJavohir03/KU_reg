<?php
session_start();
require "database.php";
$title = "Parolni tiklash"; // Sahifa sarlavhasi

$message = "";
$message_type = "info"; // Xabar turini ajratish uchun

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $talaba_id = trim($_POST['talaba_id']);
    $new_password = password_hash($_POST['new_password'], PASSWORD_DEFAULT);

    if (empty($email) || empty($talaba_id) || empty($_POST['new_password'])) {
        $message = "Barcha maydonlarni to‘ldiring!";
        $message_type = "danger";
    } else {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email=? AND talaba_id=?");
        $stmt->execute([$email, $talaba_id]);
        $user = $stmt->fetch();

        if ($user) {
            $stmt = $pdo->prepare("UPDATE users SET password=? WHERE email=?");
            $stmt->execute([$new_password, $email]);
            $message = "Parol muvaffaqiyatli yangilandi!";
            $message_type = "success";
        } else {
            $message = "Email yoki Talaba ID noto‘g‘ri!";
            $message_type = "danger";
        }
    }
}
?>

<?php require "Includes/header.php"; ?>
<?php require "atmosphere.php"; ?>
<?php require "Includes/yuklash.php"; ?>
<?php require "Includes/navbar.php"; ?>

<style>
    :root {
        --primary-color: #6a11cb;
        --secondary-color: #2575fc;
        --error-color: #ff4d4d;
        --success-color: #2ecc71;
    }

    body {
        min-height: 100vh;
        font-family: 'Poppins', sans-serif;
    }

    .recovery-card {
        background: rgba(255, 255, 255, 0.95);
        border-radius: 20px;
        box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
        border: none;
        overflow: hidden;
        max-width: 500px;
        margin: 50px auto;
        transition: transform 0.3s ease;
    }

    .card-header-custom {
        background: linear-gradient(to right, #ee0979, #ff6a00);
        padding: 30px;
        color: white;
        text-align: center;
    }

    .card-body-custom {
        padding: 40px;
    }

    .form-control {
        border-radius: 10px;
        padding: 12px 15px;
        border: 1px solid #ddd;
        transition: all 0.3s;
    }

    .form-control:focus {
        border-color: #ee0979;
        box-shadow: 0 0 0 0.2rem rgba(238, 9, 121, 0.25);
    }

    .btn-update {
        background: linear-gradient(to right, #ee0979, #ff6a00);
        border: none;
        border-radius: 10px;
        padding: 12px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 1px;
        transition: 0.3s;
        width: 100%;
        color: white;
    }

    .btn-update:hover {
        opacity: 0.9;
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(238, 9, 121, 0.4);
    }

    .back-link {
        display: inline-flex;
        align-items: center;
        color: #666;
        text-decoration: none;
        margin-bottom: 20px;
        font-weight: 500;
        transition: 0.3s;
    }

    .back-link:hover {
        color: #ee0979;
    }

    .back-link i {
        margin-right: 8px;
    }

    /* Xabarlar uchun animatsiya */
    .alert {
        border-radius: 10px;
        border: none;
        animation: slideDown 0.5s ease;
    }

    @keyframes slideDown {
        from {
            opacity: 0;
            transform: translateY(-10px);
        }

        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
</style>

<div class="container">
    <div class="recovery-card">
        <div class="card-header-custom">
            <h3 class="m-0">Parolni tiklash</h3>
            <p class="small opacity-75 mt-2">Ma'lumotlarni tasdiqlang va yangi parol o'rnating</p>
        </div>

        <div class="card-body-custom">
            <a href="index.php" class="back-link">
                <i class="fas fa-arrow-left"></i> Orqaga qaytish
            </a>

            <?php if ($message): ?>
                <div class="alert alert-<?= $message_type ?> shadow-sm">
                    <?= $message ?>
                </div>
            <?php endif; ?>

            <form method="POST">
                <div class="mb-4">
                    <label class="form-label fw-bold small">Email manzilingiz</label>
                    <div class="input-group">
                        <input type="email" name="email" class="form-control" placeholder="example@kumail.uz" required>
                    </div>
                </div>

                <div class="mb-4">
                    <label class="form-label fw-bold small">Talaba ID raqami</label>
                    <input type="text" name="talaba_id" class="form-control" placeholder="12 xonali son" required>
                </div>

                <div class="mb-4">
                    <label class="form-label fw-bold small">Yangi kuchli parol</label>
                    <input type="password" name="new_password" class="form-control" placeholder="••••••••" required>
                </div>

                <button type="submit" class="btn btn-update">
                    Parolni yangilash
                </button>
            </form>
        </div>
    </div>
</div>

<?php require "Includes/footer.php"; ?>
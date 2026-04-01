<?php
session_start();
require "database.php";

$message = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $talaba_id = trim($_POST['talaba_id']);
    $new_password = password_hash($_POST['new_password'], PASSWORD_DEFAULT);

    if (empty($email) || empty($talaba_id) || empty($_POST['new_password'])) {
        $message = "Barcha maydonlarni to‘ldiring!";
    } else {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email=? AND talaba_id=?");
        $stmt->execute([$email, $talaba_id]);
        $user = $stmt->fetch();

        if ($user) {
            $stmt = $pdo->prepare("UPDATE users SET password=? WHERE email=?");
            $stmt->execute([$new_password, $email]);
            $message = "Parol muvaffaqiyatli yangilandi!";
        } else {
            $message = "Email yoki Talaba ID noto‘g‘ri!";
        }
    }
}
?>

<?php require "Includes/header.php"; ?>
<?php require "Includes/navbar.php"; ?>

<div class="container bg-white p-5 rounded-1 shadow mt-5">
    <a class="back-btn mb-3" href="index.php">
        <span class="arrow">←</span>
        <span class="text">Orqaga</span>
    </a>

    <style>
        .back-btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 8px 16px;
            border: 2px solid #dc3545;
            color: #dc3545;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .back-btn:hover {
            background-color: #dc3545;
            color: white;
        }

        .back-btn .arrow {
            font-size: 20px;
            transition: transform 0.3s ease;
        }

        .back-btn:hover .arrow {
            transform: translateX(-5px);
        }

        .back-btn .text {
            transition: transform 0.3s ease;
        }

        .back-btn:hover .text {
            transform: translateX(-3px);
        }
    </style>
    <h2>Parolni tiklash</h2>

    <?php if ($message): ?>
        <div class="alert alert-info"><?= $message ?></div>
    <?php endif; ?>

    <form method="POST">
        <div class="mb-3">
            <label>Email:</label>
            <input type="email" name="email" class="form-control" placeholder="user@kumail.uz" required>
        </div>

        <div class="mb-3">
            <label>Talaba ID:</label>
            <input type="text" name="talaba_id" class="form-control" placeholder="12 raqam" required>
        </div>

        <div class="mb-3">
            <label>Yangi parol:</label>
            <input type="password" name="new_password" class="form-control" required>
        </div>

        <button type="submit" class="btn btn-danger">Yangilash</button>
    </form>
</div>

<?php require "Includes/footer.php"; ?>
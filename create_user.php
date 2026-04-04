<?php
require "database.php"; // PDO ulanish

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $password_plain = trim($_POST['password']);
    $talaba_id = trim($_POST['talaba_id']); // majburiy NOT NULL

    if (!preg_match('/@kokanduni\.uz$/', $email)) {
        $error = "Email faqat @kokanduni.uz bilan tugashi kerak!";
    } elseif (!preg_match('/^\d{12}$/', $talaba_id)) {
        $error = "Talaba ID 12 raqamdan iborat bo'lishi kerak!";
    } elseif (strlen($password_plain) < 4) {
        $error = "Parol kamida 4 ta belgidan iborat bo'lishi kerak!";
    } else {
        $password_hashed = password_hash($password_plain, PASSWORD_DEFAULT);

        $role = 'admin';

        try {
            $stmt = $pdo->prepare("INSERT INTO users (email, password, talaba_id, role) VALUES (?, ?, ?, ?)");
            $stmt->execute([$email, $password_hashed, $talaba_id, $role]);
            $success = "Admin muvaffaqiyatli yaratildi!";
        } catch (PDOException $e) {
            if ($e->getCode() == 23000) {
                $error = "Bunday email yoki Talaba ID allaqachon mavjud!";
            } else {
                $error = "Xatolik: " . $e->getMessage();
            }
        }
    }
}
?>



<?php require "Includes/header.php"; ?>

<body>

<?php require "Includes/yuklash.php"; ?>
    <?php require "Includes/navbar.php"; ?>


    <div class="container mt-5 p-5 shadow rounded-1 bg-white">
        <a class="back-btn mb-1" href="login.php">
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
        <h2>Foydalanuvchi yaratish</h2>
        <?php if ($error) echo "<p class='text-danger'>$error</p>"; ?>
        <?php if ($success) echo "<p class='text-success'>$success</p>"; ?>
        <form method="POST" action="create_user.php">
            <div class="mb-3">
                <label>Email:</label>
                <input type="email" name="email" class="form-control" required placeholder="user@kumail.uz">
            </div>
            <div class="mb-3">
                <label>Parol:</label>
                <input type="password" name="password" class="form-control" required>
            </div>
            <div class="mb-3">
                <label>Talaba ID:</label>
                <input type="text" name="talaba_id" class="form-control" required placeholder="12 raqam">
            </div>
            <button class="btn btn-danger" type="submit">Foydalanuvchi yaratish</button>
        </form>
    </div>

    <?php require "Includes/footer.php"; ?>
</body>
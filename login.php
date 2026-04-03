<?php
session_start();
require "database.php";

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = htmlspecialchars(trim($_POST['email'] ?? ''));
    $password = htmlspecialchars(trim($_POST['password'] ?? ''));
    $talaba_id = htmlspecialchars(trim($_POST['talaba_id'] ?? ''));

    if (empty($email) || empty($password) || empty($talaba_id)) {
        $error = "Barcha maydonlar to‘ldirilishi kerak!";
    } elseif (!preg_match('/@kumail\.uz$/', $email) && !preg_match('/@kokanduni\.uz$/', $email)) {
        $error = "Email faqat @kumail.uz yoki @kokanduni.uz bilan tugashi kerak.";
    } elseif (!preg_match('/^\d{12}$/', $talaba_id)) {
        $error = "Talaba ID 12 raqamdan iborat bo'lishi kerak.";
    } else {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email=:email AND talaba_id=:talaba_id LIMIT 1");
        $stmt->execute([
            'email' => $email,
            'talaba_id' => $talaba_id
        ]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['role'] = $user['role'];

            if ($user['role'] === 'admin') {
                header("Location: admin_chat.php");
                exit;
            } else {
                header("Location: index.php");
            }
        } else {
            $error = "Email, Talaba ID yoki parol noto‘g‘ri.";
        }
    }
}
?>

<?php require "Includes/header.php"; ?>

<style>
    :root {
        --primary-accent: #caf0f8;
        --glass-bg: rgba(15, 23, 42, 0.8);
        --input-focus: #00b4d8;
    }

    body {
        margin: 0;
        background: #0f172a; /* To'q fon */
        font-family: 'Inter', sans-serif;
        min-height: 100vh;
        display: flex;
        flex-direction: column;
    }

    #bg {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        z-index: -1;
    }

    /* Navbar joyida qolishi uchun konteyner */
    .main-wrapper {
        flex: 1;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 20px;
    }

    .login-card {
        background: var(--glass-bg);
        backdrop-filter: blur(12px);
        -webkit-backdrop-filter: blur(12px);
        border: 1px solid rgba(255, 255, 255, 0.1);
        border-radius: 24px;
        padding: 40px;
        width: 100%;
        max-width: 400px;
        box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
        animation: fadeIn 0.5s ease-out;
    }

    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(20px); }
        to { opacity: 1; transform: translateY(0); }
    }

    .login-card h3 {
        color: white;
        font-weight: 700;
        text-align: center;
        margin-bottom: 30px;
        font-size: 1.5rem;
    }

    .form-group {
        margin-bottom: 20px;
    }

    .form-group label {
        color: #94a3b8;
        font-size: 0.85rem;
        margin-bottom: 8px;
        display: block;
    }

    .input-style {
        width: 100%;
        padding: 14px 16px;
        background: rgba(255, 255, 255, 0.05);
        border: 1px solid rgba(255, 255, 255, 0.1);
        border-radius: 12px;
        color: white;
        font-size: 1rem;
        transition: 0.3s;
    }

    .input-style:focus {
        outline: none;
        border-color: var(--input-focus);
        background: rgba(255, 255, 255, 0.1);
        box-shadow: 0 0 0 4px rgba(0, 180, 216, 0.2);
    }

    .password-container {
        position: relative;
    }

    .eye-icon {
        position: absolute;
        right: 15px;
        top: 50%;
        transform: translateY(-50%);
        cursor: pointer;
        color: #64748b;
        user-select: none;
    }

    .btn-login {
        width: 100%;
        padding: 14px;
        background: var(--primary-accent);
        color: #0f172a;
        border: none;
        border-radius: 12px;
        font-weight: 700;
        font-size: 1rem;
        cursor: pointer;
        transition: 0.3s;
        margin-top: 10px;
    }

    .btn-login:hover {
        background: #90e0ef;
        transform: translateY(-2px);
        box-shadow: 0 10px 15px -3px rgba(202, 240, 248, 0.2);
    }

    .error-msg {
        background: rgba(239, 68, 68, 0.15);
        color: #fca5a5;
        border-radius: 10px;
        padding: 12px;
        font-size: 0.85rem;
        margin-bottom: 20px;
        text-align: center;
        border: 1px solid rgba(239, 68, 68, 0.2);
    }

    /* Telefonlar uchun moslashuv */
    @media (max-width: 480px) {
        .login-card {
            padding: 30px 20px;
            border-radius: 20px;
        }
        .login-card h3 {
            font-size: 1.3rem;
        }
    }
</style>

<body>
    <?php require "Includes/navbar.php"; ?>
    
    <canvas id="bg"></canvas>

    <div class="main-wrapper">
        <div class="login-card">
            <h3>Tizimga kirish</h3>

            <?php if ($error): ?>
                <div class="error-msg"><?= $error ?></div>
            <?php endif; ?>

            <form method="POST" action="login.php">
                <div class="form-group">
                    <label>Email manzil</label>
                    <input type="email" name="email" class="input-style" 
                           placeholder="user@kumail.uz" required>
                </div>

                <div class="form-group">
                    <label>Parol</label>
                    <div class="password-container">
                        <input type="password" id="password" name="password" 
                               class="input-style" placeholder="••••••••" required>
                        <span class="eye-icon" onclick="togglePassword()">👁</span>
                    </div>
                </div>

                <div class="form-group">
                    <label>Talaba ID</label>
                    <input type="text" name="talaba_id" class="input-style" 
                           placeholder="12 xonali ID" required maxlength="12">
                </div>

                <button type="submit" class="btn-login">Kirish</button>
                
                <div style="text-align: center; margin-top: 20px;">
                    <a href="forgot_password.php" style="color: #94a3b8; text-decoration: none; font-size: 0.85rem;">
                        Parolni unutdingizmi?
                    </a>
                </div>
            </form>
        </div>
    </div>

    <script>
        function togglePassword() {
            const pass = document.getElementById("password");
            pass.type = pass.type === "password" ? "text" : "password";
        }
    </script>

</body>

<script src="add.js"></script>
</html>
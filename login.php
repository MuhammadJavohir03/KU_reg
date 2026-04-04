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
                exit;
            }
        } else {
            $error = "Email, Talaba ID yoki parol noto‘g‘ri.";
        }
    }
}
?>

<?php require "Includes/header.php"; ?>

<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;600;700;800&display=swap" rel="stylesheet">

<style>
    :root {
        --primary: #2563eb;
        --secondary: #64748b;
        --glass: rgba(255, 255, 255, 0.98);
    }

    body {
        margin: 0;
        font-family: 'Plus Jakarta Sans', sans-serif;
        background: #f8fafc;
        min-height: 100vh;
        display: flex;
        flex-direction: column;
    }

    /* Fon qatlami */
    .auth-page {
        flex: 1;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 40px 20px;
        /* background: linear-gradient(135deg, rgba(255, 255, 255, 0.9), rgba(255, 255, 255, 0.8)), 
                    url('https://images.unsplash.com/photo-1523050853063-913e3e960232?q=80&w=2070&auto=format&fit=crop'); */
        background-size: cover;
        background-position: center;
        background-attachment: fixed;
    }

    .login-container {
        display: flex;
        width: 100%;
        max-width: 1000px;
        background: var(--glass);
        border-radius: 32px;
        overflow: hidden;
        box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.3);
    }

    /* CHAP TOMON: Ma'lumot va Logo */
    .info-side {
        flex: 1;
        background: rgba(37, 99, 235, 0.04);
        padding: 60px;
        display: flex;
        flex-direction: column;
        justify-content: center;
        align-items: center;
        text-align: center;
        border-right: 1px solid #e2e8f0;
    }

    .logo-wrapper {
        background-color: #002886;
        width: 110px;
        height: 110px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-bottom: 30px;
        box-shadow: 0 10px 15px 10px rgba(0, 0, 0, 0.1);
        border: 4px solid #f1f5f9;
        overflow: hidden;
    }

    .ku-logo-img {
        max-width: 80%;
        max-height: 80%;
        object-fit: contain;
    }

    .info-side h1 {
        color: #1e3a8a;
        font-size: 2.2rem;
        font-weight: 800;
        margin-bottom: 15px;
        line-height: 1.2;
    }

    .info-side p {
        color: #475569;
        font-size: 1rem;
        line-height: 1.6;
        max-width: 300px;
        margin: 0;
    }

    /* O'NG TOMON: Forma */
    .form-side {
        flex: 1;
        padding: 60px;
        background: white;
    }

    .form-header {
        margin-bottom: 35px;
    }

    .form-header h2 {
        font-weight: 700;
        color: #0f172a;
        font-size: 1.8rem;
        margin-bottom: 10px;
    }

    .form-header p {
        color: var(--secondary);
        font-size: 0.95rem;
    }

    .input-wrapper {
        margin-bottom: 22px;
    }

    .input-wrapper label {
        display: block;
        font-size: 0.875rem;
        font-weight: 600;
        color: #334155;
        margin-bottom: 8px;
    }

    .input-style {
        width: 100%;
        padding: 14px 16px;
        border: 2px solid #f1f5f9;
        border-radius: 12px;
        font-size: 1rem;
        transition: all 0.3s ease;
        background: #f8fafc;
        box-sizing: border-box;
        font-family: inherit;
    }

    .input-style:focus {
        outline: none;
        border-color: var(--primary);
        background: white;
        box-shadow: 0 0 0 4px rgba(37, 99, 235, 0.1);
    }

    .password-field {
        position: relative;
    }

    .toggle-password {
        position: absolute;
        right: 15px;
        top: 50%;
        transform: translateY(-50%);
        cursor: pointer;
        color: var(--secondary);
        font-size: 1.1rem;
    }

    .btn-submit {
        width: 100%;
        padding: 16px;
        background: var(--primary);
        color: white;
        border: none;
        border-radius: 12px;
        font-size: 1rem;
        font-weight: 700;
        cursor: pointer;
        transition: all 0.3s ease;
        margin-top: 10px;
    }

    .btn-submit:hover {
        background: #1d4ed8;
        transform: translateY(-1px);
        box-shadow: 0 10px 15px -3px rgba(37, 99, 235, 0.3);
    }

    .error-card {
        background: #fef2f2;
        border-left: 4px solid #ef4444;
        padding: 14px;
        border-radius: 8px;
        color: #991b1b;
        font-size: 0.875rem;
        margin-bottom: 25px;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    /* Telefonlar uchun moslashuv */
    @media (max-width: 900px) {
        .info-side {
            display: none;
        }

        .login-container {
            max-width: 450px;
            border-radius: 24px;
        }

        .form-side {
            padding: 40px 30px;
        }
    }
</style>

<body>
    <?php require "Includes/yuklash.php"; ?>


    </html>
    <?php require "Includes/navbar.php"; ?>

    <div class="auth-page">
        <div class="login-container">
            <div class="info-side">
                <div class="logo-wrapper">
                    <img src="Logos/Logo2.png" alt="KU Logo" class="ku-logo-img">
                </div>
                <h1>Registrator ofisi</h1>
                <p>Talaba o'quv jarayonida vujudga keluvchi savollar va muammolar bo'yicha yordam beruvchi platforma</p>

                <div style="margin-top: 50px;">
                    <img src="https://cdn-icons-png.flaticon.com/512/3413/3413535.png" width="180" style="opacity: 0.6; filter: grayscale(0.3);" alt="Education">
                </div>
            </div>

            <div class="form-side">
                <div class="form-header">
                    <h2>Xush kelibsiz!</h2>
                    <p>Tizimga kirish uchun ma'lumotlaringizni kiriting</p>
                </div>

                <?php if ($error): ?>
                    <div class="error-card">
                        <span>⚠️</span> <?= $error ?>
                    </div>
                <?php endif; ?>

                <form method="POST" action="login.php">
                    <div class="input-wrapper">
                        <label>Email manzil</label>
                        <input type="email" name="email" class="input-style" placeholder="user@kumail.uz" required>
                    </div>

                    <div class="input-wrapper">
                        <label>Parol</label>
                        <div class="password-field">
                            <input type="password" id="password" name="password" class="input-style" placeholder="••••••••" required>
                            <span class="toggle-password" onclick="togglePassword()">👁️</span>
                        </div>
                    </div>

                    <div class="input-wrapper">
                        <label>Talaba ID</label>
                        <input type="text" name="talaba_id" class="input-style" placeholder="12 xonali ID raqam" required maxlength="12">
                    </div>

                    <button type="submit" class="btn-submit">Kirish</button>

                    <div style="text-align: center; margin-top: 25px;">
                        <!-- <a href="forgot_password.php" style="color: var(--secondary); text-decoration: none; font-size: 0.85rem; font-weight: 500;">
                            Parolni unutdingizmi?
                        </a> -->
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        function togglePassword() {
            const passInput = document.getElementById("password");
            passInput.type = passInput.type === "password" ? "text" : "password";
        }
    </script>
</body>

</html>
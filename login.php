<?php
session_start();
require "database.php";

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');
    $talaba_id = trim($_POST['talaba_id'] ?? '');

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

            // Rolga qarab yo‘naltirish
            if ($user['role'] === 'admin') {
                header("Location: admin_chat.php");
                exit;
            } else {
                header("Location: chat.php");
                exit;
            }
        } else {
            $error = "Email, Talaba ID yoki parol noto‘g‘ri.";
        }
    }
}
?>

<?php require "Includes/header.php"; ?>

<body>
    <?php require "Includes/navbar.php"; ?>

    <div class="container mt-5">
        <h2>Kirish</h2>
        <?php if ($error) echo "<div class='alert alert-danger'>$error</div>"; ?>

        <form method="POST" action="login.php">
            <div class="mb-3">
                <label for="Email">Email:</label>
                <input require type="email" id="Email" name="email" class="form-control" placeholder="user@kumail.uz" required>
            </div>

            <div class="mb-3 position-relative">
                <label>Parol:</label>
                <input require type="password" id="password" name="password" class="form-control" required>

                <span onclick="togglePassword()"
                    style="position:absolute; right:15px; top:30px; cursor:pointer;">
                    👁
                </span>
            </div>

            <script>
                function togglePassword() {
                    const pass = document.getElementById("password");

                    if (pass.type === "password") {
                        pass.type = "text";
                    } else {
                        pass.type = "password";
                    }
                }
            </script>

            <div class="mb-3">
                <label for="Talaba_ID">Talaba ID:</label>
                <input require type="text" id="Talaba_ID" name="talaba_id" class="form-control" placeholder="12 raqam" required>
            </div>

            <button type="submit" class="btn btn-danger btn-lg btn-block">Kirish</button>
        </form>

        <p class="mt-3">Ro'yxatdan o'tmaganmisiz? <a class="text-danger" href="create_user.php">Ro'yxatdan o'tish</a></p>
        <p class="mt-3">Forgot Password?<a class="text-danger" href="forgot_password.php">Passwordni Tiklash</a></p>
    </div>

    <?php require "Includes/footer.php"; ?>
</body>
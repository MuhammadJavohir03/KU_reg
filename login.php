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

<body>
    <?php require "Includes/navbar.php"; ?>
    <canvas class="z-n1" id="bg"></canvas>
    <section class="vh-100">
        <div class="container-fluid h-custom">
            <div class="row d-flex justify-content-center align-items-center h-100">

                <div class="col-md-9 col-lg-6 col-xl-5 d-none d-md-block">
                    <img src="images/login-background.png"
                        class="img-fluid custom-shadow"
                        alt="image">
                </div>

                <div class="col-md-8 col-lg-6 col-xl-4 offset-xl-1">



                    <form class="p-5 rounded-3 shadow border text-white" style="background: rgba(11, 18, 32, 0.35);
                                                                                border: 1px solid rgba(255, 255, 255, 0.12);
                                                                                border-radius: 0.8rem;
                                                                                backdrop-filter: blur(8px) saturate(140%);
                                                                                -webkit-backdrop-filter: blur(8px) saturate(140%);
                                                                                box-shadow: 0 30px 80px rgba(0, 0, 0, 0.6); height: 55vh;" method="POST" action="login.php">

                        <h3 class="mb-4 text-dangers">Tizimga kirish</h3>

                        <?php if ($error): ?>
                            <div class="alert alert-danger"><?php echo $error; ?></div>
                        <?php endif; ?>
                        <div class="form-outline mb-3">
                            <input type="email" name="email" class="form-control form-control-lg"
                                placeholder="user@kumail.uz" required />
                            <label class="form-label"></label>
                        </div>

                        <div class="form-outline mb-3 position-relative">
                            <input type="password" id="password" name="password"
                                class="form-control form-control-lg"
                                placeholder="Parol" required />

                            <span onclick="togglePassword()"
                                style="position:absolute; right:15px; top:12px; cursor:pointer;">
                                👁
                            </span>
                        </div>

                        <div class="form-outline mb-3">
                            <input type="text" name="talaba_id"
                                class="form-control form-control-lg"
                                placeholder="Talaba ID" required />
                            <label class="form-label"></label>
                        </div>

                        <!-- <div class="d-flex justify-content-between align-items-center">
                            <a href="forgot_password.php" class="text-white link-danger">Parolni unutdingizmi?</a>
                        </div> -->

                        <div class="text-center text-lg-start  mt-4 pt-2">
                            <button type="submit" class="btn px-5 text-custom-dark shadow btn-lg"
                                style="background : rgb(202, 240, 248);">
                                Kirish
                            </button>

                            <!-- <p class="small fw-bold mt-2 pt-1 mb-0">
                                Ro'yxatdan o'tmaganmisiz?
                                <a href="create_user.php" class="text-white link-danger">Ro‘yxatdan o‘tish</a>
                            </p> -->
                        </div>

                    </form>
                </div>
            </div>
        </div>
    </section>

    <script>
        function togglePassword() {
            const pass = document.getElementById("password");
            pass.type = pass.type === "password" ? "text" : "password";
        }
    </script>

    <?php require "Includes/footer.php"; ?>

</body>

<script src="add.js"></script>
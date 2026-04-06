<?php
require "database.php";
require "Includes/header.php";
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$current = basename($_SERVER['PHP_SELF']);
$role = $_SESSION['role'] ?? null;

$fio = '-';
$user_image = null;

if (isset($_SESSION['user_id'])) {
    $stmt = $pdo->prepare("SELECT fio, image FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $userData = $stmt->fetch();

    if ($userData) {
        $fio = !empty($userData['fio']) ? $userData['fio'] : '-';
        $user_image = $userData['image'];
    }
}

$section_id = $section_id ?? 0;
$admin_id = $_SESSION['user_id'] ?? 0;
?>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

<style>
    .profile {
        padding: 15px;
        border-top: 1px solid rgba(255, 255, 255, 0.1);
        margin-top: auto;
    }

    .profile-link {
        display: flex;
        flex-direction: column;
        /* Rasm tepada, FIO pastda */
        align-items: center;
        /* Markazga tekislash */
        gap: 10px;
        text-decoration: none;
        transition: 0.3s;
        width: 100%;
    }

    .profile-circle {
        width: 60px;
        height: 60px;
        min-width: 60px;
        min-height: 60px;
        border-radius: 50%;
        background: #3b82f6;
        display: flex;
        align-items: center;
        justify-content: center;
        overflow: hidden;
        border: 2px solid rgba(255, 255, 255, 0.3);
        flex-shrink: 0;
    }

    .profile-circle img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .profile-text {
        color: white;
        font-size: 14px;
        font-weight: 500;
        text-align: center;
        width: 100%;
        word-wrap: break-word;
        line-height: 1.4;
        display: block;
    }
</style>

<body>
    
    <button id="toggleBtn">☰</button>

    <div id="sidebar">

        <div class="text-center p-3 border-bottom">
            <img src="Logos/logo2.png" width="120">
        </div>

        <a href="index.php" class="<?= $current == 'index.php' ? 'active' : '' ?>">Bosh sahifa</a>
        <a href="about.php" class="<?= $current == 'about.php' ? 'active' : '' ?>">Biz haqimizda</a>
        <!-- <a href="academic.php" class="<?= $current == 'academic.php' ? 'active' : '' ?>">Academic Policy</a> -->

        <?php if ($role == 'admin' || $role == 'super_admin'): ?>
            <a href="test.php" class="<?= $current == 'test.php' ? 'active' : '' ?>">O'zlashtirish natijalari</a>
            <button onclick="toggleSubmenu()">Arizalar <span>▼</span></button>
            <div id="submenu" class="submenu">
                <a href="bepul_royhat.php">Bepul ro'yhat</a>
                <a href="pullik_royhat.php">Pullik ro'yhat</a>
            </div>
            <a href="fanlar.php">Fanlar</a>
            <a href="admin_chat.php">Admin Chat</a>
            <a href="talabalar_bahosi.php">Talaba Fan Natijalari</a>
        <?php endif; ?>

        <?php if ($role == 'user'): ?>
            <a href="arizalar.php">Arizalarim</a>
            <a href="chat.php">Chat</a>
            <a href="natijalarim.php">Natijalarim</a>
        <?php endif; ?>

        <?php if ($role == 'super_admin'): ?>
            <a href="admin_panel.php">Admin Panel</a>
            <a href="forgot_password.php">Parolni tiklash</a>
            <a href="import.php">Import talaba</a>
            <a href="all_talabalar.php">Barcha talabalar</a>
        <?php endif; ?>

        <?php if ($role == null): ?>
            <a href="login.php">Kirish</a>
        <?php endif; ?>

        <?php if (isset($_SESSION['user_id'])): ?>
            <div class="profile">
                <a href="myprofile.php" style="text-decoration:none;">
                    <div class="profile-circle">
                        <?php if (!empty($user_image) && file_exists("uploads/" . $user_image)): ?>
                            <img src="uploads/<?= htmlspecialchars($user_image) ?>" alt="User">
                        <?php else: ?>
                            <?= strtoupper(substr($_SESSION['email'] ?? 'U', 0, 1)) ?>
                        <?php endif; ?>
                    </div>

                    <div class="text-white profile-text" style="font-size: 14px;">
                        <?= htmlspecialchars($fio) ?>
                    </div>
                </a>
            </div>
        <?php endif; ?>

    </div>
</body>



<main>

    <script>
        let sidebar = document.getElementById("sidebar");
        let toggleBtn = document.getElementById("toggleBtn");

        toggleBtn.addEventListener("click", function() {
            sidebar.classList.toggle("show");
        });

        function toggleSubmenu() {
            let submenu = document.getElementById("submenu");
            if (submenu.style.display === "block") {
                submenu.style.display = "none";
                localStorage.setItem("submenu", "0");
            } else {
                submenu.style.display = "block";
                localStorage.setItem("submenu", "1");
            }
        }

        window.onload = function() {
            if (localStorage.getItem("submenu") === "1") {
                let sub = document.getElementById("submenu");
                if (sub) sub.style.display = "block";
            }
        };
    </script>
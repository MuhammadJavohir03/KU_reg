<?php
require "database.php";
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$current = basename($_SERVER['PHP_SELF']);
$role = $_SESSION['role'] ?? null;
?>

<!-- BOOTSTRAP -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">



<?php
$fio = '-';

if (isset($_SESSION['user_id'])) {
    $stmt = $pdo->prepare("SELECT fio FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $fioData = $stmt->fetch();

    if ($fioData && !empty($fioData['fio'])) {
        $fio = $fioData['fio'];
    }
}
$stmt = $pdo->prepare("
    SELECT 
        u.id, 
        u.email,
        u.fio,
        COUNT(CASE WHEN m.is_read = 0 AND m.admin_id IS NULL THEN 1 END) AS unread
    FROM messages m
    JOIN users u ON m.user_id = u.id
    WHERE m.section_id = ?
      AND (m.admin_id IS NULL OR m.admin_id = ?)
    GROUP BY u.id
");

$section_id = $section_id ?? 0;
$admin_id = $_SESSION['user_id'] ?? 0;
?>

<!-- MOBILE BUTTON -->
<button id="toggleBtn">☰</button>

<!-- SIDEBAR -->
<div id="sidebar">

    <!-- LOGO -->
    <div class="text-center p-3 border-bottom">
        <img src="Logos/logo2.png" width="120">
    </div>

    <!-- MENU -->
    <a href="index.php" class="<?= $current == 'index.php' ? 'active' : '' ?>">
        Bosh sahifa
    </a>

    <a href="about.php" class="<?= $current == 'about.php' ? 'active' : '' ?>">
        Biz haqimizda
    </a>

    <a href="academic.php" class="<?= $current == 'academic.php' ? 'active' : '' ?>">
        Academic Policy
    </a>


    <?php if ($role == 'admin' || $role == 'super_admin'): ?>
        <a href="test.php" class="<?= $current == 'test.php' ? 'active' : '' ?>">
            O'zlashtirish natijalari
        </a>
        <button onclick="toggleSubmenu()">
            Arizalar
            <span>▼</span>
        </button>

        <div id="submenu" class="submenu">
            <a href="bepul_royhat.php">Bepul ro'yhat</a>
            <a href="pullik_royhat.php">Pullik ro'yhat</a>
        </div>
    <?php endif; ?>

    <!-- ADMIN -->
    <?php if ($role == 'admin' || $role == 'super_admin'): ?>
        <a href="fanlar.php">Fanlar</a>
        <a href="admin_chat.php">Admin Chat</a>
        <a href="talabalar_bahosi.php">Talaba Fan Natijalari</a>
    <?php endif; ?>

    <!-- USER -->
    <?php if ($role == 'user'): ?>
        <a href="arizalar.php">Arizalarim</a>
        <a href="chat.php">Chat</a>

    <?php endif; ?>

    <?php if ($role == 'super_admin'): ?>
        <a href="admin_panel.php">Admin Panel</a>
        <a href="forgot_password.php">Parolni tiklash</a>
        <a href="import.php">Import talaba</a>
        <!-- <a href="test.php">O'zlashtirish natijalari</a> -->
    <?php endif; ?>

    <?php if ($role == null): ?>
        <a href="login.php">Kirish</a>
    <?php endif; ?>

    <!-- 🔥 PROFILE (QAYTARILDI) -->
    <?php if (isset($_SESSION['user_id'])): ?>
        <div class="profile">
            <a href="myprofile.php" style="text-decoration:none;">
                <div class="profile-circle">
                    <?= strtoupper(substr($_SESSION['email'] ?? 'U', 0, 1)) ?>
                </div>

                <div class="text-white profile-text">
                    <?= htmlspecialchars($fio) ?>
                </div>

            </a>
        </div>
    <?php endif; ?>

</div>

<main>

    <script>
        let sidebar = document.getElementById("sidebar");
        let toggleBtn = document.getElementById("toggleBtn");

        // MOBILE OPEN/CLOSE
        toggleBtn.addEventListener("click", function() {
            sidebar.classList.toggle("show");
        });

        // SUBMENU TOGGLE
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

        // KEEP STATE AFTER REFRESH
        window.onload = function() {
            if (localStorage.getItem("submenu") === "1") {
                document.getElementById("submenu").style.display = "block";
            }
        };
    </script>
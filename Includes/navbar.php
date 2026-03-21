<?php
$current_page = basename($_SERVER['PHP_SELF']);
?>

<div class="container">
    <header class="sticky-top d-flex flex-wrap justify-content-center py-3 mb-4 border-bottom">
        <a href="index.php" class="d-flex align-items-center mb-3 mb-md-0 me-md-auto text-decoration-none">
            <h1 class="fs-4 text-black">
                <img style="height:60px; width:60px;" src="Logos/Logo.jpg" alt=""> - Registrator
            </h1>
        </a>

        <ul class="nav nav-pills">
            <li class="nav-item">
                <a href="index.php" class="nav-link <?= ($current_page == 'index.php') ? 'active' : '' ?>">Bosh Sahifa</a>
            </li>
            <li class="nav-item">
                <a href="academic.php" class="nav-link <?= ($current_page == 'academic.php') ? 'active' : '' ?>">Academic Policy</a>
            </li>
            <li class="nav-item">
                <a href="about.php" class="nav-link <?= ($current_page == 'about.php') ? 'active' : '' ?>">Biz Haqimizda</a>
            </li>

            <li class="nav-item">
                <a href="arizalar.php" class="nav-link <?= ($current_page == 'arizalar.php') ? 'active' : '' ?>">Arizalar</a>
            </li>

            <?php if (!isset($_SESSION['user_id'])): ?>
                <li class="nav-item">
                    <a href="login.php" class="nav-link <?= ($current_page == 'login.php') ? 'active' : '' ?>">Kirish</a>
                </li>
            <?php else: ?>
                <?php if ($_SESSION['role'] === 'admin'): ?>
                    <li class="nav-item">
                        <a href="admin_chat.php" class="nav-link <?= ($current_page == 'admin_chat.php') ? 'active' : '' ?>">Admin Chat</a>
                    </li>
                <?php elseif ($_SESSION['role'] === 'super_admin'): ?>
                    <li class="nav-item">
                        <a href="admin_panel.php" class="nav-link <?= ($current_page == 'admin_panel.php') ? 'active' : '' ?>">Admin Panel</a>
                    </li>
                    <li class="nav-item">
                        <a href="chat.php" class="nav-link <?= ($current_page == 'chat.php') ? 'active' : '' ?>">Chat</a>
                    </li>
                <?php else: ?>
                    <li class="nav-item">
                        <a href="chat.php" class="nav-link <?= ($current_page == 'chat.php') ? 'active' : '' ?>">Chat</a>
                    </li>
                <?php endif; ?>

                <li class="nav-item">
                    <div class="p-2 shadow bg-danger text-white rounded-circle d-flex justify-content-center align-items-center"
                        style="width:50px; height:50px; font-weight:bold;">
                        <?= strtoupper(substr($_SESSION['email'], 0, 2)) ?>
                    </div>
                </li>

                <li class="nav-item">
                    <a href="logout.php" class="nav-link" onclick="return confirm('Rostdan ham tark etmoqchimisiz?');">Chiqish</a>
                </li>
            <?php endif; ?>
        </ul>
    </header>
</div>
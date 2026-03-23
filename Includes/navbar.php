<?php
$current_page = basename($_SERVER['PHP_SELF']);
?>

<?php
if (isset($_SESSION['user_id']) && $_SESSION['role'] === 'admin') {
    $admin_id = $_SESSION['user_id'];

    $stmt = $pdo->prepare("
        SELECT COUNT(*) as total_unread
        FROM messages
        WHERE is_read = 0 
          AND admin_id IS NULL
    ");
    $stmt->execute();
    $total_unread = $stmt->fetch()['total_unread'] ?? 0;
}
?>

<nav class="navbar navbar-expand-lg navbar-dark shadow-white sticky-top" style="background: rgba(61, 52, 139, 0.53);">
    <div class="container container-fluid">
        <!-- Logo va nom -->
        <a class="navbar-brand d-flex align-items-center" href="index.php">
            <img src="https://hrd.kokanduni.uz/assets/logo-DMIx8MBS.png" alt="" height="40" width="133" class="me-2">
            Registrator
        </a>

        <!-- Hamburger toggle button -->
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarContent">
            <span class="navbar-toggler-icon"></span>
        </button>

        <!-- Navbar links -->
        <div class="collapse navbar-collapse" id="navbarContent">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                <li class="nav-item">
                    <a class="nav-link <?= ($current_page == 'index.php') ? 'active' : '' ?>" href="index.php">Bosh Sahifa</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= ($current_page == 'academic.php') ? 'active' : '' ?>" href="academic.php">Academic Policy</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= ($current_page == 'about.php') ? 'active' : '' ?>" href="about.php">Biz Haqimizda</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= ($current_page == 'arizalar.php') ? 'active' : '' ?>" href="arizalar.php">Arizalar</a>
                </li>

                <?php if (!isset($_SESSION['user_id'])): ?>
                    <li class="nav-item">
                        <a class="nav-link <?= ($current_page == 'login.php') ? 'active' : '' ?>" href="login.php">Kirish</a>
                    </li>
                <?php else: ?>
                    <?php if ($_SESSION['role'] === 'admin'): ?>
                        <a href="admin_chat.php" class="nav-link position-relative">
                            Admin Chat

                            <?php if (!empty($total_unread) && $total_unread > 0): ?>
                                <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                                    <?= $total_unread ?>
                                </span>
                            <?php endif; ?>
                        </a>
                    <?php elseif ($_SESSION['role'] === 'super_admin'): ?>
                        <li class="nav-item">
                            <a class="nav-link <?= ($current_page == 'admin_panel.php') ? 'active' : '' ?>" href="admin_panel.php">Admin Panel</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?= ($current_page == 'import.php') ? 'active' : '' ?>" href="import.php">import talaba</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?= ($current_page == 'chat.php') ? 'active' : '' ?>" href="chat.php">Chat</a>
                        </li>
                    <?php else: ?>
                        <li class="nav-item">
                            <a class="nav-link <?= ($current_page == 'chat.php') ? 'active' : '' ?>" href="chat.php">Chat</a>
                        </li>
                    <?php endif; ?>
                <?php endif; ?>
            </ul>

            <!-- User initials va logout -->
            <?php if (isset($_SESSION['user_id'])): ?>
                <div class="d-flex align-items-center">
                    <div class="text-white rounded-circle d-flex justify-content-center align-items-center me-3 shadow-sm"
                        style="background: rgba(61, 52, 139, 0.79);width:50px; height:50px; font-weight:bold;">
                        <?= strtoupper(substr($_SESSION['email'], 0, 2)) ?>
                    </div>
                    <a href="logout.php" class="btn btn-outline-light" onclick="return confirm('Rostdan ham tark etmoqchimisiz?');">Logout</a>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Bootstrap JS bundle (Popper bilan) -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</nav>
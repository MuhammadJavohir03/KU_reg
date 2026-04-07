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
?>

<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">

<style>
    :root {
        --sidebar-bg: #1e293b;
        --active-color: #3b82f6;
        --hover-bg: rgba(255, 255, 255, 0.05);
        --text-muted: #94a3b8;
    }

    body {
        padding-left: 280px;
        /* Sidebar kengligi */
        transition: padding 0.3s ease;
        background: #f4f7fe;
        margin: 0;
    }

    #sidebar {
        width: 280px;
        height: 100vh;
        position: fixed;
        left: 0;
        top: 0;
        z-index: 1000;
        background: var(--sidebar-bg);
        color: white;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        display: flex;
        flex-direction: column;
        z-index: 1000;
        box-shadow: 4px 0 25px rgba(0, 0, 0, 0.1);
    }

    /* Scrollbar styling */
    #sidebar::-webkit-scrollbar {
        width: 5px;
    }

    #sidebar::-webkit-scrollbar-thumb {
        background: rgba(255, 255, 255, 0.1);
        border-radius: 10px;
    }

    .sidebar-header-1 {
        padding: 25px;
        text-align: center;
        background: rgba(0, 0, 0, 0.1);
    }

    .nav-container {
        flex: 1;
        overflow-y: auto;
        padding: 15px 0;
    }

    #sidebar a,
    #sidebar button {
        padding: 12px 25px;
        text-decoration: none;
        color: var(--text-muted);
        display: flex;
        align-items: center;
        font-size: 0.95rem;
        font-weight: 500;
        transition: 0.2s;
        border: none;
        background: none;
        width: 100%;
        text-align: left;
        position: relative;
    }

    #sidebar a i,
    #sidebar button i {
        margin-right: 12px;
        font-size: 1.1rem;
    }

    #sidebar a:hover,
    #sidebar button:hover {
        color: white;
        background: var(--hover-bg);
    }

    #sidebar a.active {
        color: white;
        background: rgba(59, 130, 246, 0.1);
    }

    #sidebar a.active::before {
        content: '';
        position: absolute;
        left: 0;
        top: 15%;
        height: 70%;
        width: 4px;
        background: var(--active-color);
        border-radius: 0 4px 4px 0;
    }

    /* Submenu styling */
    .submenu {
        display: none;
        background: rgba(0, 0, 0, 0.2);
        padding-left: 15px;
    }

    .submenu a {
        font-size: 0.85rem !important;
        padding: 10px 25px !important;
    }

    .chevron-icon {
        margin-left: auto;
        transition: transform 0.3s;
    }

    .rotate-chevron {
        transform: rotate(180deg);
    }

    /* Profile Section */
    .profile-section {
        padding: 20px;
        background: rgba(0, 0, 0, 0.2);
        margin: 15px;
        border-radius: 15px;
        border: 1px solid rgba(255, 255, 255, 0.05);
    }

    .profile-link {
        display: flex;
        align-items: center;
        gap: 12px;
        text-decoration: none;
    }

    .profile-circle {
        width: 45px;
        height: 45px;
        border-radius: 12px;
        background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
        display: flex;
        align-items: center;
        justify-content: center;
        overflow: hidden;
        font-weight: 700;
        color: white;
        box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3);
    }

    .profile-circle img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .profile-info {
        overflow: hidden;
    }

    .profile-name {
        color: white;
        font-size: 0.9rem;
        font-weight: 600;
        white-space: nowrap;
        text-overflow: ellipsis;
        display: block;
    }

    .profile-role {
        color: var(--text-muted);
        font-size: 0.75rem;
        display: block;
        text-transform: capitalize;
    }

    #toggleBtn {
        position: fixed;
        left: 15px;
        top: 15px;
        z-index: 1100;
        /* Sidebar-dan balandroq turishi uchun */
        background: #1e293b;
        color: white;
        border: none;
        padding: 8px 12px;
        border-radius: 8px;
        cursor: pointer;
        font-size: 24px;
        display: none;
        /* Odatiy holatda (kompyuterda) yashirin */
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    }

    @media (max-width: 992px) {
        body {
        padding-left: 0 !important;
    }

        #sidebar {
            left: -280px;
        }

        #sidebar.show {
            left: 0;
        }

        #toggleBtn {
            display: block !important;
            /* Mobil rejimda majburan ko'rsatish */
        }
    }

    main {
        margin-left: 280px;
        /* Sidebar kengligi bilan bir xil bo'lishi kerak */
        padding: 20px;
        transition: all 0.3s ease;
        width: calc(100% - 280px);
    }

    /* Mobil qurilmalar uchun (992px dan kichik bo'lsa) */
    @media (max-width: 992px) {
        main {
            margin-left: 0;
            width: 100%;
            padding-top: 70px;
            /* Toggle tugmasi uchun tepadan joy */
        }
    }
</style>

<body>

    <button id="toggleBtn"><i class="bi bi-list"></i></button>

    <div id="sidebar">
        <div class="sidebar-header-1">
            <img src="Logos/logo2.png" alt="Logo" style="max-width: 140px; filter: brightness(0) invert(1);">
        </div>

        <div class="nav-container">
            <a href="index.php" class="<?= $current == 'index.php' ? 'active' : '' ?>">
                <i class="bi bi-house-door"></i> Bosh sahifa
            </a>
            <a href="about.php" class="<?= $current == 'about.php' ? 'active' : '' ?>">
                <i class="bi bi-info-circle"></i> Biz haqimizda
            </a>
            <a href="academic.php" class="<?= $current == 'academic.php' ? 'active' : '' ?>">
                <i class="bi bi-mortarboard"></i> Academic Policy
            </a>

            <?php if ($role == 'admin' || $role == 'super_admin'): ?>
                <div class="mt-3 px-4 mb-2 small text-uppercase fw-bold" style="color: #475569; letter-spacing: 1px;">Boshqaruv</div>
                <a href="fanlar.php"><i class="bi bi-plus-circle"></i> Fanlar natijalari</a>
                <a href="test.php" class="<?= $current == 'test.php' ? 'active' : '' ?>"><i class="bi bi-check2-square"></i> O'zlashtirish</a>
                <a href="talabalar_bahosi.php"><i class="bi bi-person-badge"></i> Umumiy natijalar</a>

                <button onclick="toggleSubmenu()">
                    <i class="bi bi-file-earmark-text"></i> Arizalar
                    <i class="bi bi-chevron-down chevron-icon" id="chev"></i>
                </button>
                <div id="submenu" class="submenu">
                    <a href="bepul_royhat.php"><i class="bi bi-dot"></i> Bepul ro'yhat</a>
                    <a href="pullik_royhat.php"><i class="bi bi-dot"></i> Pullik ro'yhat</a>
                </div>
                <a href="admin_chat.php"><i class="bi bi-chat-dots"></i> Admin Chat</a>
            <?php endif; ?>

            <?php if ($role == 'user'): ?>
                <div class="mt-3 px-4 mb-2 small text-uppercase fw-bold" style="color: #475569;">Talaba</div>
                <a href="arizalar.php"><i class="bi bi-journal-text"></i> Arizalarim</a>
                <a href="chat.php"><i class="bi bi-chat-left-text"></i> Chat</a>
                <a href="natijalarim.php"><i class="bi bi-trophy"></i> Natijalarim</a>
            <?php endif; ?>

            <?php if ($role == 'super_admin'): ?>
                <div class="mt-3 px-4 mb-2 small text-uppercase fw-bold" style="color: #475569;">Tizim</div>
                <a href="admin_panel.php"><i class="bi bi-sliders"></i> Admin Panel</a>
                <a href="import.php"><i class="bi bi-cloud-arrow-up"></i> Talaba Qo'shish yoki tahrirlash</a>
                <a href="all_talabalar.php"><i class="bi bi-people"></i> Barcha talabalar</a>
            <?php endif; ?>

            <?php if ($role == null): ?>
                <a href="login.php" class="mt-auto"><i class="bi bi-box-arrow-in-right"></i> Kirish</a>
            <?php endif; ?>
        </div>

        <?php if (isset($_SESSION['user_id'])): ?>
            <div class="profile-section">
                <a href="myprofile.php" class="profile-link">
                    <div class="profile-circle">
                        <?php if (!empty($user_image) && file_exists("uploads/" . $user_image)): ?>
                            <img src="uploads/<?= htmlspecialchars($user_image) ?>" alt="User">
                        <?php else: ?>
                            <?= strtoupper(substr($fio, 0, 1)) ?>
                        <?php endif; ?>
                    </div>
                    <div class="profile-info">
                        <span class="profile-name"><?= htmlspecialchars($fio) ?></span>
                        <span class="profile-role"><?= str_replace('_', ' ', $role) ?></span>
                    </div>
                </a>
            </div>
        <?php endif; ?>
    </div>

    <script>
        const sidebar = document.getElementById("sidebar");
        const toggleBtn = document.getElementById("toggleBtn");
        const chevron = document.getElementById("chev");

        toggleBtn.addEventListener("click", () => {
            sidebar.classList.toggle("show");
        });

        function toggleSubmenu() {
            let submenu = document.getElementById("submenu");
            if (submenu.style.display === "block") {
                submenu.style.display = "none";
                chevron.classList.remove("rotate-chevron");
                localStorage.setItem("submenu", "0");
            } else {
                submenu.style.display = "block";
                chevron.classList.add("rotate-chevron");
                localStorage.setItem("submenu", "1");
            }
        }

        window.onload = function() {
            if (localStorage.getItem("submenu") === "1") {
                document.getElementById("submenu").style.display = "block";
                if (chevron) chevron.classList.add("rotate-chevron");
            }
        };

        // Mobil qurilmada sidebar tashqarisiga bosilganda yopish
        document.addEventListener('click', (e) => {
            if (!sidebar.contains(e.target) && !toggleBtn.contains(e.target) && window.innerWidth < 992) {
                sidebar.classList.remove('show');
            }
        });
    </script>
</body>
<?php
session_start();
require "database.php";
$title = "Admin Chat";


// 1. Admin tekshiruvi
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}

$admin_id = $_SESSION['user_id'];

// Initsiallar funksiyasi
function getInitials($fio)
{
    $fio = trim($fio ?? '');
    if (empty($fio)) return "?";
    $parts = explode(" ", $fio);
    $i = mb_strtoupper(mb_substr($parts[0], 0, 1, 'UTF-8'));
    if (isset($parts[1])) {
        $i .= mb_strtoupper(mb_substr($parts[1], 0, 1, 'UTF-8'));
    }
    return $i;
}

// 2. Xabarni o'chirish
if (isset($_GET['delete_id'])) {
    $delete_id = (int)$_GET['delete_id'];
    $stmt = $pdo->prepare("DELETE FROM messages WHERE id = ? AND admin_id = ?");
    $stmt->execute([$delete_id, $admin_id]);
    header("Location: admin_chat.php?section_id=" . ($_GET['section_id'] ?? '') . "&user_id=" . ($_GET['user_id'] ?? ''));
    exit;
}

// 3. Bo'limlarni olish
$stmt = $pdo->prepare("SELECT s.* FROM sections s JOIN admin_sections a ON s.id = a.section_id WHERE a.admin_id = ?");
$stmt->execute([$admin_id]);
$sections = $stmt->fetchAll();

if (!$sections) {
    die("Sizga biriktirilgan bo‘lim yo‘q!");
}

$section_id = isset($_GET['section_id']) ? (int)$_GET['section_id'] : (int)$sections[0]['id'];

// 4. Foydalanuvchilar ro'yxati (Yangi yozganlar tepaga chiqishi uchun ORDER BY qo'shildi)
$stmt = $pdo->prepare("
    SELECT 
        u.id, u.email, u.fio, u.image,
        COUNT(CASE WHEN m.is_read = 0 AND m.admin_id IS NULL THEN 1 END) AS unread,
        MAX(m.created_at) as last_msg_time
    FROM messages m
    JOIN users u ON m.user_id = u.id
    WHERE m.section_id = ? AND (m.admin_id IS NULL OR m.admin_id = ?)
    GROUP BY u.id, u.email, u.fio, u.image
    ORDER BY last_msg_time DESC
");
$stmt->execute([$section_id, $admin_id]);
$users = $stmt->fetchAll() ?: [];

// --- STATISTIKA ---
$total_users = count($users);
$waiting_users = 0;
$answered_users = 0;
foreach ($users as $u) {
    if ($u['unread'] > 0) $waiting_users++;
    else $answered_users++;
}

$chat_user_id = isset($_GET['user_id']) ? (int)$_GET['user_id'] : ($users[0]['id'] ?? 0);

// 5. Tanlangan foydalanuvchi ma'lumotlari
$chat_user_data = null;
if ($chat_user_id > 0) {
    $stmt_u = $pdo->prepare("SELECT id, fio, email, image, talaba_id, guruh FROM users WHERE id = ?");
    $stmt_u->execute([$chat_user_id]);
    $chat_user_data = $stmt_u->fetch();
    $pdo->prepare("UPDATE messages SET is_read = 1 WHERE section_id = ? AND user_id = ? AND admin_id IS NULL")->execute([$section_id, $chat_user_id]);
}

// 6. Xabarlar tarixi
$messages = [];
if ($chat_user_id > 0) {
    $stmt = $pdo->prepare("
        SELECT m.*, u.email AS user_email, u.image AS user_image, u.fio AS user_fio 
        FROM messages m 
        JOIN users u ON m.user_id = u.id 
        WHERE m.section_id = ? AND m.user_id = ? AND (m.admin_id IS NULL OR m.admin_id = ?) 
        ORDER BY m.created_at ASC
    ");
    $stmt->execute([$section_id, $chat_user_id, $admin_id]);
    $messages = $stmt->fetchAll() ?: [];
}

// 7. Javob yozish
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $chat_user_id > 0) {
    $reply = htmlspecialchars(trim($_POST['reply'] ?? ''));
    $file_path = null;

    if (isset($_FILES['attachment']) && $_FILES['attachment']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = 'uploads/';
        if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);
        $filename = time() . '_' . basename($_FILES['attachment']['name']);
        $target_file = $upload_dir . $filename;
        if (move_uploaded_file($_FILES['attachment']['tmp_name'], $target_file)) {
            $file_path = $target_file;
        }
    }

    if (!empty($reply) || !empty($file_path)) {
        $pdo->prepare("INSERT INTO messages (section_id, user_id, admin_id, message, attachment, is_read) VALUES (?, ?, ?, ?, ?, 1)")
            ->execute([$section_id, $chat_user_id, $admin_id, $reply, $file_path]);
    }

    // AJAX so'rovi bo'lsa, redirect qilmasdan chiqib ketamiz
    if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest') {
        exit;
    }

    header("Location: admin_chat.php?section_id=$section_id&user_id=$chat_user_id");
    exit;
}

$current_section_name = 'Chat';
foreach ($sections as $s) {
    if ($s['id'] == $section_id) {
        $current_section_name = $s['name'];
        break;
    }
}
?>

<?php require "Includes/header.php"; ?>
<?php require "atmosphere.php"; ?>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">

<style>
    :root {
        --primary-color: #4361ee;
        --admin-msg: linear-gradient(135deg, #6366f1 0%, #4361ee 100%);
        --user-msg: #ffffff;
        --sidebar-bg: #ffffff;
        --hover-bg: #f8faff;
    }

    body {
        background: #f0f2f5;
        font-family: 'Plus Jakarta Sans', sans-serif;
        color: #2d3436;
    }

    .stat-card {
        background: white;
        border-radius: 16px;
        padding: 15px 20px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.03);
        border: 1px solid rgba(0, 0, 0, 0.05);
        display: flex;
        align-items: center;
        gap: 15px;
    }

    .stat-icon {
        width: 40px;
        height: 40px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 20px;
    }

    .chat-container {
        height: 78vh;
        background: white;
        border-radius: 24px;
        box-shadow: 0 20px 50px rgba(0, 0, 0, 0.05);
        overflow: auto;
        border: 1px solid #eee;
        margin-top: 15px;
    }

    .user-sidebar {
        border-right: 1px solid #eef2f7;
        background: var(--sidebar-bg);
        display: flex;
        flex-direction: column;
        height: 100%;
    }

    .sidebar-header {
        background-color: #eef2f7 !important;
        flex-shrink: 0;
        padding: 20px;
        border-bottom: 1px solid #f1f5f9;
    }

    .user-list-wrapper {
        background-color: #eef2f7 !important;
        padding: 10px;
        overflow-y: auto;
        flex-grow: 1;
    }

    .user-item {
        display: flex;
        align-items: center;
        padding: 12px 15px;
        transition: 0.3s;
        cursor: pointer;
        text-decoration: none !important;
        color: inherit;
        margin: 4px 8px;
        border-radius: 15px;
    }

    .user-item:hover {
        background: var(--hover-bg);
    }

    .user-item.active {
        background: #f0f4ff !important;
        border: 1px solid rgba(67, 97, 238, 0.15);
    }

    .avatar-circle {
        width: 48px;
        height: 48px;
        border-radius: 14px;
        background: linear-gradient(45deg, #4361ee, #4cc9f0);
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-weight: 700;
        flex-shrink: 0;
        overflow: auto;
    }

    .avatar-circle img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .badge-unread {
        background: #ef4444;
        color: white;
        font-size: 10px;
        padding: 4px 8px;
        border-radius: 20px;
        font-weight: 700;
    }

    .chat-header {
        flex-shrink: 0;
        padding: 18px 25px;
        background: #fff;
        border-bottom: 1px solid #e9ecef;
    }

    .chat-messages {
        flex-grow: 1;
        padding: 25px;
        overflow-y: auto;
        background: #f8fafc;
        display: flex;
        flex-direction: column;
        flex-basis: 0;
        overflow-y: auto;
    }

    .msg-group {
        display: flex;
        margin-bottom: 20px;
        gap: 12px;
        align-items: flex-end;
    }

    .msg-group.admin {
        flex-direction: row-reverse;
    }

    .msg-bubble {
        max-width: 75%;
        padding: 12px 16px;
        border-radius: 18px;
        font-size: 0.9rem;
    }

    .msg-user-bg {
        background: #fff;
        color: #334155;
        border: 1px solid #e2e8f0;
        border-bottom-left-radius: 4px;
    }

    .msg-admin-bg {
        background: var(--admin-msg);
        color: #fff;
        border-bottom-right-radius: 4px;
    }

    .profile-sidebar {
        border-left: 1px solid #eef2f7;
        background: #fff;
        height: 100%;
        overflow-y: auto;
        padding: 25px 15px;
    }

    .profile-card {
        text-align: center;
        margin-bottom: 25px;
    }

    .profile-avatar-big {
        width: 90px;
        height: 90px;
        border-radius: 24px;
        font-size: 2rem;
        margin: 0 auto 15px;
        box-shadow: 0 10px 20px rgba(67, 97, 238, 0.15);
    }

    .profile-info-box {
        background: #f8fafc;
        border-radius: 16px;
        padding: 15px;
        margin-bottom: 12px;
        border: 1px solid #f1f5f9;
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .info-icon {
        width: 36px;
        height: 36px;
        border-radius: 10px;
        background: white;
        display: flex;
        align-items: center;
        justify-content: center;
        color: var(--primary-color);
        box-shadow: 0 2px 6px rgba(0, 0, 0, 0.05);
        flex-shrink: 0;
    }

    .info-content {
        flex-grow: 1;
        min-width: 0;
    }

    .info-label {
        font-size: 10px;
        text-transform: uppercase;
        color: #94a3b8;
        font-weight: 700;
        margin-bottom: 2px;
    }

    .info-text {
        font-size: 13px;
        font-weight: 600;
        color: #1e293b;
        word-break: break-word;
        line-height: 1.4;
    }

    .chat-input-area {
        flex-shrink: 0;
        padding: 20px;
        background: white;
        border-top: 1px solid #eee;
    }

    .input-wrapper {
        background: #f1f5f9;
        padding: 8px 15px;
        border-radius: 15px;
        display: flex;
        align-items: center;
    }

    .reply-input {
        border: none;
        background: transparent;
        padding: 8px;
        flex-grow: 1;
        outline: none;
    }

    .btn-send {
        background: var(--primary-color);
        color: white;
        border: none;
        width: 40px;
        height: 40px;
        border-radius: 10px;
        transition: 0.3s;
    }

    .btn-send:hover {
        transform: scale(1.05);
        opacity: 0.9;
    }

    @keyframes iosSlideInRight {
        from {
            opacity: 0;
            transform: translateY(20px) scale(0.9);
        }

        to {
            opacity: 1;
            transform: translateY(0) scale(1);
        }
    }

    @keyframes iosSlideInLeft {
        from {
            opacity: 0;
            transform: translateX(-20px) scale(0.9);
        }

        to {
            opacity: 1;
            transform: translateX(0) scale(1);
        }
    }

    /* Yangi xabarlar uchun klasslar */
    .msg-group.admin.new-anim {
        animation: iosSlideInRight 0.35s cubic-bezier(0.15, 0.85, 0.35, 1.2) both;
    }

    .msg-group:not(.admin).new-anim {
        animation: iosSlideInLeft 0.35s cubic-bezier(0.15, 0.85, 0.35, 1.2) both;
    }

    /* Silliq skroll */
    #chatBox {
        scroll-behavior: smooth;
    }

    .chat-date-separator {
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 25px 0 15px 0;
        position: relative;
        clear: both;
    }

    .chat-date-separator::before {
        content: "";
        position: absolute;
        width: 90%;
        height: 1px;
        background: rgba(0, 0, 0, 0.06);
        z-index: 1;
    }

    .chat-date-separator span {
        background: #f8fafc;
        /* Chat orqa foniga mos rang */
        padding: 5px 15px;
        border-radius: 50px;
        font-size: 11px;
        font-weight: 700;
        color: #64748b;
        position: relative;
        z-index: 2;
        text-transform: uppercase;
        box-shadow: 0 2px 5px rgba(0, 0, 0, 0.03);
        border: 1px solid rgba(0, 0, 0, 0.05);
    }
</style>

<body>
    <?php require "Includes/navbar.php"; ?>

    <div class="container-fluid py-3 px-lg-5">
        <div class="row g-3 mb-2">
            <div class="col-md-4">
                <div class="stat-card">
                    <div class="stat-icon bg-primary text-white"><i class="bi bi-people"></i></div>
                    <div>
                        <h5 class="mb-0 fw-bold"><?= $total_users ?></h5><small class="text-muted">Jami foydalanuvchilar</small>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="stat-card">
                    <div class="stat-icon bg-success text-white"><i class="bi bi-check2-circle"></i></div>
                    <div>
                        <h5 class="mb-0 fw-bold"><?= $answered_users ?></h5><small class="text-muted">Javob berilgan</small>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="stat-card">
                    <div class="stat-icon bg-danger text-white"><i class="bi bi-clock-history"></i></div>
                    <div>
                        <h5 class="mb-0 fw-bold"><?= $waiting_users ?></h5><small class="text-muted">Kutilmoqda</small>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-0 chat-container">
            <div class="col-md-3 user-sidebar">
                <div class="sidebar-header">
                    <div class="position-relative">
                        <i class="bi bi-search position-absolute top-50 start-0 translate-middle-y ms-3 text-muted"></i>
                        <input type="text" id="userSearch" class="form-control ps-5 border-0 bg-light" style="border-radius:12px" placeholder="Qidirish...">
                    </div>
                </div>
                <div class="user-list-wrapper" id="userList">
                    <?php foreach ($users as $u): ?>
                        <a href="?section_id=<?= $section_id ?>&user_id=<?= $u['id'] ?>" class="user-item <?= ($u['id'] == $chat_user_id) ? 'active' : '' ?>">
                            <div class="avatar-circle">
                                <?php if ($u['image']): ?>
                                    <img src="uploads/<?= $u['image'] ?>" onerror="this.parentElement.innerHTML='<?= getInitials($u['fio']) ?>'">
                                <?php else: ?>
                                    <?= getInitials($u['fio']) ?>
                                <?php endif; ?>
                            </div>
                            <div class="ms-3 overflow-hidden flex-grow-1">
                                <h6 class="mb-0 fw-bold text-truncate" style="font-size:0.85rem"><?= htmlspecialchars($u['fio']) ?></h6>
                                <small class="text-muted text-truncate d-block" style="font-size:0.75rem"><?= htmlspecialchars($u['email']) ?></small>
                            </div>
                            <?php if ($u['unread'] > 0): ?>
                                <span class="badge-unread ms-auto"><?= $u['unread'] ?></span>
                            <?php endif; ?>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="col-md-6 d-flex flex-column">
                <div class="chat-header">
                    <h6 class="mb-0 fw-bold"><?= htmlspecialchars($chat_user_data['fio'] ?? 'Suhbatdosh') ?></h6>
                    <small class="text-muted"><?= htmlspecialchars($current_section_name) ?></small>
                </div>

                <div class="chat-messages" id="chatBox">
                    <?php if (empty($messages)): ?>
                        <div class="text-center my-auto text-muted opacity-50">
                            <i class="bi bi-chat-dots fs-1"></i>
                            <p>Suhbatni boshlash uchun foydalanuvchini tanlang</p>
                        </div>
                    <?php endif; ?>

                    <?php
                    $last_date = null; // Oxirgi sanani saqlash uchun

                    // Oylarni o'zbekchaga o'girish uchun massiv
                    $months_uz = [
                        'January' => 'Yanvar',
                        'February' => 'Fevral',
                        'March' => 'Mart',
                        'April' => 'Aprel',
                        'May' => 'May',
                        'June' => 'Iyun',
                        'July' => 'Iyul',
                        'August' => 'Avgust',
                        'September' => 'Sentyabr',
                        'October' => 'Oktyabr',
                        'November' => 'Noyabr',
                        'December' => 'Dekabr'
                    ];

                    foreach ($messages as $msg):
                        // Xabar yozilgan kunni aniqlaymiz (masalan: 2026-04-10)
                        $msg_full_time = strtotime($msg['created_at']);
                        $msg_date = date('Y-m-d', $msg_full_time);

                        // Agar oldingi xabar bilan sanasi farq qilsa, sana blokini chiqaramiz
                        if ($msg_date !== $last_date):
                            $last_date = $msg_date;
                            $today = date('Y-m-d');
                            $yesterday = date('Y-m-d', strtotime('-1 day'));

                            if ($msg_date === $today) {
                                $display_date = "Bugun";
                            } elseif ($msg_date === $yesterday) {
                                $display_date = "Kecha";
                            } else {
                                // Kun va oyni chiqaramiz (masalan: 10-Aprel)
                                $day = date('d', $msg_full_time);
                                $month = $months_uz[date('F', $msg_full_time)];
                                $year = date('Y', $msg_full_time);

                                $display_date = "$day-$month";
                                // Agar yil joriy yildan farqli bo'lsa, yilni ham qo'shamiz
                                if ($year !== date('Y')) {
                                    $display_date .= ", $year-yil";
                                }
                            }
                    ?>
                            <div class="chat-date-separator">
                                <span><?= $display_date ?></span>
                            </div>
                        <?php
                        endif;

                        // Admin yoki User ekanligini aniqlash (sizning kodingizdagi klasslar)
                        $is_adm = !is_null($msg['admin_id']);
                        ?>

                        <div class="msg-group <?= $is_adm ? 'admin' : '' ?>">
                            <div class="msg-bubble <?= $is_adm ? 'msg-admin-bg' : 'msg-user-bg' ?>">
                                <?= nl2br(htmlspecialchars($msg['message'])) ?>

                                <?php if (!empty($msg['attachment'])): ?>
                                    <div class="mt-2 border-top pt-2">
                                        <a href="<?= $msg['attachment'] ?>" target="_blank" class="text-decoration-none small <?= $is_adm ? 'text-white' : 'text-primary' ?> d-flex align-items-center gap-1">
                                            <i class="bi bi-file-earmark-arrow-down-fill"></i> Yuklab olish
                                        </a>
                                    </div>
                                <?php endif; ?>

                                <small class="d-block mt-1 opacity-75" style="font-size:10px">
                                    <?= date('H:i', $msg_full_time) ?>
                                </small>
                            </div>

                            <?php if ($is_adm && isset($section_id)): // Faqat admin o'chirishi uchun 
                            ?>
                                <a href="?delete_id=<?= $msg['id'] ?>&section_id=<?= $section_id ?>&user_id=<?= $chat_user_id ?>"
                                    class="text-danger ms-2 me-2 mb-1" onclick="return confirm('O\'chirish?')">
                                    <i class="bi bi-trash"></i>
                                </a>
                            <?php endif; ?>
                        </div>

                    <?php endforeach; ?>
                </div>

                <div class="chat-input-area">
                    <form method="POST" enctype="multipart/form-data">
                        <div class="input-wrapper">
                            <label class="mb-0 me-2 text-muted" for="fileAttach" style="cursor:pointer" id="fileLabel">
                                <i class="bi bi-paperclip fs-5"></i>
                            </label>
                            <input type="file" name="attachment" id="fileAttach" class="d-none">
                            <input type="text" name="reply" class="reply-input" placeholder="Xabaringizni yozing..." autocomplete="off">
                            <button class="btn-send" type="submit"><i class="bi bi-send-fill"></i></button>
                        </div>
                    </form>
                </div>
            </div>

            <div class="col-md-3">
                <div class="profile-sidebar">
                    <?php if ($chat_user_data): ?>
                        <div class="profile-card">
                            <div class="avatar-circle profile-avatar-big">
                                <?php if ($chat_user_data['image']): ?>
                                    <img src="uploads/<?= $chat_user_data['image'] ?>" onerror="this.parentElement.innerHTML='<?= getInitials($chat_user_data['fio']) ?>'">
                                <?php else: ?>
                                    <?= getInitials($chat_user_data['fio']) ?>
                                <?php endif; ?>
                            </div>
                            <h6 class="fw-bold mb-1 px-2"><?= htmlspecialchars($chat_user_data['fio']) ?></h6>
                            <span class="badge rounded-pill bg-primary bg-opacity-10 text-primary px-3 py-2" style="font-size: 11px;">Foydalanuvchi</span>
                        </div>

                        <div class="profile-info-box">
                            <div class="info-icon"><i class="bi bi-person-badge"></i></div>
                            <div class="info-content">
                                <div class="info-label">FIO</div>
                                <div class="info-text"><?= htmlspecialchars($chat_user_data['fio']) ?></div>
                            </div>
                        </div>

                        <div class="profile-info-box">
                            <div class="info-icon"><i class="bi bi-envelope"></i></div>
                            <div class="info-content">
                                <div class="info-label">Email manzil</div>
                                <div class="info-text"><?= htmlspecialchars($chat_user_data['email']) ?></div>
                            </div>
                        </div>

                        <div class="profile-info-box">
                            <div class="info-icon"><i class="bi bi-hash"></i></div>
                            <div class="info-content">
                                <div class="info-label">Talaba ID</div>
                                <div class="info-text"><?= htmlspecialchars($chat_user_data['talaba_id'] ?: '---') ?></div>
                            </div>
                        </div>

                        <div class="profile-info-box">
                            <div class="info-icon"><i class="bi bi-grid-3x3-gap"></i></div>
                            <div class="info-content">
                                <div class="info-label">Guruh</div>
                                <div class="info-text"><?= htmlspecialchars($chat_user_data['guruh'] ?: '---') ?></div>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="text-center mt-5 text-muted opacity-25">
                            <i class="bi bi-person-x fs-1"></i>
                            <p class="small mt-2">Ma'lumotlar yo'q</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <script>
        // 1. Ovozli obyektlarni e'lon qilish
        const sendSound = new Audio('assets/sounds/sent.mp3');
        const receiveSound = new Audio('assets/sounds/received.mp3');

        // 2. Elementlarni aniqlash
        const chatBox = document.getElementById("chatBox");
        const replyForm = document.querySelector('.chat-input-area form');
        const replyInput = document.querySelector('.reply-input');
        const fileAttach = document.getElementById('fileAttach');
        const userList = document.getElementById('userList');

        // 3. Skrollni doim pastga tushirish
        function scrollToBottom() {
            if (chatBox) {
                chatBox.scrollTo({
                    top: chatBox.scrollHeight,
                    behavior: 'smooth'
                });
            }
        }
        // Sahifa yuklanganda skrollni tushirish
        scrollToBottom();

        // 4. Chat va Sidebar-ni yangilash funksiyasi
        function refreshAdminChat(isAuto = false) {
            fetch(window.location.href, {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(response => response.text())
                .then(html => {
                    const parser = new DOMParser();
                    const doc = parser.parseFromString(html, 'text/html');

                    const newChatHtml = doc.getElementById('chatBox').innerHTML;
                    const newUserListHtml = doc.getElementById('userList').innerHTML;

                    // Chat oynasini tekshirish va yangilash
                    if (chatBox.innerHTML.trim() !== newChatHtml.trim()) {
                        chatBox.innerHTML = newChatHtml;

                        // Agar avtomatik tekshiruvda yangi xabar kelsa (isAuto = true)
                        if (isAuto) {
                            const lastMsg = chatBox.lastElementChild;
                            // Agar oxirgi xabar foydalanuvchidan bo'lsa (admin klassi yo'q bo'lsa)
                            if (lastMsg && !lastMsg.classList.contains('admin')) {
                                lastMsg.classList.add('new-anim'); // iPhone animatsiyasi
                                receiveSound.play().catch(e => console.log("Ovoz bloklandi")); // Kelgan xabar ovozi
                            }
                        }
                        scrollToBottom();
                    }

                    // Foydalanuvchilar ro'yxatini (Sidebar) miltillashsiz yangilash
                    if (userList.innerHTML.trim() !== newUserListHtml.trim()) {
                        userList.innerHTML = newUserListHtml;
                    }
                })
                .catch(err => console.error("Yangilashda xatolik:", err));
        }

        // 5. Formani yuborish (AJAX + Animatsiya + Ovoz)
        if (replyForm) {
            replyForm.addEventListener('submit', function(e) {
                e.preventDefault();

                const text = replyInput.value.trim();
                const hasFile = fileAttach.files.length > 0;

                if (text === "" && !hasFile) return;

                const formData = new FormData(this);

                // --- OPTIMISTIC UI: Admin xabarini darhol chiqarish ---
                const now = new Date();
                const time = now.getHours().toString().padStart(2, '0') + ':' + now.getMinutes().toString().padStart(2, '0');

                const tempMsg = `
                <div class="msg-group admin new-anim">
                    <div class="msg-bubble msg-admin-bg">
                        ${text.replace(/\n/g, '<br>')}
                        ${hasFile ? '<div class="mt-2 small"><i>📎 Fayl yuborilmoqda...</i></div>' : ''}
                        <small class="d-block mt-1 opacity-75" style="font-size:10px">${time}</small>
                    </div>
                </div>`;

                chatBox.insertAdjacentHTML('beforeend', tempMsg);
                scrollToBottom();

                // Yuborish ovozini chalish
                sendSound.play().catch(e => console.log("Ovoz bloklandi"));

                // Inputlarni tozalash
                replyInput.value = '';
                replyInput.style.height = 'auto';
                fileAttach.value = '';
                document.getElementById('fileLabel').innerHTML = '<i class="bi bi-paperclip fs-5"></i>';

                // Serverga yuborish
                fetch(window.location.href, {
                        method: 'POST',
                        body: formData,
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    })
                    .then(response => {
                        if (response.ok) {
                            refreshAdminChat(); // Server bilan sinxronlash
                        }
                    });
            });
        }

        // 6. Tugmalar va Qo'shimcha funksiyalar
        if (replyInput) {
            // Enter bosilganda yuborish (Shift+Enter yangi qator)
            replyInput.addEventListener('keydown', function(e) {
                if (e.key === 'Enter' && !e.shiftKey) {
                    e.preventDefault();
                    replyForm.dispatchEvent(new Event('submit'));
                }
            });

            // Input balandligini matnga qarab o'zgartirish
            replyInput.addEventListener('input', function() {
                this.style.height = 'auto';
                this.style.height = (this.scrollHeight) + 'px';
            });
        }

        // Fayl tanlanganda belgini yashil qilish
        fileAttach.addEventListener('change', function() {
            const label = document.getElementById('fileLabel');
            if (this.files.length > 0) {
                label.innerHTML = '<i class="bi bi-file-earmark-check-fill fs-5 text-success"></i>';
            }
        });

        // Foydalanuvchilarni qidirish
        document.getElementById('userSearch').addEventListener('input', function() {
            let filter = this.value.toLowerCase();
            document.querySelectorAll('#userList .user-item').forEach(function(item) {
                let text = item.textContent.toLowerCase();
                item.style.display = text.includes(filter) ? 'flex' : 'none';
            });
        });

        // 7. AVTOMATIK YANGILASH (Har 3 soniyada)
        setInterval(() => {
            refreshAdminChat(true);
        }, 1000);
    </script>
</body>

</html>
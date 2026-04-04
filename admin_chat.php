<?php
session_start();
require "database.php";

// 1. O'chirish funksiyasi (O'zgarishsiz)
if (isset($_GET['delete_id'])) {
    $delete_id = (int)$_GET['delete_id'];
    $stmt = $pdo->prepare("SELECT * FROM messages WHERE id = ?");
    $stmt->execute([$delete_id]);
    $msg = $stmt->fetch();
    if ($msg) {
        if ($_SESSION['role'] === 'admin' || $msg['user_id'] == $_SESSION['user_id']) {
            $stmt = $pdo->prepare("DELETE FROM messages WHERE id = ?");
            $stmt->execute([$delete_id]);
        }
    }
    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}

// 2. Admin tekshiruvi
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}

$admin_id = $_SESSION['user_id'];

// 3. Bo'limlarni olish
$stmt = $pdo->prepare("SELECT s.* FROM sections s JOIN admin_sections a ON s.id = a.section_id WHERE a.admin_id = ?");
$stmt->execute([$admin_id]);
$sections = $stmt->fetchAll();

if (!$sections) {
    die("Sizga biriktirilgan bo‘lim yo‘q!");
}

$section_id = isset($_GET['section_id']) ? (int)$_GET['section_id'] : (int)$sections[0]['id'];

// 4. Foydalanuvchilarni olish (Statistikadan oldin bo'lishi shart)
$stmt = $pdo->prepare("
    SELECT 
        u.id, u.email, u.fio,
        COUNT(CASE WHEN m.is_read = 0 AND m.admin_id IS NULL THEN 1 END) AS unread
    FROM messages m
    JOIN users u ON m.user_id = u.id
    WHERE m.section_id = ? AND (m.admin_id IS NULL OR m.admin_id = ?)
    GROUP BY u.id
");
$stmt->execute([$section_id, $admin_id]);
$users = $stmt->fetchAll() ?: []; // Agar bo'sh bo'lsa massiv qaytarsin

// STATISTIKA HISOBI
$total_users_count = count($users);
$answered_count = 0;
foreach ($users as $u) {
    if ($u['unread'] == 0) $answered_count++;
}

$chat_user_id = isset($_GET['user_id']) ? (int)$_GET['user_id'] : ($users[0]['id'] ?? 0);

// 5. Xabarlarni o'qilgan qilish va olish mantiqi
if ($chat_user_id > 0) {
    $pdo->prepare("UPDATE messages SET is_read = 1 WHERE section_id = ? AND user_id = ? AND admin_id IS NULL")->execute([$section_id, $chat_user_id]);
}

$stmt = $pdo->prepare("SELECT m.*, u.email AS user_email FROM messages m JOIN users u ON m.user_id = u.id WHERE m.section_id = ? AND m.user_id = ? AND (m.admin_id IS NULL OR m.admin_id = ?) ORDER BY m.created_at ASC");
$stmt->execute([$section_id, $chat_user_id, $admin_id]);
$messages = $stmt->fetchAll();

// 6. Javob yozish
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($chat_user_id > 0)) {
    $reply = htmlspecialchars(trim($_POST['reply'] ?? ''));
    $file_path = null;
    if (isset($_FILES['attachment']) && $_FILES['attachment']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = __DIR__ . '/uploads/';
        if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);
        $filename = time() . '_' . basename($_FILES['attachment']['name']);
        $target_file = $upload_dir . $filename;
        if (move_uploaded_file($_FILES['attachment']['tmp_name'], $target_file)) {
            $file_path = 'uploads/' . $filename;
        }
    }
    if (!empty($reply) || $file_path) {
        $pdo->prepare("INSERT INTO messages (section_id, user_id, admin_id, message, attachment, is_read) VALUES (?, ?, ?, ?, ?, 1)")
            ->execute([$section_id, $chat_user_id, $admin_id, $reply, $file_path]);
    }
    header("Location: admin_chat.php?section_id=$section_id&user_id=$chat_user_id");
    exit;
}

$current_section_name = '';
foreach ($sections as $s) {
    if ($s['id'] == $section_id) {
        $current_section_name = $s['name'];
        break;
    }
}
?>

<?php require "Includes/header.php"; ?>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">

<style>
    body {
        background-color: #f0f2f5;
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    }

    /* Statistika kartochkalari */
    .card-stat {
        border: none;
        border-radius: 12px;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
    }

    .icon-box {
        width: 48px;
        height: 48px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 24px;
    }

    /* Chat struktura */
    /* Chat struktura - Balandlikni to'g'irlash */
    .chat-container {
        height: 70vh;
        /* Balandlikni biroz kamaytirdik yoki o'z holicha qoldiring */
        min-height: 500px;
        /* Minimal balandlik beramiz */
        background: #fff;
        border-radius: 15px;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
        overflow: hidden;
        display: flex;
        /* Flexbox qo'shildi */
    }

    /* Ro'yxat qismi o'zidan ortiqcha joyni scroll qilishi uchun */
    .user-sidebar {
        border-right: 1px solid #eee;
        background: #f8f9fa;
        display: flex;
        flex-direction: column;
        height: 100%;
        /* Konteyner balandligini to'liq egallaydi */
    }

    .chat-main {
        display: flex;
        flex-direction: column;
        background: #fff;
        height: 100%;
        /* Balandlikni qat'iy ushlaydi */
        overflow: hidden;
    }

    /* Xabarlar maydoni o'ziga tegishli joyni egallab, qolganini scroll qiladi */
    .chat-messages {
        flex: 1;
        /* Inputni pastga itaradi */
        padding: 20px;
        overflow-y: auto;
        background-color: #fdfdfd;
    }

    /* Input qismi doim ko'rinib turishi uchun */
    .chat-input-area {
        padding: 15px;
        border-top: 1px solid #eee;
        background: white;
        flex-shrink: 0;
        /* Input qisqarib ketmasligi uchun */
    }

    .user-sidebar {
        border-right: 1px solid #eee;
        background: #f8f9fa;
    }

    .user-item {
        border: none;
        margin-bottom: 2px;
        padding: 15px;
        transition: 0.2s;
        border-radius: 0 !important;
    }

    .user-item:hover {
        background: #e9ecef;
    }

    .user-item.active {
        background: #fff !important;
        border-left: 4px solid #0d6efd !important;
        color: #0d6efd !important;
        font-weight: 600;
    }

    .chat-main {
        display: flex;
        flex-direction: column;
        background: #fff;
    }

    .chat-messages {
        flex-grow: 1;
        padding: 20px;
        overflow-y: auto;
        background-color: #fdfdfd;
    }

    /* Xabar bulutlari */
    .msg-wrapper {
        display: flex;
        margin-bottom: 15px;
        flex-direction: column;
    }

    .msg-bubble {
        max-width: 70%;
        padding: 10px 15px;
        border-radius: 15px;
        position: relative;
        font-size: 14px;
        line-height: 1.5;
    }

    .msg-admin {
        align-self: flex-end;
        background-color: #0d6efd;
        color: #fff;
        border-bottom-right-radius: 2px;
    }

    .msg-user {
        align-self: flex-start;
        background-color: #f0f2f5;
        color: #333;
        border-bottom-left-radius: 2px;
    }

    .msg-info {
        font-size: 10px;
        margin-top: 4px;
        opacity: 0.7;
    }

    .msg-admin .msg-info {
        color: #fff;
        text-align: right;
    }

    /* Input qismi */
    .chat-input-area {
        padding: 15px;
        border-top: 1px solid #eee;
    }

    .search-input {
        background: #eee;
        border: none;
        border-radius: 8px;
    }
</style>

<body>
    <?php require "Includes/yuklash.php"; ?>
    <?php require "Includes/navbar.php"; ?>

    <div class="container-fluid py-4 px-4">

        <div class="row mb-4">
            <div class="col-md-4">
                <div class="card card-stat p-3 mb-3">
                    <div class="d-flex align-items-center">
                        <div class="icon-box bg-primary text-white me-3"><i class="bi bi-people"></i></div>
                        <div>
                            <p class="text-muted mb-0 small">Jami murojaat qilganlar</p>
                            <h4 class="mb-0 fw-bold"><?= $total_users_count ?> ta</h4>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card card-stat p-3 mb-3">
                    <div class="d-flex align-items-center">
                        <div class="icon-box bg-success text-white me-3"><i class="bi bi-check-circle"></i></div>
                        <div>
                            <p class="text-muted mb-0 small">Javob berilgan</p>
                            <h4 class="mb-0 fw-bold"><?= $answered_count ?> ta</h4>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card card-stat p-3 mb-3">
                    <div class="d-flex align-items-center">
                        <div class="icon-box bg-danger text-white me-3"><i class="bi bi-clock-history"></i></div>
                        <div>
                            <p class="text-muted mb-0 small">Kutilmoqda</p>
                            <h4 class="mb-0 fw-bold"><?= $total_users_count - $answered_count ?> ta</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-0 chat-container">
            <div class="col-md-4 col-lg-3 user-sidebar d-flex flex-column">
                <div class="p-3 border-bottom bg-white">
                    <h6 class="fw-bold mb-3">Suhbatlar</h6>
                    <input type="text" id="userSearch" class="form-control search-input shadow-none" placeholder="Ism bo'yicha qidiruv...">
                </div>
                <div class="list-group list-group-flush overflow-auto flex-grow-1">
                    <?php foreach ($users as $u): ?>
                        <a href="?section_id=<?= $section_id ?>&user_id=<?= $u['id'] ?>"
                            class="user-item list-group-item list-group-item-action <?= ($u['id'] == $chat_user_id) ? 'active' : '' ?>">
                            <div class="d-flex justify-content-between align-items-center">
                                <div class="text-truncate me-2"><?= htmlspecialchars($u['fio']) ?></div>
                                <?php if ($u['unread'] > 0): ?>
                                    <span class="badge bg-danger rounded-pill"><?= $u['unread'] ?></span>
                                <?php endif; ?>
                            </div>
                            <small class="opacity-50 d-block"><?= htmlspecialchars($u['email']) ?></small>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="col-md-8 col-lg-9 chat-main">
                <div class="p-3 border-bottom bg-white d-flex justify-content-between align-items-center">
                    <h6 class="mb-0 fw-bold"><?= htmlspecialchars($current_section_name) ?> bo'limi</h6>
                    <small class="text-muted">Admin Panel</small>
                </div>

                <div class="chat-messages" id="chatMessages">
                    <?php foreach ($messages as $msg):
                        $is_admin = !is_null($msg['admin_id']);
                    ?>
                        <div class="msg-wrapper">
                            <div class="msg-bubble <?= $is_admin ? 'msg-admin' : 'msg-user' ?>">
                                <?php if (!$is_admin): ?>
                                    <div class="fw-bold mb-1" style="font-size: 11px; opacity: 0.8;">
                                        <?= htmlspecialchars($msg['user_email']) ?>
                                    </div>
                                <?php endif; ?>

                                <?= nl2br(htmlspecialchars($msg['message'])) ?>

                                <?php if ($msg['attachment']): ?>
                                    <div class="mt-2 pt-2 border-top border-light border-opacity-25">
                                        <a href="<?= $msg['attachment'] ?>" target="_blank" class="btn btn-sm btn-light py-0 px-2" style="font-size: 11px;">
                                            <i class="bi bi-file-earmark"></i> Faylni ko'rish
                                        </a>
                                    </div>
                                <?php endif; ?>

                                <div class="msg-info">
                                    <?= date('H:i', strtotime($msg['created_at'])) ?>
                                    <?php if ($is_admin): ?>
                                        <a href="?delete_id=<?= $msg['id'] ?>" class="text-white ms-2" onclick="return confirm('Xabar o\'chirilsinmi?')">
                                            <i class="bi bi-trash"></i>
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <div class="chat-input-area bg-white">
                    <form method="POST" enctype="multipart/form-data">
                        <div class="input-group">
                            <input type="hidden" name="chat_user_id" value="<?= $chat_user_id ?>">
                            <label class="btn btn-outline-secondary border-0 bg-light" for="fileUp">
                                <i class="bi bi-paperclip"></i>
                                <input type="file" name="attachment" id="fileUp" class="d-none">
                            </label>
                            <input type="text" name="reply" class="form-control border-0 bg-light shadow-none" placeholder="Xabaringizni yozing..." required>
                            <button class="btn btn-primary px-4 shadow-none" type="submit">
                                <i class="bi bi-send"></i>
                        </div>
                </div>
                </form>
            </div>
        </div>
    </div>
    </div>

    <script>
        // Chatni doim eng pastga tushirish
        const objDiv = document.getElementById("chatMessages");
        objDiv.scrollTop = objDiv.scrollHeight;

        // Qidiruv
        document.getElementById('userSearch').addEventListener('keyup', function() {
            let filter = this.value.toLowerCase();
            let items = document.querySelectorAll('.user-item');
            items.forEach(item => {
                let text = item.innerText.toLowerCase();
                item.style.display = text.includes(filter) ? 'block' : 'none';
            });
        });
    </script>
</body>
<?php require "Includes/footer.php"; ?>
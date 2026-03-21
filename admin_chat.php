<?php
session_start();
require "database.php";

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}

$admin_id = $_SESSION['user_id'];

// ================== SECTIONLAR ==================
$stmt = $pdo->prepare("
    SELECT s.* 
    FROM sections s
    JOIN admin_sections a ON s.id = a.section_id
    WHERE a.admin_id = ?
");
$stmt->execute([$admin_id]);
$sections = $stmt->fetchAll();

if (!$sections) {
    die("Sizga biriktirilgan bo‘lim yo‘q!");
}

// Tanlangan section
$section_id = isset($_GET['section_id'])
    ? (int)$_GET['section_id']
    : (int)$sections[0]['id'];

// Tanlangan user
$chat_user_id = isset($_GET['user_id'])
    ? (int)$_GET['user_id']
    : ($users[0]['id'] ?? 0);

// ================== USERLAR (UNREAD COUNT BILAN) ==================
$stmt = $pdo->prepare("
    SELECT 
        u.id, 
        u.email,
        COUNT(CASE WHEN m.is_read = 0 AND m.admin_id IS NULL THEN 1 END) AS unread
    FROM messages m
    JOIN users u ON m.user_id = u.id
    WHERE m.section_id = ?
      AND (m.admin_id IS NULL OR m.admin_id = ?)
    GROUP BY u.id
");
$stmt->execute([$section_id, $admin_id]);
$users = $stmt->fetchAll();

// Tanlangan user
$chat_user_id = isset($_GET['user_id'])
    ? (int)$_GET['user_id']
    : ($users[0]['id'] ?? 0);

// ================== O‘QILDI QILISH ==================
if ($chat_user_id > 0) {
    $pdo->prepare("
        UPDATE messages 
        SET is_read = 1 
        WHERE section_id = ? 
          AND user_id = ? 
          AND admin_id IS NULL
    ")->execute([$section_id, $chat_user_id]);
}

// ================== XABARLAR ==================
$stmt = $pdo->prepare("
    SELECT m.*, u.email AS user_email
    FROM messages m
    JOIN users u ON m.user_id = u.id
    WHERE m.section_id = ? 
      AND m.user_id = ? 
      AND (m.admin_id IS NULL OR m.admin_id = ?)
    ORDER BY m.created_at ASC
");
$stmt->execute([$section_id, $chat_user_id, $admin_id]);
$messages = $stmt->fetchAll();

// ================== JAVOB YOZISH ==================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($chat_user_id > 0)) {

    $reply = trim($_POST['reply'] ?? '');
    $file_path = null;

    // ===================== Faylni saqlash =====================
    if (isset($_FILES['attachment']) && $_FILES['attachment']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = __DIR__ . '/uploads/';
        if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);

        $filename = time() . '_' . basename($_FILES['attachment']['name']);
        $target_file = $upload_dir . $filename;

        $allowed_ext = ['jpg', 'jpeg', 'png', 'pdf', 'doc', 'docx', 'txt', 'zip'];
        $file_ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

        if (in_array($file_ext, $allowed_ext)) {
            if (move_uploaded_file($_FILES['attachment']['tmp_name'], $target_file)) {
                $file_path = 'uploads/' . $filename; // DB ga saqlash
            }
        }
    }

    // ===================== DB ga qo‘shish =====================
    if (!empty($reply) || $file_path) {
        $stmt = $pdo->prepare("
            INSERT INTO messages (section_id, user_id, admin_id, message, attachment, is_read)
            VALUES (?, ?, ?, ?, ?, 1)
        ");
        $stmt->execute([$section_id, $chat_user_id, $admin_id, $reply, $file_path]);
    }

    // Redirect, dublikatni oldini olish
    header("Location: admin_chat.php?section_id=$section_id&user_id=$chat_user_id");
    exit;
}

// Section nomi
$current_section_name = '';
foreach ($sections as $s) {
    if ($s['id'] == $section_id) {
        $current_section_name = $s['name'];
        break;
    }
}
?>

<?php require "Includes/header.php"; ?>
<?php require "Includes/navbar.php"; ?>

<div class="container mt-4 d-flex flex-column flex-lg-row gap-3">

    <!-- CHAP PANEL -->
    <div class="col-12 col-lg-4 col-xl-3 mb-3 mb-lg-0">
        <h5 class="mb-3">Users</h5>

        <div class="list-group shadow-sm rounded-3" style="backdrop-filter: blur(8px); background: rgba(255,0,0,0.1);">
            <?php foreach ($users as $u): ?>
                <a href="admin_chat.php?section_id=<?= $section_id ?>&user_id=<?= $u['id'] ?>"
                    class="list-group-item list-group-item-action d-flex justify-content-between align-items-center 
                   <?= ($u['id'] == $chat_user_id) ? 'active bg-danger text-white' : 'bg-light text-dark' ?> rounded-2 mb-1">

                    <span><?= htmlspecialchars($u['email']) ?></span>

                    <?php if ($u['unread'] > 0): ?>
                        <span class="badge bg-danger rounded-pill px-2 py-1">
                            <?= $u['unread'] ?>
                        </span>
                    <?php endif; ?>

                </a>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- CHAT PANEL -->
    <div class="col-12 col-lg-8 col-xl-9 d-flex flex-column">
        <h5 class="mb-3">Chat - Bo‘lim: <?= htmlspecialchars($current_section_name) ?></h5>

        <div class="chat-box flex-grow-1 p-3 mb-3 rounded-3 shadow-sm overflow-auto" style="
            backdrop-filter: blur(8px); 
            background: rgba(220,0,0,0.1); min-height: 400px;">

            <?php foreach ($messages as $msg): ?>

                <?php
                $is_admin = $msg['admin_id'] == $admin_id;
                $bubble_bg = $is_admin
                    ? 'rgba(220,0,0,0.7)'
                    : 'rgba(220,0,0,0.5)';
                ?>

                <div class="d-flex <?= $is_admin ? 'justify-content-end' : 'justify-content-start' ?> mb-2">
                    <div class="p-2 rounded-3 text-white" style="
                        max-width:70%;
                        background: <?= $bubble_bg ?>;
                        backdrop-filter: blur(6px);
                        box-shadow: 0 2px 10px rgba(0,0,0,0.15);">

                        <?php if (!$is_admin): ?>
                            <strong><?= substr($msg['user_email'], 0, 5) ?>:</strong>
                        <?php endif; ?>

                        <?= htmlspecialchars($msg['message']) ?>

                        <?php if ($msg['attachment']): ?>
                            <div class="mt-1">
                                <a href="<?= htmlspecialchars($msg['attachment']) ?>" target="_blank" class="btn btn-sm btn-outline-light">
                                    Yuklangan fayl
                                </a>
                            </div>
                        <?php endif; ?>

                        <div class="text-end small opacity-75"><?= $msg['created_at'] ?></div>
                    </div>
                </div>

            <?php endforeach; ?>
        </div>

        <!-- JAVOB FORM -->
        <form method="POST" enctype="multipart/form-data" class="mt-auto">
            <div class="input-group shadow-sm rounded-3" style="backdrop-filter: blur(8px); background: rgba(255,0,0,0.1);">
                 <input type="hidden" name="chat_user_id" value="<?= $chat_user_id ?>">
                <input type="text" name="reply" class="form-control border-0" placeholder="Javob yozing...">
                <input type="file" name="attachment" class="form-control border-0">
                <button class="btn btn-danger" type="submit">Yuborish</button>
            </div>
        </form>
    </div>

</div>

<script>
    // Auto scroll oxirgi xabarga
    var chatBox = document.querySelector('.chat-box');
    if (chatBox) chatBox.scrollTop = chatBox.scrollHeight;
</script>

<?php require "Includes/footer.php"; ?>
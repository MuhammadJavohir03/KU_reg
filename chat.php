<?php
// PHP mantiqiy qismi o'zgarishsiz qoladi
session_start();
require "database.php";

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

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$role = $_SESSION['role'];

if ($role === 'admin') {
    header("Location: admin_chat.php");
    exit;
}

$sections = $pdo->query("SELECT * FROM sections ORDER BY name")->fetchAll();
if (!$sections) die("Bo‘limlar mavjud emas!");

$section_id = isset($_GET['section_id']) ? (int)$_GET['section_id'] : (int)$sections[0]['id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $message = htmlspecialchars(trim($_POST['message'] ?? ''));
    $file_path = null;

    if (isset($_FILES['attachment']) && $_FILES['attachment']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = __DIR__ . '/uploads/';
        if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);
        $filename = time() . '_' . basename($_FILES['attachment']['name']);
        $target_file = $upload_dir . $filename;
        $allowed_ext = ['jpg', 'jpeg', 'png', 'pdf', 'doc', 'docx', 'txt', 'zip'];
        $file_ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        if (in_array($file_ext, $allowed_ext)) {
            if (move_uploaded_file($_FILES['attachment']['tmp_name'], $target_file)) {
                $file_path = 'uploads/' . $filename;
            }
        }
    }

    if (!empty($message) || $file_path) {
        $stmt = $pdo->prepare("INSERT INTO messages (section_id, user_id, message, attachment, is_read) VALUES (?, ?, ?, ?, 0)");
        $stmt->execute([$section_id, $user_id, $message, $file_path]);
    }
    header("Location: chat.php?section_id=$section_id");
    exit;
}

$stmt = $pdo->prepare("
    SELECT m.*, u.email AS user_email, a.email AS admin_email
    FROM messages m
    LEFT JOIN users u ON m.user_id = u.id
    LEFT JOIN users a ON m.admin_id = a.id
    WHERE m.section_id = ? AND m.user_id = ?
    ORDER BY m.created_at ASC
");
$stmt->execute([$section_id, $user_id]);
$messages = $stmt->fetchAll();
?>

<?php require "Includes/header.php"; ?>

<style>
    :root {
        --user-bubble: #dcf8c6;
        --admin-bubble: #ffffff;
        --chat-bg: #e5ddd5;
        --accent: #25d366;
    }

    body {
        background: #f0f2f5;
        font-family: 'Inter', sans-serif;
    }

    .chat-container {
        max-width: 900px;
        margin: 20px auto;
        background: white;
        border-radius: 15px;
        overflow: hidden;
        box-shadow: 0 10px 25px rgba(0,0,0,0.1);
        display: flex;
        flex-direction: column;
        height: 85vh;
    }

    .chat-header {
        padding: 15px 25px;
        background: #075e54;
        color: white;
        display: flex;
        align-items: center;
        justify-content: space-between;
    }

    .chat-box {
        flex: 1;
        padding: 20px;
        background-color: var(--chat-bg);
        background-image: url('https://user-images.githubusercontent.com/15075759/28719144-86dc0f70-73b1-11e7-911d-60d70fcded21.png'); /* WhatsApp pattern */
        overflow-y: auto;
        display: flex;
        flex-direction: column;
        gap: 10px;
    }

    .message {
        max-width: 75%;
        padding: 8px 12px;
        border-radius: 10px;
        position: relative;
        font-size: 15px;
        box-shadow: 0 1px 2px rgba(0,0,0,0.1);
    }

    .message.user {
        align-self: flex-end;
        background: var(--user-bubble);
        border-top-right-radius: 2px;
    }

    .message.admin {
        align-self: flex-start;
        background: var(--admin-bubble);
        border-top-left-radius: 2px;
    }

    .msg-info {
        font-size: 11px;
        color: #667781;
        margin-top: 4px;
        text-align: right;
        display: block;
    }

    .attachment-box {
        display: block;
        background: rgba(0,0,0,0.05);
        padding: 8px;
        border-radius: 8px;
        margin-top: 5px;
        text-decoration: none;
        color: #056162;
        font-weight: 500;
        font-size: 13px;
    }

    .chat-footer {
        padding: 15px;
        background: #f0f2f5;
        border-top: 1px solid #ddd;
    }

    .input-area {
        display: flex;
        gap: 10px;
        align-items: flex-end;
    }

    .message-input {
        border-radius: 25px !important;
        border: none !important;
        padding: 12px 20px !important;
        resize: none;
        box-shadow: 0 1px 3px rgba(0,0,0,0.1);
    }

    .file-input-label {
        background: #e9edef;
        padding: 10px;
        border-radius: 50%;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: 0.3s;
    }

    .btn-send {
        background: #00a884;
        color: white;
        border-radius: 50%;
        width: 45px;
        height: 45px;
        display: flex;
        align-items: center;
        justify-content: center;
        border: none;
        box-shadow: 0 1px 3px rgba(0,0,0,0.2);
    }

    @media (max-width: 768px) {
        .chat-container { margin: 0; height: 92vh; border-radius: 0; }
        .message { max-width: 85%; }
    }
</style>

<body>
    <?php require "Includes/navbar.php"; ?>
    <canvas class="z-n1" id="bg"></canvas>

    <div class="container py-3">
        <div class="chat-container">
            <div class="chat-header">
                <div>
                    <h6 class="mb-0">Muloqot markazi</h6>
                    <small style="opacity: 0.8;">Sizga yordam berishdan mamnunmiz</small>
                </div>
                <select class="form-select form-select-sm w-auto" onchange="window.location.href='chat.php?section_id='+this.value;">
                    <?php foreach ($sections as $sec): ?>
                        <option value="<?= $sec['id'] ?>" <?= ($sec['id'] == $section_id) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($sec['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="chat-box" id="chatBox">
                <?php if (!empty($messages)): ?>
                    <?php foreach ($messages as $msg): ?>
                        <?php $is_admin = !is_null($msg['admin_id']); ?>
                        <div class="message <?= $is_admin ? 'admin' : 'user' ?>">
                            <?php if ($is_admin): ?>
                                <strong style="color: #075e54; font-size: 12px; display: block; margin-bottom: 2px;">
                                    Admin (<?= htmlspecialchars(substr($msg['admin_email'], 0, 5)) ?>)
                                </strong>
                            <?php endif; ?>

                            <div class="msg-text"><?= nl2br(htmlspecialchars($msg['message'])) ?></div>

                            <?php if ($msg['attachment']): ?>
                                <a href="<?= htmlspecialchars($msg['attachment']) ?>" target="_blank" class="attachment-box">
                                    📎 Faylni ko'rish
                                </a>
                            <?php endif; ?>

                            <span class="msg-info">
                                <?= date('H:i', strtotime($msg['created_at'])) ?>
                            </span>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="text-center mt-5 text-muted">
                        <p>Hali xabarlar mavjud emas. Birinchi xabarni yuboring!</p>
                    </div>
                <?php endif; ?>
            </div>

            <div class="chat-footer">
                <form method="POST" enctype="multipart/form-data" id="chatForm">
                    <div class="input-area">
                        <label class="file-input-label" title="Fayl biriktirish">
                            <input type="file" name="attachment" style="display: none;">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="#667781" class="bi bi-paperclip" viewBox="0 0 16 16">
                                <path d="M4.5 3a2.5 2.5 0 0 1 5 0v9a1.5 1.5 0 0 1-3 0V5a.5.5 0 0 1 1 0v7a.5.5 0 0 0 1 0V3a1.5 1.5 0 1 0-3 0v9a2.5 2.5 0 0 0 5 0V5a.5.5 0 0 1 1 0v7a3.5 3.5 0 1 1-7 0z"/>
                            </svg>
                        </label>
                        <textarea name="message" class="form-control message-input" placeholder="Xabar yozing..." rows="1"></textarea>
                        <button class="btn-send" type="submit">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" class="bi bi-send-fill" viewBox="0 0 16 16">
                                <path d="M15.964.686a.5.5 0 0 0-.65-.65L.767 5.855H.766l-.452.18a.5.5 0 0 0-.082.887l.41.26.001.002 4.995 3.178 3.178 4.995.002.001.26.41a.5.5 0 0 0 .886-.083zm-1.833 1.89L6.637 10.07l-.215-.338a.5.5 0 0 0-.154-.154l-.338-.215 7.494-7.494 1.178-.471z"/>
                            </svg>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        // Avtomatik pastga tushirish
        const chatBox = document.getElementById('chatBox');
        chatBox.scrollTop = chatBox.scrollHeight;

        // Fayl tanlanganda rangini o'zgartirish
        document.querySelector('input[name="attachment"]').addEventListener('change', function() {
            if (this.files.length > 0) {
                document.querySelector('.file-input-label').style.background = '#25d366';
                document.querySelector('.file-input-label svg').setAttribute('fill', 'white');
            }
        });
    </script>
</body>
<?php require "Includes/footer.php"; ?>
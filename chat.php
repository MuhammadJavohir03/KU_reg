<?php
session_start();
require "database.php";

// 1. Xabarni o'chirish mantiqi
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
    // Mobil brauzerlar uchun to'liq URL bilan redirection
    $redirect_url = "chat.php" . (isset($_GET['section_id']) ? "?section_id=" . (int)$_GET['section_id'] : "");
    header("Location: " . $redirect_url);
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

// Bo'limlarni olish
$sections = $pdo->query("SELECT * FROM sections ORDER BY name")->fetchAll();
if (!$sections) die("Bo‘limlar mavjud emas!");

$section_id = isset($_GET['section_id']) ? (int)$_GET['section_id'] : null;

// 2. Xabar yuborish mantiqi
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $section_id) {
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

// 3. Xabarlarni olish
$messages = [];
if ($section_id) {
    $stmt = $pdo->prepare("
        SELECT m.*, u.email AS user_email, a.email AS admin_email, a.fio AS admin_name, a.role AS admin_role
        FROM messages m
        LEFT JOIN users u ON m.user_id = u.id
        LEFT JOIN users a ON m.admin_id = a.id
        WHERE m.section_id = ? AND m.user_id = ?
        ORDER BY m.created_at ASC
    ");
    $stmt->execute([$section_id, $user_id]);
    $messages = $stmt->fetchAll();

    $update_stmt = $pdo->prepare("UPDATE messages SET is_read = 1 WHERE section_id = ? AND user_id = ? AND admin_id IS NOT NULL AND is_read = 0");
    $update_stmt->execute([$section_id, $user_id]);
    
    $stmt_partner = $pdo->prepare("SELECT fio, email, role FROM users WHERE role = 'admin' LIMIT 1");
    $stmt_partner->execute();
    $partner = $stmt_partner->fetch();
}
?>

<?php require "Includes/header.php"; ?>
<?php require "atmosphere.php"; ?>

<style>
    :root {
        --user-bubble: #dcf8c6;
        --admin-bubble: #ffffff;
        --chat-bg: #e5ddd5;
        --accent: #00a884;
        --sidebar-bg: #ffffff;
        --text-main: #111b21;
        --text-muted: #667781;
    }

    body { background: #f0f2f5; font-family: 'Inter', sans-serif; height: 100dvh; overflow: hidden; margin: 0; }
    .chat-container { max-width: 1400px; margin: 20px auto; background: white; border-radius: 0; display: flex; height: calc(100dvh - 100px); box-shadow: 0 2px 5px rgba(0,0,0,0.1); position: relative; }
    .chat-sidebar { width: 350px; border-right: 1px solid #ddd; display: flex; flex-direction: column; background: var(--sidebar-bg); }
    .sidebar-header { padding: 15px 20px; background: #008069; color: white; font-weight: bold; font-size: 18px; }
    .section-list { overflow-y: auto; flex: 1; }
    .section-item { display: flex; justify-content: space-between; align-items: center; padding: 12px 15px; border-bottom: 1px solid #f0f2f5; text-decoration: none !important; color: var(--text-main); transition: 0.2s; }
    .section-item:hover { background: #f5f6f7; }
    .section-item.active { background-color: #f0f2f5 !important; border-left: 4px solid #00a884; }
    .unread-badge { background: #25d366; color: white; font-size: 11px; padding: 2px 7px; border-radius: 10px; font-weight: bold; min-width: 20px; text-align: center; display: inline-block; }
    .last-msg-text { font-size: 13px; color: var(--text-muted); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 200px; display: block; }
    .chat-main { flex: 1; display: flex; flex-direction: column; background: var(--chat-bg); position: relative; }
    .chat-header { padding: 10px 20px; background: #008069; color: white; display: flex; align-items: center; gap: 15px; flex-shrink: 0; cursor: pointer; }
    .chat-box { flex: 1; padding: 20px; overflow-y: auto; display: flex; flex-direction: column; gap: 10px; background-image: url('https://user-images.githubusercontent.com/15075759/28719144-86dc0f70-73b1-11e7-911d-60d70fcded21.png'); }
    .message { max-width: 70%; padding: 8px 12px; border-radius: 8px; position: relative; font-size: 14.5px; box-shadow: 0 1px 1px rgba(0,0,0,0.1); }
    .message.user { align-self: flex-end; background: var(--user-bubble); border-top-right-radius: 2px; }
    .message.admin { align-self: flex-start; background: var(--admin-bubble); border-top-left-radius: 2px; }
    .msg-info { font-size: 10px; color: var(--text-muted); display: block; text-align: right; margin-top: 4px; }
    .chat-footer { padding: 10px 15px; background: #f0f2f5; flex-shrink: 0; }
    .input-area { display: flex; gap: 10px; align-items: center; background: white; padding: 5px 15px; border-radius: 25px; }
    .message-input { border: none !important; box-shadow: none !important; padding: 8px 0; resize: none; overflow-y: hidden; }
    .btn-send { color: #00a884; border: none; background: none; transition: 0.2s; }

    .info-sidebar { width: 300px; background: #ffffff; border-left: 1px solid #ddd; display: flex; flex-direction: column; padding: 20px; text-align: center; transition: 0.3s ease; }
    .info-sidebar .avatar { width: 100px; height: 100px; background: #e5ddd5; border-radius: 50%; margin: 0 auto 15px; display: flex; align-items: center; justify-content: center; font-size: 40px; color: #008069; }
    .info-label { font-size: 12px; color: var(--text-muted); text-transform: uppercase; margin-top: 15px; margin-bottom: 2px; text-align: left; }
    .info-value { font-size: 15px; color: var(--text-main); font-weight: 500; text-align: left; border-bottom: 1px solid #f0f2f5; padding-bottom: 5px; }
    .close-info { display: none; margin-bottom: 15px; text-align: right; cursor: pointer; color: var(--text-muted); }

    @media (max-width: 992px) {
        .info-sidebar { 
            position: absolute; right: -100%; top: 0; height: 100%; z-index: 1000; box-shadow: -5px 0 15px rgba(0,0,0,0.1); width: 280px;
        }
        .info-sidebar.show { right: 0; }
        .close-info { display: block; }
    }

    @media (max-width: 768px) {
        .chat-container { margin: 0; height: 100dvh; }
        .chat-sidebar { width: 100%; display: <?= $section_id ? 'none' : 'flex' ?>; }
        .chat-main { display: <?= $section_id ? 'flex' : 'none' ?>; height: 100dvh; }
        body { padding-top: 0 !important; }
    }
</style>

<body>
    <?php require "Includes/navbar.php"; ?>

    <div class="chat-container">
        <div class="chat-sidebar">
            <div class="sidebar-header"><h1 class="h5 ms-4">Muloqotlar</h1></div>
            <div class="section-list">
                <?php foreach ($sections as $sec): 
                    $stmt_last = $pdo->prepare("SELECT message, created_at FROM messages WHERE section_id = ? AND user_id = ? ORDER BY created_at DESC LIMIT 1");
                    $stmt_last->execute([$sec['id'], $user_id]);
                    $last_msg = $stmt_last->fetch();

                    $stmt_count = $pdo->prepare("SELECT COUNT(*) FROM messages WHERE section_id = ? AND user_id = ? AND admin_id IS NOT NULL AND is_read = 0");
                    $stmt_count->execute([$sec['id'], $user_id]);
                    $unread = $stmt_count->fetchColumn();
                ?>
                    <a href="chat.php?section_id=<?= $sec['id'] ?>" class="section-item <?= ($sec['id'] == $section_id) ? 'active' : '' ?>">
                        <div style="flex: 1; min-width: 0;">
                            <div class="fw-bold text-truncate"><?= htmlspecialchars($sec['name']) ?></div>
                            <small class="last-msg-text"><?= $last_msg ? ($last_msg['message'] ?: "📎 Fayl") : "Xabarlar yo'q" ?></small>
                        </div>
                        <div style="text-align: right; margin-left: 10px;">
                            <div style="font-size: 11px; color: var(--text-muted);"><?= $last_msg ? date('H:i', strtotime($last_msg['created_at'])) : '' ?></div>
                            <?php if ($unread > 0): ?><span class="unread-badge"><?= $unread ?></span><?php endif; ?>
                        </div>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="chat-main">
            <?php if ($section_id): ?>
                <div class="chat-header" id="chatHeader">
                    <a href="chat.php" class="text-white ms-2 me-3 d-md-none">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor" viewBox="0 0 16 16"><path fill-rule="evenodd" d="M15 8a.5.5 0 0 0-.5-.5H2.707l3.147-3.146a.5.5 0 1 0-.708-.708l-4 4a.5.5 0 0 0 0 .708l4 4a.5.5 0 0 0 .708-.708L2.707 8.5H14.5A.5.5 0 0 0 15 8z"/></svg>
                    </a>
                    <div style="flex: 1;">
                        <h6 class="mb-0"><?php foreach($sections as $s) if($s['id'] == $section_id) echo htmlspecialchars($s['name']); ?></h6>
                        <small style="opacity: 0.8;">Batafsil ko'rish uchun bosing</small>
                    </div>
                </div>

                <div class="chat-box" id="chatBox">
                    <?php foreach ($messages as $msg): 
                        $is_admin = !is_null($msg['admin_id']); ?>
                        <div class="message <?= $is_admin ? 'admin' : 'user' ?>">
                            <?php if($is_admin): ?><strong style="color: #008069; font-size: 12px; display: block;">Admin</strong><?php endif; ?>
                            <div class="msg-text"><?= nl2br(htmlspecialchars($msg['message'])) ?></div>
                            <?php if ($msg['attachment']): ?>
                                <a href="<?= htmlspecialchars($msg['attachment']) ?>" target="_blank" style="display:block; background:rgba(0,0,0,0.05); padding:5px; border-radius:5px; margin-top:5px; text-decoration:none; color:#008069; font-size:13px;">📎 Faylni ko'rish</a>
                            <?php endif; ?>
                            <span class="msg-info"><?= date('H:i', strtotime($msg['created_at'])) ?></span>
                        </div>
                    <?php endforeach; ?>
                </div>

                <div class="chat-footer">
                    <form method="POST" enctype="multipart/form-data" id="chatForm">
                        <div class="input-area">
                            <label style="cursor:pointer; margin-bottom:0;">
                                <input type="file" name="attachment" style="display: none;" onchange="if(this.value) alert('Fayl tanlandi: ' + this.value.split('\\').pop())">
                                <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" fill="#667781" viewBox="0 0 16 16"><path d="M4.5 3a2.5 2.5 0 0 1 5 0v9a1.5 1.5 0 0 1-3 0V5a.5.5 0 0 1 1 0v7a.5.5 0 0 0 1 0V3a1.5 1.5 0 1 0-3 0v9a2.5 2.5 0 0 0 5 0V5a.5.5 0 0 1 1 0v7a3.5 3.5 0 1 1-7 0z"/></svg>
                            </label>
                            <textarea name="message" id="messageInput" class="form-control message-input" placeholder="Xabar yozing..." rows="1"></textarea>
                            <button class="btn-send" type="submit">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor" viewBox="0 0 16 16"><path d="M15.964.686a.5.5 0 0 0-.65-.65L.767 5.855H.766l-.452.18a.5.5 0 0 0-.082.887l.41.26.001.002 4.995 3.178 3.178 4.995.002.001.26.41a.5.5 0 0 0 .886-.083zm-1.833 1.89L6.637 10.07l-.215-.338a.5.5 0 0 0-.154-.154l-.338-.215 7.494-7.494 1.178-.471z"/></svg>
                            </button>
                        </div>
                    </form>
                </div>
            <?php else: ?>
                <div class="h-100 d-flex flex-column align-items-center justify-content-center text-muted">
                    <img src="https://cdn-icons-png.flaticon.com/512/1041/1041916.png" width="80" style="opacity: 0.2; margin-bottom: 20px;">
                    <p>Xabarlarni ko'rish uchun bo'limni tanlang</p>
                </div>
            <?php endif; ?>
        </div>

        <?php if ($section_id && isset($partner)): ?>
        <div class="info-sidebar" id="infoSidebar">
            <div class="close-info" id="closeInfo">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor" viewBox="0 0 16 16"><path d="M4.646 4.646a.5.5 0 0 1 .708 0L8 7.293l2.646-2.647a.5.5 0 0 1 .708.708L8.707 8l2.647 2.646a.5.5 0 0 1-.708.708L8 8.707l-2.646 2.647a.5.5 0 0 1-.708-.708L7.293 8 4.646 5.354a.5.5 0 0 1 0-.708z"/></svg>
            </div>
            <div class="avatar">
                <svg xmlns="http://www.w3.org/2000/svg" width="50" height="50" fill="currentColor" viewBox="0 0 16 16"><path d="M11 6a3 3 0 1 1-6 0 3 3 0 0 1 6 0zM0 8a8 8 0 1 1 16 0A8 8 0 0 1 0 8zm8-7a7 7 0 0 0-5.468 11.37C3.242 11.226 4.805 10 8 10s4.757 1.225 5.468 2.37A7 7 0 0 0 8 1z"/></svg>
            </div>
            <h5 class="mb-0"><?= htmlspecialchars($partner['fio'] ?? 'Adminstrator') ?></h5>
            <span class="badge bg-success mx-auto mt-1" style="width: fit-content;">Online</span>

            <div class="info-label">Roli</div>
            <div class="info-value"><?= ucfirst(htmlspecialchars($partner['role'])) ?></div>

            <div class="info-label">F.I.O</div>
            <div class="info-value"><?= htmlspecialchars($partner['fio'] ?? 'Ko\'rsatilmagan') ?></div>

            <div class="info-label">Email Manzili</div>
            <div class="info-value" style="font-size: 13px; word-break: break-all;"><?= htmlspecialchars($partner['email']) ?></div>
            
            <div class="mt-4 p-2 bg-light rounded" style="font-size: 12px; color: var(--text-muted);">
                Ushbu bo'lim yuzasidan barcha so'rovlaringizga mutaxassis tez orada javob beradi.
            </div>
        </div>
        <?php endif; ?>
    </div>

    <script>
        const chatBox = document.getElementById('chatBox');
        const messageInput = document.getElementById('messageInput');
        const chatForm = document.getElementById('chatForm');
        const chatHeader = document.getElementById('chatHeader');
        const infoSidebar = document.getElementById('infoSidebar');
        const closeInfo = document.getElementById('closeInfo');

        if (chatHeader && infoSidebar) {
            chatHeader.addEventListener('click', (e) => {
                if(e.target.closest('a')) return;
                infoSidebar.classList.add('show');
            });
        }
        if (closeInfo && infoSidebar) {
            closeInfo.addEventListener('click', () => {
                infoSidebar.classList.remove('show');
            });
        }

        if (chatBox) chatBox.scrollTop = chatBox.scrollHeight;

        if (messageInput) {
            messageInput.addEventListener('keydown', function(e) {
                if (e.key === 'Enter' && !e.shiftKey) {
                    e.preventDefault();
                    if (this.value.trim() !== "" || document.querySelector('input[name="attachment"]').value !== "") {
                        chatForm.submit();
                    }
                }
            });

            messageInput.addEventListener('input', function() {
                this.style.height = 'auto';
                this.style.height = (this.scrollHeight) + 'px';
            });
        }

        // Mobil rejimi uchun optimizatsiya
        const handleResize = () => {
            if (chatBox) chatBox.scrollTop = chatBox.scrollHeight;
        };

        window.addEventListener('resize', handleResize);
        if (window.visualViewport) {
            window.visualViewport.addEventListener('resize', handleResize);
        }
    </script>
</body>
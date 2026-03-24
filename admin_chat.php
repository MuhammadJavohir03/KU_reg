<?php
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

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}

$admin_id = $_SESSION['user_id'];

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

$section_id = isset($_GET['section_id'])
    ? (int)$_GET['section_id']
    : (int)$sections[0]['id'];

$chat_user_id = isset($_GET['user_id'])
    ? (int)$_GET['user_id']
    : ($users[0]['id'] ?? 0);

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
$stmt->execute([$section_id, $admin_id]);
$users = $stmt->fetchAll();

$chat_user_id = isset($_GET['user_id'])
    ? (int)$_GET['user_id']
    : ($users[0]['id'] ?? 0);

if ($chat_user_id > 0) {
    $pdo->prepare("
        UPDATE messages 
        SET is_read = 1 
        WHERE section_id = ? 
          AND user_id = ? 
          AND admin_id IS NULL
    ")->execute([$section_id, $chat_user_id]);
}

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

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($chat_user_id > 0)) {

    $reply = trim($_POST['reply'] ?? '');
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
                $file_path = 'uploads/' . $filename; // DB ga saqlash
            }
        }
    }

    if (!empty($reply) || $file_path) {
        $stmt = $pdo->prepare("
            INSERT INTO messages (section_id, user_id, admin_id, message, attachment, is_read)
            VALUES (?, ?, ?, ?, ?, 1)
        ");
        $stmt->execute([$section_id, $chat_user_id, $admin_id, $reply, $file_path]);
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
<?php require "Includes/navbar.php"; ?>

<body class="gradient-custom">

    <div class="container py-5">
        <div class="row">

            <!-- USER PANEL -->
            <div class="col-12 col-lg-4 col-xl-3 mb-3 mb-lg-0">
                <h5 class="mb-3">Users</h5>
                <input type="text" id="userSearch" class="form-control mb-2 border" placeholder="Foydalanuvchini qidirish...">
                <script>
                    document.getElementById('userSearch').addEventListener('keyup', function() {
                        let search = this.value.toLowerCase();
                        let users = document.querySelectorAll('.user-item');

                        users.forEach(function(user) {
                            let name = user.textContent.toLowerCase();

                            if (name.includes(search)) {
                                user.style.display = 'block';
                            } else {
                                user.style.display = 'none';
                            }
                        });
                    });
                </script>
                <div class="list-group shadow rounded-3 p-3" style="background: rgba(255, 255, 255, 0.5); min-height: 700px;">

                    <?php foreach ($users as $u): ?>
                        <div class="user-item">
                            <a href="admin_chat.php?section_id=<?= $section_id ?>&user_id=<?= $u['id'] ?>"
                                style="color: white; background: rgba(131, 56, 236);"
                                class="copy-text border-white list-group-item list-group-item-action d-flex justify-content-between align-items-center 
                                <?= ($u['id'] == $chat_user_id) ? 'active' : 'bg-light text-dark shadow' ?> rounded-2 shadow-sm mb-1">

                                <span><?= htmlspecialchars($u['fio']) ?></span>

                                <?php if ($u['unread'] > 0): ?>
                                    <span class="badge bg-danger rounded-pill px-2 py-1">
                                        <?= $u['unread'] ?>
                                    </span>
                                <?php endif; ?>

                            </a>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- CHAT PANEL -->
            <div class="col-12 col-lg-8 col-xl-9 d-flex flex-column">
                <h5 class="mb-3">Chat - Bo‘lim: <?= htmlspecialchars($current_section_name) ?></h5>

                <div class="chat-box flex-grow-1 p-3 mb-3 rounded-3 shadow overflow-auto" style="
                        backdrop-filter: blur(8px); 
                        background: rgba(255, 255, 255, 0.8); 
                        min-height:550px;">

                    <?php foreach ($messages as $msg): ?>

                        <?php
                        $is_admin = $msg['admin_id'] == $admin_id;
                        $bubble_bg = $is_admin
                            ? 'rgba(131, 56, 236)'
                            : 'rgba(251, 86, 7, 0.7)';
                        ?>

                        <div class="d-flex <?= $is_admin ? 'justify-content-end' : 'justify-content-start' ?> mb-2">
                            <div class="border shadow p-2 rounded-3 text-white position-relative shadow-sm" style="
                                max-width:70%;
                                background: <?= $bubble_bg ?>;
                                backdrop-filter: blur(6px);">

                                <?php if (!$is_admin): ?>
                                    <strong><?= substr($msg['user_email'], 0, 5) ?>:</strong>
                                <?php endif; ?>

                                <?= htmlspecialchars($msg['message']) ?>

                                <?php if ($msg['attachment']): ?>
                                    <div class="mt-1">
                                        <a href="<?= htmlspecialchars($msg['attachment']) ?>" target="_blank" class="text-primary btn btn-sm btn-outline-primary">
                                            Yuklangan fayl
                                        </a>
                                    </div>
                                <?php endif; ?>

                                <?php if ($_SESSION['role'] === 'admin' || $msg['user_id'] == $_SESSION['user_id']): ?>
                                    <a href="?delete_id=<?= $msg['id'] ?>"
                                        class="m-2 btn btn-sm btn-light text-danger"
                                        style="top:5px; right:5px; padding:2px 6px;"
                                        onclick="return confirm('O‘chirilsinmi?')">
                                        -Olish
                                    </a>
                                <?php endif; ?>

                                <div class="text-end small opacity-75"><?= $msg['created_at'] ?></div>
                            </div>
                        </div>

                    <?php endforeach; ?>
                </div>

                <form method="POST" enctype="multipart/form-data" class="mt-auto">
                    <div class="input-group shadow-sm rounded-3" style="backdrop-filter: blur(8px); background: rgba(255,0,0,0.1);">
                        <input type="hidden" name="chat_user_id" value="<?= $chat_user_id ?>">
                        <input type="text" name="reply" class="form-control border-0" placeholder="Javob yozing...">
                        <input type="file" name="attachment" class="form-control border-0">
                        <button class="btn border-white" style=" background: rgba(255, 255, 255, 0.51);" type="submit">Yuborish</button>
                    </div>
                </form>
            </div>

        </div>

        <script>
            var chatBox = document.querySelector('.chat-box');
            if (chatBox) chatBox.scrollTop = chatBox.scrollHeight;
        </script>

</body>

<?php require "Includes/footer.php"; ?>
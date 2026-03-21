<?php
session_start();
require "database.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$role = $_SESSION['role'];

// Admin bo‘lsa admin_chat.php ga yo‘naltirish
if ($role === 'admin') {
    header("Location: admin_chat.php");
    exit;
}

// Bo‘limlarni olish
$sections = $pdo->query("SELECT * FROM sections ORDER BY name")->fetchAll();

if (!$sections) {
    die("Bo‘limlar mavjud emas!");
}

// Tanlangan section
$section_id = isset($_GET['section_id'])
    ? (int)$_GET['section_id']
    : (int)$sections[0]['id'];

// ================== XABAR YUBORISH ==================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $message = trim($_POST['message'] ?? '');
    $file_path = null;

    // Fayl upload
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
        $stmt = $pdo->prepare("
            INSERT INTO messages (section_id, user_id, message, attachment, is_read)
            VALUES (?, ?, ?, ?, 0)
        ");
        $stmt->execute([$section_id, $user_id, $message, $file_path]);
    }

    // Redirect POSTdan keyin (refresh dublikatni oldini oladi)
    header("Location: chat.php?section_id=$section_id");
    exit;
}

// ================== XABARLARNI OLISH ==================
$stmt = $pdo->prepare("
    SELECT m.*, u.email AS user_email, a.email AS admin_email
    FROM messages m
    LEFT JOIN users u ON m.user_id = u.id
    LEFT JOIN users a ON m.admin_id = a.id
    WHERE m.section_id = ?
      AND (m.user_id = ? OR m.admin_id IS NULL OR m.admin_id = ?)
    ORDER BY m.created_at ASC
");
$stmt->execute([$section_id, $user_id, $user_id]);
$messages = $stmt->fetchAll();
?>

<?php require "Includes/header.php"; ?>
<?php require "Includes/navbar.php"; ?>

<div class="container mt-4">

    <h4>Chat - Bo‘lim:</h4>

    <select class="form-select mb-3" onchange="changeSection(this.value)">
        <?php foreach ($sections as $sec): ?>
            <option value="<?= $sec['id'] ?>" <?= ($sec['id'] == $section_id) ? 'selected' : '' ?>>
                <?= htmlspecialchars($sec['name']) ?>
            </option>
        <?php endforeach; ?>
    </select>

    <!-- CHAT OYNA -->
    <div class="chat-box p-3 mb-3 border rounded" style="height:800px; overflow-y:auto; background:#f8f9fa;">
        <?php foreach ($messages as $msg): ?>

            <?php
            // User xabari: user_id == hozirgi user va admin_id NULL
            $is_user_message = ($msg['user_id'] == $user_id && is_null($msg['admin_id']));
            ?>

            <?php if ($is_user_message): ?>
                <!-- USER MESSAGE -->
                <div class="d-flex justify-content-end mb-2">
                    <div class="bg-white border rounded px-3 py-2" style="max-width:70%;">
                        <?= htmlspecialchars($msg['message']) ?>
                        <?php if ($msg['attachment']): ?>
                            <div class="mt-1">
                                <a href="<?= htmlspecialchars($msg['attachment']) ?>" target="_blank">Yuklangan fayl</a>
                            </div>
                        <?php endif; ?>
                        <div class="text-end small text-muted"><?= $msg['created_at'] ?></div>
                    </div>
                </div>

            <?php else: ?>
                <!-- ADMIN MESSAGE -->
                <div class="d-flex justify-content-start mb-2">
                    <div class="bg-light border rounded px-3 py-2" style="max-width:70%;">
                        <?php if ($msg['admin_email']): ?>
                            <strong><?= substr($msg['admin_email'], 0, 5) ?>:</strong>
                        <?php endif; ?>
                        <?= htmlspecialchars($msg['message']) ?>
                        <?php if ($msg['attachment']): ?>
                            <div class="mt-1">
                                <a href="<?= htmlspecialchars($msg['attachment']) ?>" target="_blank">Yuklangan fayl</a>
                            </div>
                        <?php endif; ?>
                        <div class="text-start small text-muted"><?= $msg['created_at'] ?></div>
                    </div>
                </div>
            <?php endif; ?>

        <?php endforeach; ?>
    </div>
    <!-- XABAR YUBORISH -->
    <form method="POST" enctype="multipart/form-data">
        <div class="input-group mb-3">
            <input type="text" name="message" class="form-control" placeholder="Xabar yozing..." required>
            <input type="file" name="attachment" class="form-control">
            <button class="btn btn-danger" type="submit">Yuborish</button>
        </div>
    </form>

</div>

<script>
    function changeSection(id) {
        window.location.href = 'chat.php?section_id=' + id;
    }

    // Auto scroll pastga
    var chatBox = document.querySelector('.chat-box');
    if (chatBox) chatBox.scrollTop = chatBox.scrollHeight;
</script>

<?php require "Includes/footer.php"; ?>
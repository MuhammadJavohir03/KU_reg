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

$section_id = isset($_GET['section_id'])
    ? (int)$_GET['section_id']
    : (int)$sections[0]['id'];

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
    WHERE m.section_id = ?
      AND m.user_id = ?
    ORDER BY m.created_at ASC
");
$stmt->execute([$section_id, $user_id]);
$messages = $stmt->fetchAll();
?>

<?php require "Includes/header.php"; ?>

<body>
    <?php require "Includes/navbar.php"; ?>
    <canvas class="z-n1" id="bg"></canvas>
    <div class="container mt-4 text-dark">
        <h4 class="bg-white p-3 rounded-2">Chat - Bo‘limni tanlang:</h4>

        <select class="form-select mb-3" onchange="window.location.href='chat.php?section_id='+this.value;">
            <?php foreach ($sections as $sec): ?>
                <option value="<?= $sec['id'] ?>" <?= ($sec['id'] == $section_id) ? 'selected' : '' ?>>
                    <?= htmlspecialchars($sec['name']) ?>
                </option>
            <?php endforeach; ?>
        </select>

        <div class="chat-box p-3 mb-3 border rounded" style="height:550px; overflow-y:auto;background: rgb(255, 255, 255);">
            <?php if (!empty($messages)): ?>
                <?php foreach ($messages as $msg): ?>
                    <?php
                    $is_admin = !is_null($msg['admin_id']);
                    $bubble_bg = $is_admin ? 'rgba(33, 138, 255)' : 'rgba(106, 204, 70)';
                    ?>
                    <div class="d-flex <?= $is_admin ? 'justify-content-start' : 'justify-content-end' ?> mb-2">
                        <div class="shadow p-2 rounded-3 text-white" style="max-width:70%; background:<?= $bubble_bg ?>;">
                            <?php if ($is_admin): ?>
                                <strong><?= substr($msg['admin_email'], 0, 5) ?>:</strong>
                            <?php endif; ?>
                            <?= htmlspecialchars($msg['message']) ?>
                            <?php if ($msg['attachment']): ?>
                                <div class="mt-1">
                                    <a href="<?= htmlspecialchars($msg['attachment']) ?>" target="_blank" class="text-white">Yuklangan fayl</a>
                                </div>
                            <?php endif; ?>
                            <div class="text-end small"><?= $msg['created_at'] ?></div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p class="text-dark">Hali xabar yo‘q.</p>
            <?php endif; ?>
        </div>

        <form method="POST" enctype="multipart/form-data">
            <div class="input-group mb-3">
                <textarea name="message" class="form-control" placeholder="Xabar yozing..."></textarea>
                <input type="file" name="attachment" class="form-control">
                <button class="btn text-white" style="background : rgba(106, 204, 70);" type="submit">Yuborish <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-send" viewBox="0 0 16 16">
                        <path d="M15.854.146a.5.5 0 0 1 .11.54l-5.819 14.547a.75.75 0 0 1-1.329.124l-3.178-4.995L.643 7.184a.75.75 0 0 1 .124-1.33L15.314.037a.5.5 0 0 1 .54.11ZM6.636 10.07l2.761 4.338L14.13 2.576zm6.787-8.201L1.591 6.602l4.339 2.76z" />
                    </svg></button>
            </div>
        </form>
    </div>
</body>



<script>
    var chatBox = document.querySelector('.chat-box');
    if (chatBox) chatBox.scrollTop = chatBox.scrollHeight;
</script>
<script src="add.js"></script>
<?php require "Includes/footer.php"; ?>
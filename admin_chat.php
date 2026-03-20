<?php
session_start();
require "database.php";

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}

$admin_id = $_SESSION['user_id'];

// Adminga biriktirilgan sectionlar
$stmt = $pdo->prepare("
    SELECT s.* 
    FROM sections s
    JOIN admin_sections a ON s.id=a.section_id
    WHERE a.admin_id=?
");
$stmt->execute([$admin_id]);
$sections = $stmt->fetchAll();

// Tanlangan section
$section_id = (int)($_GET['section_id'] ?? $sections[0]['id']);

// Chap panel uchun userlar (shu sectionga xabar yozgan)
$stmt = $pdo->prepare("
    SELECT DISTINCT u.id, u.email
    FROM messages m
    JOIN users u ON m.user_id=u.id
    WHERE m.section_id=? AND (m.admin_id IS NULL OR m.admin_id=?)
");
$stmt->execute([$section_id, $admin_id]);
$users = $stmt->fetchAll();

// Tanlangan user
$chat_user_id = (int)($_GET['user_id'] ?? ($users[0]['id'] ?? 0));

// Shu user va sectiondagi xabarlar
$stmt = $pdo->prepare("
    SELECT m.*, u.email AS user_email
    FROM messages m
    JOIN users u ON m.user_id=u.id
    WHERE m.section_id=? AND m.user_id=? AND (m.admin_id IS NULL OR m.admin_id=?)
    ORDER BY m.created_at ASC
");
$stmt->execute([$section_id, $chat_user_id, $admin_id]);
$messages = $stmt->fetchAll();

// Admin javobi yozish
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['reply'])) {
    $reply = trim($_POST['reply']);
    $stmt = $pdo->prepare("
        INSERT INTO messages (section_id, user_id, admin_id, message)
        VALUES (?, ?, ?, ?)
    ");
    $stmt->execute([$section_id, $chat_user_id, $admin_id, $reply]);
}
?>

<?php require "Includes/header.php"; ?>
<?php require "Includes/navbar.php"; ?>

<div class="container mt-4 d-flex">
    <div class="col-3">
        <h5>Userlar</h5>
        <div class="list-group">
            <?php foreach ($users as $u): ?>
                <a href="admin_chat.php?section_id=<?= $section_id ?>&user_id=<?= $u['id'] ?>"
                    class="list-group-item list-group-item-action <?= ($u['id'] == $chat_user_id) ? 'active' : '' ?>">
                    <?= htmlspecialchars($u['email']) ?>
                </a>
            <?php endforeach; ?>
        </div>
    </div>

    <div class="col-9">
        <h5>Chat - Bo'lim: <?= htmlspecialchars($sections[array_search($section_id, array_column($sections, 'id'))]['name'] ?? '') ?></h5>
        <div class="chat-box border p-3 mb-3" style="height:400px; overflow-y:auto; background:#f8f9fa;">
            <?php foreach ($messages as $msg): ?>
                <?php if ($msg['admin_id'] == $admin_id): ?>
                    <div class="d-flex justify-content-end mb-2">
                        <div class="p-2 bg-primary text-white rounded" style="max-width:70%;">
                            <?= htmlspecialchars($msg['message']) ?>
                            <div class="text-end" style="font-size:10px;"><?= $msg['created_at'] ?></div>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="d-flex justify-content-start mb-2">
                        <div class="p-2 bg-secondary text-white rounded" style="max-width:70%;">
                            <strong><?= substr($msg['user_email'], 0, 5) ?>:</strong> <?= htmlspecialchars($msg['message']) ?>
                            <div class="text-start" style="font-size:10px;"><?= $msg['created_at'] ?></div>
                        </div>
                    </div>
                <?php endif; ?>
            <?php endforeach; ?>
        </div>

        <form method="POST">
            <div class="input-group">
                <input type="text" name="reply" class="form-control" placeholder="Javob yozing..." required>
                <button class="btn btn-primary" type="submit">Yuborish</button>
            </div>
        </form>
    </div>
</div>

<script>
    var chatBox = document.querySelector('.chat-box');
    chatBox.scrollTop = chatBox.scrollHeight;
</script>

<?php require "Includes/footer.php"; ?>
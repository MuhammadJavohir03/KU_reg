<?php
session_start();
require "database.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$role = $_SESSION['role'];

// Agar admin bo'lsa, admin_chat.php ga yo'naltirish
if ($role === 'admin') {
    header("Location: admin_chat.php");
    exit;
}

// Bo'limlar
$sections = $pdo->query("SELECT * FROM sections ORDER BY name")->fetchAll();
$section_id = (int)($_GET['section_id'] ?? $sections[0]['id']);

// Xabar yuborish
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['message'])) {
    $message = trim($_POST['message']);

    // Foydalanuvchi yozgan xabar → admin_id NULL
    $stmt = $pdo->prepare("INSERT INTO messages (section_id, user_id, message) VALUES (?, ?, ?)");
    $stmt->execute([$section_id, $user_id, $message]);
}

// Shu sectiondagi xabarlarni olish
$stmt = $pdo->prepare("
    SELECT m.*, u.email AS user_email, a.email AS admin_email
    FROM messages m
    LEFT JOIN users u ON m.user_id=u.id
    LEFT JOIN users a ON m.admin_id=a.id
    WHERE m.section_id=? AND (m.user_id=? OR m.admin_id=?)
    ORDER BY m.created_at ASC
");
$stmt->execute([$section_id, $user_id, $user_id]);
$messages = $stmt->fetchAll();
?>

<?php require "Includes/header.php"; ?>
<?php require "Includes/navbar.php"; ?>

<div class="container mt-4">
    <h3>Chat - Bo'lim:
        <select id="sectionSelect" onchange="changeSection(this.value)">
            <?php foreach ($sections as $sec): ?>
                <option value="<?= $sec['id'] ?>" <?= ($sec['id'] == $section_id) ? 'selected' : '' ?>>
                    <?= htmlspecialchars($sec['name']) ?>
                </option>
            <?php endforeach; ?>
        </select>
    </h3>

    <div class="chat-box border p-3 mb-3" style="height:400px; overflow-y:auto; background:#f8f9fa;">
        <?php foreach ($messages as $msg): ?>
            <?php if ($msg['user_id'] == $user_id): ?>
                <div class="d-flex justify-content-end mb-2">
                    <div class="p-2 bg-primary text-white rounded" style="max-width:70%;">
                        <?= htmlspecialchars($msg['message']) ?>
                        <div class="text-end" style="font-size:10px;"><?= $msg['created_at'] ?></div>
                    </div>
                </div>
            <?php else: ?>
                <div class="d-flex justify-content-start mb-2">
                    <div class="p-2 bg-secondary text-white rounded" style="max-width:70%;">
                        <strong><?= substr($msg['admin_email'], 0, 5) ?>:</strong> <?= htmlspecialchars($msg['message']) ?>
                        <div class="text-start" style="font-size:10px;"><?= $msg['created_at'] ?></div>
                    </div>
                </div>
            <?php endif; ?>
        <?php endforeach; ?>
    </div>

    <form method="POST">
        <div class="input-group">
            <input type="text" name="message" class="form-control" placeholder="Xabar yozing..." required>
            <button class="btn btn-primary" type="submit">Yuborish</button>
        </div>
    </form>
</div>

<script>
    function changeSection(id) {
        window.location.href = 'chat.php?section_id=' + id;
    }
    var chatBox = document.querySelector('.chat-box');
    chatBox.scrollTop = chatBox.scrollHeight;
</script>

<?php require "Includes/footer.php"; ?>
<?php
session_start();
require "database.php";
$user_id = $_SESSION['user_id'];
$sections = $pdo->query("SELECT * FROM sections ORDER BY name")->fetchAll();

foreach ($sections as $sec):
    $stmt_last = $pdo->prepare("SELECT message, attachment FROM messages WHERE section_id = ? AND user_id = ? ORDER BY created_at DESC LIMIT 1");
    $stmt_last->execute([$sec['id'], $user_id]);
    $last_msg = $stmt_last->fetch();

    $stmt_count = $pdo->prepare("SELECT COUNT(*) FROM messages WHERE section_id = ? AND user_id = ? AND admin_id IS NOT NULL AND is_read = 0");
    $stmt_count->execute([$sec['id'], $user_id]);
    $unread = $stmt_count->fetchColumn();

    $active_class = (isset($_GET['section_id']) && $_GET['section_id'] == $sec['id']) ? 'active' : '';
?>
    <a href="chat.php?section_id=<?= $sec['id'] ?>" class="section-item <?= $active_class ?>">
        <div style="flex: 1;">
            <div class="fw-bold"><?= htmlspecialchars($sec['name']) ?></div>
            <small class="last-msg-text">
                <?= $last_msg ? ($last_msg['message'] ?: "📎 Fayl...") : "Xabarlar yo'q" ?>
            </small>
        </div>
        <?php if ($unread > 0): ?>
            <span class="unread-badge"><?= $unread ?></span>
        <?php endif; ?>
    </a>
<?php endforeach; ?>
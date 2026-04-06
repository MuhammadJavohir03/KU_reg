<?php
session_start();
require "database.php";
$user_id = $_SESSION['user_id'];
$section_id = $_GET['section_id'] ?? null;
$title = "Muloqot"; // Sahifa sarlavhasi

if ($section_id) {
    // Xabarlarni o'qilgan qilish
    $pdo->prepare("UPDATE messages SET is_read = 1 WHERE section_id = ? AND user_id = ? AND admin_id IS NOT NULL")->execute([$section_id, $user_id]);

    $stmt = $pdo->prepare("SELECT m.* FROM messages m WHERE m.section_id = ? AND m.user_id = ? ORDER BY m.created_at ASC");
    $stmt->execute([$section_id, $user_id]);
    $messages = $stmt->fetchAll();

    foreach ($messages as $msg):
        $is_admin = !is_null($msg['admin_id']); ?>
        <div class="message <?= $is_admin ? 'admin' : 'user' ?>">
            <?php if ($is_admin): ?><strong style="color: #075e54; font-size: 12px; display: block;">Admin</strong><?php endif; ?>
            <div class="msg-text"><?= nl2br(htmlspecialchars($msg['message'])) ?></div>
            <?php if ($msg['attachment']): ?>
                <a href="<?= htmlspecialchars($msg['attachment']) ?>" target="_blank" class="d-block mt-1 small text-success">📎 Faylni ko'rish</a>
            <?php endif; ?>
            <span class="msg-info"><?= date('H:i', strtotime($msg['created_at'])) ?></span>
        </div>
<?php endforeach;
}

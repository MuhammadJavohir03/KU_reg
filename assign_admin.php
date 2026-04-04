<?php
session_start();
require "database.php";

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'super_admin') {
    header("Location: login.php");
    exit;
}

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['assign_admin'])) {
    $admin_id = (int)$_POST['admin_id'];
    $section_id = (int)$_POST['section_id'];

    $stmt = $pdo->prepare("SELECT * FROM users WHERE id=? AND role='admin'");
    $stmt->execute([$admin_id]);
    if (!$stmt->fetch()) {
        $message = "Xato: tanlangan foydalanuvchi admin emas!";
    } else {
        $stmt = $pdo->prepare("INSERT IGNORE INTO admin_sections (admin_id, section_id) VALUES (?, ?)");
        $stmt->execute([$admin_id, $section_id]);
        $message = "Admin muvaffaqiyatli biriktirildi!";
    }
}

?>
<!DOCTYPE html>
<html>

<head>
    <title>Admin biriktirish</title>
</head>

<body>
    <?php require "Includes/yuklash.php"; ?>

    <a href="admin_panel.php" class="nav-link">Orqaga</a>

    <h2>Adminni bo‘limga biriktirish</h2>

    <?php if ($message) echo "<p>$message</p>"; ?>

    <form method="POST">
        <label>Adminni tanlang:</label>
        <select name="admin_id" required>
            <?php
            $admins = $pdo->query("SELECT id, email FROM users WHERE role='admin'")->fetchAll();
            foreach ($admins as $a) {
                echo "<option value='{$a['id']}'>{$a['email']}</option>";
            }
            ?>
        </select>

        <label>Bo‘limni tanlang:</label>
        <select name="section_id" required>
            <?php
            $sections = $pdo->query("SELECT id, name FROM sections")->fetchAll();
            foreach ($sections as $s) {
                echo "<option value='{$s['id']}'>{$s['name']}</option>";
            }
            ?>
        </select>

        <button type="submit" name="assign_admin">Biriktirish</button>
    </form>
</body>

</html>
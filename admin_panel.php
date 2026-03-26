<?php
session_start();
require "database.php";

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'super_admin') {
    header("Location: login.php");
    exit;
}

$message = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_admin'])) {
    $email = trim($_POST['email']);
    $password_plain = trim($_POST['password']);
    $talaba_id = trim($_POST['talaba_id']);

    if (!preg_match('/@kokanduni\.uz$/', $email)) {
        $message = "Admin email faqat @kokanduni.uz bilan tugashi kerak!";
    } elseif (!preg_match('/^\d{12}$/', $talaba_id)) {
        $message = "Talaba ID 12 raqamdan iborat bo‘lishi kerak!";
    } else {
        $password_hashed = password_hash($password_plain, PASSWORD_DEFAULT);
        try {
            $stmt = $pdo->prepare("INSERT INTO users (email, password, talaba_id, role) VALUES (?, ?, ?, 'admin')");
            $stmt->execute([$email, $password_hashed, $talaba_id]);
            $message = "Admin muvaffaqiyatli qo‘shildi!";
        } catch (PDOException $e) {
            if ($e->getCode() == 23000) $message = "Bunday email yoki Talaba ID mavjud!";
            else $message = "Xatolik: " . $e->getMessage();
        }
    }
}

$stmt = $pdo->query("SELECT id, email, talaba_id, status FROM users WHERE role='admin'");
$admins = $stmt->fetchAll();
?>

<?php require "Includes/header.php"; ?>
<?php require "Includes/navbar.php"; ?>

<div class="container mt-5 bg-white p-4 shadow">
    <h2 class="text-dark">Admin Panel</h2>

    <?php if ($message) echo "<div class='alert alert-info'>$message</div>"; ?>

    <h4 class="text-white">Admin qo‘shish</h4>
    <form method="POST">
        <input type="hidden" name="add_admin" value="1">
        <div class="mb-3">
            <label class="text-white">Email (@kokanduni.uz):</label>
            <input type="email" name="email" class="form-control" placeholder="admin@kokanduni.uz" required>
        </div>
        <div class="mb-3">
            <label class="text-white">Talaba ID (12 raqam):</label>
            <input type="text" name="talaba_id" class="form-control" placeholder="ID=12ta raqam" required>
        </div>
        <div class="mb-3">
            <label class="text-white">Parol:</label>
            <input type="password" name="password" class="form-control" required>
        </div>
        <button type="submit" class="btn btn-success">Admin qo‘shish</button>
    </form>

    <hr>
    <h4 class="text-white">Barcha Adminlar</h4>
    <table class="table table-bordered">
        <tr>
            <th>ID</th>
            <th>Email</th>
            <th>Talaba ID</th>
            <th>Status</th>
            <th>Amallar</th>
        </tr>
        <?php foreach ($admins as $a): ?>
            <tr>
                <td><?= $a['id'] ?></td>
                <td><?= $a['email'] ?></td>
                <td><?= $a['talaba_id'] ?></td>
                <td><?= $a['status'] ?></td>
                <td>
                    <a href="admin_delete.php?id=<?= $a['id'] ?>" onclick="return confirm('Haqiqatan ham o‘chirmoqchimisiz?');" class="btn btn-danger btn-sm">O‘chirish</a>
                </td>
            </tr>
        <?php endforeach; ?>
    </table>
    <a class="btn btn-danger" href="assign_admin.php">Admin roli</a>
</div>

<?php require "Includes/footer.php"; ?>
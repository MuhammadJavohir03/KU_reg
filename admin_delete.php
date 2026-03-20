<?php
session_start();
require "database.php";

// Faqat Super Admin kirishi mumkin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'super_admin') {
    header("Location: login.php");
    exit;
}

// GET orqali kelgan id bo‘yicha adminni o‘chirish
if (isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $stmt = $pdo->prepare("DELETE FROM users WHERE id=? AND role='admin'");
    $stmt->execute([$id]);
}

// Admin panelga qaytish
header("Location: admin_panel.php");
exit;

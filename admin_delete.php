<?php
session_start();
require "database.php";

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'super_admin') {
    header("Location: login.php");
    exit;
}

if (isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $stmt = $pdo->prepare("DELETE FROM users WHERE id=? AND role='admin'");
    $stmt->execute([$id]);
}

header("Location: admin_panel.php");
exit;

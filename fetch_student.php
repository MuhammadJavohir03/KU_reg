<?php
require "database.php";

$query = $_GET['query'] ?? '';
$mode = $_GET['mode'] ?? 'list';

if (empty($query)) {
    echo json_encode(['success' => false]);
    exit;
}

if ($mode === 'list') {
    // Ism yoki ID bo'yicha qidirish (barcha mos keladiganlarni chiqaradi)
    $searchTerm = "%$query%";
    $stmt = $pdo->prepare("SELECT id, fio, talaba_id FROM users WHERE (fio LIKE ? OR talaba_id LIKE ?) AND role = 'user' LIMIT 15");
    $stmt->execute([$searchTerm, $searchTerm]);
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode(['success' => true, 'users' => $users]);
} else {
    // Tanlangan aniq bir talabaning barcha ma'lumotlarini olish
    $stmt = $pdo->prepare("SELECT id, fio, talaba_id, email, kurs FROM users WHERE id = ? LIMIT 1");
    $stmt->execute([$query]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    echo json_encode(['success' => true, 'user' => $user]);
}
<?php
session_start();
require "database.php";

// Xatoliklarni JSON formatida ko'rish uchun (faqat debug paytida)
error_reporting(0); 

header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    echo json_encode(['error' => 'Ruxsat yoq']);
    exit;
}

$query = isset($_GET['q']) ? trim($_GET['q']) : '';

if (mb_strlen($query, 'UTF-8') < 2) {
    echo json_encode([]);
    exit;
}

try {
    $stmt = $pdo->prepare("
        SELECT id, fio, email, image, talaba_id, guruh 
        FROM users 
        WHERE role = 'user' 
        AND (fio LIKE :q OR email LIKE :q OR talaba_id LIKE :q) 
        LIMIT 10
    ");
    
    $search = "%$query%";
    $stmt->execute(['q' => $search]);
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($results);
} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
exit;
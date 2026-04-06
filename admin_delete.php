<?php
session_start();
require "database.php";
$title = "Admin O'chirish";

// 🔐 Faqat Super Admin o'chira olishi uchun xavfsizlik tekshiruvi
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'super_admin') {
    header("Location: login.php");
    exit;
}

if (isset($_GET['id'])) {
    $id = (int)$_GET['id'];

    // Super admin o'zini o'zi o'chirib yubormasligi uchun tekshiruv
    if ($id == $_SESSION['user_id']) {
        header("Location: admin_panel.php?msg=error_self");
        exit;
    }

    try {
        // Tranzaksiyani boshlaymiz - bu juda muhim!
        // Agar birorta jadvaldan o'chmay qolsa, hamma amal bekor qilinadi.
        $pdo->beginTransaction();

        // 1️⃣ Admin yozgan barcha xabarlarni o'chirish
        // (messages jadvalida user_id yoki admin_id ustuni bor deb hisoblaymiz)
        $stmt1 = $pdo->prepare("DELETE FROM messages WHERE user_id = ?");
        $stmt1->execute([$id]);

        // 2️⃣ Adminning bo'limlarga biriktirilganlik ma'lumotlarini o'chirish
        $stmt2 = $pdo->prepare("DELETE FROM admin_sections WHERE admin_id = ?");
        $stmt2->execute([$id]);

        // 3️⃣ Eng asosiysi: Users jadvalidan adminni o'chirish (Login, Parol, Talaba_id)
        $stmt3 = $pdo->prepare("DELETE FROM users WHERE id = ? AND role = 'admin'");
        $stmt3->execute([$id]);

        // Agar hamma o'chirishlar muvaffaqiyatli bo'lsa, bazaga tasdiqlaymiz
        $pdo->commit();

        header("Location: admin_panel.php?msg=admin_deleted");
        exit;

    } catch (Exception $e) {
        // Xatolik yuz bersa, barcha o'chirilgan narsalarni qayta tiklaymiz (rollback)
        $pdo->rollBack();
        // Xatoni ko'rish uchun: die($e->getMessage());
        header("Location: admin_panel.php?msg=error_delete");
        exit;
    }
}

// Agar ID kelmasa, shunchaki panelga qaytaramiz
header("Location: admin_panel.php");
exit;
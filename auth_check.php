<?php
// Sessiyani boshlash (agar hali boshlanmagan bo'lsa)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 1 soat soniyalarda (3600 soniya)
$timeout_duration = 3600;

// Oxirgi faollik vaqtini tekshiramiz
if (isset($_SESSION['last_activity'])) {
    // Hozirgi vaqt va oxirgi faollik orasidagi farqni hisoblaymiz
    $elapsed_time = time() - $_SESSION['last_activity'];

    if ($elapsed_time >= $timeout_duration) {
        // Vaqt o'tib ketgan bo'lsa, sessiyani tozalaymiz va logout qilamiz
        session_unset();
        session_destroy();
        header("Location: login.php?reason=timeout");
        exit();
    }
}

// Har safar sahifa yangilanganda "oxirgi faollik" vaqtini yangilab boramiz
$_SESSION['last_activity'] = time();
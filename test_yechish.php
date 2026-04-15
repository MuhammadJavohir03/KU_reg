<?php
session_start();
require "database.php";

$talaba_id = $_SESSION['talaba_id'];
$f_id = (int)$_GET['fan_id'];
$m_id = (int)$_GET['maktab_id'];

// 1. Natijani hisoblash (Forma yuborilganda)
if (isset($_POST['finish_test'])) {
    $answers = $_POST['q'] ?? [];
    $total_score = 0;

    foreach ($answers as $q_id => $user_ans) {
        $check = $pdo->prepare("SELECT variant_t, ball FROM testlar WHERE id = ?");
        $check->execute([$q_id]);
        $q_data = $check->fetch();

        if ($q_data['variant_t'] === $user_ans) {
            $total_score += $q_data['ball'];
        }
    }

    // 2. Eski ballni aniqlash
    $old_score_stmt = $pdo->prepare("SELECT yakuniy_nazorat FROM talabalar WHERE talaba_id = ? AND fan_id = ?");
    $old_score_stmt->execute([$talaba_id, $f_id]);
    $old_score = (float)$old_score_stmt->fetchColumn();

    // 3. Bazani yangilash
    $update = $pdo->prepare("UPDATE talabalar SET yakuniy_nazorat = ? WHERE talaba_id = ? AND fan_id = ?");
    $update->execute([$total_score, $talaba_id, $f_id]);

    // Natija oynasi
    die("
        <div style='text-align:center; margin-top:100px; font-family:sans-serif;'>
            <h2>Test yakunlandi!</h2>
            <p style='font-size:20px;'>Eski ball: <b>$old_score</b> → Yangi ball: <b>$total_score</b></p>
            <a href='bepul_maktab.php' style='padding:10px 20px; background:blue; color:white; text-decoration:none; border-radius:5px;'>Fanlarga qaytish</a>
        </div>
    ");
}

// 4. Savollarni chiqarish
$stmt = $pdo->prepare("SELECT * FROM testlar WHERE fan_id = ? AND maktab_id = ? ORDER BY RAND()");
$stmt->execute([$f_id, $m_id]);
$questions = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="uz">

<head>
    <meta charset="UTF-8">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <title>Test yechish</title>
</head>

<body class="bg-light">
    <div class="container py-5">
        <div class="card shadow border-0 p-4">
            <h3 class="mb-4">Savollarga javob bering</h3>
            <form method="POST">
                <?php foreach ($questions as $i => $q):
                    $options = [$q['variant_t'], $q['variant_1'], $q['variant_2'], $q['variant_3']];
                    shuffle($options); // Variantlarni aralashtirish
                ?>
                    <div class="mb-4 p-3 border rounded">
                        <h5><?= ($i + 1) ?>. <?= htmlspecialchars($q['savol']) ?> <small class="text-muted">(<?= $q['ball'] ?> ball)</small></h5>
                        <?php foreach ($options as $opt): ?>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="q[<?= $q['id'] ?>]" value="<?= htmlspecialchars($opt) ?>" required>
                                <label class="form-check-label"><?= htmlspecialchars($opt) ?></label>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endforeach; ?>
                <button type="submit" name="finish_test" class="btn btn-success btn-lg px-5">Testni tugatish</button>
            </form>
        </div>
    </div>
</body>

</html>
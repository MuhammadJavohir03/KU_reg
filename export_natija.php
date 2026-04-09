<?php
require "database.php";
session_start();

$yonalish = $_GET['yonalish'] ?? '';
$guruh    = $_GET['guruh'] ?? '';
$semestr  = $_GET['semestr'] ?? '';
$fan     = $_GET['fan'] ?? '';

if (!$yonalish) exit("Yo'nalish tanlanmagan!");

// 1. Fanlarni va ularning semestrini olish
$sub_sql = "SELECT DISTINCT f.id, f.nomi, f.semestr FROM fanlar f JOIN talabalar t ON f.id = t.fan_id WHERE f.yonalish = ?";
$sub_params = [$yonalish];
if ($semestr) {
    $sub_sql .= " AND f.semestr = ?";
    $sub_params[] = $semestr;
}
if ($guruh) {
    $sub_sql .= " AND t.guruh = ?";
    $sub_params[] = $guruh;
}
if ($fan) {
    $sub_sql .= " AND f.nomi = ?";
    $sub_params[] = $fan;
}

$sub_stmt = $pdo->prepare($sub_sql);
$sub_stmt->execute($sub_params);
$active_subjects = $sub_stmt->fetchAll(PDO::FETCH_ASSOC);

// 2. Talaba ma'lumotlarini olish
$sql = "SELECT t.user_id as fio, u.talaba_id, t.guruh ";
foreach ($active_subjects as $subject) {
    $id = $subject['id'];
    $sql .= ", MAX(CASE WHEN t.fan_id = $id THEN t.reyting END) as r_$id ";
    $sql .= ", MAX(CASE WHEN t.fan_id = $id THEN t.umumiy END) as u_$id ";
    $sql .= ", MAX(CASE WHEN t.fan_id = $id THEN t.davomat END) as d_$id ";
}
$sql .= " FROM talabalar t 
          LEFT JOIN users u ON t.user_id = u.fio 
          LEFT JOIN fanlar f ON t.fan_id = f.id 
          WHERE f.yonalish = ? ";
$params = [$yonalish];
if ($semestr) {
    $sql .= " AND f.semestr = ?";
    $params[] = $semestr;
}
if ($guruh) {
    $sql .= " AND t.guruh = ?";
    $params[] = $guruh;
}
if ($fan) {
    $sql .= " AND f.nomi = ?";
    $params[] = $fan;
}
$sql .= " GROUP BY t.user_id, u.talaba_id, t.guruh";

$main_stmt = $pdo->prepare($sql);
$main_stmt->execute($params);
$students = $main_stmt->fetchAll(PDO::FETCH_ASSOC);

$filename = "O'zlashtirish_Natijalari_" . date('Y-m-d') . ".xls";
header("Content-Type: application/vnd.ms-excel; charset=utf-8");
header("Content-Disposition: attachment; filename=\"$filename\"");

echo "\xEF\xBB\xBF"; // UTF-8 BOM

$color_map = [
    ['head' => '#D9E1F2', 'body' => '#EBF1DE'],
    ['head' => '#FFF2CC', 'body' => '#FFF9E6'],
    ['head' => '#E2EFDA', 'body' => '#F2F7EE'],
    ['head' => '#FCE4D6', 'body' => '#FDF2EB'],
    ['head' => '#E7E6E6', 'body' => '#F2F2F2'],
];
?>

<style>
    .vertical-text {
        mso-rotate: 90;
        height: 180px;
        /* Semestr sig'ishi uchun biroz balandlik berildi */
        text-align: center;
        vertical-align: middle;
        font-weight: bold;
    }

    .main-header {
        background-color: #ffffff;
        font-weight: bold;
        vertical-align: middle;
        text-align: center;
    }

    td,
    th {
        border: 0.5pt solid #000000;
    }
</style>

<table border="1">
    <thead>
        <tr>
            <th rowspan="2" class="main-header">№</th>
            <th rowspan="2" class="main-header">Talaba F.I.O</th>
            <th rowspan="2" class="main-header">Talaba ID</th>
            <th rowspan="2" class="main-header">Guruh</th>
            <?php foreach ($active_subjects as $index => $f):
                $color = $color_map[$index % count($color_map)];
                // Fan nomi va semestrni birlashtiramiz
                $full_name = htmlspecialchars($f['nomi']) . " (" . $f['semestr'] . "-sem)";
            ?>
                <th colspan="3" style="background-color: <?= $color['head'] ?>; vertical-align: middle;">
                    <div class="vertical-text"><?= $full_name ?></div>
                </th>
            <?php endforeach; ?>
        </tr>
        <tr>
            <?php foreach ($active_subjects as $index => $f):
                $color = $color_map[$index % count($color_map)];
            ?>
                <th style="background-color: <?= $color['head'] ?>;">R</th>
                <th style="background-color: <?= $color['head'] ?>;">U</th>
                <th style="background-color: <?= $color['head'] ?>;">D</th>
            <?php endforeach; ?>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($students as $idx => $s):
            // 1. Avval butun qator "xavf" ostidami yoki yo'qligini aniqlaymiz
            $is_danger = false;
            foreach ($active_subjects as $f) {
                $id = $f['id'];
                if (($s["d_$id"] ?? 0) >= 33 || ($s["u_$id"] ?? 0) < 60 || ($s["r_$id"] ?? 0) < 20) {
                    $is_danger = true;
                    break;
                }
            }

            // Qator uchun asosiy fon rangi
            $row_bg = $is_danger ? '#ffd6d6' : '#ffffff'; // Och qizil yoki oq
        ?>
            <tr>
                <td align="center" style="background-color: <?= $row_bg ?>;"><?= $idx + 1 ?></td>
                <td align="left" style="background-color: <?= $row_bg ?>;"><?= htmlspecialchars($s['fio']) ?></td>
                <td align="center" style="background-color: <?= $row_bg ?>;"><?= htmlspecialchars($s['talaba_id'] ?? '-') ?></td>
                <td align="center" style="background-color: <?= $row_bg ?>;"><?= htmlspecialchars($s['guruh']) ?></td>

                <?php foreach ($active_subjects as $index => $f):
                    $id = $f['id'];
                    $color = $color_map[$index % count($color_map)];

                    // Har bir katak uchun alohida tekshiramiz:
                    $r_val = $s["r_$id"] ?? 0;
                    $u_val = $s["u_$id"] ?? 0;
                    $d_val = $s["d_$id"] ?? 0;

                    // Katak ranglarini aniqlash (Agar xato bo'lsa #ff7373 - to'qroq qizil)
                    $r_bg = ($r_val < 20) ? '#ff7373' : ($is_danger ? '#ffd6d6' : $color['body']);
                    $u_bg = ($u_val < 60) ? '#ff7373' : ($is_danger ? '#ffd6d6' : $color['body']);
                    $d_bg = ($d_val >= 33) ? '#ff7373' : ($is_danger ? '#ffd6d6' : $color['body']);
                ?>
                    <td align="center" style="background-color: <?= $r_bg ?>;"><?= $r_val ?></td>
                    <td align="center" style="background-color: <?= $u_bg ?>;"><?= $u_val ?></td>
                    <td align="center" style="background-color: <?= $d_bg ?>;"><?= round($d_val, 0) ?>%</td>
                <?php endforeach; ?>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>
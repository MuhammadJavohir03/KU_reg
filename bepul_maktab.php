<?php
session_start();
require "database.php";

// Brauzer keshini yopamiz (Orqaga qaytganda eski javoblar chiqmasligi uchun)
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

if (!isset($_SESSION['talaba_id'])) {
    header("Location: login.php");
    exit;
}

// SESSİYANI YANGİLASh (Agar ma'lumotlar bo'lmasa, bazadan olib sessiyaga yozib qo'yamiz)
if (!isset($_SESSION['talaba_id']) || !isset($_SESSION['fio'])) {
    $user_id = $_SESSION['user_id'];
    $stmt = $pdo->prepare("SELECT talaba_id, fio FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    $user_data = $stmt->fetch();

    if ($user_data) {
        $_SESSION['talaba_id'] = $user_data['talaba_id'];
        $_SESSION['fio'] = $user_data['fio'];
    }
}

// Endi pastdagi o'zgaruvchilarni bemalol ishlatsangiz bo'ladi
$real_talaba_id = $_SESSION['talaba_id'];

$user_id = $_SESSION['user_id'];
$user_stmt = $pdo->prepare("SELECT talaba_id FROM users WHERE id = ?");
$user_stmt->execute([$user_id]);
$real_talaba_id = $user_stmt->fetchColumn();

// --- TEST NATIJASINI SAQLASH ---
if (isset($_POST['finish_test'])) {
    $f_id = (int)$_POST['current_fan_id'];

    // Agar bu fan uchun session vaqti bo'lmasa, demak u allaqachon topshirilgan yoki ruxsatsiz kirilgan
    if (!isset($_SESSION['current_test_time_' . $f_id])) {
        header("Location: bepul_maktab.php");
        exit;
    }

    $answers = $_POST['q'] ?? [];
    $total_earned_score = 0;
    $attempt_time = $_SESSION['current_test_time_' . $f_id];

    foreach ($answers as $q_id => $user_ans) {
        $check = $pdo->prepare("SELECT variant_t, ball FROM testlar WHERE id = ?");
        $check->execute([$q_id]);
        $q_data = $check->fetch();

        if ($q_data) {
            $correct = trim((string)$q_data['variant_t']);
            $submitted = trim((string)$user_ans);
            $is_correct = ($correct === $submitted) ? 1 : 0;
            $score = $is_correct ? (float)$q_data['ball'] : 0;
            $total_earned_score += $score;

            $ins = $pdo->prepare("INSERT INTO test_jarayonlari (talaba_id, fan_id, savol_id, tanlangan_javob, togri_javob, holat, ball, sana) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            $ins->execute([$real_talaba_id, $f_id, $q_id, $submitted, $correct, $is_correct, $score, $attempt_time]);
        }
    }

    $del = $pdo->prepare("DELETE FROM test_jarayonlari WHERE talaba_id = ? AND fan_id = ? AND savol_id = 0 AND sana = ?");
    $del->execute([$real_talaba_id, $f_id, $attempt_time]);

    if (isset($_SESSION['fio'], $_SESSION['talaba_id'])) {
        $upd = $pdo->prepare("INSERT INTO bepul_qayta (fio, talaba_id, yakuniy, fan_id) VALUES (?, ?, ?, ?)");
        $upd->execute([$_SESSION['fio'], $user_stmt, $total_earned_score, $f_id]);
    } else {
        die("Xato: Sessiya ma'lumotlari topilmadi. Iltimos, qayta login qiling.");
    }

    // MUHIM: Urinish yakunlangach sessionni o'chiramiz
    unset($_SESSION['current_test_time_' . $f_id]);

    $_SESSION['test_result'] = $total_earned_score;
    header("Location: bepul_maktab.php");
    exit;
}

require 'Includes/header.php';
require 'Includes/navbar.php';
?>

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<?php require 'atmosphere.php'; ?>
<div class="container py-4">

    <?php if (isset($_SESSION['test_result'])): ?>
        <script>
            Swal.fire({
                title: 'Test yakunlandi!',
                text: 'Sizning natijangiz: <?= $_SESSION['test_result'] ?> ball',
                icon: 'success',
                confirmButtonText: 'OK'
            });
        </script>
        <?php unset($_SESSION['test_result']); ?>
    <?php endif; ?>

    <?php if (isset($_GET['start_test'])):
        $f_id = (int)$_GET['start_test'];
        $m_id = (int)$_GET['m_id'];

        $sett = $pdo->prepare("SELECT test_vaqti, limit_urunish FROM testlar WHERE fan_id = ? AND maktab_id = ? LIMIT 1");
        $sett->execute([$f_id, $m_id]);
        $config = $sett->fetch();

        $v_limit = $config['test_vaqti'] ?? 30;
        $u_limit = $config['limit_urunish'] ?? 1;

        $count = $pdo->prepare("SELECT COUNT(DISTINCT sana) FROM test_jarayonlari WHERE talaba_id = ? AND fan_id = ?");
        $count->execute([$real_talaba_id, $f_id]);
        $used_attempts = $count->fetchColumn();

        // Agar urinishlar tugagan bo'lsa va bu fandan faol session bo'lmasa - BLOKLASH
        if ($used_attempts >= $u_limit && !isset($_SESSION['current_test_time_' . $f_id])): ?>
            <div class="card border-0 shadow-sm rounded-4 p-5 text-center">
                <i class="fas fa-lock fa-4x text-danger mb-3"></i>
                <h3 class="fw-bold">Urinishlar tugagan</h3>
                <p class="text-muted">Siz ushbu testni topshirib bo'lgansiz.</p>
                <a href="bepul_maktab.php" class="btn btn-primary rounded-pill px-4">Orqaga qaytish</a>
            </div>
        <?php else:
            // Urinishni darhol band qilish
            if (!isset($_SESSION['current_test_time_' . $f_id])) {
                $start_now = date("Y-m-d H:i:s");
                $_SESSION['current_test_time_' . $f_id] = $start_now;

                $lock = $pdo->prepare("INSERT INTO test_jarayonlari (talaba_id, fan_id, savol_id, tanlangan_javob, togri_javob, holat, ball, sana) VALUES (?, ?, 0, 'STARTED', '---', 0, 0, ?)");
                $lock->execute([$real_talaba_id, $f_id, $start_now]);
            }

            $stmt = $pdo->prepare("SELECT * FROM testlar WHERE fan_id = ? AND maktab_id = ?");
            $stmt->execute([$f_id, $m_id]);
            $questions = $stmt->fetchAll();
        ?>
            <div class="sticky-top py-3 mb-4" style="top: 0; z-index: 1020; background: rgba(255,255,255,0.9); backdrop-filter: blur(10px); border-bottom: 2px solid #0d6efd;">
                <div class="container d-flex justify-content-between align-items-center">
                    <div class="fw-bold text-primary"><i class="fas fa-clock me-2"></i> <span id="timer_text">--:--</span></div>
                    <div class="progress w-50" style="height: 10px;">
                        <div id="timer_bar" class="progress-bar bg-primary" style="width: 100%"></div>
                    </div>
                </div>
            </div>

            <form id="quizForm" method="POST">
                <input type="hidden" name="current_fan_id" value="<?= $f_id ?>">
                <?php foreach ($questions as $idx => $q):
                    $vars = [$q['variant_t'], $q['variant_1'], $q['variant_2'], $q['variant_3']];
                    shuffle($vars);
                ?>
                    <div class="card mb-4 border-0 shadow-sm rounded-4">
                        <div class="card-body p-4">
                            <h6 class="fw-bold mb-3"><?= ($idx + 1) ?>. <?= htmlspecialchars($q['savol']) ?></h6>
                            <?php foreach ($vars as $v): ?>
                                <label class="d-block p-3 border rounded-3 mb-2 bg-light shadow-sm cursor-pointer">
                                    <input type="radio" name="q[<?= $q['id'] ?>]" value="<?= htmlspecialchars($v) ?>" required>
                                    <span class="ms-2"><?= htmlspecialchars($v) ?></span>
                                </label>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
                <button type="button" onclick="confirmFinish()" class="btn btn-primary btn-lg w-100 rounded-pill shadow mb-5">Testni topshirish</button>
            </form>

            <script>
                // Brauzer "Back" tugmasi bosilganda sahifani qayta yuklashga majburlash
                window.onpageshow = function(event) {
                    if (event.persisted) {
                        window.location.reload();
                    }
                };

                let time = <?= $v_limit * 60 ?>;
                const total = time;
                const timerText = document.getElementById('timer_text');
                const timerBar = document.getElementById('timer_bar');

                const interval = setInterval(() => {
                    let m = Math.floor(time / 60);
                    let s = time % 60;
                    timerText.innerText = `${m}:${s < 10 ? '0'+s : s}`;
                    timerBar.style.width = (time / total * 100) + '%';
                    if (time <= 0) {
                        clearInterval(interval);
                        submitForm();
                    }
                    time--;
                }, 1000);

                function confirmFinish() {
                    Swal.fire({
                        title: 'Testni yakunlaysizmi?',
                        icon: 'question',
                        showCancelButton: true,
                        confirmButtonText: 'Ha',
                        cancelButtonText: 'Yo\'q'
                    }).then((r) => {
                        if (r.isConfirmed) submitForm();
                    });
                }

                function submitForm() {
                    document.querySelectorAll('input[required]').forEach(i => i.required = false);
                    const hidden = document.createElement('input');
                    hidden.type = 'hidden';
                    hidden.name = 'finish_test';
                    hidden.value = '1';
                    document.getElementById('quizForm').appendChild(hidden);
                    document.getElementById('quizForm').submit();
                }
            </script>
        <?php endif; ?>

    <?php else: ?>
        <h3 class="fw-bold text-center mb-4">Mening fanlarim</h3>
        <div class="row g-4 text-center">
            <?php
            $stmt = $pdo->prepare("SELECT * FROM bepul WHERE talaba_id = ?");
            $stmt->execute([$real_talaba_id]);
            $b_row = $stmt->fetch();
            if ($b_row) {
                $fans = array_filter([$b_row['fan1'], $b_row['fan2'], $b_row['fan3'], $b_row['fan4']]);
                foreach ($fans as $f_id) {
                    $fn = $pdo->prepare("SELECT nomi FROM fanlar WHERE id = ?");
                    $fn->execute([$f_id]);
                    $f_nomi = $fn->fetchColumn();
                    $rx = $pdo->prepare("SELECT status, maktab_id FROM bepul_ruxsatlar WHERE bepul_id = ? AND fan_id = ?");
                    $rx->execute([$b_row['id'], $f_id]);
                    $ruxsat = $rx->fetch();
            ?>
                    <div class="col-md-4">
                        <div class="card border-0 shadow-sm p-4 rounded-4 h-100">
                            <i class="fas fa-book-open fa-3x text-primary mb-3"></i>
                            <h5><?= htmlspecialchars($f_nomi) ?></h5>
                            <?php if ($ruxsat && $ruxsat['status'] == 1): ?>
                                <button onclick="confirmStart(<?= $f_id ?>, <?= $ruxsat['maktab_id'] ?>)" class="btn btn-primary rounded-pill mt-3">Testni boshlash</button>
                            <?php else: ?>
                                <button class="btn btn-light rounded-pill mt-3" disabled>Yopiq</button>
                            <?php endif; ?>
                        </div>
                    </div>
            <?php }
            } ?>
        </div>
        <script>
            function confirmStart(f_id, m_id) {
                Swal.fire({
                    title: 'Boshlaymizmi?',
                    icon: 'info',
                    showCancelButton: true,
                    confirmButtonText: 'Boshlash'
                }).then((r) => {
                    if (r.isConfirmed) window.location.href = `?start_test=${f_id}&m_id=${m_id}`;
                });
            }
        </script>
    <?php endif; ?>
</div>

<?php require 'Includes/footer.php'; ?>
<?php
session_start();
require 'database.php';

// Action handler
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? 'add';
    $talaba_id = trim($_POST['talaba_id'] ?? '');
    $familiya = trim($_POST['familiya'] ?? '');
    $ism = trim($_POST['ism'] ?? '');
    $otasi = trim($_POST['otasi'] ?? '');
    $guruh = trim($_POST['guruh'] ?? '');
    $yonalish = trim($_POST['yonalish'] ?? '');
    $kurs = trim($_POST['kurs'] ?? '');
    $parol = trim($_POST['parol'] ?? '');
    $fan1 = trim($_POST['fan1'] ?? '');
    $fan2 = trim($_POST['fan2'] ?? '');
    $fan3 = trim($_POST['fan3'] ?? '');

    if ($action === 'add') {
        $stmt = $pdo->prepare("INSERT INTO bepul (talaba_id, familiya, ism, otasi, guruh, yonalish, kurs, parol, fan1, fan2, fan3, created_at)
                               VALUES (:talaba_id, :familiya, :ism, :otasi, :guruh, :yonalish, :kurs, :parol, :fan1, :fan2, :fan3, NOW())");
        $stmt->execute([
            'talaba_id' => $talaba_id,
            'familiya' => $familiya,
            'ism' => $ism,
            'otasi' => $otasi,
            'guruh' => $guruh,
            'yonalish' => $yonalish,
            'kurs' => $kurs,
            'parol' => $parol,
            'fan1' => $fan1,
            'fan2' => $fan2,
            'fan3' => $fan3,
        ]);

        header('Location: bepul_royhat.php?success=add');
        exit;
    }

    if ($action === 'edit') {
        $id = intval($_POST['id'] ?? 0);
        if ($id > 0) {
            $stmt = $pdo->prepare("UPDATE bepul SET
                talaba_id = :talaba_id,
                familiya = :familiya,
                ism = :ism,
                otasi = :otasi,
                guruh = :guruh,
                yonalish = :yonalish,
                kurs = :kurs,
                parol = :parol,
                fan1 = :fan1,
                fan2 = :fan2,
                fan3 = :fan3
                WHERE id = :id");
            $stmt->execute([
                'talaba_id' => $talaba_id,
                'familiya' => $familiya,
                'ism' => $ism,
                'otasi' => $otasi,
                'guruh' => $guruh,
                'yonalish' => $yonalish,
                'kurs' => $kurs,
                'parol' => $parol,
                'fan1' => $fan1,
                'fan2' => $fan2,
                'fan3' => $fan3,
                'id' => $id,
            ]);
            header('Location: bepul_royhat.php?success=edit');
            exit;
        }
    }
}

if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    if ($id > 0) {
        $stmt = $pdo->prepare("DELETE FROM bepul WHERE id = :id");
        $stmt->execute(['id' => $id]);
    }
    header('Location: bepul_royhat.php?success=delete');
    exit;
}

$search = trim($_GET['search'] ?? '');
$filterSql = '';
$params = [];
if ($search !== '') {
    $filterSql = "WHERE talaba_id LIKE :q OR familiya LIKE :q OR ism LIKE :q OR otasi LIKE :q OR guruh LIKE :q OR yonalish LIKE :q OR kurs LIKE :q OR fan1 LIKE :q OR fan2 LIKE :q OR fan3 LIKE :q";
    $params['q'] = "%$search%";
}

$sql = "SELECT * FROM bepul $filterSql ORDER BY created_at DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (isset($_GET['export']) && $_GET['export'] === 'csv') {
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename=bepul_royhat_' . date('Ymd_His') . '.csv');

    $output = fopen('php://output', 'w');
    fputcsv($output, ['id','talaba_id','familiya','ism','otasi','guruh','yonalish','kurs','parol','fan1','fan2','fan3','created_at']);
    foreach ($rows as $row) {
        fputcsv($output, [$row['id'],$row['talaba_id'],$row['familiya'],$row['ism'],$row['otasi'],$row['guruh'],$row['yonalish'],$row['kurs'],$row['parol'],$row['fan1'],$row['fan2'],$row['fan3'],$row['created_at']]);
    }
    fclose($output);
    exit;
}

?>

<?php require 'Includes/header.php'; ?>
<?php require 'Includes/navbar.php'; ?>

<div class="container my-5">
    <div class="d-flex justify-content-between text-white align-items-center mb-4">
        <h2>Bepul ariza ro'yhati</h2>
        <div>
            <a href="bepul_royhat.php?export=csv&search=<?= urlencode($search) ?>" class="btn btn-success">Excelga export (CSV)</a>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addModal" onclick="openAddModal();">Yangi talaba qo'shish</button>
        </div>
    </div>

    <form method="get" class="row g-2 align-items-center mb-4">
        <div class="col-sm-8">
            <input type="text" id="searchInput" class="form-control" name="search" placeholder="Qidirish" value="<?= htmlspecialchars($search) ?>" oninput="liveSearch(this.value)">
        </div>
        <div class="col-sm-4">
            <button type="submit" class="btn btn-outline-primary w-100">Qidirish</button>
        </div>
    </form>

    <?php if (isset($_GET['success'])): ?>
        <?php $msg = ['add' => 'Yangi talaba qo`shildi.', 'edit' => 'Talaba ma`lumotlari yangilandi.', 'delete' => 'Talaba o`chirildi.'][$_GET['success']] ?? null; ?>
        <?php if ($msg): ?>
            <div class="alert alert-success"><?= htmlspecialchars($msg) ?></div>
        <?php endif; ?>
    <?php endif; ?>

    <div class="table-responsive">
        <table class="table table-bordered table-striped table-hover">
            <thead class="table-dark">
                <tr>
                    <th>#</th>
                    <th>Talaba ID</th>
                    <th>Familiya</th>
                    <th>Ism</th>
                    <th>Otasi</th>
                    <th>Guruh</th>
                    <th>Yo'nalish</th>
                    <th>Kurs</th>
                    <th>Fan1</th>
                    <th>Fan2</th>
                    <th>Fan3</th>
                    <th>Qo'shilgan vaqti</th>
                    <th>Amallar</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!$rows): ?>
                    <tr><td colspan="13" class="text-center">Ma'lumot topilmadi</td></tr>
                <?php endif; ?>
                <?php foreach ($rows as $row): ?>
                    <tr>
                        <td><?= htmlspecialchars($row['id']) ?></td>
                        <td><?= htmlspecialchars($row['talaba_id']) ?></td>
                        <td><?= htmlspecialchars($row['familiya']) ?></td>
                        <td><?= htmlspecialchars($row['ism']) ?></td>
                        <td><?= htmlspecialchars($row['otasi']) ?></td>
                        <td><?= htmlspecialchars($row['guruh']) ?></td>
                        <td><?= htmlspecialchars($row['yonalish']) ?></td>
                        <td><?= htmlspecialchars($row['kurs']) ?></td>
                        <td><?= htmlspecialchars($row['fan1']) ?></td>
                        <td><?= htmlspecialchars($row['fan2']) ?></td>
                        <td><?= htmlspecialchars($row['fan3']) ?></td>
                        <td><?= htmlspecialchars($row['created_at']) ?></td>
                        <td>
                            <button class="btn btn-sm btn-warning" onclick='editRow(<?= json_encode($row, JSON_HEX_APOS|JSON_HEX_QUOT) ?>)'>Tahrir</button>
                            <a href="bepul_royhat.php?delete=<?= urlencode($row['id']) ?>" class="btn btn-sm btn-danger" onclick="return confirm('Rostdan ham o`chirmoqchimisiz?');">O'chirish</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal -->
<div class="modal fade" id="addModal" tabindex="-1" aria-labelledby="modalTitle" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalTitle">Yangi talaba qo'shish</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Yopish"></button>
            </div>
            <form method="post" id="dataform">
                <input type="hidden" name="action" id="action" value="add">
                <input type="hidden" name="id" id="id" value="">
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-4"><label class="form-label">Talaba ID</label><input class="form-control" name="talaba_id" id="talaba_id" required></div>
                        <div class="col-md-4"><label class="form-label">Familiya</label><input class="form-control" name="familiya" id="familiya" required></div>
                        <div class="col-md-4"><label class="form-label">Ism</label><input class="form-control" name="ism" id="ism" required></div>
                        <div class="col-md-4"><label class="form-label">Otasi</label><input class="form-control" name="otasi" id="otasi"></div>
                        <div class="col-md-4"><label class="form-label">Guruh</label><input class="form-control" name="guruh" id="guruh"></div>
                        <div class="col-md-4"><label class="form-label">Yo'nalish</label><input class="form-control" name="yonalish" id="yonalish"></div>
                        <div class="col-md-4"><label class="form-label">Kurs</label><input class="form-control" name="kurs" id="kurs"></div>
                        <div class="col-md-4"><label class="form-label">Parol</label><input class="form-control" name="parol" id="parol"></div>
                        <div class="col-md-4"><label class="form-label">Fan 1</label><input class="form-control" name="fan1" id="fan1"></div>
                        <div class="col-md-4"><label class="form-label">Fan 2</label><input class="form-control" name="fan2" id="fan2"></div>
                        <div class="col-md-4"><label class="form-label">Fan 3</label><input class="form-control" name="fan3" id="fan3"></div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Bekor qilish</button>
                    <button type="submit" class="btn btn-primary">Saqlash</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    function openAddModal() {
        document.getElementById('action').value = 'add';
        document.getElementById('id').value = '';
        document.getElementById('talaba_id').value = '';
        document.getElementById('familiya').value = '';
        document.getElementById('ism').value = '';
        document.getElementById('otasi').value = '';
        document.getElementById('guruh').value = '';
        document.getElementById('yonalish').value = '';
        document.getElementById('kurs').value = '';
        document.getElementById('parol').value = '';
        document.getElementById('fan1').value = '';
        document.getElementById('fan2').value = '';
        document.getElementById('fan3').value = '';
        document.getElementById('modalTitle').textContent = '➕ Yangi talaba qo\'shish';
    }

    function editRow(data) {
        document.getElementById('action').value = 'edit';
        document.getElementById('id').value = data.id;
        document.getElementById('talaba_id').value = data.talaba_id;
        document.getElementById('familiya').value = data.familiya;
        document.getElementById('ism').value = data.ism;
        document.getElementById('otasi').value = data.otasi;
        document.getElementById('guruh').value = data.guruh;
        document.getElementById('yonalish').value = data.yonalish;
        document.getElementById('kurs').value = data.kurs;
        document.getElementById('parol').value = data.parol;
        document.getElementById('fan1').value = data.fan1;
        document.getElementById('fan2').value = data.fan2;
        document.getElementById('fan3').value = data.fan3;
        document.getElementById('modalTitle').textContent = '✏️ Talaba ma\'lumotlarini tahrirlash';

        const modal = new bootstrap.Modal(document.getElementById('addModal'));
        modal.show();
    }

    document.getElementById('addModal').addEventListener('hidden.bs.modal', function() {
        document.getElementById('dataform').reset();
        document.getElementById('action').value = 'add';
        document.getElementById('id').value = '';
        document.getElementById('modalTitle').textContent = '➕ Yangi talaba qo\'shish';
    });

    function liveSearch(query) {
        const url = new URL(window.location.href);
        if (query) {
            url.searchParams.set('search', query);
        } else {
            url.searchParams.delete('search');
        }
        // debouncing kiritish
        if (window.liveSearchTimeout) {
            clearTimeout(window.liveSearchTimeout);
        }
        window.liveSearchTimeout = setTimeout(() => {
            window.location.href = url.toString();
        }, 250);
    }
</script>

<?php require 'Includes/footer.php'; ?>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>

<?php
require "database.php";
session_start();
$title = "Talabalar Bahosi";

$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$limit = 50;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if ($page < 1) $page = 1;
$offset = ($page - 1) * $limit;

// 1. Jami talabalar sonini qidiruvga qarab aniqlash
if (!empty($search)) {
    // Bu yerda user_id o'rniga FIO yoki ism ustunini yozing, masalan 'user_id'
    $total_stmt = $pdo->prepare("SELECT COUNT(DISTINCT user_id) FROM talabalar WHERE user_id LIKE :s");
    $total_stmt->execute([':s' => "%$search%"]);
} else {
    $total_stmt = $pdo->query("SELECT COUNT(DISTINCT user_id) FROM talabalar");
}
$total_students = $total_stmt->fetchColumn();
$total_pages = ceil($total_students / $limit);

$sql = "
    SELECT t.*, f.nomi as fan_nomi, f.semestr 
    FROM talabalar t 
    LEFT JOIN fanlar f ON t.fan_id = f.id
    WHERE t.user_id IN (
        SELECT user_id FROM (
            SELECT DISTINCT user_id FROM talabalar 
            WHERE user_id LIKE :search_query 
            ORDER BY user_id ASC 
            LIMIT :limit OFFSET :offset
        ) AS temp_table
    )
";

$stmt = $pdo->prepare($sql);
$stmt->bindValue(':search_query', "%$search%", PDO::PARAM_STR);
$stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$all_data = $stmt->fetchAll(PDO::FETCH_ASSOC);

$js_registry = [];
foreach ($all_data as $row) {
    $uid = $row['user_id']; // Bu yerda talaba ismini kalit qilib olish modal uchun qulay
    $guruh = trim($row['guruh']);

    // Ta'lim shaklini aniqlash mantiqi (Sizniki o'zgarishsiz qoldi)
    $onlyLetters = preg_replace('/[^a-zA-Z]/', '', $guruh);
    $lastChar = strtoupper(substr($onlyLetters, -1));
    $letterCount = strlen($onlyLetters);

    if ($lastChar === 'S') {
        $talim_shakli = "Sirtqi";
    } elseif ($lastChar === 'M') {
        $talim_shakli = "Masofaviy";
    } elseif ($letterCount >= 2) {
        $talim_shakli = "Kunduzgi";
    } else {
        $talim_shakli = "Boshqa";
    }

    if (!isset($js_registry[$uid])) {
        $js_registry[$uid] = [
            'guruh' => $guruh,
            'shakl' => $talim_shakli,
            'results' => []
        ];
    }

    $js_registry[$uid]['results'][] = [
        'fan' => $row['fan_nomi'],
        'semestr' => $row['semestr'],
        'jn' => $row['joriy_nazorat'],
        'on' => $row['oraliq_nazorat'],
        'r' => $row['reyting'],
        'yn' => $row['yakuniy_nazorat'],
        'u' => $row['umumiy'],
        'd' => $row['davomat']
    ];
}
?>

<?php require "Includes/header.php"; ?>
<?php require "atmosphere.php"; ?>

<style>
    #hemisModal {
        display: none;
        position: fixed;
        z-index: 10000;
        inset: 0;
        /* top, left, bottom, right: 0 */
        background-color: rgba(15, 23, 42, 0.6);
        /* Navy Blue shaffofligi */
        backdrop-filter: blur(10px);
        /* Shishasimon effekt */
        -webkit-backdrop-filter: blur(10px);
        align-items: center;
        justify-content: center;
        padding: 20px;
    }

    .modal-dialog {
        background: #ffffff;
        width: 100%;
        max-width: 1100px;
        max-height: 90vh;
        border-radius: 20px;
        /* Kattaroq burchaklar */
        display: flex;
        flex-direction: column;
        position: relative;
        box-shadow: 0 40px 100px -20px rgba(0, 0, 0, 0.4);
        border: 1px solid rgba(255, 255, 255, 0.1);
        animation: modalFadeIn 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
        overflow: hidden;
    }

    @keyframes modalFadeIn {
        from {
            transform: translateY(30px) scale(0.98);
            opacity: 0;
        }

        to {
            transform: translateY(0) scale(1);
            opacity: 1;
        }
    }

    .modal-header {
        background: #1e293b;
        /* Navy Blue */
        color: white;
        padding: 25px 35px;
        /* Kengroq padding */
        display: flex;
        justify-content: space-between;
        align-items: center;
        border-bottom: 1px solid rgba(255, 255, 255, 0.1);
    }

    .modal-header h2 {
        margin: 0;
        font-weight: 800;
        /* Qalinroq */
        font-size: 1.25rem;
        letter-spacing: 0.5px;
        color: #f8fafc;
    }

    .modal-header .modal-subtitle {
        font-size: 12px;
        color: rgba(255, 255, 255, 0.7);
        font-weight: 400;
        margin-top: 5px;
        display: block;
    }

    .modal-body {
        padding: 25px;
        overflow-y: auto;
        background: #f8fafc;
        /* Och kulrang fon */
    }

    .modal-table {
        width: 100%;
        border-collapse: separate;
        border-spacing: 0 10px;
        /* Qatorlar orasida masofa (havo) */
    }

    .modal-table thead th {
        background: transparent;
        color: #64748b;
        /* Nozikroq matn */
        font-weight: 600;
        text-transform: uppercase;
        font-size: 11px;
        padding: 10px 18px;
        letter-spacing: 1px;
        border: none;
        position: sticky;
        top: 0;
        z-index: 10;
    }


    .modal-table tbody tr {
        background: #ffffff;
        transition: all 0.25s ease;
        border-radius: 12px;
        box-shadow: 0 2px 5px rgba(0, 0, 0, 0.02);
    }

    .modal-table tbody tr:hover {
        transform: translateY(-2px);
        /* Ko'tarilish effekti */
        box-shadow: 0 10px 20px rgba(0, 0, 0, 0.05);
        background: #ffffff;
    }

    .modal-table td {
        padding: 20px 18px;
        /* Maksimal havo - bloklarga yopishmaydi */
        border: none;
        font-size: 14px;
        color: #1e293b;
        vertical-align: middle;
    }

    .modal-table td:first-child {
        border-radius: 12px 0 0 12px;
        text-align: center;
    }

    .modal-table td:last-child {
        border-radius: 0 12px 12px 0;
        text-align: center;
    }

    /* Fan nomi va "Majburiy" yozuvi */
    .fan-cell-main {
        font-weight: 700;
        color: #1e293b;
        font-size: 15px;
    }

    .fan-cell-majburiy {
        font-size: 10px;
        color: #94a3b8;
        font-weight: 800;
        text-transform: uppercase;
        margin-top: 3px;
    }

    /* Semestr, JN, ON, Reyting */
    .score-cell {
        font-weight: 600;
        color: #475569;
        text-align: center;
    }

    .score-cell-bold {
        font-weight: 800;
        color: #1e293b;
        text-align: center;
    }

    .score-total-card {
        display: inline-block;
        padding: 8px 18px;
        border-radius: 8px;
        font-weight: 800;
        font-size: 16px;
        min-width: 60px;
        text-align: center;
        border: 1px solid rgba(0, 0, 0, 0.05);
    }

    .total-pass {
        background: rgba(34, 197, 94, 0.08);
        color: #15803d;
    }

    /* O'tolmagan baholar */
    .total-fail {
        background: rgba(239, 68, 68, 0.1);
        color: #b91c1c;
    }

    /* Davomat % */
    .davomat-normal {
        font-weight: 700;
        color: #1e293b;
    }

    .davomat-danger {
        font-weight: 800;
        color: #ef4444;
    }

    /* Scrollbar stili */
    .modal-body::-webkit-scrollbar {
        width: 5px;
    }

    .modal-body::-webkit-scrollbar-thumb {
        background: rgba(0, 0, 0, 0.1);
        border-radius: 10px;
    }

    .modal-body::-webkit-scrollbar-track {
        background: transparent;
    }

    .modal-close-header-btn {
        background: rgba(255, 255, 255, 0.05);
        border: none;
        color: rgba(255, 255, 255, 0.6);
        width: 36px;
        height: 36px;
        border-radius: 50%;
        cursor: pointer;
        transition: 0.3s;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
    }

    .modal-close-header-btn:hover {
        background: rgba(239, 68, 68, 0.2);
        color: #f87171;
    }

    .search-container {
        margin-bottom: 20px;
    }

    .search-input {
        width: 100%;
        padding: 12px 20px;
        border-radius: 8px;
        border: 1px solid #e2e8f0;
        box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05);
        font-size: 14px;
        outline: none;
    }

    .shakl-badge {
        padding: 4px 8px;
        border-radius: 4px;
        font-size: 10px;
        font-weight: 700;
        text-transform: uppercase;
    }

    .kunduzgi {
        background: #dcfce7;
        color: #166534;
        border: 1px solid #bbf7d0;
    }

    .sirtqi {
        background: #fef9c3;
        color: #854d0e;
        border: 1px solid #fef08a;
    }

    .masofaviy {
        background: #e0f2fe;
        color: #075985;
        border: 1px solid #bae6fd;
    }

    /* "Batafsil" tugmasi stili */
    .action-details-btn {
        background-color: rgba(59, 130, 246, 0.08);
        /* Juda och ko'k */
        color: #3b82f6;
        /* Moviy ko'k */
        border: 1px solid rgba(59, 130, 246, 0.2);
        padding: 8px 16px;
        border-radius: 10px;
        font-size: 13px;
        font-weight: 700;
        cursor: pointer;
        display: flex;
        align-items: center;
        gap: 8px;
        transition: all 0.3s ease;
    }

    .action-details-btn:hover {
        background-color: #3b82f6;
        /* Hover bo'lganda to'liq ko'k */
        color: #ffffff;
        box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3);
        transform: translateY(-2px);
    }

    .action-details-btn i {
        font-size: 14px;
    }

    /* Jadval qatoriga chiroyli effekt */
    .student-row {
        cursor: pointer;
        /* Butun qa
        torni bosish mumkinligi ko'rinishi uchun */
    }

    /* Pagination umumiy konteyneri */
    .pagination-bar {
        display: flex;
        justify-content: center;
        align-items: center;
        gap: 8px;
        /* Tugmalar orasidagi masofa */
        margin: 40px 0;
        padding: 15px;
        background: rgba(255, 255, 255, 0.5);
        /* Glassmorphism effekti uchun */
        backdrop-filter: blur(5px);
        border-radius: 50px;
        /* Yumshoq oval ko'rinish */
    }

    /* Asosiy tugmalar stili */
    .p-link {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 45px;
        height: 45px;
        padding: 0 15px;
        border-radius: 12px;
        background-color: #ffffff;
        border: 1px solid #e2e8f0;
        color: #1e293b;
        /* Navy Blue matn */
        text-decoration: none;
        font-weight: 700;
        font-size: 14px;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.02);
    }

    /* Sichqoncha ustiga kelganda (Hover) */
    .p-link:hover {
        background-color: #f8fafc;
        border-color: #3b82f6;
        color: #3b82f6;
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(59, 130, 246, 0.15);
    }

    /* Faol sahifa (Active) */
    .p-link.active {
        background-color: #1e293b;
        /* Navy Blue fon */
        border-color: #1e293b;
        color: #ffffff;
        /* Oq matn */
        box-shadow: 0 10px 15px -3px rgba(30, 41, 59, 0.3);
    }

    /* Oldinga/Orqaga tugmalari uchun maxsus (Ikonkalar bilan) */
    .p-nav {
        background-color: rgba(59, 130, 246, 0.1);
        border-color: rgba(59, 130, 246, 0.2);
        color: #3b82f6;
    }

    /* O'tib bo'lmaydigan (Disabled) tugmalar */
    .p-disabled {
        opacity: 0.5;
        cursor: not-allowed;
        pointer-events: none;
    }
</style>

<body>
    <?php require "Includes/yuklash.php"; ?>
    <?php include "Includes/navbar.php"; ?>
    <div class="container">
        <div class="p-6">
            <div class="search-container">
                <form method="GET">
                    <input type="text"
                        name="search"
                        value="<?= htmlspecialchars($search) ?>"
                        placeholder="Talaba F.I.O yoki Guruhni yozing va Enter bosing..."
                        class="search-input">
                    <input type="hidden" name="page" value="1">
                </form>
            </div>

            <div class="bg-white rounded shadow-sm border overflow-hidden">
                <table class="w-full text-left" id="mainTable">
                    <thead class="bg-gray-50 border-b">
                        <tr class="text-[11px] font-bold text-gray-500 uppercase">
                            <th class="p-4 text-center w-12">#</th>
                            <th class="p-4">Talaba F.I.O / Guruh</th>
                            <th class="p-4 text-center">Ta'lim shakli</th>
                            <th class="p-4 text-center w-24">Batafsil</th>
                        </tr>
                    </thead>
                    <tbody id="mainTableBody">
                        <?php $i = 1;
                        foreach ($js_registry as $name => $data):
                            $badgeClass = '';
                            if ($data['shakl'] == 'Kunduzgi') $badgeClass = 'kunduzgi';
                            elseif ($data['shakl'] == 'Sirtqi') $badgeClass = 'sirtqi';
                            elseif ($data['shakl'] == 'Masofaviy') $badgeClass = 'masofaviy';
                        ?>
                            <tr class="border-b hover:bg-gray-50 student-row">
                                <td class="p-4 text-center text-gray-400 index-td"><?= $i++ ?></td>
                                <td class="p-4">
                                    <div class="search-name" style="color: #1e293b; font-weight: 700; text-transform: uppercase;"><?= htmlspecialchars($name) ?></div>
                                    <div class="search-guruh" style="color: #2563eb; font-size: 11px; font-weight: 600;"><?= htmlspecialchars($data['guruh']) ?></div>
                                </td>
                                <td class="p-4 text-center">
                                    <span class="shakl-badge <?= $badgeClass ?>"><?= $data['shakl'] ?></span>
                                </td>
                                <td class="p-4 text-center">
                                    <button onclick="openHemisModal('<?= addslashes($name) ?>')" class="action-details-btn">
                                        <i class="fas fa-chart-line"></i> Baholar
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <div class="pagination-bar">
            <?php if ($page > 1): ?>
                <a href="?page=<?= $page - 1 ?>&search=<?= urlencode($search) ?>" class="p-link">« Avvalgi</a>
            <?php endif; ?>

            <?php
            // Sahifalar juda ko'p bo'lsa, faqat ma'lum qismini chiqarish (optimizatsiya)
            for ($i = 1; $i <= $total_pages; $i++):
            ?>
                <a href="?page=<?= $i ?>&search=<?= urlencode($search) ?>"
                    class="p-link <?= ($i == $page) ? 'active' : '' ?>">
                    <?= $i ?>
                </a>
            <?php endfor; ?>

            <?php if ($page < $total_pages): ?>
                <a href="?page=<?= $page + 1 ?>&search=<?= urlencode($search) ?>" class="p-link">Keyingi »</a>
            <?php endif; ?>
        </div>
    </div>
    <div id="hemisModal" onclick="closeOutside(event)">
        <div class="modal-dialog">
            <div class="modal-header">
                <div>
                    <h2 id="modalName" style="margin:0; text-transform:uppercase; font-size:16px;"></h2>
                    <div id="modalSub" style="font-size:11px; opacity:0.8; margin-top:3px;"></div>
                </div>
                <span style="cursor:pointer; font-size:24px;" onclick="closeHemisModal()">&times;</span>
            </div>
            <div class="modal-body">
                <table class="modal-table">
                    <thead>
                        <tr>
                            <th width="50">#</th>
                            <th align="left">Fan nomi</th>
                            <th>Semestr</th>
                            <th>JN</th>
                            <th>ON</th>
                            <th>Reyting</th>
                            <th>YN</th>
                            <th>Umumiy</th>
                            <th>Davomat</th>
                        </tr>
                    </thead>
                    <tbody id="modalTableBody"></tbody>
                </table>
            </div>
            <div class="modal-footer">
                <button style="background:#e2e8f0; border:none; padding:8px 20px; border-radius:4px; cursor:pointer; font-weight:bold;" onclick="closeHemisModal()">Yopish</button>
            </div>

        </div>

        <script>
            const REGISTRY = <?= json_encode($js_registry) ?>;

            function openHemisModal(name) {
                const data = REGISTRY[name];
                if (!data) return;

                document.getElementById('modalName').innerText = name;
                document.getElementById('modalSub').innerText = "Guruh: " + data.guruh + " | Ta'lim: " + data.shakl;

                const tbody = document.getElementById('modalTableBody');
                tbody.innerHTML = '';

                data.results.forEach((res, index) => {
                    const u = parseInt(res.u) || 0;
                    const scoreClass = (u < 60) ? 'background:#fee2e2; color:#ef4444;' : '';

                    tbody.innerHTML += `
                    <tr>
                        <td align="center" style="color:#94a3b8;">${index + 1}</td>
                        <td>
                            <div style="font-weight:700; color:#1e293b;">${res.fan}</div>
                            <div style="font-size:10px; color:#94a3b8; font-weight:800; text-transform:uppercase;">Majburiy</div>
                        </td>
                        <td align="center" style="color:#475569; font-weight:600;">${res.semestr || '-'}</td>
                        <td align="center" style="color:#475569;">${res.jn || 0}</td>
                        <td align="center" style="color:#475569;">${res.on || 0}</td>
                        <td align="center" style="font-weight:700; color:#475569;">${res.r || 0}</td>
                        <td align="center" style="color:#475569;">${res.yn || 0}</td>
                        <td align="center">
                            <div style="display:inline-block; padding:4px 10px; border-radius:4px; font-weight:800; border:1px solid #cbd5e1; min-width:50px; ${scoreClass}">
                                ${u}
                            </div>
                        </td>
                        <td align="center" style="font-weight:700; color:${parseInt(res.d) > 25 ? '#ef4444' : '#64748b'};">
                            ${res.d}%
                        </td>
                    </tr>
                `;
                });

                document.getElementById('hemisModal').style.display = "flex";
                document.body.style.overflow = "hidden";
            }

            function closeHemisModal() {
                document.getElementById('hemisModal').style.display = "none";
                document.body.style.overflow = "auto";
            }

            function closeOutside(e) {
                if (e.target.id === "hemisModal") closeHemisModal();
            }
        </script>
    </div>

</body>
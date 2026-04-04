<?php
require "database.php";
session_start();

$stmt = $pdo->query("SELECT t.*, f.nomi as fan_nomi, f.semestr FROM talabalar t LEFT JOIN fanlar f ON t.fan_id = f.id");
$all_data = $stmt->fetchAll(PDO::FETCH_ASSOC);

$js_registry = [];
foreach ($all_data as $row) {
    $uid = $row['user_id'];
    $guruh = trim($row['guruh']);

    $onlyLetters = preg_replace('/[^a-zA-Z]/', '', $guruh);
    $lastChar = strtoupper(substr($onlyLetters, -1));
    $letterCount = strlen($onlyLetters);

    if ($lastChar === 'S') {
        $talim_shakli = "Sirtqi";
    } elseif ($lastChar === 'M') {
        $talim_shakli = "Masofaviy";
    } elseif ($letterCount === 2) {
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

<style>
    #hemisModal {
        display: none;
        position: fixed;
        z-index: 10000;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0, 0, 0, 0.6);
        backdrop-filter: blur(3px);
        align-items: center;
        justify-content: center;
    }

    .modal-dialog {
        background: white;
        width: 95%;
        max-width: 1100px;
        max-height: 85vh;
        border-radius: 6px;
        display: flex;
        flex-direction: column;
        position: relative;
        box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
        animation: modalScale 0.2s ease-out;
    }

    @keyframes modalScale {
        from {
            transform: scale(0.95);
            opacity: 0;
        }

        to {
            transform: scale(1);
            opacity: 1;
        }
    }

    .modal-header {
        background-color: #3498db;
        color: white;
        padding: 15px 20px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        border-radius: 6px 6px 0 0;
    }

    .modal-body {
        padding: 0;
        overflow-y: auto;
        background: #fff;
    }

    .modal-table {
        width: 100%;
        border-collapse: collapse;
    }

    .modal-table thead {
        background: #3498db;
        color: white;
        position: sticky;
        top: 0;
        z-index: 5;
    }

    .modal-table th {
        padding: 12px 10px;
        font-size: 11px;
        text-transform: uppercase;
        border: 1px solid rgba(255, 255, 255, 0.1);
    }

    .modal-table td {
        padding: 12px 10px;
        border-bottom: 1px solid #eee;
        font-size: 13px;
    }

    .modal-footer {
        padding: 12px 20px;
        background-color: #f8fafc;
        border-top: 1px solid #e2e8f0;
        display: flex;
        justify-content: flex-end;
        border-radius: 0 0 6px 6px;
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
</style>

<body>
    <?php require "Includes/yuklash.php"; ?>
    <?php include "Includes/navbar.php"; ?>
    <div class="p-6">
        <div class="search-container">
            <input type="text" id="mainSearch" placeholder="Talaba F.I.O yoki Guruhni yozing..." class="search-input" onkeyup="filterTable()">
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
                                <button onclick="openHemisModal('<?= addslashes($name) ?>')" class="text-gray-500 hover:text-blue-600">
                                    <i class="fas fa-eye text-xl"></i>
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
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
    </div>

    <script>
        const REGISTRY = <?= json_encode($js_registry) ?>;

        function filterTable() {
            const input = document.getElementById('mainSearch');
            const filter = input.value.toUpperCase();
            const rows = document.querySelectorAll('.student-row');
            let counter = 1;

            rows.forEach(row => {
                const name = row.querySelector('.search-name').innerText.toUpperCase();
                const guruh = row.querySelector('.search-guruh').innerText.toUpperCase();
                if (name.includes(filter) || guruh.includes(filter)) {
                    row.style.display = "";
                    row.querySelector('.index-td').innerText = counter++;
                } else {
                    row.style.display = "none";
                }
            });
        }

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
</body>
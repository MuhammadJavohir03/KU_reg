<?php
session_start();
/**
 * 1. SERVER QISMI (PHP)
 */
if (isset($_POST['action']) && $_POST['action'] == 'process_batch') {
    header('Content-Type: application/json');
    require_once 'database.php';

    try {
        $rows = json_decode($_POST['batch'], true);
        if (!$rows) throw new Exception("JSON dekodlashda xato.");

        $res = ['updated' => 0, 'skipped' => 0, 'not_found' => 0, 'failed_data' => []];

        $pdo->beginTransaction();

        // SQL: Tutuq belgilarini ( ‘ ’ ' ` ) filtrlab qidirish
        $sql = "SELECT talaba_id, fio FROM users 
                WHERE REPLACE(REPLACE(REPLACE(REPLACE(LOWER(TRIM(fio)), '‘', ''), '’', ''), '`', ''), '''', '') 
                = REPLACE(REPLACE(REPLACE(REPLACE(LOWER(TRIM(:fio)), '‘', ''), '’', ''), '`', ''), '''', '') 
                LIMIT 1";

        $check_stmt = $pdo->prepare($sql);

        foreach ($rows as $line) {
            if (empty(trim($line))) continue;
            $data = str_getcsv($line, ";");

            if (count($data) < 2) continue;

            $import_id = trim($data[0]);
            $import_fio = trim($data[1]);

            $check_stmt->execute(['fio' => $import_fio]);
            $user = $check_stmt->fetch(PDO::FETCH_ASSOC);

            if ($user) {
                if (trim($user['talaba_id']) !== $import_id) {
                    $upd = $pdo->prepare("UPDATE users SET talaba_id = :id WHERE fio = :fio");
                    $upd->execute(['id' => $import_id, 'fio' => $user['fio']]);
                    $res['updated']++;
                } else {
                    $res['skipped']++;
                }
            } else {
                $res['not_found']++;
                $res['failed_data'][] = [$import_id, $import_fio];
            }
        }

        $pdo->commit();
        echo json_encode($res);
    } catch (Exception $e) {
        if (isset($pdo) && $pdo->inTransaction()) $pdo->rollBack();
        echo json_encode(['error' => $e->getMessage(), 'updated' => 0, 'skipped' => 0, 'not_found' => 0, 'failed_data' => []]);
    }
    exit;
}

require "atmosphere.php";
require "Includes/header.php";
?>

<!DOCTYPE html>
<html lang="uz">

<head>
    <meta charset="UTF-8">
    <title>Safe Sync Pro</title>
    <style>
        :root {
            --main: #00f2fe;
            --bg: #0b0f19;
            --err: #ff4b2b;
        }

        body {
            background: var(--bg);
            font-family: 'Inter', sans-serif;
            color: white;
            margin: 0;
        }

        .container {
            max-width: 850px;
            margin: 40px auto;
            background: rgba(255, 255, 255, 0.03);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 20px;
            padding: 30px;
            backdrop-filter: blur(15px);
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 15px;
            margin: 20px 0;
        }

        .stat-item {
            background: rgba(0, 0, 0, 0.3);
            padding: 15px;
            border-radius: 12px;
            text-align: center;
            border: 1px solid rgba(255, 255, 255, 0.05);
        }

        .stat-item b {
            font-size: 26px;
            color: var(--main);
            display: block;
        }

        .progress-bar-bg {
            background: rgba(255, 255, 255, 0.1);
            height: 12px;
            border-radius: 6px;
            overflow: hidden;
            margin: 20px 0;
            position: relative;
        }

        .progress-bar-fill {
            width: 0%;
            height: 100%;
            background: var(--main);
            transition: 0.4s;
            box-shadow: 0 0 15px var(--main);
        }

        .btn-group {
            display: flex;
            gap: 10px;
        }

        .btn {
            flex: 1;
            padding: 15px;
            border-radius: 10px;
            border: none;
            font-weight: bold;
            cursor: pointer;
            transition: 0.3s;
        }

        .btn-run {
            background: var(--main);
            color: #000;
        }

        .btn-stop {
            background: var(--err);
            color: #fff;
            display: none;
        }

        .btn-export {
            background: #fff;
            color: #000;
            display: none;
            margin-top: 15px;
            width: 100%;
        }

        .terminal {
            background: #000;
            height: 150px;
            border-radius: 10px;
            padding: 15px;
            font-family: 'Consolas', monospace;
            font-size: 12px;
            overflow-y: auto;
            color: #00ff00;
            border: 1px solid #333;
            margin-top: 20px;
        }
    </style>
</head>

<body>
    <?php require "Includes/navbar.php"; ?>

    <div class="container">
        <h2 style="margin-top:0; color: #ffffff;">🔄Sinxronlash</h2>

        <div style="border: 2px dashed #444; padding: 30px; text-align: center; cursor: pointer; border-radius: 15px;" onclick="document.getElementById('csvInput').click()">
            <input type="file" id="csvInput" accept=".csv" style="display:none">
            <span id="fileText">📁 CSV faylni tanlang (ID;FIO)</span>
        </div>

        <div class="stats-grid">
            <div class="stat-item text-white"><span>Yangilandi</span><b id="c-upd">0</b></div>
            <div class="stat-item text-white"><span>O'zgarishsiz</span><b id="c-skp">0</b></div>
            <div class="stat-item text-white"><span>Topilmadi</span><b id="c-nft">0</b></div>
        </div>

        <div class="progress-bar-bg">
            <div class="progress-bar-fill" id="barFill"></div>
        </div>

        <div class="btn-group">
            <button id="btnRun" class="btn  text-white btn-run">START</button>
            <button id="btnStop" class="btn text-white  btn-stop">STOP</button>
        </div>

        <div class="terminal" id="terminal">Tizim tayyor...</div>

        <button id="btnExport" class="btn text-white btn-export">⚠️ TOPILMAGANLARNI CSV EKSPORT QILISH</button>
    </div>

    <script>
        let isActive = false;
        let stats = {
            updated: 0,
            skipped: 0,
            notFound: 0,
            failedRows: []
        };

        document.getElementById('csvInput').onchange = (e) => {
            if (e.target.files[0]) document.getElementById('fileText').innerText = "✅ " + e.target.files[0].name;
        };

        function log(msg, color = "#00ff00") {
            const term = document.getElementById('terminal');
            term.innerHTML += `<div style="color:${color}">> ${msg}</div>`;
            term.scrollTop = term.scrollHeight;
        }

        document.getElementById('btnRun').onclick = function() {
            const file = document.getElementById('csvInput').files[0];
            if (!file) return alert("Faylni tanlang!");

            const reader = new FileReader();
            reader.onload = async function(e) {
                const lines = e.target.result.split(/\r?\n/).filter(l => l.trim() !== "");
                lines.shift(); // Header olib tashlash

                isActive = true;
                document.getElementById('btnRun').disabled = true;
                document.getElementById('btnStop').style.display = 'block';
                log(`Jarayon boshlandi. Jami: ${lines.length} qator.`, "#00f2fe");

                const BATCH_SIZE = 50; // Xatolik bermasligi uchun paketni kichraytirdik
                for (let i = 0; i < lines.length; i += BATCH_SIZE) {
                    if (!isActive) break;

                    const batch = lines.slice(i, i + BATCH_SIZE);
                    const fd = new FormData();
                    fd.append('action', 'process_batch');
                    fd.append('batch', JSON.stringify(batch));

                    try {
                        const resp = await fetch(window.location.href, {
                            method: 'POST',
                            body: fd
                        });
                        const data = await resp.json();

                        if (data.error) {
                            log(`Paketda xato: ${data.error}`, "#ff4b2b");
                        }

                        stats.updated += data.updated;
                        stats.skipped += data.skipped;
                        stats.notFound += data.not_found;
                        if (data.failed_data && data.failed_data.length > 0) {
                            stats.failedRows.push(...data.failed_data);
                            document.getElementById('btnExport').style.display = 'block';
                        }

                        // UI update
                        let p = Math.round(((i + batch.length) / lines.length) * 100);
                        document.getElementById('barFill').style.width = p + '%';
                        document.getElementById('c-upd').innerText = stats.updated;
                        document.getElementById('c-skp').innerText = stats.skipped;
                        document.getElementById('c-nft').innerText = stats.notFound;

                        if (i % 500 === 0) log(`${i + batch.length} qator o'tildi...`);

                    } catch (err) {
                        log("Tarmoq xatosi yoki Server Timeout. Jarayon davom etmoqda...", "#ff4b2b");
                        // To'xtab qolmaslik uchun davom etamiz
                    }
                }

                log(isActive ? "✅ TO'LIQ YAKUNLANDI!" : "⏹ JARAYON TO'XTATILDI!", "#00f2fe");
                document.getElementById('btnRun').disabled = false;
                document.getElementById('btnStop').style.display = 'none';
            };
            reader.readAsText(file);
        };

        document.getElementById('btnStop').onclick = () => {
            isActive = false;
            log("To'xtatish so'rovi yuborildi...", "#ff4b2b");
        };

        document.getElementById('btnExport').onclick = function() {
            if (stats.failedRows.length === 0) return alert("Topilmaganlar mavjud emas.");

            let csvContent = "\uFEFFID;FIO\n";
            stats.failedRows.forEach(row => {
                csvContent += row.join(";") + "\n";
            });

            const blob = new Blob([csvContent], {
                type: 'text/csv;charset=utf-8;'
            });
            const url = URL.createObjectURL(blob);
            const link = document.createElement("a");
            link.setAttribute("href", url);
            link.setAttribute("download", "topilmagan_talabalar.csv");
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
        };
    </script>
</body>

</html>
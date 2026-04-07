<?php
session_start();
require "database.php"; 
$title = "Ma'lumotlar Boshqaruvi";

// PHP mantiqiy qismi o'zgarishsiz qoladi
set_time_limit(0);
ini_set('memory_limit', '1024M');
$response = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['file'])) {
    $file = $_FILES['file']['tmp_name'];
    if (($handle = fopen($file, "r")) !== FALSE) {
        $pdo->exec("DELETE FROM users WHERE role != 'super_admin'");
        $count = 0;
        $header = fgetcsv($handle, 1000, ";") ?: fgetcsv($handle, 1000, "\t") ?: fgetcsv($handle, 1000, ",");
        while (($data = fgetcsv($handle, 1000, ";")) !== FALSE || ($data = fgetcsv($handle, 1000, "\t")) !== FALSE || ($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
            $talaba_id = isset($data[0]) ? trim($data[0]) : '';
            $fio = isset($data[1]) ? trim($data[1]) : '';
            $kurs = isset($data[2]) ? trim($data[2]) : '';
            $email = isset($data[3]) ? trim($data[3]) : '';
            if (empty($email)) continue;
            $password = password_hash("a1234567", PASSWORD_DEFAULT);
            try {
                $stmt = $pdo->prepare("INSERT INTO users (email, password, talaba_id, role, status, fio, kurs, guruh) VALUES (?, ?, ?, 'user', 'active', ?, ?, ?)");
                $stmt->execute([$email, $password, $talaba_id, $fio, $kurs, $kurs]); 
                $count++;
            } catch (Exception $e) { continue; }
        }
        fclose($handle);
        $response = ['success' => true, 'message' => "Tizim muvaffaqiyatli yangilandi ($count ta foydalanuvchi)."];
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_student'])) {
    $stmt = $pdo->prepare("UPDATE users SET fio = ?, talaba_id = ?, email = ?, kurs = ? WHERE id = ?");
    if ($stmt->execute([$_POST['u_fio'], $_POST['u_talaba_id'], $_POST['u_email'], $_POST['u_kurs'], $_POST['u_id']])) {
        $response = ['success' => true, 'message' => "Ma'lumotlar saqlandi."];
    }
}

require 'Includes/header.php';
?>

<style>
    :root {
        --bg: #09090b;
        --card: #18181b;
        --border: #27272a;
        --accent: #3b82f6;
        --accent-glow: rgba(59, 130, 246, 0.5);
        --text-main: #fafafa;
        --text-muted: #a1a1aa;
    }

    body {
        background-color: var(--bg);
        color: var(--text-main);
        font-family: 'Inter', system-ui, sans-serif;
    }

    .glass-card {
        background: var(--card);
        border: 1px solid var(--border);
        border-radius: 12px;
        box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.5);
        transition: border-color 0.3s ease;
    }

    .glass-card:hover {
        border-color: var(--accent);
    }

    .section-title {
        font-size: 1.1rem;
        font-weight: 600;
        color: var(--text-main);
        display: flex;
        align-items: center;
        gap: 10px;
        margin-bottom: 20px;
    }

    /* Qidiruv Inputi */
    .search-group {
        position: relative;
    }

    .custom-input {
        background: #09090b !important;
        border: 1px solid var(--border) !important;
        color: #fff !important;
        padding: 14px 16px;
        border-radius: 8px;
        width: 100%;
        font-size: 1rem;
        transition: all 0.3s ease;
    }

    .custom-input:focus {
        border-color: var(--accent) !important;
        box-shadow: 0 0 15px var(--accent-glow) !important;
        outline: none;
    }

    /* Autocomplete Natijalari (Bax...) */
    #search_results {
        position: absolute;
        top: calc(100% + 8px);
        left: 0;
        right: 0;
        background: #18181b;
        border: 1px solid var(--border);
        border-radius: 8px;
        z-index: 2000;
        max-height: 320px;
        overflow-y: auto;
        display: none;
        box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.7);
    }

    .result-item {
        padding: 12px 16px;
        cursor: pointer;
        border-bottom: 1px solid var(--border);
        display: flex;
        flex-direction: column;
        transition: background 0.2s;
    }

    .result-item:last-child { border-bottom: none; }

    .result-item:hover {
        background: #27272a;
    }

    .result-item .name {
        color: var(--accent);
        font-weight: 600;
        font-size: 0.95rem;
    }

    .result-item .sub {
        color: var(--text-muted);
        font-size: 0.8rem;
    }

    /* Tugma */
    .btn-action {
        background: var(--accent);
        color: white;
        border: none;
        padding: 12px 24px;
        border-radius: 8px;
        font-weight: 600;
        width: 100%;
        transition: transform 0.2s, opacity 0.2s;
    }

    .btn-action:hover {
        opacity: 0.9;
        transform: translateY(-1px);
    }

    .label-tag {
        font-size: 0.75rem;
        text-transform: uppercase;
        color: var(--text-muted);
        letter-spacing: 0.5px;
        margin-bottom: 6px;
        display: block;
    }

    /* Skrollbar stili */
    #search_results::-webkit-scrollbar { width: 6px; }
    #search_results::-webkit-scrollbar-thumb { background: var(--border); border-radius: 10px; }
</style>

<body>
    <?php require 'Includes/navbar.php' ?>

    <div class="container py-5">
        <div class="row g-4">
            <div class="col-lg-8">
                <div class="glass-card p-4">
                    <div class="section-title">
                        <i class="bi bi-person-badge"></i> Talaba Ma'lumotlarini Tahrirlash
                    </div>

                    <div class="search-group mb-4">
                        <label class="label-tag">Ism yoki ID bo'yicha qidiruv</label>
                        <input type="text" id="student_search" class="custom-input" placeholder="Masalan: Baxrom..." autocomplete="off">
                        <div id="search_results"></div>
                    </div>

                    <div id="edit_form_container" style="display:none; animation: fadeIn 0.4s ease;">
                        <form method="POST" class="row g-3">
                            <input type="hidden" name="u_id" id="u_id">
                            <div class="col-md-6">
                                <label class="label-tag">To'liq F.I.O</label>
                                <input type="text" name="u_fio" id="u_fio" class="custom-input">
                            </div>
                            <div class="col-md-6">
                                <label class="label-tag">Talaba ID</label>
                                <input type="text" name="u_talaba_id" id="u_talaba_id" class="custom-input">
                            </div>
                            <div class="col-md-6">
                                <label class="label-tag">Elektron Pochta</label>
                                <input type="email" name="u_email" id="u_email" class="custom-input">
                            </div>
                            <div class="col-md-6">
                                <label class="label-tag">Guruh / Kurs</label>
                                <input type="text" name="u_kurs" id="u_kurs" class="custom-input">
                            </div>
                            <div class="col-12 mt-4">
                                <button type="submit" name="update_student" class="btn-action">O'zgarishlarni Saqlash</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="glass-card p-4 mb-4">
                    <div class="section-title">
                        <i class="bi bi-file-earmark-arrow-up"></i> CSV Import
                    </div>
                    <form method="post" enctype="multipart/form-data">
                        <div class="mb-4">
                            <label class="label-tag">Faylni tanlang</label>
                            <input type="file" name="file" class="form-control bg-dark border-secondary text-white" accept=".csv" required>
                        </div>
                        <button type="submit" class="btn-action">Importni boshlash</button>
                    </form>
                </div>

                <?php if ($response): ?>
                <div class="p-3 rounded border border-info bg-dark text-info font-monospace" style="font-size: 0.85rem;">
                   <i class="bi bi-terminal me-2"></i> <?= $response['message'] ?>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script>
    const searchInput = document.getElementById('student_search');
    const resultsDiv = document.getElementById('search_results');

    searchInput.addEventListener('input', function() {
        let val = this.value.trim();
        if (val.length < 2) {
            resultsDiv.style.display = 'none';
            return;
        }

        fetch('fetch_student.php?mode=list&query=' + encodeURIComponent(val))
            .then(res => res.json())
            .then(data => {
                if (data.success && data.users.length > 0) {
                    resultsDiv.innerHTML = '';
                    data.users.forEach(user => {
                        const div = document.createElement('div');
                        div.className = 'result-item';
                        div.innerHTML = `
                            <span class="name">${user.fio}</span>
                            <span class="sub">ID: ${user.talaba_id}</span>
                        `;
                        div.onclick = () => selectUser(user.id, user.fio);
                        resultsDiv.appendChild(div);
                    });
                    resultsDiv.style.display = 'block';
                } else {
                    resultsDiv.style.display = 'none';
                }
            });
    });

    function selectUser(id, name) {
        searchInput.value = name;
        resultsDiv.style.display = 'none';
        
        fetch('fetch_student.php?mode=single&query=' + id)
            .then(res => res.json())
            .then(data => {
                if(data.success) {
                    document.getElementById('edit_form_container').style.display = 'block';
                    document.getElementById('u_id').value = data.user.id;
                    document.getElementById('u_fio').value = data.user.fio;
                    document.getElementById('u_talaba_id').value = data.user.talaba_id;
                    document.getElementById('u_email').value = data.user.email;
                    document.getElementById('u_kurs').value = data.user.kurs;
                }
            });
    }

    document.addEventListener('click', (e) => {
        if (!searchInput.contains(e.target)) resultsDiv.style.display = 'none';
    });
    </script>
</body>
</html>
<?php
session_start();
require "database.php";

// --- ICHKI API QISMI ---
if (isset($_GET['api_mode'])) {
    header('Content-Type: application/json');
    $query = $_GET['query'] ?? '';
    $mode = $_GET['api_mode'];

    if ($mode === 'list' && !empty($query)) {
        $searchTerm = "%$query%";
        $stmt = $pdo->prepare("SELECT id, fio, talaba_id, email FROM users WHERE (fio LIKE ? OR talaba_id LIKE ? OR email LIKE ?) AND role = 'user' LIMIT 10");
        $stmt->execute([$searchTerm, $searchTerm, $searchTerm]);
        echo json_encode(['success' => true, 'users' => $stmt->fetchAll(PDO::FETCH_ASSOC)]);
    } elseif ($mode === 'single' && !empty($query)) {
        $stmt = $pdo->prepare("SELECT id, fio, talaba_id, email, kurs FROM users WHERE id = ? LIMIT 1");
        $stmt->execute([$query]);
        echo json_encode(['success' => true, 'user' => $stmt->fetch(PDO::FETCH_ASSOC)]);
    }
    exit;
}

$response = null;

// 1. CSV IMPORT
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['file'])) {
    $file = $_FILES['file']['tmp_name'];
    if (($handle = fopen($file, "r")) !== FALSE) {
        $pdo->exec("DELETE FROM users WHERE role != 'super_admin'");
        $count = 0;
        while (($data = fgetcsv($handle, 1000, ";")) !== FALSE || ($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
            $email = isset($data[3]) ? trim($data[3]) : '';
            if (empty($email)) continue;
            $password = password_hash("a1234567", PASSWORD_DEFAULT);
            try {
                $stmt = $pdo->prepare("INSERT INTO users (email, password, talaba_id, role, status, fio, kurs, guruh) VALUES (?, ?, ?, 'user', 'active', ?, ?, ?)");
                $stmt->execute([$email, $password, trim($data[0]), trim($data[1]), trim($data[2]), trim($data[2])]);
                $count++;
            } catch (Exception $e) {
                continue;
            }
        }
        fclose($handle);
        $response = ['success' => true, 'message' => "Import tugadi: $count ta talaba qo'shildi."];
    }
}

// 2. TAHRIRLASH VA QO'SHISH LOGIKASI (O'zgarishsiz qoldi)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_student'])) {
    $stmt = $pdo->prepare("UPDATE users SET fio = ?, talaba_id = ?, email = ?, kurs = ? WHERE id = ?");
    if ($stmt->execute([$_POST['u_fio'], $_POST['u_talaba_id'], $_POST['u_email'], $_POST['u_kurs'], $_POST['u_id']])) {
        $response = ['success' => true, 'message' => "Ma'lumotlar muvaffaqiyatli yangilandi."];
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_student'])) {
    $email = trim($_POST['a_email']);
    $check = $pdo->prepare("SELECT id FROM users WHERE email = ?");
    $check->execute([$email]);
    if ($check->rowCount() > 0) {
        $response = ['success' => false, 'message' => "Xatolik: Ushbu email band!"];
    } else {
        try {
            $pass = password_hash($_POST['a_password'], PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO users (email, password, talaba_id, role, status, fio, kurs, guruh) VALUES (?, ?, ?, 'user', 'active', ?, ?, ?)");
            $stmt->execute([$email, $pass, $_POST['a_talaba_id'], $_POST['a_fio'], $_POST['a_kurs'], $_POST['a_guruh']]);
            $response = ['success' => true, 'message' => "Yangi talaba qo'shildi!"];
        } catch (Exception $e) {
            $response = ['success' => false, 'message' => $e->getMessage()];
        }
    }
}
?>
<!DOCTYPE html>
<html lang="uz">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ma'lumotlar Boshqaruvi</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        :root {
            --bg: #0b1120;
            --card: rgba(30, 41, 59, 0.7);
            --border: rgba(255, 255, 255, 0.1);
            --accent: #3b82f6;
            --text: #f1f5f9;
        }

        body {
            background: var(--bg) !important;
            color: var(--text) !important;
            font-family: 'Inter', sans-serif;
            min-height: 100vh;
        }

        .atm-bg {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: -1;
        }

        /* Navbar Glassmorphism */
        .navbar-custom {
            background: rgba(15, 23, 42, 0.8) !important;
            backdrop-filter: blur(12px);
            border-bottom: 1px solid var(--border) !important;
        }

        .glass-card {
            background: var(--card) !important;
            backdrop-filter: blur(16px);
            border: 1px solid var(--border) !important;
            border-radius: 20px;
            padding: 24px;
            box-shadow: 0 20px 50px rgba(0, 0, 0, 0.3) !important;
            position: relative;
        }

        .custom-input {
            background: rgba(15, 23, 42, 0.6) !important;
            border: 1px solid var(--border) !important;
            color: #fff !important;
            border-radius: 12px;
            padding: 12px;
        }

        #search_results {
            position: absolute;
            top: 100%;
            left: 0;
            width: 100%;
            background: #1e293b !important;
            border: 1px solid var(--accent) !important;
            border-radius: 14px;
            z-index: 1050;
            display: none;
            max-height: 300px;
            overflow-y: auto;
        }

        .result-item {
            padding: 12px;
            cursor: pointer;
            border-bottom: 1px solid var(--border) !important;
        }

        .result-item:hover {
            background: rgba(59, 130, 246, 0.2) !important;
        }

        .hidden-panel {
            display: none;
        }

        .btn-action {
            background: var(--accent) !important;
            border: none;
            padding: 12px;
            border-radius: 12px;
            color: white;
            width: 100%;
            font-weight: 600;
        }
    </style>
</head>

<body>
    <div class="atm-bg"></div>

    <?php require "Includes/navbar.php"; ?>
    <?php require "atmosphere.php"; ?>

    <div class="container pb-5 mt-5">
        <div class="row g-4">
            <div class="col-lg-7">
                <div class="glass-card">
                    <h5 class="mb-4 text-white"><i class="bi bi-search me-2 text-primary"></i> Talaba tahrirlash</h5>
                    <div class="position-relative mb-4">
                        <input type="text" id="student_search" class="custom-input w-100" placeholder="F.I.O yoki ID kiriting..." autocomplete="off">
                        <div id="search_results"></div>
                    </div>

                    <div id="edit_form_container" class="hidden-panel pt-4 border-top border-secondary">
                        <form method="POST" class="row g-3">
                            <input type="hidden" name="u_id" id="u_id">
                            <div class="col-12">
                                <label class="text-white-50 small">F.I.O</label>
                                <input type="text" name="u_fio" id="u_fio" class="custom-input w-100" required>
                            </div>
                            <div class="col-md-6">
                                <label class="text-white-50 small">Talaba ID</label>
                                <input type="text" name="u_talaba_id" id="u_talaba_id" class="custom-input w-100" required>
                            </div>
                            <div class="col-md-6">
                                <label class="text-white-50 small">Kurs</label>
                                <input type="text" name="u_kurs" id="u_kurs" class="custom-input w-100" required>
                            </div>
                            <div class="col-12">
                                <label class="text-white-50 small">Email</label>
                                <input type="email" name="u_email" id="u_email" class="custom-input w-100" required>
                            </div>
                            <div class="col-12 mt-4">
                                <button type="submit" name="update_student" class="btn-action">O'zgarishlarni saqlash</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-lg-5">
                <div class="glass-card mb-4">
                    <h6 class="mb-3"><i class="bi bi-person-plus me-2 text-info"></i> Yangi talaba</h6>
                    <form method="POST" class="row g-2">
                        <input type="text" name="a_fio" placeholder="F.I.O" class="custom-input w-100 mb-2" required>
                        <div class="col-6"><input type="text" name="a_talaba_id" placeholder="ID" class="custom-input w-100" required></div>
                        <div class="col-6"><input type="text" name="a_kurs" placeholder="Kurs" class="custom-input w-100" required></div>
                        <input type="email" name="a_email" placeholder="Email" class="custom-input w-100 my-2" required>
                        <input type="password" name="a_password" value="a1234567" class="custom-input w-100 mb-2" required>
                        <button type="submit" name="add_student" class="btn btn-info w-100 text-white fw-bold">Qo'shish</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        // JS Qidiruv va yuklash funksiyalari (Oldingi kod kabi)
        const sInput = document.getElementById('student_search');
        const sRes = document.getElementById('search_results');

        sInput.addEventListener('input', function() {
            let val = this.value.trim();
            if (val.length < 2) {
                sRes.style.display = 'none';
                return;
            }

            fetch(`?api_mode=list&query=${encodeURIComponent(val)}`)
                .then(res => res.json())
                .then(data => {
                    sRes.innerHTML = '';
                    if (data.success && data.users.length > 0) {
                        data.users.forEach(u => {
                            const div = document.createElement('div');
                            div.className = 'result-item';
                            div.innerHTML = `<strong>${u.fio}</strong><br><small class="text-info">${u.email}</small>`;
                            div.onclick = () => {
                                sInput.value = u.fio;
                                sRes.style.display = 'none';
                                loadStudent(u.id);
                            };
                            sRes.appendChild(div);
                        });
                        sRes.style.display = 'block';
                    }
                });
        });

        function loadStudent(id) {
            fetch(`?api_mode=single&query=${id}`)
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        const panel = document.getElementById('edit_form_container');
                        panel.classList.remove('hidden-panel');
                        document.getElementById('u_id').value = data.user.id;
                        document.getElementById('u_fio').value = data.user.fio;
                        document.getElementById('u_talaba_id').value = data.user.talaba_id;
                        document.getElementById('u_email').value = data.user.email;
                        document.getElementById('u_kurs').value = data.user.kurs;
                    }
                });
        }
    </script>
</body>
<?php $a=5; ?>
</html>

<?php require "Includes/header.php"; ?>

<?php
require "database.php";

$error = "";
$success = false;

if (isset($_POST['submit'])) {
    $familiya = $_POST['familiya'];
    $ism = $_POST['ism'];
    $otasining_ismi = $_POST['otasi'];
    $guruh = $_POST['guruh'];
    $yonalish = $_POST['yonalish'];
    $kurs = $_POST['kurs'];
    $hemis_parol = $_POST['parol'];
    $talaba_id_manual = $_POST['id'];

    // fanlar arrayini olish
    $fanlar = $_POST['fanlar'] ?? [];
    $fan1 = $fanlar[0] ?? null;
    $fan2 = $fanlar[1] ?? null;
    $fan3 = $fanlar[2] ?? null;

    try {
        $sql = "INSERT INTO bepul (talaba_id, familiya, ism, otasi, guruh, yonalish, kurs, parol, fan1, fan2, fan3)
                VALUES (:talaba_id, :familiya, :ism, :otasi, :guruh, :yonalish, :kurs, :parol, :fan1, :fan2, :fan3)";

        $stmt = $conn->prepare($sql);
        $stmt->execute([
            ':talaba_id' => $talaba_id_manual,
            ':familiya' => $familiya,
            ':ism' => $ism,
            ':otasi' => $otasining_ismi,
            ':guruh' => $guruh,
            ':yonalish' => $yonalish,
            ':kurs' => $kurs,
            ':parol' => $hemis_parol,
            ':fan1' => $fan1,
            ':fan2' => $fan2,
            ':fan3' => $fan3,
        ]);

        $success = true;
    } catch (PDOException $e) {
        if ($e->errorInfo[1] == 1062) {
            $error = "❌ Bu Talaba ID allaqachon mavjud!";
        } else {
            $error = "❌ Xatolik yuz berdi!";
        }
    }
}
?>

<body>

    <div class="container bg-body-tertiary p-3">

        <a class="back-btn mb-3" href="arizalar.php">
            <span class="arrow">←</span>
            <span class="text">Orqaga</span>
        </a>

        <style>
            .back-btn {
                display: inline-flex;
                align-items: center;
                gap: 8px;
                padding: 8px 16px;
                border: 2px solid #dc3545;
                color: #dc3545;
                text-decoration: none;
                border-radius: 8px;
                font-weight: 500;
                transition: all 0.3s ease;
            }

            .back-btn:hover {
                background-color: #dc3545;
                color: white;
            }

            /* strelka animatsiyasi */
            .back-btn .arrow {
                font-size: 20px;
                transition: transform 0.3s ease;
            }

            .back-btn:hover .arrow {
                transform: translateX(-5px);
            }

            /* text ham ozgina siljiydi */
            .back-btn .text {
                transition: transform 0.3s ease;
            }

            .back-btn:hover .text {
                transform: translateX(-3px);
            }
        </style>



        <?php if ($error): ?>
            <div class="alert alert-danger text-center"><?= $error ?></div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="alert alert-success text-center">✅ Ma'lumot saqlandi!</div>
        <?php endif; ?>

        <script>
            setTimeout(() => {
                let alertBox = document.querySelector('.alert');
                if (alertBox) alertBox.style.display = 'none';
            }, 2000);
        </script>
        <form id="myForm" action="" method="POST">
            <div class="input-group mb-3">
                <input required name="familiya" type="text" class="form-control border-danger" placeholder="Familiyangiz">
                <input required name="ism" type="text" class="form-control border-danger" placeholder="Ismingiz">
                <input required name="otasi" type="text" class="form-control border-danger" placeholder="Otangizni ismi">
            </div>

            <div class="input-group mb-3">
                <input required name="guruh" type="text" class="form-control border-danger" placeholder="Guruhingiz">
                <input required name="yonalish" type="text" class="form-control border-danger" placeholder="Yo'nalishingiz">
                <input required name="kurs" type="text" class="form-control border-danger" placeholder="Kursingiz">
            </div>

            <div class="input-group mb-3">
                <input required name="parol" type="password" class="form-control border-danger" placeholder="HEMIS parolingiz">
                <input
                    name="id"
                    type="text"
                    class="form-control border-danger"
                    placeholder="Talaba ID"
                    pattern="\d{12}"
                    title="Talaba ID aniq 12 raqamdan iborat bo'lishi kerak"
                    required>

                <script>
                    const talabaInput = document.querySelector('input[name="id"]');

                    talabaInput.addEventListener('input', () => {
                        const val = talabaInput.value;
                        if (/^\d{12}$/.test(val)) {
                            talabaInput.style.borderColor = 'green';
                        } else {
                            talabaInput.style.borderColor = 'red';
                        }
                    });
                </script>
            </div>

            <div id="fan-container">
                <div class="input-group mb-3">
                    <input name="fanlar[]" type="text" class="form-control border-danger" placeholder="Fan nomi">
                    <button type="button" class="btn btn-danger removeFan">- Olib tashlash</button>
                </div>
            </div>

            <script>
                document.getElementById("myForm").addEventListener("submit", function(e) {
                    let inputs = document.querySelectorAll("#myForm input");
                    let isValid = true;

                    inputs.forEach(input => {
                        if (input.type !== "button" && input.type !== "submit") {
                            if (input.value.trim() === "") {
                                input.style.borderColor = "red";
                                isValid = false;
                            } else {
                                input.style.borderColor = "green";
                            }
                        }
                    });

                    if (!isValid) {
                        e.preventDefault();
                        alert("❗ Barcha maydonlarni to‘ldiring!");
                    }
                });
            </script>

            <button type="button" id="addFan" class="btn bg-danger text-white">
                FAN QO'SHISH
            </button>

            <button name="submit" type="submit" class="btn bg-success text-white">
                Saqlash va Jo'natish
            </button>

            <script>
                document.getElementById("myForm").addEventListener("submit", function(e) {
                    let inputs = document.querySelectorAll("#myForm input[type='text'], #myForm input[type='password']");
                    let isValid = true;

                    inputs.forEach(input => {
                        if (input.value.trim() === "") {
                            input.style.borderColor = "red";
                            isValid = false;
                        } else {
                            input.style.borderColor = "green";
                        }
                    });

                    if (!isValid) {
                        e.preventDefault(); // submitni to‘xtatadi
                        alert("❗ Iltimos, barcha maydonlarni to‘ldiring!");
                    }
                });
            </script>

        </form>

        <script>
            let maxFans = 3;
            let count = 1;

            document.getElementById("addFan").addEventListener("click", function() {
                if (count >= maxFans) {
                    alert("Maximum 3 ta fan qo‘shish mumkin!");
                    return;
                }

                let container = document.getElementById("fan-container");

                let div = document.createElement("div");
                div.className = "input-group mb-3";
                div.innerHTML = `
        <input name="fanlar[]" type="text" class="form-control border-danger" placeholder="Fan nomi">
        <button type="button" class="btn btn-danger removeFan">- Olib tashlash</button>
    `;

                container.appendChild(div);
                count++;
            });
        </script>

        <script>
            document.addEventListener("click", function(e) {
                if (e.target.classList.contains("removeFan")) {
                    let container = document.getElementById("fan-container");

                    // kamida 1 ta fan qolishi kerak
                    if (container.children.length > 1) {
                        e.target.parentElement.remove();
                        count--;
                    } else {
                        alert("Kamida 1 ta fan bo‘lishi kerak!");
                    }
                }
            });
        </script>
    </div>

</body>
<?php require "Includes/footer.php"; ?>
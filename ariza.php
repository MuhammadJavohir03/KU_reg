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

        <a class="btn btn-outline-danger " href="arizalar.php">
            <h5><- Orqaga</h5>
        </a>



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
        <form action="" method="POST">
            <div class="input-group mb-3">
                <input name="familiya" type="text" class="form-control border-danger" placeholder="Familiyangiz">
                <input name="ism" type="text" class="form-control border-danger" placeholder="Ismingiz">
                <input name="otasi" type="text" class="form-control border-danger" placeholder="Otangizni ismi">
            </div>

            <div class="input-group mb-3">
                <input name="guruh" type="text" class="form-control border-danger" placeholder="Guruhingiz">
                <input name="yonalish" type="text" class="form-control border-danger" placeholder="Yo'nalishingiz">
                <input name="kurs" type="text" class="form-control border-danger" placeholder="Kursingiz">
            </div>

            <div class="input-group mb-3">
                <input name="parol" type="password" class="form-control border-danger" placeholder="HEMIS parolingiz">
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
                </div>
            </div>

            <button type="button" id="addFan" class="btn bg-danger text-white">
                FAN QO'SHISH
            </button>

            <button name="submit" type="submit" class="btn bg-success text-white">
                Saqlash va Jo'natish
            </button>
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
                div.innerHTML = `<input name="fanlar[]" type="text" class="form-control border-danger" placeholder="Fan nomi">`;

                container.appendChild(div);
                count++;
            });
        </script>
        <?php $success;

        ?>

    </div>

</body>
<?php require "Includes/footer.php"; ?>
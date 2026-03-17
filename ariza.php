<?php require "Includes/header.php"; ?>
<?php require "database.php"; ?>

<?php
require "Includes/header.php";
require "database.php";

if (isset($_POST['submit'])) {

    // 1️⃣ Talaba ma'lumotlari
    $familiya = $_POST['familiya'];
    $ism = $_POST['ism'];
    $otasining_ismi = $_POST['otasi'];
    $guruh = $_POST['guruh'];
    $yonalish = $_POST['yonalish'];
    $kurs = $_POST['kurs'];
    $hemis_parol = $_POST['parol'];
    $talaba_id_manual = $_POST['id']; // HEMIS ID

    // 2️⃣ Talabani “bepul” jadvaliga qo‘shish
    $sql = "INSERT INTO bepul (talaba_id, familiya, ism, otasi, guruh, yonalish, kurs, parol)
            VALUES (:talaba_id, :familiya, :ism, :otasi, :guruh, :yonalish, :kurs, :parol)";
    $stmt = $conn->prepare($sql);
    $stmt->execute([
        ':talaba_id' => $talaba_id_manual,
        ':familiya' => $familiya,
        ':ism' => $ism,
        ':otasi' => $otasining_ismi,
        ':guruh' => $guruh,
        ':yonalish' => $yonalish,
        ':kurs' => $kurs,
        ':parol' => $hemis_parol
    ]);

    // 3️⃣ AUTO_INCREMENT ichki ID sini olish
    $bepul_id = $conn->lastInsertId();

    // 4️⃣ Fanlarni alohida jadvalga qo‘shish
    if (!empty($_POST['fanlar'])) {
        foreach ($_POST['fanlar'] as $fan) {
            $fan = trim($fan);
            if ($fan == '') continue; // bo‘sh inputlarni o‘tkazib yuboradi

            // Shu yerda qo‘shiladi ✅
            $sql = "INSERT INTO fanlar (bepul_id, fan_nomi) VALUES (:bepul_id, :fan)";
            $stmt = $conn->prepare($sql);
            $stmt->execute([
                ':bepul_id' => $bepul_id,
                ':fan' => $fan
            ]);
        }
    }

    echo "Ma'lumot saqlandi!";
}
?>

<body>

    <?php require "Includes/navbar.php"; ?>

    <div class="container bg-body-tertiary p-3">
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
                <input name="parol" type="text" class="form-control border-danger" placeholder="HEMIS parolingiz">
                <input name="id" type="text" class="form-control border-danger" placeholder="Talaba ID">
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
            let maxFans = 10;
            let count = 1;

            document.getElementById("addFan").addEventListener("click", function() {
                if (count >= maxFans) {
                    alert("Maximum 10 ta fan qo‘shish mumkin!");
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
    </div>

</body>
<?php require "Includes/footer.php"; ?>
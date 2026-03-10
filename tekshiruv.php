<?php $title = "Bosh Sahifa"; ?>
<?php require "Includes/header.php"; ?>

<body>
    <?php require "Includes/navbar.php"; ?>

    <div class="container bg-body-tertiary p-5 rounded-2">
        <form action="" method="POST" autocomplete="off">
            <div id="mavzular" class="nav">
                <div class="nav">
                    <input type="number" name="mavzu[]" placeholder="1-Topshiriq" class="btn btn-outline-danger" min="0" max="100">

                </div>
            </div>

            <script>
                let count = 1;
                const maxInputs = 15;

                function addInput() {
                    if (count >= maxInputs) {
                        alert("Maksimal 15 ta mavzu kiritish mumkin!");
                        return;
                    }
<<<<<<< HEAD
                
                    count++;
                    const div = document.createElement("div");
                    div.classList.add("topshiriqlar");
                    div.innerHTML = `<input type="number" name="mavzu[]" placeholder="${count}-Topshiriq" class="btn bg-danger" min="0" max="100">`;
=======

                    count++;
                    const div = document.createElement("div");
                    div.classList.add("topshiriqlar");
                    div.innerHTML = `<input type="number" name="mavzu[]" placeholder="${count}-Topshiriq" class="btn btn-outline-danger" min="0" max="100">`;
>>>>>>> c7e3039978decd051f5b213bfd00d72d48d66559
                    document.getElementById("mavzular").appendChild(div);
                }
            </script>

            <div class="topshiriqlar">
                <input type="number" name="mavzuoraliq" placeholder="Oraliq" class="btn btn-outline-danger" min=0, max=100>
                <input type="number" name="mavzuyakuniy" placeholder="Yakuniy" class="btn btn-outline-danger" min=0, max=100>
                <button type="button" class="btn btn-outline-danger" onclick="addInput()">+</button>
                <button type="submit" class="btn btn-success">Hisobla</button>
            </div>
        </form>

    </div>



    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>
</body>
<div class="container">
    <?php


    function hisobla($mavzular, $mavzuoraliq, $mavzuyakuniy)
    {

        if (count($mavzular) == 0) return;

        $umumiy = array_sum($mavzular);
        $orta = $umumiy / count($mavzular);

        $Joriy  = $orta * 0.4;
        $Oraliq = $mavzuoraliq * 0.2;
        $Yakuniy = $mavzuyakuniy * 0.4;

        $umumiy_Foiz = round($Joriy + $Oraliq + $Yakuniy);

        if ($umumiy_Foiz >= 60) {
            echo "
    <div  style='margin:auto; with:50%; text-align:center; padding:15px; background:#e0f7fa;
                border:2px solid #26a69a; font-size:20px; background-color: #cdfffa; font-weight:bold;'>
        Umumiy: -- $umumiy_Foiz % -- Sizda yetarli % bor ;)
    </div>";
        } else {
            echo "
    <div style='margin:auto; with:50%; text-align:center; padding:15px; background:#e0f7fa;
                border:2px solid #da2424; background-color: #ffbfbf; font-size:20px; font-weight:bold;'>
        Umumiy: -- $umumiy_Foiz % -- Afsus yetarli % to'play olmadingiz :(
    </div>";
        }
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {

        $mavzular = isset($_POST['mavzu'])
            ? array_map('floatval', $_POST['mavzu'])
            : [];

        $mavzuoraliq  = (float)($_POST['mavzuoraliq'] ?? 0);
        $mavzuyakuniy = (float)($_POST['mavzuyakuniy'] ?? 0);

        hisobla($mavzular, $mavzuoraliq, $mavzuyakuniy);
    }
    ?>
    <?php
    $umumiy_Foiz = 70; // misol
    $umumiy_Foiz = max(0, min(100, $umumiy_Foiz));
    ?>

</div>

<?php require "Includes/footer.php"; ?>
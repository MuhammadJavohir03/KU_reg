<?php
session_start();
require "database.php";
?>

<?php require "Includes/header.php"; ?>
<?php require "atmosphere.php"; ?>

<body>
    <?php require "Includes/yuklash.php"; ?>
    <?php require "Includes/navbar.php"; ?>


    <div class="container text-center">
        <div class="row">
            <div class="col">
                <a class="nav-link btn bg-white p-2 m-3 shadow" href="bepul_royhat.php">Bepul Arizalar Ro'yxati</a>
            </div>
            <div class="col">
                <a class="nav-link btn bg-white p-2 m-3 shadow" href="pullik_royhat.php">Pullik Arizalar Ro'yxati</a>
            </div>
        </div>


    </div>

</body>
<?php require "Includes/footer.php"; ?>
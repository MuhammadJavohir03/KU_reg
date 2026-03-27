<?php session_start(); ?>
<?php require "Includes/header.php"; ?>


<body>

    <?php require "Includes/navbar.php"; ?>
    <canvas class="z-n1" id="bg"></canvas>
    <div class="bg-white container p-5" style="height: 100vh;">
        <a class="m-2 btn btn-outline-success" href="ariza_bepul.php">
            Qayta topshirish (Bepul imkoniyat)
        </a>
        <a class="m-2 btn btn-outline-success" href="ariza_pullik.php">
            Qayta topshirish (Pullik)
        </a>
    </div>
</body>
<?php require "Includes/footer.php"; ?>
<script src="add.js"></script>
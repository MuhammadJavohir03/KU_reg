<?php
$current_page = basename($_SERVER['PHP_SELF']);
?>

<div class="container">
    <header class="sticky-top d-flex flex-wrap justify-content-center py-3 mb-4 border-bottom">

        <a href="index.php" class="d-flex align-items-center mb-3 mb-md-0 me-md-auto link-body-emphasis text-decoration-none">
            <span class="fs-4 text-black">
                <h1><img class="" style="height:60px; with:60px;" src="Logos/Logo.jpg" alt="">-Registrator</h1>
            </span>
        </a>



        <ul class="nav nav-pills">

            <li class="nav-item">
                <a href="index.php"
                    class="nav-link shadow-sm <?= ($current_page == 'index.php') ? 'active' : '' ?>">
                    Bosh Sahifa
                </a>
            </li>

            <!-- <li class="nav-item">
                <a href="tekshiruv.php"
                    class="nav-link shadow-sm <?= ($current_page == 'tekshiruv.php') ? 'active' : '' ?>">
                    Tekshiruv
                </a>
            </li> -->

            <li class="nav-item">
                <a href="academic.php"
                    class="nav-link shadow-sm <?= ($current_page == 'academic.php') ? 'active' : '' ?>">
                    Academic Policy
                </a>
            </li>

            <li class="nav-item">
                <a href="about.php"
                    class="nav-link shadow-sm <?= ($current_page == 'about.php') ? 'active' : '' ?>">
                    Biz Haqimizda
                </a>
            </li>

            <li class="nav-item">
                <a href="ariza.php"
                    class="nav-link shadow-sm <?= ($current_page == 'ariza.php') ? 'active' : '' ?>">
                    Ariza
                </a>
            </li>

        </ul>
    </header>
</div>
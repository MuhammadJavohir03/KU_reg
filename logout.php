<?php
session_start();

// sessionni tozalash
session_unset();
session_destroy();

// bosh sahifa yoki login sahifaga qaytarish
header("Location: index.php"); // yoki index.php
exit;

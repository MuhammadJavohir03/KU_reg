<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $title ?? "REGISTRATOR"; ?></title>

    <link rel="icon" type="image/png" href="Logos/logo_login.png?v=2">

    <script src="script.js" defer></script>
    <script src="hover.js" defer></script>
    <link rel="stylesheet" href="css-file/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
</head>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<style>
    #nprogress-bar {
        position: fixed;
        top: 0; left: 0; width: 0%; height: 3px;
        background: #0dcaf0; /* Text-info rangi */
        z-index: 9999;
        transition: width 0.4s ease;
        box-shadow: 0 0 10px #0dcaf0;
    }
</style>

<div id="nprogress-bar"></div>
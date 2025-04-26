<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Giniy</title>
    <link href="static/css/vid.css" rel="stylesheet">
    <link href="static/css/auth.css" rel="stylesheet">
</head>
<?php
require_once '../includes/config.php';

session_destroy();
header('Location: ../index.php');
exit;
?>
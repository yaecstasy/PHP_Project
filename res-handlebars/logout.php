<?php // Do not put any HTML above this line

session_start();
unset($_SESSION['name']); // To Log the user out
unset($_SESSION['user_id']); // To Log the user out
header('Location: index.php');

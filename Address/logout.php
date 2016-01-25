<?php
session_start();
require_once "pdo.php";
unset($_SESSION['name']);
unset($_SESSION['user_id']);
header('Location: index.php');
?>
<?php
// This script works even if you are not logged in
require_once 'pdo.php';
header("Content-type: application/json; charset=utf-8");

$stmt = $pdo->query('SELECT * FROM Profile');
$profiles = $stmt->fetchAll(PDO::FETCH_ASSOC);
echo(json_encode($profiles));

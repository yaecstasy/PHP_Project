<?php // Do not put any HTML above this line

require_once 'pdo.php';

$pdo->query("DROP TABLE IF EXISTS users");

$pdo->query("DROP TABLE IF EXISTS autos");

$pdo->query("
CREATE TABLE users (
   user_id INT UNSIGNED NOT NULL
     AUTO_INCREMENT KEY,
   name VARCHAR(128),
   email VARCHAR(128),
   password VARCHAR(128));
");

$pdo->query("
ALTER TABLE users ADD INDEX(email);
");

$pdo->query("
INSERT INTO users (name,email,password) VALUES ('Chuck','csev@umich.edu','1a52e17fa899cf40fb04cfc42e6352f1');
");

$pdo->query("
CREATE TABLE autos (
   auto_id INT UNSIGNED NOT NULL
     AUTO_INCREMENT KEY,
   user_id INTEGER,
   make VARCHAR(128),
   year INTEGER,
   mileage INTEGER
);
");

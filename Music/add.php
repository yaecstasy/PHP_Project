<?php

// Make the database connection and leave it in the variable $pdo
require_once 'pdo.php';

session_start();

// If the user is not logged in redirect back to index.php
// with an error
if ( ! isset($_SESSION['user_id']) ) {
    die("ACCESS DENIED");
    exit();
}

// If the user requested cancel go back to index.php
if ( isset($_POST['cancel']) ) {
    header('Location: index.php');
    exit();
}


// Handle the incoming data
if ( isset($_POST['title']) && isset($_POST['album']) && 
     isset($_POST['artist']) && isset($_POST['seconds']) && 
     isset($_POST['rating']) && isset($_POST['add']) ){

    if ( strlen($_POST['title']) < 1 || strlen($_POST['album'])<1 || strlen($_POST['artist'])<1 || strlen($_POST['seconds']) <1 || strlen($_POST['rating']) <1 ) {
        $failure = "All fields are required";
        $_SESSION['error']=$failure;
         header("Location: add.php");
         exit();
    } 

    if ( !is_numeric($_POST['seconds'])) {
        $failure = "Seconds must be an integer";
        $_SESSION['error'] = $failure;
        header("Location: add.php");
        exit();
    }
    if ( !is_numeric($_POST['rating']) ) {
        $failure = "Rating must be an integer";
        $_SESSION['error'] = $failure;
        header("Location: add.php");
        exit();
    }


  $stmt = $pdo->prepare('INSERT INTO Track
        (title, album, artist, seconds, rating) VALUES (:mk, :yr, :mi, :md, :pr)');
    $stmt->execute(array(
        ':mk' => $_POST['title'],
        ':yr' => $_POST['album'],
        ':mi' => $_POST['artist'],
        ':md' => $_POST['seconds'],
        ':pr' => $_POST['rating'])
    );

    $_SESSION['success'] = "Record added";
    header("Location: index.php");
    exit();
}


?>
<!DOCTYPE html>
<html>
<head>
<title>Tzu-I Lee</title>
</head>
<body style="font-family: sans-serif;">
<h1>Adding Music Track for <?= htmlentities($_SESSION['name']); ?></h1>
<?php
if ( isset($_SESSION['error']) ) {
    echo('<p style="color: red;">'.htmlentities($_SESSION['error'])."</p>\n");
    unset($_SESSION['error']);
}
if ( isset($_SESSION['success']) ) {
    echo('<p style="color: green;">'.htmlentities($_SESSION['success'])."</p>\n");
    unset($_SESSION['success']);
}
?>
<form method="post" action="add.php">

<p>Title:
<input type="text" name="title" size="60"/></p>
<p>Album:
<input type="text" name="album" size="60"/></p>
<p>Artist:
<input type="text" name="artist" size="30"/></p>
<p>Seconds:
<input type="text" name="seconds" size="30"/></p>
<p>Rating:
<input name="rating" type="text" size="30"></p>

<p>
<input type="submit" value="Add" name="add">
<input type="submit" name="cancel" value="Cancel">
</p>
</form>

</body>
</html>
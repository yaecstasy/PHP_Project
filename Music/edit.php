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


// Load up the profile in question
$stmt = $pdo->prepare('SELECT * FROM Track
    WHERE track_id = :uid');
$stmt->execute(array( ':uid' => $_REQUEST['track_id']));
$profile = $stmt->fetch(PDO::FETCH_ASSOC);
if ( $profile === false ) {
    $_SESSION['error'] = "Could not load profile";
    header('Location: index.php');
    exit();
}

// Handle the incoming data
if ( isset($_POST['title']) && isset($_POST['album']) && 
     isset($_POST['artist']) && isset($_POST['seconds']) && 
     isset($_POST['rating']) ) {

     if ( strlen($_POST['title']) < 1 || strlen($_POST['album'])<1 || strlen($_POST['artist'])<1 || strlen($_POST['seconds']) <1 || strlen($_POST['rating']) <1 ) {
        $failure = "All fields are required";
        $_SESSION['error']=$failure;
         header("Location: edit.php");
         exit();
    } 

    if ( !is_numeric($_POST['seconds'])) {
        $failure = "Seconds must be an integer";
        $_SESSION['error'] = $failure;
        header("Location: edit.php");
        exit();
    }
    if ( !is_numeric($_POST['rating']) ) {
        $failure = "Rating must be an integer";
        $_SESSION['error'] = $failure;
        header("Location: edit.php");
        exit();
    }


    $stmt = $pdo->prepare('UPDATE Track SET
        title=:fn, album=:ln, 
        artist=:em, seconds=:he, rating=:su
        WHERE track_id=:pid');

    $stmt->execute(array( 
        ':pid' => $_REQUEST['track_id'],
        ':fn' => $_POST['title'],
        ':ln' => $_POST['album'],
        ':em' => $_POST['artist'],
        ':he' => $_POST['seconds'],
        ':su' => $_POST['rating'])
    );


    $_SESSION['success'] = "Record edited";
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
<h1>Editing Track></h1>
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
<form method="post" action="edit.php">
<input type="hidden" name="track_id"
value="<?= htmlentities($_REQUEST['track_id']); ?>"
/>
<p>Title:
<input type="text" name="title" size="60"
value="<?= htmlentities($profile['title']); ?>"
/></p>
<p>Album:
<input type="text" name="album" size="60"
value="<?= htmlentities($profile['album']); ?>"
/></p>
<p>Artist:
<input type="text" name="artist" size="30"
value="<?= htmlentities($profile['artist']); ?>"
/></p>
<p>Seconds:<br/>
<input type="text" name="seconds" size="80"
value="<?= htmlentities($profile['seconds']); ?>"
/></p>
<p>Rating:<br/>
<input type="text" name="rating" size="80"
value="<?= htmlentities($profile['rating']); ?>"
/></p>


<p>
<input type="submit" value="Save">
<input type="submit" name="cancel" value="Cancel">
</p>
</form>

</body>
</html>

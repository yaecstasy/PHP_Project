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
if ( isset($_POST['name']) && isset($_POST['addr']) && isset($_POST['city']) && isset($_POST['state']) && isset($_POST['zip'])){


    if ( strlen($_POST['name']) < 1 || strlen($_POST['addr'])<1 || strlen($_POST['zip']) <1 || strlen($_POST['city']) <1 ) {
        $failure = "All fields are required";
        $_SESSION['error']=$failure;
         header("Location: add.php");
         exit();
    } 
    if(strlen($_POST['state']) !==2){
        $failure = "State must be exactly 2 characters long";
        $_SESSION['error']=$failure;
        header("Location: add.php");
        exit();
    }

    if ( !is_numeric($_POST['zip'])) {
        $failure = "Zip must be an integer";
        $_SESSION['error'] = $failure;
        header("Location: add.php");
        exit();
    }


  $stmt = $pdo->prepare('INSERT INTO Address
        (name, addr, city, state, zip) VALUES (:mk, :yr, :mi, :md, :pr)');
    $stmt->execute(array(
        ':mk' => $_POST['name'],
        ':yr' => $_POST['addr'],
        ':mi' => $_POST['city'],
        ':md' => $_POST['state'],
        ':pr' => $_POST['zip'])
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
<h1>Tracking Addresses for <?= htmlentities($_SESSION['name']); ?></h1>

<?php
if ( isset($_SESSION['error'])) {
    echo('<p style="color: red;">'.htmlentities($_SESSION['error'])."</p>\n");
    unset($_SESSION['error']);
}
if ( isset($_SESSION['success']) ) {
    echo('<p style="color: green;">'.htmlentities($_SESSION['success'])."</p>\n");
    unset($_SESSION['success']);
}
?>
<form method="post" action="add.php">

<p>Name:
<input type="text" name="name" size="60"/></p>
<p>Address:
<input type="text" name="addr" size="60"/></p>
<p>City:
<input type="text" name="city" size="30"/></p>
<p>State:
<input type="text" name="state" size="30"/></p>
<p>Zip:
<input name="zip" type="text" size="30"></p>

<p>
<input type="submit" value="Add" name="add">
<input type="submit" name="cancel" value="Cancel">
</p>
</form>

</body>
</html>
<?php
session_start();
require_once "pdo.php";
// If the user is not logged in redirect back to index.php
// with an error
if ( ! isset($_SESSION['name']) ) {
    die("ACCESS DENIED");
}
// If the user requested logout go back to index.php

// If the user requested cancel
if ( isset($_POST['cancel']) ) {

    header('Location: index.php');
    exit();
}

if ( isset($_POST['make']) && isset($_POST['year']) && isset($_POST['mileage']) && isset($_POST['price']) && isset($_POST['model'])) {
    if ( strlen($_POST['make']) < 1 || strlen($_POST['model']) < 1) {
        $failure = "Error in input data";
        $_SESSION['error']=$failure;
         header("Location: view.php");
         exit();
    } 
    else if ( !is_numeric($_POST['year']) || !is_numeric($_POST['mileage']) || !is_numeric($_POST['price']) ) {
  	  $_SESSION['error'] = "Error in input data";
   	 header("Location: view.php");
  	  exit();
    }
    else{
    $stmt = $pdo->prepare('INSERT INTO autos
        (user_id, make, year, mileage, model, price) VALUES ( :uid, :mk, :yr, :mi, :md, :pr)');
    $stmt->execute(array(
        ':uid' => $_SESSION['user_id'],
        ':mk' => $_POST['make'],
        ':yr' => $_POST['year'],
        ':mi' => $_POST['mileage'],
        ':md' => $_POST['model'],
        ':pr' => $_POST['price'])
    );
		$_SESSION['success']="Record added";
		header("Location: view.php");
  	    return;
    }
}    



?>
<!DOCTYPE html>
<html>
<head>
<title>Tzu-I Lee's Resume Registry</title>
</head>
<body style="font-family: sans-serif;">
<h1>Add Profile for Tzu-I</h1> 
<form method="post">
<p>Make:
<input type="text" size="40" name="make"></p>
<p>Model:
<input type="text" size="40" name="model"></p>
<p>Year:
<input type="text" size="40" name="year"></p>
<p>Mileage:
<input type="text" size="40" name="mileage"></p>
<P>Price
<input type="text" size="40" name="price"></p>
<input type="submit" value="Add" name="add">
<input type="submit" value="cancel" name="cancel">
</form>

</body>
</html>
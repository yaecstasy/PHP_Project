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

    header('Location: view.php');
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
    	  $sql = "UPDATE autos SET 
            make = :mk, year = :yr, mileage = :mi, model = :md, price = :pr WHERE auto_id = :auto_id ";
  		  $stmt = $pdo->prepare($sql);


  		$stmt->execute(
  		 	array(
        ':mk' => $_POST['make'],
        ':yr' => $_POST['year'],
        ':mi' => $_POST['mileage'],
        ':md' => $_POST['model'],
        ':pr' => $_POST['price'],
        ':auto_id' => $_POST['auto_id']
        ));

		$_SESSION['success']="Record added";

		header("Location: view.php");
  	    return;

    }

}    

$stmt = $pdo->prepare("SELECT * FROM autos where auto_id = :xyz");
$stmt->execute(array(":xyz" => $_GET['auto_id']));
$row = $stmt->fetch(PDO::FETCH_ASSOC);
if ( $row === false ) {
    $_SESSION['error'] = 'Bad value for user_id';
    header( 'Location: view.php' ) ;
    return;
}

$make = htmlentities($row['make']);
$model = htmlentities($row['model']);
$year = htmlentities($row['year']);
$mileage= htmlentities($row['mileage']);
$price= htmlentities($row['price']);
$auto_id = htmlentities($row['auto_id']);

?>
<!DOCTYPE html>
<html>
<head>
<title></title>
</head>
<body style="font-family: sans-serif;">
<p>Update</p>
<form method="post">
<p>Make:
<input type="text" size="40" name="make" value="<?= $make ?>"></p>
<p>Model:
<input type="text" size="40" name="model" value="<?= $model ?>"></p>
<p>Year:
<input type="text" size="40" name="year" value="<?= $year ?>"></p>
<p>Mileage:
<input type="text" size="40" name="mileage" value="<?= $mileage ?>"></p>
<P>Price
<input type="text" size="40" name="price" value="<?= $price ?>"></p>
<input type="hidden" size="40" name="auto_id" value="<?= $auto_id ?>"></p>
<input type="submit" value="Update" name="update">
<a href="view.php">Cancel</a></p>
</form>


</body>
</html>
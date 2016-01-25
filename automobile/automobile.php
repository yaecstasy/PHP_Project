<?php
session_start();
// If the user is not logged in redirect back to index.php
// with an error
if ( ! isset($_SESSION['name']) ) {
    die("ACCESS DENIED");
}
// If the user requested logout go back to index.php
if ( isset($_POST['logout']) ) {
    header('Location: index.php');
    exit();
}
// If the user requested clear all
if ( isset($_POST['clear']) ) {
    $_SESSION['autos'] = array();
    $_SESSION['success'] = "Database reset";
    header('Location: automobile.php');
    exit();
}

if ( isset($_POST['make']) && isset($_POST['year']) && isset($_POST['mile']) ) {
    if ( strlen($_POST['make']) < 1) {
        $failure = "Make is required";
        $_SESSION['error']=$failure;
         header("Location: automobile.php");
         exit();
    } 
    else if ( !is_numeric($_POST['year']) || !is_numeric($_POST['mile']) ) {
  	  $_SESSION['error'] = "Mileage and year must be numeric";
   	 header("Location: automobile.php");
  	  exit();
    }
    else{
    	$_SESSION['autos'][] = array(
   	 'make' => $_POST['make'],
    'year' => $_POST['year'],
    'mileage' => $_POST['mile']
		);
		header("Location: automobile.php");
  	  exit();
    }
}    



?>
<!DOCTYPE html>
<html>
<head>
<title></title>
</head>
<body style="font-family: sans-serif;">
<h1>Tracking Autos for <?= htmlentities($_SESSION['name']); ?></h1>
<?php
if ( isset($_SESSION['success']) ) {
   echo('<p style="color: green;">'.htmlentities($_SESSION['success'])."</p>\n");
    unset($_SESSION['success']);
}
if ( isset($_SESSION['error']) ) {
    echo('<p style="color: red;">'.htmlentities($_SESSION['error'])."</p>\n");
    unset($_SESSION['error']);
}
?>

<form method="post">
<p>Make:
<input type="text" size="40" name="make"></p>
<p>Year:
<input type="text" size="40" name="year"></p>
<p>Mileage:
<input type="text" size="40" name="mile"></p>
<input type="submit" value="Add" name="add">
<input type="submit" value="Clear all" name="clear">
<input type="submit" name="logout" value="Log out">
</form>
<h1>Automobiles</h1>
<ul>
<?php 
  foreach ($_SESSION['autos'] as $items) {
  	
  		echo('<li>'.htmlentities($items['year']).' '.htmlentities($items['make']).' / '.htmlentities($items['mileage'])."</li>\n");

  }

?>
</ul>
</body>
</html>
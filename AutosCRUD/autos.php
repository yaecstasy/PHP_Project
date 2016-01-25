<?php
session_start();
require_once "pdo.php";
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

    $stmt = $pdo->prepare('DELETE FROM autos WHERE user_id = :uid');
    $stmt->execute(array( ':uid' => $_SESSION['user_id']) ) ;

    $_SESSION['success'] = "Database reset";
    header('Location: autos.php');
    exit();
}

if ( isset($_POST['make']) && isset($_POST['year']) && isset($_POST['mileage']) ) {
    if ( strlen($_POST['make']) < 1) {
        $failure = "Make is required";
        $_SESSION['error']=$failure;
         header("Location: autos.php");
         exit();
    } 
    else if ( !is_numeric($_POST['year']) || !is_numeric($_POST['mileage']) ) {
  	  $_SESSION['error'] = "Mileage and year must be numeric";
   	 header("Location: autos.php");
  	  exit();
    }
    else{
    $stmt = $pdo->prepare('INSERT INTO autos
        (user_id, make, year, mileage) VALUES ( :uid, :mk, :yr, :mi)');
    $stmt->execute(array(
        ':uid' => $_SESSION['user_id'],
        ':mk' => $_POST['make'],
        ':yr' => $_POST['year'],
        ':mi' => $_POST['mileage'])
    );
		header("Location: autos.php");
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
<h1>Tracking autos for <?= htmlentities($_SESSION['name']); ?></h1>
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
<input type="text" size="40" name="mileage"></p>
<input type="submit" value="Add" name="add">
<input type="submit" value="Clear all" name="clear">
<input type="submit" name="logout" value="Log out">
</form>
<h1>autosmobiles</h1>
<ul>
<?php 
  $stmt = $pdo->query("SELECT make, year, mileage FROM autos");
while ( $row = $stmt->fetch(PDO::FETCH_ASSOC) ) {
    echo('<li>'.$row['year']." ".$row['make']." / ".$row['mileage']."</li>");
}

?>
</ul>
</body>
</html>
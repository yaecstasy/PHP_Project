<?php
require_once "pdo.php";
session_start();

?>
<html>
<head></head><body>
<title>Tzu-I Lee's Resume Registry</title>
<h1>Tzu-I Lee's Resume Registry</h1>
<?php
if (isset($_SESSION['error'])) {
    echo '<p style="color:red">'.$_SESSION['error']."</p>\n";
    unset($_SESSION['error']);
}
if (isset($_SESSION['success'])) {
    echo '<p style="color:green">'.$_SESSION['success']."</p>\n";
    unset($_SESSION['success']);
}

if (isset($_SESSION['name'])){   
    echo '<a href="logout.php">Log out'."</a>\n";
}else{
    echo '<a href="login.php">Log in'."</a>\n";
}


echo('<p>'."</p>\n");
echo('<table border="1">'."\n");
echo "<tr><td>";
echo("Name");
echo("</td><td>");
echo("Headline");
if (isset($_SESSION['name'])){
    echo("</td><td>");
    echo("Action");
}

$stmt =$stmt = $pdo->query("SELECT * FROM Profile");
while ( $row = $stmt->fetch(PDO::FETCH_ASSOC) ) {
    echo "<tr><td>";
    echo('<a href="view.php?profile_id='.$row['profile_id'].'">');
    echo(htmlentities($row['first_name']).' '.htmlentities($row['last_name']));
    echo "</a>";
    echo("</td><td>");
    echo(htmlentities($row['headline']));
    if (isset($_SESSION['name'])){
     echo("</td><td>");
     echo('<a href="edit.php?profile_id='.$row['profile_id'].'">Edit</a> ');
     echo('<a href="delete.php?profile_id='.$row['profile_id'].'">Delete</a>');
    }
}

?>
</table>
<br>
<a href="add.php">Add New</a>
</body>

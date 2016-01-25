<?php
require_once "pdo.php";
session_start();

?>
<html>
<head></head>

<body style="font-family: sans-serif;">
<title>Tzu-I Lee</title>
<h1>Welcome to the Tracks Database</h1>
<?php
if (isset($_SESSION['error'])) {
    echo '<p style="color:red">'.$_SESSION['error']."</p>\n";
    unset($_SESSION['error']);
}
if (isset($_SESSION['success'])) {
    echo '<p style="color:green">'.$_SESSION['success']."</p>\n";
    unset($_SESSION['success']);
}

if (!isset($_SESSION['name'])){   
    echo '<a href="login.php">Please log in'."</a>\n";
}else{

$stmt = $pdo->query("SELECT * FROM Track");

if ($stmt->rowCount() == 0){
    echo"No rows found";
    echo"<br>";
}
else{
echo('<p>'."</p>\n");
echo('<table border="1">'."\n");
echo "<tr><td>";
echo("Title");
echo("</td><td>");
echo("Album");
echo("</td><td>");
echo("Artist");
echo("</td><td>");
echo("Seconds");
echo("</td><td>");
echo("Rating");
echo("</td><td>");
echo("Action");

while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    
    echo "<tr><td>";
    echo(htmlentities($row['title']));
    echo("</td><td>");
    echo(htmlentities($row['album']));
    echo("</td><td>");
    echo(htmlentities($row['artist']));
    echo("</td><td>");
    echo(htmlentities($row['seconds']));
    echo("</td><td>");
    echo(htmlentities($row['rating']));
    echo("</td><td>");
    echo('<a href="edit.php?track_id='.$row['track_id'].'">Edit</a> ');
    echo('/');
    echo('<a href="delete.php?track_id='.$row['track_id'].'">Delete</a>');
    
}


echo"</table>";
}
echo"<br>";
echo('<a href="add.php">Add New Entry</a>');
echo"<br>";
echo"<br>";
echo ('<a href="logout.php">Log out</a>');
}

?>
</body>

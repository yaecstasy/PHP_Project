<?php
session_start();
require_once "pdo.php";

// p' OR email = 'barb@umich.edu

if ( isset($_POST['email']) && isset($_POST['password'])  ) {
    echo("Handling POST data...\n");

    $sql = "SELECT name FROM users 
        WHERE email = :em AND password = :pw";
    echo "<pre>\n$sql\n</pre>\n";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(array(
        ':em' => $_POST['email'], 
        ':pw' => $_POST['password']));
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    var_dump($row);
   if ( $row === FALSE ) {
      echo "<h1>Login incorrect.</h1>\n";
      unset($_SESSION['name']);
   } else { 
      echo "<p>Login success.</p>\n";
      $_SESSION['name'] = $row['name'];
   }
}
if ( isset($_SESSION['name']) ) {
   echo('<p>Hello '.htmlentities($_SESSION['name']).'</p>'."\n");
   echo('<p><a href="logout.php">Logout</a></p>'."\n");
   return;
}
?>
<p>Please Login</p>
<form method="post">
<p>Email:
<input type="text" size="40" name="email"></p>
<p>Password:
<input type="text" size="40" name="password"></p>
<p><input type="submit" value="Login"/>
<a href="<?php echo($_SERVER['PHP_SELF']);?>">Refresh</a></p>
</form>
<p>
Check out this 
<a href="http://xkcd.com/327/" target="_blank">XKCD comic that is relevant</a>.

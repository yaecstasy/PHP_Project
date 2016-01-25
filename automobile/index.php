<?php // Do not put any HTML above this line
// session_start() and header() fail if any (even one
// character) of output has been sent.
session_start();
unset($_SESSION['name']);
$_SESSION['autos'] = array();

$salt = 'XyZzy12*_';
$stored_hash = hash('md5', $salt.'php123');  // Pw is php123
$failure = false;  // If we have no POST data
// Check to see if we have some POST data, if we do process it
if ( isset($_POST['who']) && isset($_POST['pass']) ) {
    if ( strlen($_POST['who']) < 1 || strlen($_POST['pass']) < 1 ) {
        $failure = "User name and password are required";
        $_SESSION['error']=$failure;
         header("Location: index.php");
         exit();
    } else {
        $check = hash('md5', $salt.$_POST['pass']);
        if ( $check == $stored_hash ) {
            $_SESSION['name'] = $_POST['who'];
            // Redirect the browser to automobile.php
            header("Location: automobile.php");
            exit();
        } else {
            $failure = "Incorrect password";
            $_SESSION['error']=$failure;
            header("Location: index.php");
            exit();
        }
    }
}


// Fall through into the View 
?>
<!DOCTYPE html>
<html>
<head>
<title>Tzu-I Lee's Login Page</title>

</head>
<body style="font-family: sans-serif;">
<h1>Please Log In</h1>
<?php
if ( isset($_SESSION['error']) ) {
    echo('<p style="color: red;">'.htmlentities($_SESSION['error'])."</p>\n");
    unset($_SESSION['error']);
}

?>
<form method="POST" action="index.php">
<label for="nam">User Name</label>
<input type="text" name="who" id="nam"><br/>
<label for="id_1723">Password</label>
<input type="text" name="pass" id="id_1723"><br/>
<input type="submit" value="Log In">
</form>
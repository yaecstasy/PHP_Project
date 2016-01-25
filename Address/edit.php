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
$stmt = $pdo->prepare('SELECT * FROM Address WHERE address_id = :uid');
$stmt->execute(array(':uid' => $_REQUEST['address_id']));
$profile = $stmt->fetch(PDO::FETCH_ASSOC);
if ( $profile === false ) {
    $_SESSION['error'] = "Could not load profile";
    header('Location: index.php');
    exit();
}

// Handle the incoming data
if ( isset($_POST['name']) && isset($_POST['addr']) && isset($_POST['city']) && isset($_POST['state']) && 
     isset($_POST['zip'])){

    
     if ( strlen($_POST['name']) < 1 || strlen($_POST['addr'])<1 || strlen($_POST['zip']) <1 || strlen($_POST['city']) <1 ) {
        $failure = "All fields are required";
        $_SESSION['error']=$failure;
         header("Location: edit.php?address_id=".$_REQUEST['address_id']);
         exit();
    } 
    if(strlen($_POST['state']) !==2){
        $failure = "State must be exactly 2 characters long";
        $_SESSION['error']=$failure;
        header("Location: edit.php?address_id=".$_REQUEST['address_id']);
        exit();
    }

    if ( !is_numeric($_POST['zip'])) {
        $failure = "Zip must be an integer";
        $_SESSION['error'] = $failure;
        header("Location: edit.php?address_id=".$_REQUEST['address_id']);
        exit();
    }

    $stmt = $pdo->prepare('UPDATE Address SET
        name=:fn, addr=:ln, 
        city=:em, state=:he, zip=:su
        WHERE address_id=:pid');
    $stmt->execute(array( 
        ':pid' => $_REQUEST['address_id'],
        ':fn' => $_POST['name'],
        ':ln' => $_POST['addr'],
        ':em' => $_POST['city'],
        ':he' => $_POST['state'],
        ':su' => $_POST['zip'])
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
<h1>Editing Address</h1>
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
<input type="hidden" name="address_id"
value="<?= htmlentities($profile['address_id']); ?>"
/>
<p>Name:
<input type="text" name="name" size="60"
value="<?= htmlentities($profile['name']); ?>"
/></p>
<p>Address:
<input type="text" name="addr" size="60"
value="<?= htmlentities($profile['addr']); ?>"
/></p>
<p>City:
<input type="text" name="city" size="30"
value="<?= htmlentities($profile['city']); ?>"
/></p>
<p>State:<br/>
<input type="text" name="state" size="80"
value="<?= htmlentities($profile['state']); ?>"
/></p>
<p>Zip:<br/>
<input type="text" name="zip" size="80"
value="<?= htmlentities($profile['zip']); ?>"
/></p>


<p>
<input type="submit" value="Save">
<input type="submit" name="cancel" value="Cancel">
</p>
</form>

</body>
</html>

<?php
session_start();
require_once "pdo.php";

if ( ! isset($_SESSION['name']) ) {
    die("ACCESS DENIED");
}

if ( isset($_POST['cancel']) ) {

    header('Location: index.php');
    exit();
}

if ( isset($_POST['firstname']) && isset($_POST['lastname']) && isset($_POST['email']) && isset($_POST['headline']) && isset($_POST['summary'])) {
    if ( strlen($_POST['firstname']) < 1 || strlen($_POST['lastname']) < 1 || strlen($_POST['email']) < 1 || strlen($_POST['summary']) < 1 || strlen($_POST['headline']) < 1) {
        $failure = "All fields are required";
        $_SESSION['error']=$failure;
         header("Location: edit.php?profile_id=".$_POST['profile_id']);
         exit();
    } 
    else if ( !strpos($_POST['email'],'@') ) {
      $_SESSION['error'] = "Email address must contain @";
     header("Location: edit.php?profile_id=".$_POST['profile_id']);
      exit();
    }
    else{

      $sql = "UPDATE Profile SET 
            first_name = :mk, last_name = :yr, email = :mi, headline = :md, summary = :pr WHERE profile_id = :profile_id ";
        $stmt = $pdo->prepare($sql);


      $stmt->execute(
        array(
        ':mk' => $_POST['firstname'],
        ':yr' => $_POST['lastname'],
        ':mi' => $_POST['email'],
        ':md' => $_POST['headline'],
        ':pr' => $_POST['summary'],
        ':profile_id' => $_POST['profile_id']
        ));

    $_SESSION['success']="Record added";

    header("Location: index.php");
        return;
    }
}    

$stmt = $pdo->prepare("SELECT * FROM Profile where profile_id = :xyz");
$stmt->execute(array(":xyz" => $_GET['profile_id']));
$row = $stmt->fetch(PDO::FETCH_ASSOC);
if ( $row === false ) {
    $_SESSION['error'] = 'Bad value for user_id';
    header( 'Location: index.php' ) ;
    return;
}

$firstname = htmlentities($row['first_name']);
$lastname = htmlentities($row['last_name']);
$email = htmlentities($row['email']);
$headline= htmlentities($row['headline']);
$summary= htmlentities($row['summary']);
$profile_id = htmlentities($row['profile_id']);

?>
<!DOCTYPE html>
<html>
<head>
<title>Tzu-I Lee's Resume Registry</title>
</head>
<body style="font-family: sans-serif;">
<h1>Adding Profile for Tzu-I</h1>
<?php

if (isset($_SESSION['error'])) {
    echo '<p style="color:red">'.$_SESSION['error']."</p>\n";
    unset($_SESSION['error']);
}
?>
<form method="post">
<p>First Name:
<input type="text" size="40" name="firstname" value="<?= $firstname ?>"></p>
<p>Last Name:
<input type="text" size="40" name="lastname" value="<?= $lastname ?>"></p>
<p>Email:
<input type="text" size="46" name="email" value="<?= $email ?>"></p>
<p>Headline:<br>
<input type="text" size="54" name="headline" value="<?= $headline ?>"></p>
<P>Summary:<br>
<textarea name="summary" rows="10" cols="52"><?php echo $summary;?></textarea></P>
<input type="hidden" size="40" name="profile_id" value="<?= $profile_id ?>"></p>
<input type="submit" value="Update" name="update">
<input type="submit" value="cancel" name="cancel">
</form>

</body>
</html>
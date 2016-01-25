<?php
require_once "pdo.php";
require_once 'util.php';

session_start();
if ( ! isset($_SESSION['name']) ) {
    die("ACCESS DENIED");
}
// If the user requested logout go back to index.php
if ( isset($_POST['logout']) ) {
    header('Location: index.php');
    exit();
}

if ( ! isset($_REQUEST['profile_id']) ) {
    $_SESSION['error'] = "Missing profile_id";
    header('Location: index.php');
    exit();
}

// Load up the profile in question
$stmt = $pdo->prepare('SELECT * FROM Profile
    WHERE profile_id = :prof AND user_id = :uid');
$stmt->execute(array( ':prof' => $_REQUEST['profile_id'], 
    ':uid' => $_SESSION['user_id']));
$profile = $stmt->fetch(PDO::FETCH_ASSOC);
if ( $profile === false ) {
    $_SESSION['error'] = "Could not load profile";
    header('Location: index.php');
    exit();
}

$stmt = $pdo->prepare('SELECT * FROM Position
    WHERE profile_id = :prof');
$stmt->execute(array( ':prof' => $_REQUEST['profile_id']));

// Load up the position rows
$positions = loadPos($pdo, $_REQUEST['profile_id']);


?>
<html>
<head></head><body>

<p>First Name:<?= htmlentities($profile['first_name']); ?></p>
<p>Last Name:<?= htmlentities($profile['last_name']); ?></p>
<p>Email:<?= htmlentities($profile['email']); ?></p>
<p>Headline:<br/>
<?= htmlentities($profile['headline']); ?></p>
<p>Summary:<br/>
<?= htmlentities($profile['summary']); ?></p>


<?php

$pos = 0;
echo('<div id="position_fields"> Positions:'."\n");
echo'<ul>';
foreach( $positions as $position ){
        $pos++;
        echo '<li>'.$position['year'].':';
        echo(htmlentities($position['description']).'</li>');
};
echo("</ul></div>\n");
?>
</table>

<a href="index.php">Done</a>
</body>


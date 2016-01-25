<?php
session_start();
require_once "pdo.php";
// If the user is not logged in redirect back to index.php
// with an error
if ( ! isset($_SESSION['name']) ) {
    die("ACCESS DENIED");
}

if ( isset($_POST['delete']) && isset($_POST['track_id']) ) {
    $sql = "DELETE FROM Track WHERE track_id = :zip";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(array(':zip' => $_POST['track_id']));
    $_SESSION['success'] = 'Record deleted';
    header( 'Location: index.php' ) ;
    return;
}

$stmt = $pdo->prepare("SELECT * FROM Track where track_id = :xyz");
$stmt->execute(array(":xyz" => $_REQUEST['track_id']));

$row = $stmt->fetch(PDO::FETCH_ASSOC);

?>

<p>Confirm: <?= htmlentities($row['title']) ?></p>

<form method="post">
<input type="hidden"
name="track_id" value="<?= $row['track_id'] ?>">
<input type="submit" value="Delete" name="delete">
<a href="index.php">Cancel</a>
</form>

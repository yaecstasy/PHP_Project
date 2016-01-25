<?php

// Make the database connection and leave it in the variable $pdo
require_once 'pdo.php';
require_once 'util.php';

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

// Make sure the REQUEST parameter is present
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

// Handle the incoming data
if ( isset($_POST['first_name']) && isset($_POST['last_name']) && 
     isset($_POST['email']) && isset($_POST['headline']) && 
     isset($_POST['summary']) ) {

    $msg = validateProfile();
    if ( is_string($msg) ) {
        $_SESSION['error'] = $msg;
        header("Location: edit.php?profile_id=" . $_REQUEST["profile_id"]);
        exit();
    }

    // Validate position entries if present
    $msg = validatePos();
    if ( is_string($msg) ) {
        $_SESSION['error'] = $msg;
        header("Location: edit.php?profile_id=" . $_REQUEST["profile_id"]);
        exit();
    }

    $stmt = $pdo->prepare('UPDATE Profile SET
        first_name=:fn, last_name=:ln, 
        email=:em, headline=:he, summary=:su
        WHERE profile_id=:pid AND user_id=:uid');
    $stmt->execute(array( 
        ':pid' => $_REQUEST['profile_id'],
        ':uid' => $_SESSION['user_id'],
        ':fn' => $_POST['first_name'],
        ':ln' => $_POST['last_name'],
        ':em' => $_POST['email'],
        ':he' => $_POST['headline'],
        ':su' => $_POST['summary'])
    );


    // Clear out the old position entries
    $stmt = $pdo->prepare('DELETE FROM Position
        WHERE profile_id=:pid');
    $stmt->execute(array( ':pid' => $_REQUEST['profile_id']));

    // Insert the position entries
    $rank = 1;
    for($i=1; $i<=9; $i++) {
        if ( ! isset($_POST['year'.$i]) ) continue;
        if ( ! isset($_POST['desc'.$i]) ) continue;
        $year = $_POST['year'.$i];
        $desc = $_POST['desc'.$i];

        $stmt = $pdo->prepare('INSERT INTO Position
            (profile_id, rank, year, description) 
        VALUES ( :pid, :rank, :year, :desc)');
        $stmt->execute(array(
            ':pid' => $_REQUEST['profile_id'],
            ':rank' => $rank,
            ':year' => $year,
            ':desc' => $desc)
        );
        $rank++;
    }

    $_SESSION['success'] = "Profile updated";
    header("Location: index.php");
    exit();
}

// Load up the position rows
$positions = loadPos($pdo, $_REQUEST['profile_id']);

?>
<!DOCTYPE html>
<html>
<head>
<title>Dr. Chuck's Profile Edit</title>
</head>
<body style="font-family: sans-serif;">
<h1>Editing Profile for <?= htmlentities($_SESSION['name']); ?></h1>
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
<input type="hidden" name="profile_id"
value="<?= htmlentities($_REQUEST['profile_id']); ?>"
/>
<p>First Name:
<input type="text" name="first_name" size="60"
value="<?= htmlentities($profile['first_name']); ?>"
/></p>
<p>Last Name:
<input type="text" name="last_name" size="60"
value="<?= htmlentities($profile['last_name']); ?>"
/></p>
<p>Email:
<input type="text" name="email" size="30"
value="<?= htmlentities($profile['email']); ?>"
/></p>
<p>Headline:<br/>
<input type="text" name="headline" size="80"
value="<?= htmlentities($profile['headline']); ?>"
/></p>
<p>Summary:<br/>
<textarea name="summary" rows="8" cols="80">
<?= htmlentities($profile['summary']); ?>
</textarea>


<?php

$pos = 0;
echo('<p>Position: <input type="submit" onclick="addPos(); return false;" value="+">'."\n");
echo('<div id="position_fields">'."\n");
foreach( $positions as $position ) {
        $pos++;
    	echo('<div class="position">');
        echo
'<p>Year: <input type="text" name="year'.$pos.'" value="'.$position['year'].'" />
<input type="button" value="-" onclick="removePos(this)"></p>';
        echo '<textarea name="desc'.$pos.'" rows="8" cols="80">'."\n";
        echo htmlentities($position['description'])."\n";
        echo "\n</textarea>\n</div>\n";
}
echo("</div></p>\n");
?>

<p>
<input type="submit" value="Save">
<input type="submit" name="cancel" value="Cancel">
</p>
</form>
<script>
count = <?= $pos ?>;
// http://stackoverflow.com/questions/17650776/add-remove-html-inside-div-using-javascript
function addPos() { 
    window.console && console.log("Adding position");
    if ( count >= 9 ) {
        alert("Maximum of nine position entries exceeded");
        return;
    }
    count++;

    var div = document.createElement('div');
    div.className = 'position';
    div.innerHTML = 
'<p>Year: <input type="text" name="year'+count+'" value="" /> \
<input type="button" value="-" onclick="removePos(this)"></p>\
<textarea name="desc'+count+'" rows="8" cols="80"></textarea>';

     document.getElementById('position_fields').appendChild(div);

}

function removePos(input) {
    document.getElementById('position_fields').removeChild( input.parentNode.parentNode );
}

</script>
</body>
</html>

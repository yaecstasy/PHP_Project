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


// Handle the incoming data
if ( isset($_POST['first_name']) && isset($_POST['last_name']) && 
     isset($_POST['email']) && isset($_POST['headline']) && 
     isset($_POST['summary']) && isset($_POST['add']) ){

    $msg = validateProfile();
    if ( is_string($msg) ) {
        $_SESSION['error'] = $msg;
        header("Location:add.php");
        exit();
    }

    // Validate position entries if present
    $msg = validatePos();
    if ( is_string($msg) ) {
        $_SESSION['error'] = $msg;
        header("Location: add.php");
        exit();
    }

   /* $stmt = $pdo->prepare('UPDATE Profile SET
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
 */
  $stmt = $pdo->prepare('INSERT INTO Profile
        (user_id, first_name, last_name, email, headline, summary) VALUES ( :uid, :mk, :yr, :mi, :md, :pr)');
    $stmt->execute(array(
        ':uid' => $_SESSION['user_id'],
        ':mk' => $_POST['first_name'],
        ':yr' => $_POST['last_name'],
        ':mi' => $_POST['email'],
        ':md' => $_POST['headline'],
        ':pr' => $_POST['summary'])
    );

    $profile_id = $pdo->lastInsertId(); 
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
            ':pid' => $profile_id,
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


?>
<!DOCTYPE html>
<html>
<head>
<title>Tzu-I Lee Add</title>
</head>
<body style="font-family: sans-serif;">
<h1>Adding Profile for <?= htmlentities($_SESSION['name']); ?></h1>
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
<form method="post" action="add.php">

<p>First Name:
<input type="text" name="first_name" size="60"/></p>
<p>Last Name:
<input type="text" name="last_name" size="60"/></p>
<p>Email:
<input type="text" name="email" size="30"/></p>
<p>Headline:<br/>
<input type="text" name="headline" size="80"/></p>
<p>Summary:<br/>
<textarea name="summary" rows="8" cols="80"></textarea>


<?php

echo('<p>Position: <input type="submit" onclick="addPos(); return false;" value="+">'."\n");
echo('<div id="position_fields">'."\n");
echo("</div></p>\n");
?>

<p>
<input type="submit" value="Add" name="add">
<input type="submit" name="cancel" value="Cancel">
</p>
</form>
<script>
count = 0
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
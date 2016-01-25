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
    // Clear out the old education entires

     $stmt = $pdo->prepare('DELETE FROM Education
        WHERE profile_id=:pid');
    $stmt->execute(array( ':pid' => $_REQUEST['profile_id']));
    
    //// Insert the Education entries
    $rank = 1;
    for($i=1; $i<=9; $i++) {
    if ( !isset($_POST['edu_year'.$i]) ) continue;
    if ( !isset($_POST['edu_school'.$i]) ) continue;
    $year = $_POST['edu_year'.$i];
    $school = $_POST['edu_school'.$i];
    $profile_id=$_REQUEST['profile_id'];

    $institution_id=false;
        $stmt = $pdo->prepare('SELECT institution_id FROM Institution WHERE name = :name');
        $stmt->execute(array(':name' => $school));

        $row=$stmt->fetch(PDO::FETCH_ASSOC);
        if ($row !== false) {
            $institution_id=$row['institution_id'];
        }
        //If No institiuion was found

        if ($institution_id===false) {
            $stmt=$pdo->prepare('INSERT INTO Institution (name) VALUES(:name)');
            $stmt->execute(array(':name' => $school));
            $institution_id= $pdo->lastInsertId();
        }

    $stmt = $pdo->prepare('INSERT INTO Education
            (profile_id, rank, year, institution_id) 
        VALUES ( :pid, :rank, :year, :lid)');
        $stmt->execute(array(
            ':pid' => $profile_id,
            ':rank' => $rank,
            ':year' => $year,
            ':lid' => $institution_id)
        );
        $rank++;
    }

    $_SESSION['success'] = "Profile updated";
    header("Location: index.php");
    exit();
}

// Load up the position rows
$positions = loadPos($pdo, $_REQUEST['profile_id']);
$educations = loadEdu($pdo, $_REQUEST['profile_id']);


?>
<!DOCTYPE html>
<html>
<head>
<title>Dr. Chuck's Profile Edit</title>
<link rel="stylesheet" href="css/jquery-ui-1.11.4-ui-lightness.css">
</head>
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

<p>Education: <input type="submit" id="addEdu" value="+">
        <div id="edu_fields">
        <?php
            // put existing education
            $edu = 0;
            foreach( $educations as $education ) {
                $edu++;
                echo('<div id="edu'.$edu.'">');
                echo('<p>Year: <input type="text" name="edu_year'.$edu.'" value="'.$education['year'].'"/>');
                echo('<input type="button" value="-" onclick="$(\'#edu'.$edu.'\').remove();return false;"></p>');
                echo('<p>School: <input type="text" size="80" name="edu_school'.$edu.'" class="school" value="'.$education['name'].'" /></p></div>');
                
            }
    
        ?>
        </div></p>
        <p>Position: <input type="submit" id="addPos" value="+">
        <div id="position_fields">
        <?php
            // put existing position
            $pos = 0;
            foreach( $positions as $position ) {
                $pos++;
                echo('<div id="position'.$pos.'">');
                echo('<p>Year: <input type="text" name="year'.$pos.'" value="'.$position['year'].'"/>');
                echo('<input type="button" value="-" onclick="removePos('.$pos.')"></p>');
                echo('<textarea name=desc'.$pos.'" rows="8" cols="80">'.$position['description'].'</textarea></div>');
            }
        ?>
        </div></p>



<p>
<input type="submit" value="Save">
<input type="submit" name="cancel" value="Cancel">
</p>
</form>

<script src="js/jquery-1.10.2.js"></script>
<script src="js/jquery-ui-1.11.4.js"></script>
<script>
countPos = <?= $pos ?>;
countEdu = <?= $edu ?>;


$(document).ready(function(){

    $('#addPos').click(function(event){

       event.preventDefault();
       if ( countPos >= 9 ) {
        alert("Maximum of nine position entries exceeded");
        return;
       }
       countPos++;
       window.console && console.log("Adding position");
       $('#position_fields').append(
        '<div id="position'+countPos+'"><p>Year: <input type="text" name="year'+countPos+'" value="" /> \
        <input type="button" value="-" onclick="removePos('+countPos+') return false"></p>\
        <textarea name="desc'+countPos+'" rows="8" cols="80"></textarea></div>'
        );

    });

    $('#addEdu').click(function(event){
        event.preventDefault();
        if ( countEdu >= 9 ) {
        alert("Maximum of nine education entries exceeded");
        return;
        }
        countEdu++;
         window.console && console.log("Adding education" + countEdu);
         $('#edu_fields').append(
        '<div id="edu'+countEdu+'"><p>Year: <input type="text" name="edu_year'+countEdu+'" value="" /> \
        <input type="button" value="-" onclick="$(\'#edu'+countEdu+'\').remove()"></p>\
        <p>School: <input type="text" size="80" name="edu_school'+countEdu+'" class="school"></p></div>'
        );

        $('.school').autocomplete({
            source:"school.php"
        });
    
    });
});

function removePos(num) {
   window.console && console.log("Removing position" + num);
   $('#position'+num).remove();
}
</script>
</body>

</html>

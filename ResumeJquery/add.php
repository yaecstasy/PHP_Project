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

   // Validate eduction entrites
   $msg=validateEdu();
   if (is_string($msg)){
        $_SESSION['error'] = $msg;
        header("Location: add.php");
        exit();
   }


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

    // Insert the education entries
    $rank = 1;
    for($i=1; $i<=9; $i++){
        if ( !isset($_POST['edu_year'.$i]) ) continue;
        if ( !isset($_POST['edu_school'.$i]) ) continue;
        $year = $_POST['edu_year'.$i];
        $school = $_POST['edu_school'.$i];

        //Lookup the shcool if it is there
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


?>
<!DOCTYPE html>
<html>
<head>
<title>Tzu-I Lee Add</title>
<link rel="stylesheet" href="css/jquery-ui-1.11.4-ui-lightness.css">
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
<form method="post">

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

<p>Education: <input type="submit" id="addEdu" value="+"></p>
<div id="edu_fields"></div>

<p>Position: <input type="submit" id="addPos" value="+"></p>
<div id="position_fields"></div>


<p>
<input type="submit" value="Add" name="add">
<input type="submit" name="cancel" value="Cancel">
</p>
</form>
<script src="js/jquery-1.10.2.js"></script>
<script src="js/jquery-ui-1.11.4.js"></script>
<script>
countPos = 0;
countEdu = 0;


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
        <input type="button" value="-" onclick="removePos('+countPos+')"></p>\
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
<?php
// Make the database connection and leave it in the variable $pdo
require_once 'pdo.php';
require_once 'util.php';
session_start();

// If the user is not logged in redirect back to index.php
if ( ! isset($_SESSION['user_id']) ) {
    die("ACCESS DENIED");
    exit();
}

// If the user requested cancel go back to index.php
if ( isset($_POST['cancel']) ) {
    header('Location: index.php');
    exit();
}

// Check if the REQUEST parameter is present
$profile_id = isset($_REQUEST['profile_id']) ? $_REQUEST['profile_id'] : false;

$redirect = 'Location: form.php';
if ( $profile_id ) $redirect = 'Location: form.php?profile_id='.$profile_id;

// Handle the incoming data
if ( isset($_POST['first_name']) && isset($_POST['last_name']) && 
     isset($_POST['email']) && isset($_POST['headline']) && 
     isset($_POST['summary']) ) {

    $msg = validateProfile();
    if ( is_string($msg) ) {
        $_SESSION['error'] = $msg;
        header($redirect);
        exit();
    }

    $msg = validatePos();
    if ( is_string($msg) ) {
        $_SESSION['error'] = $msg;
        header($redirect);
        exit();
    }

    $msg = validateEdu();
    if ( is_string($msg) ) {
        $_SESSION['error'] = $msg;
        header($redirect);
        exit();
    }

    $bound = array( 
        ':uid' => $_SESSION['user_id'],
        ':fn' => $_POST['first_name'],
        ':ln' => $_POST['last_name'],
        ':em' => $_POST['email'],
        ':he' => $_POST['headline'],
        ':su' => $_POST['summary']
    );

    if ( $profile_id ) {
        $stmt = $pdo->prepare('UPDATE Profile SET
            first_name=:fn, last_name=:ln, 
            email=:em, headline=:he, summary=:su
            WHERE profile_id=:pid AND user_id=:uid');
        $bound[':pid'] = $profile_id;
        $stmt->execute($bound);
    } else {
        $stmt = $pdo->prepare('INSERT INTO Profile
            (user_id, first_name, last_name, email, headline, summary) 
        VALUES ( :uid, :fn, :ln, :em, :he, :su)');
        $stmt->execute($bound);
        $profile_id = $pdo->lastInsertId();
    }

    // Clear out the old position entries
    $stmt = $pdo->prepare('DELETE FROM Position WHERE profile_id=:pid');
    $stmt->execute(array( ':pid' => $profile_id));

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

    // Clear out the old position entries
    $stmt = $pdo->prepare('DELETE FROM Education WHERE profile_id=:pid');
    $stmt->execute(array( ':pid' => $profile_id));

    // Insert the education entries
    $rank = 1;
    for($i=1; $i<=9; $i++) {
        if ( ! isset($_POST['edu_year'.$i]) ) continue;
        if ( ! isset($_POST['edu_school'.$i]) ) continue;
        $year = $_POST['edu_year'.$i];
        $school = $_POST['edu_school'.$i];

        // Look up the school if it is there.
        $institution_id = false;
        $stmt = $pdo->prepare('SELECT institution_id FROM
            Institution WHERE name = :name');
        $stmt->execute(array(':name' => $school));
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if ( $row !== false ) $institution_id = $row['institution_id'];

        // If there was no institution, insert it
        if ( $institution_id === false ) {
            $stmt = $pdo->prepare('INSERT INTO Institution
                (name) VALUES (:name)');
            $stmt->execute(array(':name' => $school));
            $institution_id = $pdo->lastInsertId();
        }

        $stmt = $pdo->prepare('INSERT INTO Education
            (profile_id, rank, year, institution_id) 
        VALUES ( :pid, :rank, :year, :iid)');
        $stmt->execute(array(
            ':pid' => $profile_id,
            ':rank' => $rank,
            ':year' => $year,
            ':iid' => $institution_id)
        );
        $rank++;
    }

    if ( $profile_id ) {
        $_SESSION['success'] = "Profile updated";
    } else {
        $_SESSION['success'] = "Profile added";
    }
    header("Location: index.php");
    exit();
}
?>
<!DOCTYPE html>
<html>
<head>
<title>Dr. Chuck's Profile</title>
<link rel="stylesheet" href="css/jquery-ui-1.11.4-ui-lightness.css">
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.4/css/bootstrap.min.css">
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.4/css/bootstrap-theme.min.css">
</head>
<body style="padding: 10px; font-family: sans-serif;">
<h1><?= $profile_id ? "Editing" : "Adding" ?>
 Profile for <?= htmlentities($_SESSION['name']); ?></h1>
<?php
flashMessages();

echo('<form method="post" action="form.php">');
echo('<div id="profile"><img src="spinner.gif"></div>');
if ( $profile_id ) {
    echo('<input type="hidden" name="profile_id" value="'.htmlentities($profile_id).'" />');
}

echo('<p>Education: <input type="submit" id="addEdu" value="+" style="display:none;">'."\n");
echo('<div id="edu_fields">'."\n");
echo('<img src="spinner.gif">');
echo("</div></p>\n");
echo('<p>Position: <input type="submit" id="addPos" value="+" style="display:none;">'."\n");
echo('<div id="position_fields">'."\n");
echo('<img src="spinner.gif">');
echo("</div></p>\n");
?>
<p>
<input type="submit" value="Save" id="save_button" style="display:none;">
<input type="submit" name="cancel" value="Cancel">
</p>
</form>
<script src="js/jquery-1.10.2.js"></script>
<script src="js/jquery-ui-1.11.4.js"></script>
<script src="js/handlebars.js"></script>

<!-- Handlebars templates before our functions -->
<script id="profile-template" type="text/x-handlebars-template">
<p>First Name:
<input type="text" name="first_name" size="60" value="{{first_name}}" /></p>
<p>Last Name:
<input type="text" name="last_name" size="60" value="{{last_name}}" /></p>
<p>Email:
<input type="text" name="email" size="30" value="{{email}}" /></p>
<p>Headline:<br/>
<input type="text" name="headline" size="80" value="{{headline}}" /></p>
<p>Summary:<br/>
<textarea name="summary" rows="8" cols="80">{{summary}}</textarea>
</script>
<script id="edu-template" type="text/x-handlebars-template">
  <div id="edu{{count}}">
    <p>Year: <input type="text" name="edu_year{{count}}" value="{{school.year}}" />
    <input type="button" value="-" onclick="$('#edu{{count}}').remove();return false;"><br>
    <p>School: <input type="text" size="80" name="edu_school{{count}}" 
        class="school" value="{{school.name}}" />
    </p>
  </div>
</script>
<script id="pos-template" type="text/x-handlebars-template">
  <div id="position{{count}}">
    <p>Year: <input type="text" name="year{{count}}" value="{{position.year}}" />
    <input type="button" value="-" onclick="$('#position{{count}}').remove();return false;"></p>
    <textarea name="desc{{count}}" rows="8" cols="80">{{position.description}}</textarea>
  </div>
</script>

<script>
countPos = 0;
countEdu = 0;
source  = $("#profile-template").html();
templateProfile = Handlebars.compile(source);
source  = $("#edu-template").html();
templateEdu = Handlebars.compile(source);
source  = $("#pos-template").html();
templatePos = Handlebars.compile(source);

function addEdu(context) {
    context = context || {}; // optional parameter
    if ( countEdu >= 9 ) {
        alert("Maximum of nine entries exceeded");
        return;
    }
    countEdu++;
    window.console && console.log("Adding education "+countEdu);
    context.count = countEdu;
    $('#edu_fields').append(templateEdu(context));

    // Make sure to hook in all of the autocompletes
    $('.school').autocomplete({
        source: "school.php"
    });

}

function addPos(context) {
    context = context || {}; // optional parameter
    if ( countPos >= 9 ) {
        alert("Maximum of nine entries exceeded");
        return;
    }
    countPos++;
    window.console && console.log("Adding position "+countPos);
    context.count = countPos;
    $('#position_fields').append(templatePos(context));
}

function setup_events() {
    $('#addEdu').click(function(event){
        event.preventDefault();
        addEdu();
    });
    $('#addEdu').show();
    $('#addPos').click(function(event){
        event.preventDefault();
        addPos();
    });
    $('#addPos').show();
    $('#save_button').show();
}

<?php if ($profile_id) { ?>
$(document).ready(function(){
    $.getJSON('profile.php?profile_id=<?= htmlentities($profile_id) ?>', function(data) {
        window.console && console.log(data);

        $('#profile').replaceWith(templateProfile(data.profile));

        $('#position_fields').empty();
        for(var i=0; i<data.positions.length; i++) {
            var context = {};
            context.count = i;
            context.position = data.positions[i];
            console.log(context);
            addPos(context);
        }

        $('#edu_fields').empty();
        for(var i=0; i<data.schools.length; i++) {
            var context = {};
            context.count = i;
            context.school = data.schools[i];
            addEdu(context);
        }
        setup_events();
    }).fail( function() { alert('getJSON fail'); } );
});
<?php } else { ?>
$(document).ready(function(){
    $('#profile').replaceWith(templateProfile());
    $('#edu_fields').empty();
    $('#position_fields').empty();
    setup_events();
});
<?php } ?>
</script>
</body>
</html>

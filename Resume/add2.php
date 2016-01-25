<?php
session_start();
require_once "pdo.php";
require_once 'util.php';
// If the user is not logged in redirect back to index.php
// with an error


if ( ! isset($_SESSION['name']) ) {
    die("ACCESS DENIED");
}
// If the user requested logout go back to index.php

// If the user requested cancel
if ( isset($_POST['cancel']) ) {

    header('Location: index.php');
    exit();
}

if ( isset($_POST['firstname']) && isset($_POST['lastname']) && isset($_POST['email']) && isset($_POST['headline']) && isset($_POST['summary'])) {
    if ( strlen($_POST['firstname']) < 1 || strlen($_POST['lastname']) < 1 || strlen($_POST['email']) < 1 || strlen($_POST['summary']) < 1 || strlen($_POST['headline']) < 1) {
        $failure = "Error in input data";
        $_SESSION['error']=$failure;
         header("Location: add.php");
         exit();
    } 
    else if ( !strpos($_POST['email'],'@') ) {
  	  $_SESSION['error'] = "Error in input data";
   	 header("Location: add.php");
  	  exit();
    }
    else{
    $stmt = $pdo->prepare('INSERT INTO Profile
        (user_id, first_name, last_name, email, headline, summary) VALUES ( :uid, :mk, :yr, :mi, :md, :pr)');
    $stmt->execute(array(
        ':uid' => $_SESSION['user_id'],
        ':mk' => $_POST['firstname'],
        ':yr' => $_POST['lastname'],
        ':mi' => $_POST['email'],
        ':md' => $_POST['headline'],
        ':pr' => $_POST['summary'])
    );
		$_SESSION['success']="Record added";
		header("Location: index.php");
  	    return;
    }
}    



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
<input type="text" size="40" name="firstname"></p>
<p>Last Name:
<input type="text" size="40" name="lastname"></p>
<p>Email:
<input type="text" size="46" name="email"></p>
<p>Headline:<br>
<input type="text" size="54" name="headline"></p>
<P>Summary:<br>
<textarea name="summary" rows="10" cols="52"></textarea></P>
<div>Position:
<input id="add"type="button" value="+" onclick="addRow()"></div>
<div id="content"></div>
<script>
function addRow(){

    if(document.getElementById('content').children.length <8){
   var div = document.createElement('div');

    div.className = 'row';

    div.innerHTML = '<p>Year:<input type="text" name="name" value="" />\
            <input type="button" value="-" onclick="removeRow(this)">\
            </p><textarea name="year" rows="10" cols="52"></textarea>';

     document.getElementById('content').appendChild(div);
   }
   else{
     var div = document.createElement('div');

    div.className = 'row';

    div.innerHTML = '<p>Year:<input type="text" name="name" value="" />\
            <input type="button" value="-" onclick="removeRow(this)">\
            </p><textarea name="year" rows="10" cols="52"></textarea>';

     document.getElementById('content').appendChild(div);
     document.getElementById("add").disabled = true;
   }

}

function removeRow(input) {
    document.getElementById("add").disabled = false;
    document.getElementById('content').removeChild( input.parentNode.parentNode);
}

</script>
<p></p>
<input type="submit" value="Add" name="add">
<input type="submit" value="cancel" name="cancel">
<input type="hidden"
name="profile_id" value="<?= $row['profile_id'] ?>">
</form>

</body>
</html>
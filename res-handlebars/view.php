<?php
require_once 'pdo.php';
require_once 'util.php';
session_start();

// Make sure the REQUEST parameter is present
if ( ! isset($_GET['profile_id']) ) {
    $_SESSION['error'] = "Missing profile_id";
    header('Location: index.php');
    exit();
}
?>
<!DOCTYPE html>
<html>
<head>
<title>Dr. Chuck's Profile View</title>
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.4/css/bootstrap.min.css">
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.4/css/bootstrap-theme.min.css">
</head>
<body style="padding: 10px; font-family: sans-serif;">
<h1>Profile information</h1>
<div id="view-area"><img src="spinner.gif"></div>
<a href="index.php">Done</a>
</p>
<script src="js/jquery-1.10.2.js"></script>
<script src="js/jquery-ui-1.11.4.js"></script>
<script src="js/handlebars.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.4/js/bootstrap.min.js"></script>
<script id="profile-template" type="text/x-handlebars-template">
  <p>First Name: {{profile.first_name}}</p>
  <p>Last Name: {{profile.last_name}}</p>
  <p>Email: {{profile.email}}</p>
  <p>Headline:<br/>{{profile.headline}}</p>
  <p>Summary:<br/>{{profile.summary}}</p>
  <p>
  {{#if schools.length}}
    <p>Education</p><ul>
    {{#each schools}}
      <li>{{year}}: {{name}}</li>
    {{/each}}
    </ul>
  {{/if}}
  {{#if positions.length}}
    <p>Postions</p><ul>
    {{#each positions}}
      <li>{{year}}: {{description}}</li>
    {{/each}}
    </ul>
  {{/if}}
</script>

<script>
$(document).ready(function(){
    $.getJSON('profile.php?profile_id=<?= htmlentities($_GET['profile_id']) ?>', function(data) {
        window.console && console.log(data);
        source  = $("#profile-template").html();
        template = Handlebars.compile(source);
        $('#view-area').replaceWith(template(data));
    }).fail( function() { alert('getJSON fail'); } );
});
</script>
</body>
</html>

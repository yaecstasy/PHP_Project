<?php
require_once 'pdo.php';
require_once 'util.php';

header("Content-type: application/json; charset=utf-8");

  if(isset($_REQUEST['profile_id'])){
    $stmt = $pdo->prepare('SELECT * FROM Profile WHERE profile_id = :prof');
    $stmt->execute(array(':prof' => $_REQUEST['profile_id']));
	$profiles = $stmt->fetch(PDO::FETCH_ASSOC);

	$educations=loadEdu($pdo, $_REQUEST['profile_id']);
    $positions=loadPos($pdo, $_REQUEST['profile_id']);


     //put the array into this. 
    $eachProfile = array(
        "profile" => $profiles,
        "schools" => $educations,
        "positions" => $positions
        );
    
   /* $eachProfile ["profile"]= $profile;
    $eachProfile ["educations"]= $educations;
    $eachProfile ["positions"]= $positions;*/

echo(json_encode($eachProfile,JSON_PRETTY_PRINT));
}


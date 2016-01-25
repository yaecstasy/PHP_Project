<?php

// http://stackoverflow.com/questions/7803757/detect-if-session-cookie-set-properly-in-php
// Lets not start a session unless we already have one
if ( ! isset($_COOKIE[session_name()]) ) {
    die("Must be logged in");
}

session_start();

if ( ! isset($_SESSION['user_id']) ) {
    die("ACCESS DENIED");
}


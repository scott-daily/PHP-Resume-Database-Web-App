<?php

//include_once("../config.php");

// No need to continue
if (!isset($_GET['term']) ) die('Missing required parameter');

// http://stackoverflow.com/questions/7803757/detect-if-session-cookie-set-properly-in-php123
if ( !isset($_COOKIE[session_name()]) ) {
  die("Must be logged in");
}

session_start();

if (! isset($_SESSION['user_id']) ) {
  die("ACCESS DENIED");
}

// Don't even make database connection until all checks are Done
require_once 'pdo.php';

header("Content-type: application/json; charset=utf-8");

$term = $_GET['term'];
error_log("Looking up typeahead term=".$term);

$stmt = $pdo->prepare('SELECT name FROM Institution

WHERE name LIKE :prefix');

$stmt->execute(array( ':prefix' => $_REQUEST['term']."%"));

$retval = array();

while ( $row = $stmt->fetch(PDO::FETCH_ASSOC) ) {

$retval[] = $row['name'];

}

echo(json_encode($retval, JSON_PRETTY_PRINT));

?>

<?php
require_once "pdo.php";
require_once "head.php";
require_once "util.php";
session_start();

// Load the profile
$stmt = $pdo->prepare('SELECT * FROM profile WHERE profile_id = :profile_id');
$stmt->execute(array(
  ':profile_id' => $_REQUEST['profile_id']));
//  ':user_id' => $_SESSION['user_id']));
$profile = $stmt->fetch(PDO::FETCH_ASSOC);

if ( $profile === false ) {
  $_SESSION['error'] = "Could not load profile";
  header('Location: index.php');
  return;
}

$positions = loadPos($pdo, $_REQUEST['profile_id']);
$schools = loadEdu($pdo, $_REQUEST['profile_id']);

?>



<!DOCTYPE html>
<html>
<head>
  <?php require_once "head.php"; ?>
<title>Scott Daily's Profile View</title>
</head>
<body>
<div class="container">
<h1>Profile information</h1>
<p>First Name:
<?php echo htmlentities($profile['first_name']); ?>
</p>
<p>Last Name:
<?php echo htmlentities($profile['last_name']); ?>
</p>
<p>Email:
  <?= htmlentities($profile['email']); ?>
</a></p>
<p>Headline:<br/>
<?= htmlentities($profile['headline']); ?>
</p>
<p>Summary:<br/>
<?= htmlentities($profile['summary']); ?><p>

  <p>Education</p>
  <p>
    <?php
    $count= 0;
    foreach( $schools as $school ) {
      $count++;
      echo('<ul>');
      echo('<li>');
      echo(htmlentities($school['year']));
      echo(': ');
      echo(htmlentities($school['name'])."\n");
      echo('</li>');
      echo('</ul>');
    }
    ?>

<p>Postions</p>
<p>
  <?php
  $pos= 0;
  foreach( $positions as $position ) {
    $pos++;
    echo('<ul>');
    echo('<li>');
    echo(htmlentities($position['year']));
    echo(': ');
    echo(htmlentities($position['description'])."\n");
    echo('</li>');
    echo('</ul>');
  }
  ?>


<a href="index.php">Done</a>
</p>
</div>
</body>
</html>

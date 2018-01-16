<?php
require_once "pdo.php";
session_start();

if ( ! isset($_SESSION['user_id']) ) {
    die('ACCESS DENIED');
    return;
}

if ( isset($_POST['cancel']) ) {
    // Redirect the browser to index.php
    header("Location: index.php");
    return;
}

if ( isset($_POST['delete']) && isset($_POST['profile_id']) ) {
    $sql = "DELETE FROM profile WHERE profile_id = :profile_id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(array(':profile_id' => $_POST['profile_id']));
    $_SESSION['success'] = 'Record deleted';
    header( 'Location: index.php' ) ;
    return;
}

//   Guardian Pattern: Make sure that autos_id is present
 if ( ! isset($_GET['profile_id']) ) {
  $_SESSION['error'] = "Missing profile_id";
  header('Location: index.php');
  return;
 }

$stmt = $pdo->prepare("SELECT first_name, last_name, profile_id FROM profile where profile_id = :profile_id");
$stmt->execute(array(":profile_id" => $_GET['profile_id']));
$row = $stmt->fetch(PDO::FETCH_ASSOC);
if ( $row === false ) {
    $_SESSION['error'] = 'Bad value for user_id';
    header( 'Location: index.php' ) ;
    return;
}

?>
<html>
<?php require_once "head.php"; ?>
<head>
  <title>Scott Daily's Delete Profile</title>
</head>
<body>
  <div class="container">
    <h2>Deleting Profile for <?php echo $_SESSION['email'] ?> </h2>
<form method="post" action="delete.php">
  <p>First Name: <?= htmlentities($row['first_name']) ?></p>
  <p>Last Name: <?= htmlentities($row['last_name']) ?></p>
<input type="hidden" name="profile_id" value="<?= $_GET['profile_id'] ?>">
<input type="submit" value="Delete" name="delete">
<input type="submit" name="cancel" value="Cancel">
</p>
</form>
</div>
</body>
</html>

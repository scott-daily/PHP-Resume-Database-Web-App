<?php
//util.php
require_once "pdo.php";
require_once "head.php";
?>
<script>


<?php
function flashMessages() {
  if ( isset($_SESSION['error']) ) {
    echo('<p style="color: red;">'.htmlentities($_SESSION['error'])."</p>\n");
    unset($_SESSION['error']);
  }
  if ( isset($_SESSION['success']) ) {
    echo('<p style="color: green;">'.htmlentities($_SESSION['success'])."</p>\n");
    unset($_SESSION['success']);
  }
}


function validateProfile() {
  if ( strlen($_POST['first_name']) == 0 || strlen($_POST['last_name']) == 0 ||
  strlen($_POST['email']) == 0 || strlen($_POST['headline']) == 0 ||
  strlen($_POST['summary']) == 0 ) {
      return "All fields are required";
}
if(!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
  return "Email address must contain @";
}
return true;
}

?>

<?php
function validatePos() {
  for($i=1; $i<=9; $i++) {
    if ( ! isset($_POST['year'.$i]) ) continue;
    if ( ! isset($_POST['desc'.$i]) ) continue;
    $year = $_POST['year'.$i];
    $desc = $_POST['desc'.$i];
    if ( strlen($desc) == 0)  {
      return "All fields are required";
    }
    if ( !is_numeric($year) ) {
      return "Year must be numeric";
    }
  }
  return true;
}

function validateEdu() {
  for($i=1; $i<=9; $i++) {
    if ( ! isset($_POST['edu_year'.$i]) ) continue;
    if ( ! isset($_POST['edu_school'.$i]) ) continue;
    $year = $_POST['edu_year'.$i];
    $school = $_POST['edu_school'.$i];
    if ( strlen($school) == 0) {
      return "All fields are required";
    }
    if ( !is_numeric($year)) {
      return "Year must be numeric";
    }
  }
  return true;
}


?>

<?php

function loadPos($pdo, $profile_id) {
  $stmt = $pdo->prepare('SELECT * FROM Position WHERE profile_id = :prof ORDER BY rank');
  $stmt->execute(array( ':prof' => $profile_id));
  $positions = $stmt->fetchAll(PDO::FETCH_ASSOC);
  return $positions;
}

?>

<?php

function loadEdu($pdo, $profile_id) {
  $stmt = $pdo->prepare('SELECT year, name FROM Education JOIN Institution ON Education.institution_id = Institution.institution_id
  WHERE profile_id = :prof ORDER BY rank');
  $stmt->execute(array( ':prof' => $profile_id));
  $educations = $stmt->fetchAll(PDO::FETCH_ASSOC);
  return $educations;
}

?>

<?php

function insertPositions($pdo, $profile_id) {
  $rank = 1;
  for($i=1; $i<=9; $i++) {
    if ( ! isset($_POST['year'.$i]) ) continue;
    if ( ! isset($_POST['desc'.$i]) ) continue;
    $year = $_POST['year'.$i];
    $desc = $_POST['desc'.$i];

  $stmt = $pdo->prepare('INSERT INTO Position (profile_id, rank, year, description) VALUES ( :pid, :rank, :year, :desc)');
  $stmt->execute(array(
    ':pid' => $profile_id,
    ':rank' => $rank,
    ':year' => $year,
    ':desc' => $desc)
  );
  $rank++;
  }
}

?>

<?php

function insertEducations($pdo, $profile_id) {
  $rank = 1;
  for($i=1; $i<=9; $i++) {
    if (! isset($_POST['edu_year'.$i]) ) continue;
    if (! isset($_POST['edu_school'.$i]) ) continue;
    $year = $_POST['edu_year'.$i];
    $school = $_POST['edu_school'.$i];

    //Lookup if the school is there
    $institution_id = false;
    $stmt = $pdo->prepare('SELECT institution_id FROM institution WHERE name = :name');
    $stmt->execute(array(':name' => $school));
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    if ( $row !== false)  $institution_id = $row['institution_id'];

    // If there was no institution already, insert it
    if ( $institution_id === false) {
      $stmt = $pdo->prepare('INSERT INTO institution (name) VALUES (:name)');
      $stmt->execute(array(':name' => $school));
      $institution_id = $pdo->lastInsertId();
    }

    $stmt = $pdo->prepare('INSERT INTO education (profile_id, rank, year, institution_id)
    VALUES ( :pid, :rank, :year, :iid)');
    $stmt->execute(array(
      ':pid' => $profile_id,
      ':rank' => $rank,
      ':year' => $year,
      ':iid' => $institution_id)
    );
    $rank++;
  };
};

?>
</script>

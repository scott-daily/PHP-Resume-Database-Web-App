<?php
require_once "pdo.php";
require_once "util.php";
require_once "head.php";

ini_set('display_errors', 1);
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

// Load the profile
$stmt = $pdo->prepare('SELECT * FROM profile WHERE profile_id = :profile_id AND user_id = :user_id');
$stmt->execute(array(
  ':profile_id' => $_REQUEST['profile_id'],
  ':user_id' => $_SESSION['user_id']));
$profile = $stmt->fetch(PDO::FETCH_ASSOC);
if ( $profile === false ) {
  $_SESSION['error'] = "Could not load profile";
  header('Location: index.php');
  return;
}

if ( isset($_POST['first_name']) && isset ($_POST['last_name']) && isset($_POST['email']) && isset($_POST['headline']) && isset($_POST['summary'])) {
    $msg = validateProfile();
     if ( is_string($msg) ) {
       $_SESSION['error'] = $msg;
       header("Location: edit.php?profile_id=" . $_REQUEST["profile_id"]);
       return;
     }
     $msg = validatePos();
     if ( is_string($msg) ) {
      $_SESSION['error'] = $msg;
       header("Location: edit.php?profile_id=" . $_REQUEST["profile_id"]);
       return;
     }
     $msg = validateEdu();
     if ( is_string($msg) ) {
       $_SESSION['error'] = $msg;
       header("Location: edit.php?profile_id=" . $_REQUEST["profile_id"]);
       return;
     }
     // Data is valid, time to insert
     $stmt = $pdo->prepare('UPDATE profile SET first_name = :first_name,
             last_name = :last_name, email = :email, headline = :headline, summary = :summary
           WHERE profile_id = :profile_id AND user_id = :user_id');
     $stmt->execute(array(
         ':first_name' => $_POST['first_name'],
         ':last_name' => $_POST['last_name'],
         ':email' => $_POST['email'],
         ':headline' => $_POST['headline'],
         ':summary' => $_POST['summary'],
         ':profile_id' => $_REQUEST['profile_id'],
         ':user_id' => $_SESSION['user_id'])
       );

       $stmt = $pdo->prepare('DELETE FROM position
       WHERE profile_id=:pid');
       $stmt->execute(array( ':pid' => $_REQUEST['profile_id']));

       // Insert position entries
        insertPositions($pdo, $_REQUEST['profile_id']);

        //Clear out the old education entires
        $stmt = $pdo->prepare('DELETE FROM education
        WHERE profile_id=:pid');
        $stmt->execute(array( ':pid' => $_REQUEST['profile_id']));


        //Insert the education entries
        insertEducations($pdo, $_REQUEST['profile_id']);

        $_SESSION['success'] = "Profile updated";
        header( 'Location: index.php' );
        return;
    }

    $positions = loadPos($pdo, $_REQUEST['profile_id']);
    $schools = loadEdu($pdo, $_REQUEST['profile_id']);
?>



<!DOCTYPE html>
<html>
<head>
  <title>Scott Daily's Profile Edit</title>
<?php require_once "head.php"; ?>

</head>
<body>
  <div class="container">
<h2>Editing Profile for <?= htmlentities($_SESSION['name']); ?> </h2>

<?php flashMessages(); ?>

<form method="post" action="edit.php">
<input type="hidden" name="profile_id"
value="<?= htmlentities($_REQUEST['profile_id']); ?>">
<p>First Name:
<input type="text" name="first_name" size="60"
value="<?= htmlentities($profile['first_name']); ?>"
/></p>
<p>Last Name:
<input type="text" name="last_name" size="60"
value="<?= htmlentities($profile['last_name']); ?>"
/></p>
<p>Email:
<input type="text" name="email" size="30"
value="<?= htmlentities($profile['email']); ?>"
/></p>
<p>Headline:<br/>
<input type="text" name="headline" size="80"
value="<?= htmlentities($profile['headline']); ?>"
/></p>
<p>Summary:<br/>
<textarea name="summary" rows="8" cols="80">
<?= htmlentities($profile['summary']); ?>
</textarea>

<?php

$countEdu = 0;

echo('<p>Education: <input type="submit" id="addEdu" value="+">'."\n");
echo('<div id="edu_fields">'."\n");
if (count($schools) > 0 ) {
  foreach ($schools as $school ) {
    $countEdu++;
    echo('<div id="edu'.$countEdu.'">');
    echo
'<p>Year: <input type="text" name="edu_year'.$countEdu.'" value="'.$school['year'].'" />
<input type="button" value="-" onclick="$(\'#edu'.$countEdu.'\').remove();return false;"></p>
<p>School: <input type="text" size="80" name"edu_school'.$countEdu.'" class="school"
value="'.htmlentities($school['name']).'" />';
  echo "\n</div>\n";
  }
}
echo("</div></p>\n");

$countPos = 0;
echo('<p>Position: <input type="submit" id="addPos" value="+">'."\n");
echo ('<div id="position_fields">'."\n");
foreach( $positions as $position ) {
  $countPos++;
  echo('<div id="position'.$countPos.'">'."\n");
  echo('<p>Year: <input type="text" name="year'.$countPos.'"');
  echo(' value="'.$position['year'].'" />'."\n");
  echo('<input type="button" value="-" ');
  echo('onclick="$(\'#position'.$countPos.'\').remove();return false;">'."\n");
  echo("</p>\n");
  echo('<textarea name="desc'.$countPos.'" rows="8" cols="80">'."\n");
  echo(htmlentities($position['description'])."\n");
  echo("\n</textarea>\n</div>\n");
}
echo("</div></p>\n");
?>



<p><input type="submit" value="Save">
  <input type="submit" name="cancel" value="Cancel">
</p>
</form>

<script>
countPos = <?= $countPos ?>;
countEdu = <?= $countEdu ?>;

// http://stackoverflow.com/questions/17650776/add-remove-html-inside-div-using-javascript
$(document).ready(function(){
    window.console && console.log('Document ready called');
    $('#addPos').click(function(event){
        // http://api.jquery.com/event.preventdefault/
        event.preventDefault();
        if ( countPos >= 9 ) {
            alert("Maximum of nine position entries exceeded");
            return;
        }
        countPos++;
        window.console && console.log("Adding position "+countPos);

        $('#position_fields').append(
            '<div id="position'+countPos+'"> \
            <p>Year: <input type="text" name="year'+countPos+'" value="" /> \
            <input type="button" value="-" \
                onclick="$(\'#position'+countPos+'\').remove();return false;"></p> \
            <textarea name="desc'+countPos+'" rows="8" cols="80"></textarea>\
            </div>');
    });

    $('#addEdu').click(function(event){
      event.preventDefault();
      if ( countEdu >= 9 ) {
        alert("Maximum of nine education entries exceeded");
        return;
      }
      countEdu++;
      window.console && console.log("Adding education "+countEdu);

      var source = $("#edu-template").html();
      $('#edu_fields').append(source.replace(/@COUNT@/g,countEdu));

      $('.school').autocomplete({
        source: "school.php"
      });
    });
    $('.school').autocomplete({
      source: "school.php"
    });
  });

</script>

<script id="edu-template" type="text">
<div id="edu@COUNT@">
  <p>Year: <input type="text" name="edu_year@COUNT@" value="" />
  <input type="button" value="-" onclick="$('#edu@COUNT@').remove();return false;"><br>
  <p>School: <input type="text" size="80" name="edu_school@COUNT@" class="school" value="" />
  </p>
  </div>
  </script>
</div>
</body>
</html>

<?php
require_once "pdo.php";
require_once "head.php";
session_start();

$data = $pdo->prepare('SELECT profile_id, user_id, first_name, last_name, headline FROM Profile');
$data->execute();
$row = $data->fetch(PDO::FETCH_ASSOC);
?>

<html>
<head>
  <?php require_once "head.php"; ?>
<title>Scott Daily's Resume Registry</title>
</head>
<body>
  <div class="container">
  <h2>Scott Daily's Resume Registry</h2>
  <?php
  if (isset($_SESSION['error'])) {
    echo '<p style="color: red;">'.$_SESSION['error'].'</p>';
    unset($_SESSION['error']);
  }

//If not logged in
   if ( !isset($_SESSION["name"])) {
     echo '<p><a href="login.php">Please log in</a></p>';
     if ($row !== false) {
       echo '<table border="1">';
       echo '<tr><th>Name</th><th>Headline</th><tr>';
       echo '<br><br>';

      while ($row = $data->fetch(PDO::FETCH_ASSOC)) {
       $p_id = $row['profile_id'];
       $u_id = $row['user_id'];
       $name = htmlentities($row['first_name'])." ".htmlentities($row['last_name']);
       $hd = htmlentities($row['headline']);
       echo '<tr><td><a href="view.php?profile_id='.$p_id.'">'.$name.'</a></td><td>'.$hd.'</td></tr>';
     }
     echo '</table>';
   }
 }  else {
       // If logged in show this

    if ( isset($_SESSION["success"]) ) {
      echo '<p style="color: green;">'.$_SESSION['success'].'.</p>';
      unset($_SESSION["success"]);
      }

      echo '<p><a href="logout.php">Logout</a></p>';

      if ($row !== false) {
				echo '<table border="1">';
				echo '<tr><th>Name</th><th>Headline</th><th>Action</th><tr>';

				$p_id = $row['profile_id'];
				$name = htmlentities($row['first_name']." ".$row['last_name']);
				$hd = htmlentities($row['headline']);
				echo '<tr><td><a href="view.php?profile_id='.$p_id.'">'.$name.'</a></td><td>'.$hd.'</td><td><a href="edit.php?profile_id='.$p_id.'">Edit</a> <a href="delete.php?profile_id='.$p_id.'">Delete</a></td></tr>';

				while ($row = $data->fetch(PDO::FETCH_ASSOC)) {
					$p_id = $row['profile_id'];
					$name = htmlentities($row['first_name']." ".$row['last_name']);
					$hd = htmlentities($row['headline']);
					echo '<tr><td><a href="view.php?profile_id='.$p_id.'">'.$name.'</a></td><td>'.$hd.'</td><td><a href="edit.php?profile_id='.$p_id.'">Edit</a> <a href="delete.php?profile_id='.$p_id.'">Delete</a></td></tr>';
				}
				echo '</table>';
			}
			echo '<p><a href="add.php">Add New Entry</a></p>';
		}
		?>
	</div>
</body>
</html>

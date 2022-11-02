<?php
set_include_path($_SERVER['DOCUMENT_ROOT']."/wishlist");
include 'mdp.php';

session_start();
$nom = $_SESSION["nom"];
$mdp = $_SESSION["mdp"];
$_SESSION["reprise"] = true;
$id = $_POST["id"];

$con=mysqli_connect($servername,$username,$password,$dbname);
if (mysqli_connect_errno()) {
  echo "Failed to connect to MySQL: " . mysqli_connect_error();
  exit();
}

$query = "SELECT * FROM comptes WHERE nom='$nom'";
$result = $con->query($query);
$row = $result->fetch_array(MYSQLI_NUM);
if ($row != null && password_verify($mdp, $row[2])) {
	$query = "SELECT * FROM voeux WHERE id='$id'";
	$result = mysqli_query($con, $query);
	$obj = mysqli_fetch_assoc($result);
	if ($obj != null && $obj["proprietaire"] == $nom) {
		$query = "DELETE FROM voeux WHERE id='$id'";
		$result = mysqli_query($con, $query);
	}
}

mysqli_close($con);
header( "Location: /wishlist/connexion/liste" );
?>
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

$query = $con->prepare("SELECT * FROM comptes WHERE nom=?");
$query->bind_param("s", $nom);
$query->execute();
$result = $query->get_result();
$query->close();
$row = $result->fetch_array(MYSQLI_NUM);
if ($row != null && password_verify($mdp, $row[2])) {
	$query = $con->prepare("SELECT * FROM voeux WHERE id=?");
	$query->bind_param("i", $id);
	$query->execute();
	$result = $query->get_result();
	$query->close();
	$obj = mysqli_fetch_assoc($result);
	if ($obj != null && $obj["proprietaire"] == $nom) {
		if ($obj["priorite"] == "MOYENNE PLUS") {
			$nextpri = "HAUTE";
		} else if ($obj["priorite"] == "MOYENNE MOINS") {
			$nextpri = "MOYENNE PLUS";
		} else if ($obj["priorite"] == "BASSE") {
			$nextpri = "MOYENNE MOINS";
		} else {
			$nextpri = $obj["priorite"];
		}
		$query = $con->prepare("UPDATE voeux SET priorite=? WHERE id=?");
		$query->bind_param("si", $nextpri, $id);
		$query->execute();
		$query->close();
	}
}

$con->close();
header( "Location: /wishlist/connexion/liste" );
?>
<?php
include "../../INCLUDES/utils.php";

session_start();
$nom = $_SESSION["nom"];
$mdp = $_SESSION["mdp"];
$_SESSION["reprise"] = true;
$id = $_POST["id"];
$action = $_POST["action"];

$con = dbconnect();

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
		
		if ($action == 'monter') {
			if ($obj["priorite"] == "HAUTE") {
				$nextpri = "DISCONTINUE";
			} else if ($obj["priorite"] == "MOYENNE PLUS") {
				$nextpri = "HAUTE";
			}  else if ($obj["priorite"] == "MOYENNE MOINS") {
				$nextpri = "MOYENNE PLUS";
			} else if ($obj["priorite"] == "BASSE") {
				$nextpri = "MOYENNE MOINS";
			} else {
				$nextpri = $obj["priorite"];
			}
			$query = $con->prepare("UPDATE voeux SET priorite=? WHERE id=?");
			$query->bind_param("si", $nextpri, $id);
		} else if ($action == 'descendre') {			
			if ($obj["priorite"] == "DISCONTINUE") {
				$nextpri = "HAUTE";
			} else if ($obj["priorite"] == "HAUTE") {
				$nextpri = "MOYENNE PLUS";
			}  else if ($obj["priorite"] == "MOYENNE PLUS") {
				$nextpri = "MOYENNE MOINS";
			} else if ($obj["priorite"] == "MOYENNE MOINS") {
				$nextpri = "BASSE";
			} else {
				$nextpri = $obj["priorite"];
			}
			$query = $con->prepare("UPDATE voeux SET priorite=? WHERE id=?");
			$query->bind_param("si", $nextpri, $id);
		} else if ($action == 'supprimer') {
			$query = $con->prepare("DELETE FROM voeux WHERE id=?");
			$query->bind_param("i", $id);
		} else {
			exit("Fack U");
		}
		
		$query->execute();
		$query->close();
	}
}

$con->close();
header( "Location: /".BASEDIR."connexion/liste" );
?>
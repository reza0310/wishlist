<?php
include "../../INCLUDES/utils.php";

session_start();
$nom = $_SESSION["nom"];
$mdp = $_SESSION["mdp"];
$_SESSION["reprise"] = true;
$nom_s = $_POST["nom"];
$image_s = $_POST["image"];

$con = dbconnect();

$query = $con->prepare("SELECT * FROM comptes WHERE nom=?");
$query->bind_param("s", $nom);
$query->execute();
$result = $query->get_result();
$query->close();
$row = $result->fetch_array(MYSQLI_NUM);
if ($row != null && password_verify($mdp, $row[2])) {
	$query = $con->prepare("INSERT INTO styles (nom, image, proprietaire) VALUES (?, ?, ?)");
	$query->bind_param("sss", $nom_s, $image_s, $nom);
	if (!$query || !$query->execute()) {
		echo(addheader("<h1 style='color: red;'>Erreur: ".$con->error."</h1>", "tres"));
		exit();
	}
	$query->close();
}

$con->close();
header("Location: /".BASEDIR."connexion/liste");
?>
<?php
include "../../INCLUDES/utils.php";

session_start();
$nom = $_SESSION["nom"];
$mdp = $_SESSION["mdp"];
$_SESSION["reprise"] = true;
$nom_p = $_POST["nom"];
$lien_p = $_POST["lien"];
$image_p = $_POST["image"];
$prix_p = $_POST["prix"];
$quantite_p = $_POST["quantite"];
$priorite_p = $_POST["priorite"];

$con = dbconnect();

$query = $con->prepare("SELECT * FROM comptes WHERE nom=?");
$query->bind_param("s", $nom);
$query->execute();
$result = $query->get_result();
$query->close();
$row = $result->fetch_array(MYSQLI_NUM);
if ($row != null && password_verify($mdp, $row[2])) {
	$query = $con->prepare("INSERT INTO voeux (nom, lien, image, prix, quantite, priorite, proprietaire) VALUES (?, ?, ?, ?, ?, ?, ?)");
	$query->bind_param("sssdiss", $nom_p, $lien_p, $image_p, $prix_p, $quantite_p, $priorite_p, $nom);
	if (!$query || !$query->execute()) {
		echo(addheader("<h1 style='color: red;'>Erreur: ".$con->error."</h1>", "tres"));
		exit();
	}
	$query->close();
}

$con->close();
header("Location: /".BASEDIR."connexion/liste");
?>
<?php
set_include_path($_SERVER['DOCUMENT_ROOT']."/wishlist");
include 'mdp.php';

$mail = $_POST["mail"];
$nom = $_POST["nom"];
$mdp = $_POST["mdp"];

// Create conection
$con = new mysqli($servername, $username, $password, $dbname);
// Check conection
if ($con->connect_error) {
	die("Connection failed: " . $con->conect_error);
}

// Compte
$query = $con->prepare("SELECT * FROM comptes WHERE nom=?");
$query->bind_param("s", $nom);
$query->execute();
$result = $query->get_result();
$query->close();
$result = mysqli_fetch_array($result);
if ($result == null) {
	$hash = password_hash($mdp, PASSWORD_DEFAULT);
	$query = $con->prepare("INSERT INTO comptes (nom, mail, hash) VALUES (?, ?, ?)");
	$query->bind_param("sss", $nom, $mail, $hash);
	if ($query->execute() == TRUE) {
		$query->close();
		echo(str_replace("dos", "active", str_replace("%php%", file_get_contents("page.html"), file_get_contents("header.html", true))));
	} else {
		$query->close();
		echo "Request error: " . $con->error;
	}
} else {
	echo(str_replace("dos", "active", str_replace("%php%", "<h2>Ce compte existe déjà! Si il vous appartient et que vous ne pouvez plus y accéder contactez-moi.</h2>", file_get_contents("header.html", true))));
}

$con->close();
?>
<?php
set_include_path($_SERVER['DOCUMENT_ROOT']."/wishlist");
include 'mdp.php';

$mail = $_POST["mail"];
$nom = $_POST["nom"];
$mdp = $_POST["mdp"];

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);
// Check connection
if ($conn->connect_error) {
	die("Connection failed: " . $conn->connect_error);
}

// Compte
$sql = "SELECT * FROM comptes WHERE nom='{$nom}'";
$result = mysqli_fetch_array(mysqli_query($conn,$sql));
if ($result == null) {
	$hash = password_hash($mdp, PASSWORD_DEFAULT);
	$sql = "INSERT INTO comptes (nom, mail, hash) VALUES ('{$nom}', '{$mail}', '{$hash}')"; 	
	if ($conn->query($sql) === TRUE) {
		echo(str_replace("dos", "active", str_replace("%php%", file_get_contents("page.html"), file_get_contents("header.html", true))));
	} else {
		echo "Request error: " . $conn->error;
	}
} else {
	echo(str_replace("dos", "active", str_replace("%php%", "<h2>Ce compte existe déjà! Si il vous appartient et que vous ne pouvez plus y accéder contactez-moi.</h2>", file_get_contents("header.html", true))));
}

$conn->close();
?>
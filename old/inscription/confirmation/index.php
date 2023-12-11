<?php
include "../../INCLUDES/utils.php";

$mail = $_POST["mail"];
$name = $_POST["nom"];
$pass = $_POST["mdp"];

// Create conection
$con = dbconnect();

// Compte
$query = $con->prepare("SELECT * FROM comptes WHERE nom=?");
$query->bind_param("s", $name);
$query->execute();
$result = $query->get_result();
$query->close();
$result = mysqli_fetch_array($result);
if ($result == null) {
	$hash = password_hash($pass, PASSWORD_DEFAULT);
	$query = $con->prepare("INSERT INTO comptes (nom, mail, hash) VALUES (?, ?, ?)");
	$query->bind_param("sss", $name, $mail, $hash);
	if ($query->execute() == TRUE) {
		$query->close();
		echo(addheader(file_get_contents("page.html"), "dos"));
	} else {
		$query->close();
		echo "Request error: " . $con->error;
	}
} else {
	echo(addheader("<h2>Ce compte existe déjà! Si il vous appartient et que vous ne pouvez plus y accéder contactez-moi.</h2>", "dos"));
}

$con->close();
?>

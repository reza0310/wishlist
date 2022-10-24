<?php
set_include_path($_SERVER['DOCUMENT_ROOT']."/wishlist");
include 'mdp.php';

$mail = $_POST["mail"];
$nom = $_POST["nom"];

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
	$chars = ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9', 'A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z'];
	$mdp = "";
	for ($i = 0; $i < 50; $i++) {
		$mdp .= $chars[rand(0, count($chars)-1)];
	}
	$hash = password_hash($mdp, PASSWORD_DEFAULT);
	$headers = array(
		'From' => 'mdp@odaame.org',
		'X-Mailer' => 'PHP/' . phpversion()
	);
	$sql = "INSERT INTO comptes (nom, mail, hash) VALUES ('{$nom}', '{$mail}', '{$hash}')"; 	
	if ($conn->query($sql) === TRUE) {
		mail($mail, "Votre mot de passe", "Bonjour! 
		
		Vous venez de créer une liste sur mon site web seulement, les tests de sécurité n'étant pas terminés, je vous ai créé un mot de passe expréssément pour l'occasion. 
		Je n'y ai bien sûr pas accès ni personne en soit et bien sûr je ne garde qu'un hash mais on sait jamais on sait jamais...
		Je vous invite aussi à régulièrement sauvegarder votre liste à l'aide de la wayback machine (https://archive.org/web/) ou de screen shots d'autant plus que le site n'étant pas du tout terminé, nous ne sommes pas à l'abri d'une mise à jour destructive (Auquel ca vous seriez prévenus par mail mais pour peu que ça finisse dans vos spams...).
		
		Votre mot de passe est donc ".$mdp.". 
		
		Cordialement, 
		
		reza0310.", $headers);
		echo(str_replace("dos", "active", str_replace("%php%", file_get_contents("page.html"), file_get_contents("header.html", true))));
	} else {
		echo "Request error: " . $conn->error;
	}
}

$conn->close();
?>
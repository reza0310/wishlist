 <?php
include "../INCLUDES/utils.php";

// Create connection
$conn = dbconnect();

// Create database
$sql = array("CREATE TABLE comptes (
nom VARCHAR(50) NOT NULL PRIMARY KEY,
mail VARCHAR(100) NOT NULL,
hash VARCHAR(100) NOT NULL
) CHARACTER SET utf8",

"CREATE TABLE voeux (
id INT(10) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
nom VARCHAR(100) NOT NULL,
lien VARCHAR(10000),
image VARCHAR(10000),
prix FLOAT,
quantite INT(10) UNSIGNED,
priorite ENUM('DISCONTINUE', 'HAUTE', 'MOYENNE PLUS', 'MOYENNE MOINS', 'BASSE') NOT NULL,
proprietaire VARCHAR(50) NOT NULL,
FOREIGN KEY (proprietaire) REFERENCES comptes(nom)
) CHARACTER SET utf8",

"CREATE TABLE styles (
id INT(10) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
nom VARCHAR(100) NOT NULL,
image VARCHAR(10000),
proprietaire VARCHAR(50) NOT NULL,
FOREIGN KEY (proprietaire) REFERENCES comptes(nom)
) CHARACTER SET utf8",

"CREATE TABLE collections (
id INT(10) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
nom VARCHAR(50) NOT NULL,
lien VARCHAR(500),
image VARCHAR(500),
proprietaire VARCHAR(50) NOT NULL,
FOREIGN KEY (proprietaire) REFERENCES comptes(nom)
) CHARACTER SET utf8",

"CREATE TABLE contenus (
id INT(10) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
nom VARCHAR(50) NOT NULL,
lien VARCHAR(500),
image VARCHAR(500),
prix FLOAT NOT NULL,
statut ENUM('POSSEDE', 'NON-POSSEDE') NOT NULL,
collection INT(10) UNSIGNED NOT NULL,
FOREIGN KEY (collection) REFERENCES collections(id)
) CHARACTER SET utf8");

foreach ($sql as $req) {
	if ($conn->query($req) === TRUE) {
	  echo "Success";
	} else {
	  echo "Error " . $conn->error;
	}
	echo("<br><br>");
}

$conn->close();
?>

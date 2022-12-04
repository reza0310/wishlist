<?php
set_include_path($_SERVER['DOCUMENT_ROOT']."/wishlist");
include 'mdp.php';
include 'utils.php';

$nom = $_GET["nom"];

$con=mysqli_connect($servername,$username,$password,$dbname);
if (mysqli_connect_errno()) {
  echo "Failed to connect to MySQL: " . mysqli_connect_error();
  exit();
}

$page = "";
$priorites = array('HAUTE', 'MOYENNE PLUS', 'MOYENNE MOINS', 'BASSE');
$query = $con->prepare("SELECT * FROM voeux WHERE proprietaire=?");
$query->bind_param("s", $nom);
$query->execute();
$result = mysqli_fetch_all($query->get_result());
$query->close();

foreach ($priorites as $pri) {
	$page .= "<h1 class='categories'>Priorité ".strtolower($pri).":</h1>";
	foreach ($result as $colonne) {
		if ($colonne[6] == $pri) {
			if ($colonne[5] != 1) {
				$nom = strval($colonne[5]) . " &times " . $colonne[1];
			} else {
				$nom = $colonne[1];
			}
			if ($colonne[3] == NULL) {
				$image = "https://upload.wikimedia.org/wikipedia/commons/thumb/f/f8/Question_mark_alternate.svg/1200px-Question_mark_alternate.svg.png";
			} else {
				$image = $colonne[3];
			}
			if ($colonne[4] < 0) {
				$prix = "Prix inconnu";
			} else {
				$prix = clearhtml($colonne[4])."€";
			}
			$page .= "<div class='ticket'>";
			$page .= "<a href='".checkurl(clearhtml($colonne[2]))."'>";
			$page .= "<img src='".checkurl(clearhtml($image))."' alt='L image du voeux' class='image_ticket'>";
			$page .= "<div class='texte_ticket'>".clearhtml($nom)."<br>";
			$page .= $prix;
			$page .= "</div></a></div>";
		}
	}
}

$con->close();
echo(str_replace("quatro", "active", str_replace("%php%", $page, file_get_contents("header.html", true))));
?>

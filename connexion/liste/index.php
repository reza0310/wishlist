<?php
set_include_path($_SERVER['DOCUMENT_ROOT']."/wishlist");
include 'mdp.php';
include 'utils.php';

session_start();
if (isset($_SESSION["nom"]) && isset($_SESSION["mdp"])) {
	$nom = $_SESSION["nom"];
	$mdp = $_SESSION["mdp"];
} else {
	$nom = $_POST["nom"];
	$mdp = $_POST["mdp"];
	$_SESSION["nom"] = $nom;
	$_SESSION["mdp"] = $mdp;
}

$con=mysqli_connect($servername,$username,$password,$dbname);
if (mysqli_connect_errno()) {
  echo "Failed to connect to MySQL: " . mysqli_connect_error();
  exit();
}

$query = "SELECT * FROM comptes WHERE nom='$nom'";
$result = $con->query($query);
$row = $result->fetch_array(MYSQLI_NUM);

if ($row == null || !password_verify($mdp, $row[2])) {
	echo(str_replace("tres", "active", str_replace("%php%", "<h1>Nom ou mot de passe invalide</h1>", file_get_contents("header.html", true))));
} else {
	$page = "";
	$query = "";
	$priorites = array('HAUTE', 'MOYENNE PLUS', 'MOYENNE MOINS', 'BASSE');
	foreach ($priorites as $pri) {
		$query .= "SELECT * FROM voeux WHERE proprietaire='$nom' AND priorite='$pri';";
	}
	substr($query, 0, -1);
	mysqli_multi_query($con, $query);
	foreach ($priorites as $pri) {
		$result = mysqli_store_result($con);
		$page .= "<h1 class='categories'>Priorité ".strtolower($pri).":</h1>";
		while ($colonne = mysqli_fetch_array($result)) {
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
			$page .= "<div class='ticket'>";
			$page .= "<a href='".clearhtml($colonne[2])."'>";
			$page .= "<img src='".clearhtml($image)."' alt='L image du voeux' class='image_ticket'>";
			$page .= "<div class='texte_ticket'>".clearhtml($nom)."<br>";
			$page .= clearhtml($colonne[4])."€";
			$page .= "<form action='supprimer.php' method='post'><input type='hidden' name='id' value='$colonne[0]'><input type='submit' value='SUPPRIMER'></form>";
			$page .= "</div></a></div>";
		}
		mysqli_next_result($con);
	}
	
	$page .= "<h1 class='categories'>Ajouter un voeu:</h1>
			<form action='ajouter.php' method='post'>
			<table class='center'>
				<tr>
					<td>
					<label for='nom'>Nom:</label>
					</td><td>
					<input type='text' name='nom' id='nom' required>
					</td>
				</tr><tr>
					<td>
					<label for='lien'>Lien:</label>
					</td><td>
					<input type='url' id='lien' name='lien'>
					</td>
				</tr><tr>
					<td>
					<label for='image'>Lien image:</label>
					</td><td> 
					<input type='url' id='image' name='image'>
					</td>
				</tr><tr>
					<td>
					<label for='prix'>Prix total:</label>
					</td><td> 
					<input type='number' id='prix' name='prix' required>
					</td>
				</tr><tr>
					<td>
					<label for='quantite'>Quantité:</label>
					</td><td> 
					<input type='number' id='quantite' name='quantite' value='1'>
					</td>
				</tr><tr>
					<td>
					<label for='priorite'>Priorité:</label>
					</td><td>
					<input type='radio' name='priorite' value='HAUTE'> Haute <br>  
					<input type='radio' name='priorite' value='MOYENNE PLUS' checked> Moyenne + <br>  
					<input type='radio' name='priorite' value='MOYENNE MOINS'> Moyenne - <br>  
					<input type='radio' name='priorite' value='BASSE'> Basse <br>
					</td>
				</tr>
			</table>
			<input type='submit' value='Ajouter'>
			</form>";
	
	echo(str_replace("tres", "active", str_replace("%php%", $page, file_get_contents("header.html", true))));
}

mysqli_close($con);
?>
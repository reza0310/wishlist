<?php
include "../../INCLUDES/utils.php";

session_start();
if (isset($_SESSION["reprise"]) && $_SESSION["reprise"] == true) {
	$nom = $_SESSION["nom"];
	$mdp = $_SESSION["mdp"];
} else {
	$nom = $_POST["nom"];
	$mdp = $_POST["mdp"];
	$_SESSION["nom"] = $nom;
	$_SESSION["mdp"] = $mdp;
}
$_SESSION["reprise"] = false;

$con = dbconnect();

$query = $con->prepare("SELECT * FROM comptes WHERE nom=?");
$query->bind_param("s", $nom);
$query->execute();
$result = $query->get_result();
$query->close();
$row = $result->fetch_array(MYSQLI_NUM);

if ($row == null || !password_verify($mdp, $row[2])) {
	echo(str_replace("tres", "active", str_replace("%php%", "<h1>Nom ou mot de passe invalide</h1>", file_get_contents("header.html", true))));
} else {
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
					$image = checkurl(clearhtml($colonne[3]));
				}
				if ($colonne[4] < 0) {
					$prix = "Prix inconnu";
				} else {
					$prix = clearhtml(strval($colonne[4]))."€";
				}
				$page .= "<div class='ticket'>
							<a href='".checkurl(clearhtml($colonne[2]))."'>
								<img src='".$image."' alt='L image du voeux' class='image_ticket'>
								<div class='texte_ticket'>".clearhtml($nom)."<br>
									<form action='modifier.php' method='post'>
										<input type='hidden' name='id' value='$colonne[0]'>
										<input type='hidden' name='action' value='monter'>
										<input type='submit' value='MONTER EN PRIORITE'>
									</form>
									<form action='modifier.php' method='post'>
										<input type='hidden' name='id' value='$colonne[0]'>
										<input type='hidden' name='action' value='descendre'>
										<input type='submit' value='BAISSER EN PRIORITE'>
									</form>
									<form action='modifier.php' method='post'>
										<input type='hidden' name='id' value='$colonne[0]'>
										<input type='hidden' name='action' value='supprimer'>
										<input type='submit' value='SUPPRIMER'>
									</form>
									".$prix."
								</div>
							</a>
						</div>";
			}
		}
	}

	$page .= "<h1 class='categories'>Ajouter un voeu:</h1>
			<form action='ajouter.php' method='post'>
			<table class='horizontal-center'>
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
					<input type='number' step='0.01' min='-1' id='prix' name='prix' placeholder='En €' required>
					</td>
				</tr><tr>
					<td colspan='2'>
					<label for='prix'>Prix en euros, indiquer -1 pour un prix inconnu.</label>
					</td>
				</tr><tr>
					<td>
					<label for='quantite'>Quantité:</label>
					</td><td>
					<input type='number' min='0' id='quantite' name='quantite' value='1'>
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

	echo(addheader($page, "tres"));
}

$con->close();
?>
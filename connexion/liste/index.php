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
	echo(addheader("<h1>Nom ou mot de passe invalide</h1>", "tres"));
} else {
	$page = "";
	
	// Styles
	$page .= "<h1>STYLES & UNIVERS:</h1>
			  <div class='category_body'>";
	$query = $con->prepare("SELECT * FROM styles WHERE proprietaire=?");
	$query->bind_param("s", $nom);
	$query->execute();
	$result = mysqli_fetch_all($query->get_result());
	$query->close();
	foreach ($result as $colonne) {
		if ($colonne[2] == NULL) {
			$image = "https://upload.wikimedia.org/wikipedia/commons/thumb/f/f8/Question_mark_alternate.svg/1200px-Question_mark_alternate.svg.png";
		} else {
			$image = checkurl(clearhtml($colonne[2]));
		}
		$page .= "<div class='ticket ticket_padding'>
					<div class='ticket_main'>
						<img class='ticket_image' src='$image' alt='L image du voeux'>
						<div class='ticket_txt'>
							<div class='ticket_name'>$colonne[1]</div>
						</div>
					</div>
					<div class='ticket_actions'>
						<form class='ticket_action' action='supprimer_style.php' method='post'>
							<input type='hidden' name='id' value='$colonne[0]'>
							<input class='ticket_btn' type='submit' value='SUPPRIMER'>
						</form>
					</div>
				</div>";
	}

	$page .= "</div>
	
	<h1 class='category_title'>Ajouter un style ou un univers:</h1>
	<form action='ajouter_style.php' method='post'>
		<table class='form'>
			<tr>
				<td>
					<label class='form_label' for='nom'>Nom:</label>
				</td>
				<td class='form_grow'>
					<input class='form_input' type='text' name='nom' id='nom' required>
				</td>
			</tr>
			<tr>
				<td>
					<label class='form_label' for='image'>Lien image:</label>
				</td>
				<td class='form_grow'>
					<input class='form_input' type='url' id='image' name='image'>
				</td>
			</tr>
		</table>
		<input type='submit' value='Ajouter'>
	</form>";
	
	// Voeux
	$page .= "<h1>VOEUX:</h1>";
	$priorites = array('DISCONTINUE', 'HAUTE', 'MOYENNE PLUS', 'MOYENNE MOINS', 'BASSE');
	$query = $con->prepare("SELECT * FROM voeux WHERE proprietaire=?");
	$query->bind_param("s", $nom);
	$query->execute();
	$result = mysqli_fetch_all($query->get_result());
	$query->close();

	foreach ($priorites as $pri) {
		$page .= "<h1 class='category_title'>Priorité ".strtolower($pri).":</h1>";
		$page .= "<div class='category_body'>";
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
				$url = checkurl(clearhtml($colonne[2]));
				if ($url != "") {
					$page .= "<a class='ticket ticket_padding' href='$url' target='_blank'>";
				} else {
					$page .= "<div class='ticket ticket_padding'>";
				}
				$page .= "
					<div class='ticket_main'>
						<img class='ticket_image' src='$image' alt='L image du voeux'>
						<div class='ticket_txt'>
							<div class='ticket_name'>$nom</div>
							<div class='ticket_price'>$prix</div>
						</div>
					</div>
					<div class='ticket_actions'>
						<form class='ticket_action' action='modifier.php' method='post'>
							<input type='hidden' name='id' value='$colonne[0]'>
							<input type='hidden' name='action' value='monter'>
							<input class='ticket_btn' type='submit' value='⇧'>
						</form>
						<form class='ticket_action' action='modifier.php' method='post'>
							<input type='hidden' name='id' value='$colonne[0]'>
							<input type='hidden' name='action' value='descendre'>
							<input class='ticket_btn' type='submit' value='⇩'>
						</form>
						<form class='ticket_action' action='modifier.php' method='post'>
							<input type='hidden' name='id' value='$colonne[0]'>
							<input type='hidden' name='action' value='supprimer'>
							<input class='ticket_btn' type='submit' value='SUPPRIMER'>
						</form>
					</div>"; 
				if ($url != "") {
					$page .= "</a>";
				} else {
					$page .= "</div>";
				}
			}
		}
		$page .= "</div>";
	}

	$page .= "
	<h1 class='category_title'>Ajouter un voeu:</h1>
	<form action='ajouter.php' method='post'>
		<table class='form'>
			<tr>
				<td>
					<label class='form_label' for='nom'>Nom:</label>
				</td>
				<td class='form_grow'>
					<input class='form_input' type='text' name='nom' id='nom' required>
				</td>
			</tr>
			<tr>
				<td>
					<label class='form_label' for='lien'>Lien:</label>
				</td>
				<td class='form_grow'>
					<input class='form_input' type='url' id='lien' name='lien'>
				</td>
			</tr>
			<tr>
				<td>
					<label class='form_label' for='image'>Lien image:</label>
				</td>
				<td class='form_grow'>
					<input class='form_input' type='url' id='image' name='image'>
				</td>
			</tr>
			<tr>
				<td>
					<label class='form_label' for='prix'>Prix total:</label>
				</td>
				<td class='form_grow'>
					<input class='form_input' type='number' step='0.01' min='-1' id='prix' name='prix' placeholder='En €' required>
				</td>
			</tr>
			<tr>
				<td colspan='2'>
					<label for='prix'>Prix en euros, indiquer un prix négatif pour un prix inconnu.</label>
				</td>
			</tr>
			<tr>
				<td>
					<label class='form_label' for='quantite'>Quantité:</label>
				</td>
				<td class='form_grow'>
					<input class='form_input' type='number' min='0' id='quantite' name='quantite' value='1'>
				</td>
			</tr>
			<tr>
				<td>
					<label class='form_label' for='priorite'>Priorité:</label>
				</td>
				<td class='form_grow'>
					<input type='radio' name='priorite' value='HAUTE'> Discontinué <br>
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
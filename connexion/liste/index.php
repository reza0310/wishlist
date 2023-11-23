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
	echo(addheader("<h1>Session expirée ou couple nom/mot de passe invalide</h1>", "tres"));
} else {
	$page = "<script src='/wishlist/JS/dontreloadform.js'></script>";
	
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
	$miniprix = 0;
	$maxiprix = 0;

	foreach ($priorites as $pri) {
		$page .= "<h1 class='category_title'>Priorité ".strtolower($pri)." (%bang%€):";
		$page .= "<svg id='hider-".strtolower($pri)."' class='hideshow_category' xmlns='http://www.w3.org/2000/svg' viewBox='0 0 448 512'><!--! Font Awesome Pro 6.4.2 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license (Commercial License) Copyright 2023 Fonticons, Inc. --><path d='M432 256c0 17.7-14.3 32-32 32L48 288c-17.7 0-32-14.3-32-32s14.3-32 32-32l352 0c17.7 0 32 14.3 32 32z'/></svg>";
		$page .= "<svg id='shower-".strtolower($pri)."' class='hideshow_category' style='display: none;' xmlns='http://www.w3.org/2000/svg' viewBox='0 0 448 512'><!--! Font Awesome Pro 6.4.2 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license (Commercial License) Copyright 2023 Fonticons, Inc. --><path d='M256 80c0-17.7-14.3-32-32-32s-32 14.3-32 32V224H48c-17.7 0-32 14.3-32 32s14.3 32 32 32H192V432c0 17.7 14.3 32 32 32s32-14.3 32-32V288H400c17.7 0 32-14.3 32-32s-14.3-32-32-32H256V80z'/></svg>";
		$page .= "</h1>";
		$page .= "<div id='".strtolower($pri)."' class='category_body conteneur'>";
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
					$miniprix += $colonne[4];
					$maxiprix += $colonne[4];
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
		$page = str_replace("%bang%", strval($miniprix), $page);
		$page .= "</div>";
		$miniprix = 0;
	}
	$page .= "<h1 class='category_title'>Total: ($maxiprix €):</h1>";

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
					<input type='radio' name='priorite' value='DISCONTINUE'> Discontinué <br>
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

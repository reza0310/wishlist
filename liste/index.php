<?php
include "../INCLUDES/utils.php";

$nom = $_GET["nom"];

$con = dbconnect();

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
	$page .= "
	<div class='ticket'>
		<div class='ticket_main'>
			<img class='ticket_image' src='$image' alt='L image du voeux'>
			<div class='ticket_txt'>
				<div class='ticket_name'>$colonne[1]</div>
			</div>
		</div>
	</div>";
}
$page .= "</div>";

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
				$nom = clearhtml($colonne[1]);
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
				$page .= "<a class='ticket' href='$url' target='_blank'>";
			} else {
				$page .= "<div class='ticket'>";
			}
			$page .= "
			<div class='ticket_main'>
			  <img class='ticket_image' src='$image' alt='L image du voeux'>
			  <div class='ticket_txt'>
				<div class='ticket_name'>$nom</div>
				<div class='ticket_price'>$prix</div>
			  </div>
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

$con->close();

echo(addheader($page, "quatro"));
?>

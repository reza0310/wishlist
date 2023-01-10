<?php
include "../INCLUDES/utils.php";

$nom = $_GET["nom"];

$con = dbconnect();

$page = "";
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
	$page .= "</div>";
}

$con->close();

echo(addheader($page, "quatro"));
?>

<?php
function clearhtml($str) {
	return clearmulti($str, array("<", "&lt;", ">", "&gt;"));
}

function checkurl($str) {
	return "http".substr(clearmulti($str, array("'", "%27", "\\", "%5C", '"', "%22", " ", "%20")), 4);
}

function clearmulti($str, $liste) {
	if ($str == null) {
		return '';
	}
	$sortie = "";
	foreach(str_split($str) as $lettre) {
		$replaced = false;
		for($i = 0; $i < count($liste); $i += 2) {
			if ($lettre == $liste[$i]) {
				$sortie .= $liste[$i+1];
				$replaced = true;
			}
		}
		if (!$replaced) {
			$sortie .= $lettre;
		}
	}
	return $sortie;
}
?>
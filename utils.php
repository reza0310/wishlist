<?php

// Deny execution of the rest of this file if not included
if (count(get_included_files()) <= 1) {
    http_response_code(404);
	exit();
}

include "config.php";


function addheader($content, $navid) {
	$ret = file_get_contents("header.html", true);
	$ret = str_replace($navid, "active", $ret);
	$ret = str_replace("%php%", $content, $ret);
	$ret = str_replace("%basedir%", BASEDIR, $ret);
	return $ret;
}


function dbconnect() {
	// Disable error messages to avoid dumping the database password in case of error
	$old_err_lvl = error_reporting();
	error_reporting(0);
	$con = new mysqli(DB_HOST, DB_USER, DB_PWD, DB_NAME, DB_PORT);
	error_reporting($old_err_lvl);
	// Check conection
	if ($con->connect_error) {
		http_response_code(500);
		die("Failed to connect to DB: " . $con->conect_error);
	}
	return $con;
}


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
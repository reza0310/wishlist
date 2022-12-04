<?php
function clearhtml($str) {
	return str_replace(">", "", str_replace("<", "", $str));
}

function checkurl($str) {
	return "http".explode(" ", substr($str, 4))[0];
}
?>
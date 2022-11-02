<?php
function clearhtml($str) {
	return str_replace(">", "", str_replace("<", "", $str));
}
?>
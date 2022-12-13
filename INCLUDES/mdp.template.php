<?php

// ================================================
// Don't forget to rename this file to "mdp.php"
// ================================================

// Deny execution of the rest of this file if not included
if (count(get_included_files()) <= 1) {
    http_response_code(404);
	exit();
}

const DB_HOST = "host";
const DB_USER = "user";
const DB_PASS = "pass";
const DB_NAME = "wishlist";
const DB_PORT = 3306; // This is the default connection port for MySQL/MariaDB

?>
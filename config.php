<?php

// Deny execution of the rest of this file if not included
if (count(get_included_files()) <= 1) {
    http_response_code(404);
	exit();
}

// If you want to have a basedir, don't forget to add a trailing / at the end!
const BASEDIR = "wishlist";

const DB_PWD  = "the user password";
const DB_USER = "the database username";
const DB_HOST = "the mariadb/mysql server host";
const DB_NAME = "your database name";
const DB_PORT = 3306; // This is the default connection port for MySQL/MariaDB

set_include_path($_SERVER['DOCUMENT_ROOT'].DIRECTORY_SEPARATOR.BASEDIR);

?>

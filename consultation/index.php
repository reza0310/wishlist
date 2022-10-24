<?php
set_include_path($_SERVER['DOCUMENT_ROOT']."/wishlist");
echo(str_replace("quatro", "active", str_replace("%php%", file_get_contents("page.html"), file_get_contents("header.html", true))));
?>
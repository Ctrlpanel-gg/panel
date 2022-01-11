<?php
include ("functions.php");


echo "php version: ".checkPhpVersion();

echo "<br/>";

echo "mysql version: ".getMySQLVersion();

echo "<br/>";

echo "Missing extentions: "; foreach(checkExtensions() as $ext){ echo $ext.", ";};

echo "<br/>";

print_r(get_loaded_extensions());

?>
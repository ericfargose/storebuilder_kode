<?php
$dbh1 = @mysql_connect('localhost', 'root', 'root'); 
mysql_select_db('storebuilder', $dbh1);
mysql_query("SET NAMES 'utf8'", $dbh1);
mysql_query("SET CHARACTER SET utf8", $dbh1);
mysql_query("SET CHARACTER_SET_CONNECTION=utf8", $dbh1);
mysql_query("SET SQL_MODE = ''", $dbh1);
?>
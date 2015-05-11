<?php 
session_start();
require_once('db_connect.php');
$sql = "INSERT INTO `customer` SET 
		`from_location` = '".mysql_real_escape_string($_POST['from_location'], $dbh1)."',
		`to_location` = '".mysql_real_escape_string($_POST['to_location'], $dbh1)."',
		`email` = '".mysql_real_escape_string($_POST['email'], $dbh1)."',
		`phone` = '".mysql_real_escape_string($_POST['phone_number'], $dbh1)."',
		`message` = '".mysql_real_escape_string($_POST['message'], $dbh1)."' ";
db_query($sql, $dbh1);

$visitpage = $_SERVER['HTTP_REFERER'];

$_SESSION['success'] = 'You have Successfully Registered';

header("Location:$visitpage");

function db_query($sql, $db) {
	$query = mysql_query($sql,$db);
	$rows = array();
	if(is_resource($query)) {
		while($r = mysql_fetch_assoc($query)) {
		    $rows[] = $r;
		}
	}
return $rows;
}
?>
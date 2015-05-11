<?php 

/*
To delete the database you need to post the following data to this script:
1. store_id

*/

require_once("../config.php");
$link = @mysql_connect(DB_HOSTNAME, DB_USERNAME, DB_PASSWORD);

$data = file_get_contents('php://input');
$datas = json_decode($data,true);

if(isset($datas['store_id'])) {
	$sql = "DROP DATABASE `sb_".$datas['store_id']."`";
	mysql_query($sql, $link);
	    
} else {
	exit('Database not given in post');
}

?>
<?php 

/*
To create the database you need to post the following data to this script:
1. db_new_name
2. username
3. password
4. email
5. url
6. name
7. store_id
8. url_type

*/

// $datas['db_new_name'] = 'sb_630';
// $datas['username'] = 'aaron';
// $datas['password'] = 'kodeplay';
// $datas['email'] = 'aaron@kodeplay.com';
// $datas['store_url'] = 'http://storebuilder.kp/aaron/';
// $datas['name'] = 'db_check';
// $datas['store_id'] = '630';
// $datas['url_type'] = 'subdir';

require_once("../config.php");
$file = DEFAULT_DB;
$link = @mysql_connect(DB_HOSTNAME, DB_USERNAME, DB_PASSWORD);

$db_prefix = "";

$lines = file($file);

$data = file_get_contents('php://input');
$datas = json_decode($data,true);

if(isset($datas['db_new_name'])) {
	$sql = 'CREATE DATABASE '.$datas['db_new_name'];

	mysql_query($sql, $link);
	    
	mysql_select_db($datas['db_new_name'], $link);
} else {
	exit('Database not given in post');
}

if ($lines) {
	$sql = '';
	foreach($lines as $line) {
		if ($line && (substr($line, 0, 2) != '--') && (substr($line, 0, 1) != '#')) {
			$sql .= $line;
			if (preg_match('/;\s*$/', $line)) {
				// $sql = str_replace("DROP TABLE IF EXISTS `sb_", "DROP TABLE IF EXISTS `" . $db_prefix, $sql);
				// $sql = str_replace("CREATE TABLE `sb_", "CREATE TABLE `" . $db_prefix, $sql);
				// $sql = str_replace("INSERT INTO `sb_", "INSERT INTO `" . $db_prefix, $sql);
				mysql_query($sql, $link);
				$sql = '';
			}
		}
	}
	
	if(isset($datas['username']) && isset($datas['password']) && isset($datas['email']) && isset($datas['store_url'])) {

		mysql_query("SET CHARACTER SET utf8", $link);
		
		mysql_query("SET @@session.sql_mode = 'MYSQL40'", $link);

		mysql_query("DELETE FROM `" . $db_prefix . "user` WHERE user_id = '1'", $link);

		mysql_query("INSERT INTO `" . $db_prefix . "user` SET user_id = '1', user_group_id = '1', username = '" . mysql_escape_string($datas['username']) . "', salt = '" . mysql_escape_string($salt = substr(md5(uniqid(rand(), true)), 0, 9)) . "', password = '" . mysql_escape_string(sha1($salt . sha1($salt . sha1($datas['password'])))) . "', status = '1', email = '" . mysql_escape_string($datas['email']) . "', date_added = NOW()", $link);

		mysql_query("DELETE FROM `" . $db_prefix . "setting` WHERE `key` = 'config_name'", $link);
		mysql_query("INSERT INTO `" . $db_prefix . "setting` SET `code` = 'config', `key` = 'config_name', value = '" . mysql_escape_string($datas['name']) . "'", $link);

		mysql_query("DELETE FROM `" . $db_prefix . "setting` WHERE `key` = 'config_title'", $link);
		mysql_query("INSERT INTO `" . $db_prefix . "setting` SET `code` = 'config', `key` = 'config_title', value = '" . mysql_escape_string($datas['name']) . "'", $link);

		mysql_query("DELETE FROM `" . $db_prefix . "setting` WHERE `key` = 'config_meta_description'", $link);
		mysql_query("INSERT INTO `" . $db_prefix . "setting` SET `code` = 'config', `key` = 'config_meta_description', value = '" . mysql_escape_string($datas['name']) . "'", $link);

		mysql_query("DELETE FROM `" . $db_prefix . "setting` WHERE `key` = 'config_email'", $link);
		mysql_query("INSERT INTO `" . $db_prefix . "setting` SET `code` = 'config', `key` = 'config_email', value = '" . mysql_escape_string($datas['email']) . "'", $link);
		
		mysql_query("DELETE FROM `" . $db_prefix . "setting` WHERE `key` = 'config_url'", $link);
		mysql_query("INSERT INTO `" . $db_prefix . "setting` SET `code` = 'config', `key` = 'config_url', value = '" . mysql_escape_string($datas['store_url']) . "'", $link);
		
		$store_url = parse_url($datas['store_url']);
		$ssl_url = 'https://'.$store_url['host'].''.$store_url['path'];
		
		mysql_query("DELETE FROM `" . $db_prefix . "setting` WHERE `key` = 'config_ssl'", $link);
		mysql_query("INSERT INTO `" . $db_prefix . "setting` SET `code` = 'config', `key` = 'config_ssl', value = '" . mysql_escape_string($ssl_url) . "'", $link);

		mysql_query("DELETE FROM `" . $db_prefix . "setting` WHERE `key` = 'config_url_type'", $link);
		mysql_query("INSERT INTO `" . $db_prefix . "setting` SET `code` = 'config', `key` = 'config_url_type', value = '" . mysql_escape_string($datas['url_type']) . "'", $link);

		mysql_query("DELETE FROM `" . $db_prefix . "setting` WHERE `key` = 'store_url_type'", $link);
		mysql_query("INSERT INTO `" . $db_prefix . "setting` SET `code` = 'config', `key` = 'store_url_type', value = '" . mysql_escape_string($datas['url_type']) . "'", $link);
		
		mysql_query("DELETE FROM `" . $db_prefix . "setting` WHERE `key` = 'config_encryption'", $link);
		mysql_query("INSERT INTO `" . $db_prefix . "setting` SET `code` = 'config', `key` = 'config_encryption', value = '" . mysql_escape_string(md5(mt_rand())) . "'", $link);
		
		mysql_query("UPDATE `" . $db_prefix . "product` SET `viewed` = '0'", $link);

		mysql_query("DELETE FROM `" . $db_prefix . "setting` WHERE `key` = 'config_error_filename'", $link);
		$error_filename = 'error_store_'.$datas['store_id'];
		mysql_query("INSERT INTO `" . $db_prefix . "setting` SET `code` = 'config', `key` = 'config_error_filename', value = '" . mysql_escape_string($error_filename) . "'", $link);

		mysql_query("UPDATE `" . $db_prefix . "product` SET `image` = REPLACE(`image`, 'catalog/demo/', 'catalog/store_".$datas['store_id']."/')", $link);
		mysql_query("UPDATE `" . $db_prefix . "category` SET `image` = REPLACE(`image`, 'catalog/demo/', 'catalog/store_".$datas['store_id']."/')", $link);
		mysql_query("UPDATE `" . $db_prefix . "manufacturer` SET `image` = REPLACE(`image`, 'catalog/demo/', 'catalog/store_".$datas['store_id']."/')", $link);
		mysql_query("UPDATE `" . $db_prefix . "product_image` SET `image` = REPLACE(`image`, 'catalog/demo/', 'catalog/store_".$datas['store_id']."/')", $link);
		mysql_query("UPDATE `" . $db_prefix . "banner_image` SET `image` = REPLACE(`image`, 'catalog/demo/', 'catalog/store_".$datas['store_id']."/')", $link);
	}
} else {
	exit("No content in default sql file");
}

?>
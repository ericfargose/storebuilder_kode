<?php 
/*
To : Create the Images and Cache Directories
1. store id
*/
$data = file_get_contents('php://input');
$datas = json_decode($data,true);

// $datas = array();
// $datas['store_id'] = 630;

require_once("../config.php");
if(isset($datas['store_id'])) {
	$store_id = $datas['store_id'];
	//images
	mkdir( DIR_IMAGE . 'catalog/store_' . $store_id , 0777);
	chmod( DIR_IMAGE . 'catalog/store_' . $store_id , 0777);
	$error_filename = 'error_store_'.$store_id;
	//error logs
	fopen(DIR_LOGS.$error_filename, 'w+');
	//separate cache dir
	mkdir(DIR_CACHE .'stores/store_' . $store_id , 0777);	

	$srcPath = DIR_IMAGE.'catalog/demo/';
	$destPath = DIR_IMAGE.'catalog/store_'.$datas['store_id'].'/';  
	recurse_copy($srcPath, $destPath);

	/*
	$srcDir = opendir($srcPath);
	while($readFile = readdir($srcDir)) {
	    if($readFile != '.' && $readFile != '..') {
	        if (!file_exists($readFile)) {
	            if(copy($srcPath . $readFile, $destPath . $readFile)) {
	                echo "Copy file";
	            } else {
	                echo "Canot Copy file";
	            }
	        }
	    }
	}
	closedir($srcDir);
	*/
} else {
	exit('Store Id not given in post');
}

function recurse_copy($src,$dst) { 
    $dir = opendir($src); 
    @mkdir($dst, 0777); 
    while(false !== ($file = readdir($dir))) { 
        if (( $file != '.' ) && ( $file != '..' )) { 
            if (is_dir($src . '/' . $file)) { 
                recurse_copy($src . '/' . $file,$dst . '/' . $file); 
            } 
            else {
            	if (!file_exists($file)) { 
                	copy($src . '/' . $file,$dst . '/' . $file); 
            	}
            } 
        } 
    } 
    closedir($dir); 
}

?>
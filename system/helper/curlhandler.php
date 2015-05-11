<?php
class CurlHandler {
	
	public function __construct(){		
		if (!function_exists('curl_init')) {
		  throw new Exception('Require PHP CURL extension.');
		}
	}
	
	public static function Request($api, $action = "", $fields = array()){
		$url = HTTP_CATALOG.'service/'.$api.'.php?action='.$action;
		$data = json_encode($fields);
		$ch = curl_init($url);
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
		curl_setopt($ch, CURLOPT_HTTPHEADER, array(                                                                          
		    'Content-Type: application/json',                                                                                
		    'Content-Length: ' . strlen($data))                                                                       
		); 
		curl_setopt($ch,CURLOPT_POSTFIELDS, $data);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		$result = curl_exec($ch);
		curl_close($ch);
		$data = json_decode($result, true);
		return $data;
	}
	
}

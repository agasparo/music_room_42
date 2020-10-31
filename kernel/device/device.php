<?php

Class Device {


	public function GetIp() : string {

		if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
	    	$ip = $_SERVER['HTTP_CLIENT_IP'];
		} elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
		    $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
		} else {
		    $ip = file_get_contents("https://api.ipify.org");
		}

		return ($ip);
	}

	public function geolocalisation() : array {

		$content = file_get_contents("http://ip-api.com/json/" . $this->GetIp());
		return (json_decode($content, true));
	}
}

?>
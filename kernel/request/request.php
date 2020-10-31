<?php

class Request {

	/**
	 * @methode String
	 */
	private $methode;

	/**
	 * @args Array
	 */
	private $args = [];

	public function __Construct(String $methode, Array $param) {
		$this->methode = $methode;
		$this->args = $param;
	}

	public function send_request(String $url, String $token) : String {
		
		$postdata = http_build_query(
			$this->args
		);

		if ($token != "none") {
			$opts = ['http' =>
				[
					'method'  => $this->methode,
					'header' => [
						'Authorization: Bearer '.$token,
						'Content-Type: application/x-www-form-urlencoded'
					],
					'content' => $this->methode == "POST" ? $postdata : ""
				]
			];
		} else {
			$opts = ['http' =>
				[
					'method'  => $this->methode,
					'header' => [
						'Content-Type: application/x-www-form-urlencoded'
					],
					'content' => $this->methode == "POST" ? $postdata : ""
				]
			];
		}

		if ($this->methode == "GET")
			$url .= "?" . $postdata;

		$context  = stream_context_create($opts);

		return (file_get_contents($url, false, $context));
	}
}

?>
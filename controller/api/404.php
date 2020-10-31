<?php

class NotFoundAPI {

	private $response;

	public function __Construct() {

		$this->response = new Responses();
	}

	public function show() {
		
		$t0 = microtime(true);
		return ($this->response->resp(["not found"], 404, ["type" => "none"], number_format(microtime(true) - $t0, 5)));
	}
}

?>
<?php

Class lyricsApi {

	private $bdd;

	private $response;

	private $checker;

	public function __Construct() {

		$e = new Errors();
		$this->response = new Responses();
		$this->bdd = new Bdd($e);
		$this->checker = new CheckToken();
	}

	public function getAll() {

		$t0 = microtime(true);
		if (!isset($_GET["myToken"]) || !isset($_GET["apiKey"])  || !$this->checker->isValid(htmlspecialchars($_GET["myToken"]), htmlspecialchars($_GET["apiKey"])))
			return ($this->response->resp(["bad token or bad api key"], 406, ["type" => "none"], number_format(microtime(true) - $t0, 5)));
		$content = $this->bdd->AddRequest("SELECT * FROM lyrics", [], 1);
		return ($this->response->resp($this->rm_useless($content), 200, ["type" => "base64"], number_format(microtime(true) - $t0, 5)));
	}

	public function getByName(string $name) {

		$t0 = microtime(true);
		if (!isset($_GET["myToken"]) || !isset($_GET["apiKey"])  || !$this->checker->isValid(htmlspecialchars($_GET["myToken"]), htmlspecialchars($_GET["apiKey"])))
			return ($this->response->resp(["bad token or bad api key"], 406, ["type" => "none"], number_format(microtime(true) - $t0, 5)));
		$content = $this->bdd->AddRequest("SELECT * FROM lyrics WHERE name LIKE ?", ["%".$name."%"], 1);
		return ($this->response->resp($this->rm_useless($content), 200, ["type" => "base64"], number_format(microtime(true) - $t0, 5)));
	}

	private function rm_useless(array $content) : array {

		$data = [];
		$breaks = ["<br />","<br>","<br/>"];

		foreach ($content as $key => $value) {
			
			$data[] = [
				"title" => trim($value["name"]),
				"text" => $value["lyrics"]
			];
		}
		return ($data);
	}
}

?>
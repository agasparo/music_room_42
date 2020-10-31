<?php

Class Test {

	private $content = [];

	public function __Construct() {

	}

	public function setBackTest(string $testname, string $methode, string $route, string $response = "", array $params = [], string $token = "") {

		$this->content[] = [
			"name" => $testname,
			"response" => $response,
			"methode" => $methode,
			"route" => $route,
			"params" => $params,
			"token" => $token
		];
	}

	public function run() {

		for ($i = 0; $i < count($this->content); $i++) {

			echo "\033[35mTest [" . $i . "] -> " .$this->content[$i]["name"] . " : \033[0m";

			$req = new Request($this->content[$i]["methode"], $this->content[$i]["params"]);
			$response = $req->send_request("http://lvh.me/music_room/" . $this->content[$i]["route"], $this->content[$i]["token"]);
			if ($response != $this->content[$i]["response"]) {
				echo "\033[31m[KO]\033[0m\n";
				echo "response must be : \n" . $this->content[$i]["response"] . "\n";
				echo "and you return : \n" . $response . "\n";
				echo "\033[31mtest passed : " . $i . " / " . count($this->content) . "\033[0m\n";
				return;
			} else {
				echo "\033[32m[OK]\033[0m\n";
			}
		}
		echo "\033[32mtest passed : " . $i . " / " . count($this->content) . "\033[0m\n";
	}
}

?>
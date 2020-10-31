<?php

Class api {

	const TEMPLATE_BASE = "template/api/home.html";

	private $render;
	private $allvars;
	private $bdd;

	public function __Construct() {

		$e = new Errors();
		$this->render = new Render($e);
		$this->allvars = new Globale($e);
		$this->bdd = new Bdd($e);
	}

	public function home() {
		
		if (!isset($_SESSION['id']) || empty($_SESSION['id']))
			header('location:/music_room/');
		$array = $this->allvars->GetFor("api/home", 0);
		echo $this->render->run($array, self::TEMPLATE_BASE);
	}

	public function getToken() {

		if (!isset($_SESSION['id']) || empty($_SESSION['id']))
			return;

		$token = $this->GenerateToken();
		$api_key = $this->GenerateApiKey();
		echo json_encode([
			"status" => 200,
			"token" => $this->insertToken($token, $api_key),
			"api_key" => $api_key
		]);
	}

	public function getMyToken() {

		if (!isset($_SESSION['id']) || empty($_SESSION['id']))
			return;

		$data = $this->getGenerateToken();
		echo json_encode([
			"status" => 200,
			"token" => isset($data[0]) ? $data[0] : null,
			"api_key" => isset($data[1]) ? $data[1] : null,
		]);
	}

	private function getGenerateToken() {

		$user = $this->bdd->AddRequest("SELECT * FROM api WHERE id_user = ?", [$_SESSION["id"]], 2);
		if (!empty($user))
			return ([$user["token"], $user["id_key"]]);
		return (null);
	}

	private function GenerateToken() : string {

		$length = rand(25, 120);
		$token = bin2hex(random_bytes($length));
		return ($token);
	}

	private function GenerateApiKey() : string {

		$length = rand(25, 45);
		$token = bin2hex(random_bytes($length));
		return ($token);
	}

	private function insertToken(string $token, string $api_key) {

		$user = $this->bdd->AddRequest("SELECT * FROM api WHERE id_user = ?", [$_SESSION["id"]], 3);
		if ($user == 1)
			$this->bdd->AddRequest("UPDATE api SET token = ?, id_key = ? WHERE id_user = ?", [$token, $api_key, $_SESSION["id"]], 3);
		else
			$this->bdd->AddRequest("INSERT INTO api(id_user, token, id_key) VALUES(?, ?, ?)", [$_SESSION["id"], $token, $api_key], 3);
		return ($token);
	}
}

?>
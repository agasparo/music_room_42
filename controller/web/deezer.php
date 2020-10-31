<?php

Class Deezer {

	private $bdd;

	public function __Construct() {

		$e = new Errors();
		$this->bdd = new Bdd($e);
	}

	public function connexion() {

		if (!isset($_POST['name']) || empty($_POST['name'])) {
			echo ($this->response(403, "bad arguments"));
			return;
		}
		if (!isset($_POST['surname']) || empty($_POST['surname'])) {
			echo ($this->response(403, "bad arguments"));
			return;
		}
		if (!isset($_POST['img']) || empty($_POST['img'])) {
			echo ($this->response(403, "bad arguments"));
			return;
		}
		if (!isset($_POST['mail']) || empty($_POST['mail'])) {
			echo ($this->response(403, "bad arguments"));
			return;
		}

		$name = htmlspecialchars($_POST['name']);
		$surname = htmlspecialchars($_POST['surname']);
		$img = htmlspecialchars($_POST['img']);
		$email = htmlspecialchars($_POST['mail']);

		$isSaved = $this->bdd->AddRequest("SELECT * FROM users WHERE mail = ? AND api = ?", [$email, 3], 2);
  		if (!empty($isSaved)) {
  			$_SESSION['id'] = $isSaved['id'];
  		} else {
  			$this->bdd->AddRequest("INSERT INTO users(prenom, nom, img, mail, password, valid, view, api, validator) VALUES(?, ?, ?, ?, ?, ?, ?, ?, ?)",
  			[
				$name,
				$surname,
				$img,
				$email,
				"1",
				1,
				true,
				3,
				"1"
			], 3);
  			$userinfo = $this->bdd->AddRequest("SELECT * FROM users WHERE mail = ? AND api = ?", [$email, 3], 2);
  			$_SESSION['id'] = $userinfo['id'];
  			$this->bdd->AddRequest("INSERT INTO abonnement(id_user, type) VALUES(?, ?)", [$_SESSION['id'], 0], 3);
  		}
  		$this->bdd->AddRequest("INSERT INTO connect(id_user) VALUES(?)", [$_SESSION['id']], 3);
  		echo ($this->response(200, "login"));
	}

	public function attach() {

		if (!isset($_SESSION['id']) || empty($_SESSION['id'])) {
			echo ($this->response(404, "not rights"));
			return;
		}
		if (!isset($_POST['name']) || empty($_POST['name'])) {
			echo ($this->response(403, "bad arguments"));
			return;
		}
		if (!isset($_POST['surname']) || empty($_POST['surname'])) {
			echo ($this->response(403, "bad arguments"));
			return;
		}
		if (!isset($_POST['img']) || empty($_POST['img'])) {
			echo ($this->response(403, "bad arguments"));
			return;
		}
		if (!isset($_POST['mail']) || empty($_POST['mail'])) {
			echo ($this->response(403, "bad arguments"));
			return;
		}

		$name = htmlspecialchars($_POST['name']);
		$surname = htmlspecialchars($_POST['surname']);
		$img = htmlspecialchars($_POST['img']);
		$email = htmlspecialchars($_POST['mail']);

		$atta = $this->bdd->AddRequest("INSERT INTO other_count(id_user, count, api) VALUES(?, ?, ?)",
			[
				$_SESSION['id'],
				json_encode([
					"name" => $name,
					"surname" => $surname,
					"img" => $img,
					"email" => $email
				]),
				3
			], 3);
		if ($atta == 0) {
			echo ($this->response(200, "not attach"));
			return;
		}
		echo ($this->response(200, "attach"));
	}

	private function response(int $status, string $response) : string {

		return (json_encode([
			"status" => $status,
			"response" => $response
		]));
	}
}

?>
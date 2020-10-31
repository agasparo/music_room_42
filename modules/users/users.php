<?php

session_start();

Class Users {

	private $users;

	public function GetDataBaseUsers(int $id, object $bdd) {

		if ($id == 0) {

			$response = $bdd->AddRequest("SELECT * FROM users", [], 1);
		} else {

			$response = $bdd->AddRequest("SELECT * FROM users where id = ?", [$id], 2);
		}
		$this->parseResponse($response);
	}

	private function parseResponse($response) {

		if (!isset($response['nom'])) {
			$this->users = [];
			return;
		}
		$this->users = [

			"name" => $response['nom'],
			"surname" => $response['prenom'],
			"picture" => $response['img'],
			"mail" => $response['mail'],
			"password" => $response['password']
		];
	}

	public function getUserData() : array {

		return ($this->users);
	}

	public function isConnected() : bool {

		if (isset($_SESSION['id']) && !empty($_SESSION['id'])) {

			return (1);
		}
		return (0);
	}
}

?>
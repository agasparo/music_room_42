<?php

class Rights {

	private $render;
	private $allvars;
	private $bdd;
	private $users;

	public function __Construct() {

		$e = new Errors();
		$this->render = new Render($e);
		$this->allvars = new Globale($e);
		$this->bdd = new Bdd($e);
		$this->users = new Users();
	}

	public function ModifyRights() {

		if (!isset($_SESSION['id']) || empty($_SESSION['id'])) {
			echo ($this->response(404, "not rights"));
			return;
		}

		if (!isset($_POST['type']) || empty($_POST['type'])) {
			echo ($this->response(403, "bad arguments"));
			return;
		}

		if (!isset($_POST['elem_state']) || empty($_POST['elem_state'])) {
			echo ($this->response(403, "bad arguments"));
			return;
		}

		if (!isset($_POST['room_id']) || empty($_POST['room_id'])) {
			echo ($this->response(403, "bad arguments"));
			return;
		}

		if (!isset($_POST['currentUser']) || empty($_POST['currentUser'])) {
			if ($_POST['currentUser'] != 0) {
				echo ($this->response(403, "bad arguments"));
				return;
			}
		}

		if (!isset($_POST['replace']) || empty($_POST['replace'])) {
			echo ($this->response(403, "bad arguments"));
			return;
		}

		$type = htmlspecialchars($_POST['type']);
		$elem_state = htmlspecialchars($_POST['elem_state']);
		$room_id = htmlspecialchars($_POST['room_id']);
		$currentUser = htmlspecialchars($_POST['currentUser']);
		$replace = htmlspecialchars($_POST['replace']);

		$getRoom = $this->bdd->AddRequest("SELECT * FROM room WHERE leader = ? AND id = ?", [$_SESSION['id'], $room_id], 2);
		if (empty($getRoom)) {
			echo ($this->response(404, "not rights"));
			return;
		}

		$members = json_decode($getRoom['members'], true);
		$res = 0;
		if (!$elem_state || $elem_state == "false")
			$res = 1;
		$members[$this->getIdFromPos($currentUser, $members)][$replace] = $res;

		$updateRoom = $this->bdd->AddRequest("UPDATE room SET members = ? WHERE leader = ? AND id = ?", [json_encode($members), $_SESSION['id'], $room_id], 3);
		if ($updateRoom == 0) {
			echo ($this->response(404, "error"));
			return;
		}
		echo ($this->response(200, "done"));
	}

	public function SoundRights() {

		if (!isset($_SESSION['id']) || empty($_SESSION['id'])) {
			echo ($this->response(404, "not rights"));
			return;
		}
		if (!isset($_POST['room_id']) || empty($_POST['room_id'])){
			echo ($this->response(403, "bad arguments"));
			return;
		}

		$room_id = htmlspecialchars($_POST['room_id']);
		$room_details = $this->bdd->AddRequest("SELECT * FROM room WHERE id = ?", [$room_id], 2);
		$users = json_decode($room_details['members'], true);
		foreach ($users as $key => $value) {
			
			if ($key == $_SESSION['id']) {
				echo $this->response(200, $value["sound_control"]);
				return;
			}
		}
	}

	public function PlaylistRights() {

		if (!isset($_SESSION['id']) || empty($_SESSION['id'])) {
			echo ($this->response(404, "not rights"));
			return;
		}
		if (!isset($_POST['room_id']) || empty($_POST['room_id'])){
			echo ($this->response(403, "bad arguments"));
			return;
		}

		$room_id = htmlspecialchars($_POST['room_id']);
		$room_details = $this->bdd->AddRequest("SELECT * FROM room WHERE id = ?", [$room_id], 2);
		$users = json_decode($room_details['members'], true);
		foreach ($users as $key => $value) {
			
			if ($key == $_SESSION['id']) {
				echo $this->response(200, $value["edit_playlist"]);
				return;
			}
		}
	}

	private function getIdFromPos(int $pos, array $data) : int {

		$i = 0;
		foreach ($data as $key => $value) {
			
			if ($i == $pos)
				return ($key);
			$i++;
		}
		return (-1);
	}

	private function response(int $status, string $response) : string {

		return (json_encode([
			"status" => $status,
			"response" => $response
		]));
	}
}

?>
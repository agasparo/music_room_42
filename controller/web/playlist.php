<?php

class playlist {

	private $bdd;

	public function __Construct() {

		$e = new Errors();
		$this->bdd = new Bdd($e);
	}

	public function iscreated() {

		if (!isset($_SESSION['id']) || empty($_SESSION['id'])) {
			echo ($this->response(404, "not rights"));
			return;
		}
		if (!isset($_POST['id_room']) || empty($_POST['id_room'])){
			echo ($this->response(403, "bad arguments"));
			return;
		}
		$id = htmlspecialchars($_POST['id_room']);
		$datas = $this->bdd->AddRequest("SELECT * FROM playlist WHERE room_id = ? ORDER BY pos ASC", [$id], 1);
		if (empty($datas)) {
			echo ($this->response(200, "no playlist"));
			return;
		}
		echo ($this->response(200, json_encode($datas)));
	}

	public function modify() {

		if (!isset($_SESSION['id']) || empty($_SESSION['id'])) {
			echo ($this->response(404, "not rights"));
			return;
		}
		if (!isset($_POST['room_id']) || empty($_POST['room_id'])){
			echo ($this->response(403, "bad arguments"));
			return;
		}
		if (!isset($_POST['n_playlist']) || empty($_POST['n_playlist'])){
			echo ($this->response(403, "bad arguments"));
			return;
		}
		$n_playlist = json_decode($_POST['n_playlist'], true);
		$room_id = htmlspecialchars($_POST['room_id']);
		$datas = $this->bdd->AddRequest("SELECT * FROM playlist WHERE room_id = ? ORDER BY pos ASC", [$room_id], 1);
		if (empty($datas)) {
			echo ($this->response(403, "no playlist"));
			return;
		}
		foreach ($datas as $key => $value) {
			
			if ($value["pos"] != $n_playlist[$key]) {
				$this->bdd->AddRequest("UPDATE playlist SET pos = ? WHERE song_id = ?", [$n_playlist[$key]["pos"], $n_playlist[$key]["video"]], 3);
			}
		}
		$this->bdd->AddRequest("UPDATE room SET begin_m = ? WHERE id = ?", [$n_playlist[0]["video"], $room_id], 3);
		echo ($this->response(200, "ok"));
	}

	public function add() {
		
		if (!isset($_SESSION['id']) || empty($_SESSION['id'])) {
			echo ($this->response(404, "not rights"));
			return;
		}
		if (!isset($_POST['song_name']) || empty($_POST['song_name'])){
			echo ($this->response(403, "bad arguments"));
			return;
		}
		if (!isset($_POST['song_id']) || empty($_POST['song_id'])){
			echo ($this->response(403, "bad arguments"));
			return;
		}
		if (!isset($_POST['song_img']) || empty($_POST['song_img'])){
			echo ($this->response(403, "bad arguments"));
			return;
		}
		if (!isset($_POST['room_id']) || empty($_POST['room_id'])){
			echo ($this->response(403, "bad arguments"));
			return;
		}
		$song_name = htmlspecialchars($_POST['song_name']);
		$song_id = htmlspecialchars($_POST['song_id']);
		$song_img = htmlspecialchars($_POST['song_img']);
		$room_id = htmlspecialchars($_POST['room_id']);

		$room_exist = $this->bdd->AddRequest("SELECT * FROM room WHERE id = ?", [$room_id], 3);
		if ($room_exist == 0) {
			echo ($this->response(404, "not rights"));
			return;
		}
		$pos = 0;
		$datas = $this->bdd->AddRequest("SELECT * FROM playlist WHERE room_id = ? ORDER BY pos ASC", [$room_id], 1);
		if (!empty($datas))
			$pos = intval($datas[count($datas) - 1]["pos"]) + 1;
		$this->bdd->AddRequest("INSERT INTO playlist(room_id, song_id, song_name, song_img, pos) VALUES(?, ?, ?, ?, ?)", [$room_id, $song_id, html_entity_decode(htmlspecialchars_decode($song_name)), $song_img, $pos], 1);
		echo ($this->response(200, "inserted"));
	}

	private function response(int $status, string $response) : string {

		return (json_encode([
			"status" => $status,
			"response" => $response
		]));
	}
}

?>
<?php

class CheckRooms {

	private $bdd;

	private $req;

	const YOUTUBE_KEY = "TOKEN";

	const YOUTUBE_ENPOINT = "https://www.googleapis.com/youtube/v3/search";

	public function __Construct() {

		$e = new Errors();
		$this->bdd = new Bdd($e);
	}

	public function dispatch() {

		if (!isset($_SESSION['id']) || empty($_SESSION['id'])) {
			echo ($this->response(404, "not rights"));
			return;
		}
		if (!isset($_POST['tocreate']) || empty($_POST['tocreate'])){
			echo ($this->response(403, "bad arguments"));
			return;
		}
		if (!isset($_POST['name']) || empty($_POST['name'])){
			echo ($this->response(403, "bad arguments"));
			return;
		}
		if (!isset($_POST['img']) || empty($_POST['img'])) {
			echo ($this->response(403, "bad arguments"));
			return;
		}
		if (!isset($_POST['id']) || empty($_POST['id'])) {
			echo ($this->response(403, "bad arguments"));
			return;
		}

		$tocreate = htmlspecialchars($_POST['tocreate']);
		$name = htmlspecialchars($_POST['name']);
		$img = htmlspecialchars($_POST['img']);
		$id = htmlspecialchars($_POST['id']);

		$members = json_encode([
			$_SESSION["id"] => [
				"vote_playlist" => 1,
				"edit_playlist" => 1,
				"sound_control" => 1
			]
		]);
		$rest = $this->bdd->AddRequest("INSERT INTO room(leader, img, name, begin_m, members, public, h_debut, h_fin, loc, inv) VALUES(?, ?, ?, ?, ?, ?, ?, ?, ?, ?)", [$_SESSION['id'], $img, $name, $id, $members, 1, 0, 0, 0, json_encode([])], 3);
		if ($rest == 0) {
			echo ($this->response(500, "error room not created"));
			return;
		}
		$datas = $this->bdd->AddRequest("SELECT * FROM room WHERE leader = ? ORDER BY id DESC", [$_SESSION['id']], 2);
		echo ($this->response(200, $datas['id']));
	}

	public function create() {

		if (!isset($_SESSION['id']) || empty($_SESSION['id'])) {
			echo ($this->response(404, "not rights"));
			return;
		}
		if (!isset($_POST['room_name']) || empty($_POST['room_name'])) {
			echo ($this->response(403, "bad arguments"));
			return;
		}
		if (!isset($_POST['room_type']) || empty($_POST['room_type'])) {
			echo ($this->response(403, "bad arguments"));
			return;
		}
		if (!isset($_POST['room_music']) || empty($_POST['room_music'])) {
			echo ($this->response(403, "bad arguments"));
			return;
		}
		if (empty($_FILES)) {
			echo ($this->response(403, "bad arguments"));
			return;
		}

		$name = htmlspecialchars($_POST['room_name']);
		$type = htmlspecialchars($_POST['room_type']);
		$music = htmlspecialchars($_POST['room_music']);
		$info = pathinfo($_FILES['files']['name']);
		$accept_ext = [
			"image/jpeg",
			"image/png"
		];

		$this->req = new Request("GET", [
			"key" => self::YOUTUBE_KEY,
			"q" => $music,
			"part" => "snippet",
			"maxResults" => 1,
			"type" => "video",
			"format" => 5 
		]);
		$response = json_decode($this->req->send_request(self::YOUTUBE_ENPOINT, "none"), true);
		if (!isset($response["items"])) {
			echo ($this->response(406, "bad musique"));
			return;
		}
		$music = $response["items"][0]["id"]["videoId"];

		if (!in_array(mime_content_type($_FILES['files']['tmp_name']), $accept_ext)) {
			echo ($this->response(406, "bad file"));
			return;
		}
		if ($type == "public")
			$types = true;
		else
			$types = false;
		$ext = $info['extension'];
		$newname = uniqid()."_room.".$ext;
		$target = "user_image/" . $newname;
		move_uploaded_file($_FILES['files']['tmp_name'], $target);
		$rest = $this->bdd->AddRequest("INSERT INTO room(leader, img, name, begin_m, members, public, h_debut, h_fin, loc, inv) VALUES(?, ?, ?, ?, ?, ?, ?, ?, ?, ?)", [
				$_SESSION['id'],
				$target,
				$name,
				$music,
				json_encode([
					$_SESSION['id'] => [
						"vote_playlist" => 1,
						"edit_playlist" => 1,
						"sound_control" => 1
					]
				]),
				$types,
				0,
				0,
				0,
				json_encode([])
			], 3);
		if ($rest == 0) {
			echo ($this->response(406, "room not created"));
			return;
		}
		$datas = $this->bdd->AddRequest("SELECT * FROM room WHERE leader = ? ORDER BY id DESC", [$_SESSION['id']], 2);
		echo ($this->response(200, "room created", $datas['id']));
	}

	private function response(int $status, string $response, string $room_id = "") : string {

		return (json_encode([
			"status" => $status,
			"response" => $response,
			"room_id" => $room_id
		]));
	}
}

?>
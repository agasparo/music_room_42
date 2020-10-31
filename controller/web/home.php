<?php

class Home {

	const TEMPLATE_BASE = "template/home/index.html";
	const TEMPLATE_CONN = "template/home/connection.html";
	const TEMPLATE_AB = "template/home/abonnement.html";
	const TEMPLATE_ROOMS = "template/home/room.html";
	const TEMPLATE_NEW_ROOM = "template/home/new_room.html";

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

	public function show() {

		$array = $this->allvars->GetFor("home/index", 0);
		$connected = $this->allvars->GetFor("home/connection", 0);

		if (!$this->users->isConnected()) {
			$array["connection"] = $this->render->run($connected, self::TEMPLATE_CONN); 
		} else {
			$ab = $this->bdd->AddRequest("SELECT * FROM abonnement WHERE id_user = ?", [$_SESSION['id']], 2);
			$show = $this->bdd->AddRequest("SELECT * FROM connect WHERE id_user = ?", [$_SESSION['id']], 3);
			if (isset($ab['type']) && $ab['type'] == 0 && $show == 1) {
				$array['connection'] = $this->render->run([], self::TEMPLATE_AB);
				$this->bdd->AddRequest("DELETE FROM connect WHERE id_user = ?", [$_SESSION['id']], 3);
			} else {
				$array["connection"] = "";
			}
		}

		$rooms = $this->bdd->AddRequest("SELECT * FROM room WHERE public = ?", [1], 1);
		$rooms_html = "";
		foreach ($rooms as $key => $value) {
			$rooms_html .= $this->render->run([
				"room_name" => $value["name"],
				"room_img" => $value["img"],
				"room_id" => $value["id"]
			], self::TEMPLATE_ROOMS);
		}
		$array["rooms_data"] = $rooms_html . $this->getInvitedRooms();
		if (isset($_SESSION['id']) && !empty($_SESSION['id']))
			$array["id_user"] = $_SESSION['id'];
		else
			$array["id_user"] = 0;
		$array["new_room"] = $this->render->run([], self::TEMPLATE_NEW_ROOM);
		echo $this->render->run($array, self::TEMPLATE_BASE);
	}

	private function getInvitedRooms() : string {

		if (!$this->users->isConnected())
			return ("");

		$rooms = $this->bdd->AddRequest("SELECT * FROM room WHERE public = ?", [0], 1);

		$rooms_html = "";
		foreach ($rooms as $key => $value) {

			if ($this->isInvited($value["inv"])) {
				$rooms_html .= $this->render->run([
					"room_name" => $value["name"],
					"room_img" => $value["img"],
					"room_id" => $value["id"]
				], self::TEMPLATE_ROOMS);
			}
		}
		return ($rooms_html);
	}

	private function isInvited($data) : bool {

		$data = json_decode($data, true);
		foreach ($data as $key => $value) {
			
			if ($key == $_SESSION['id'])
				return (true);
		}
		return (false);
	}

	public function deco() {

		$_SESSION = array();
		session_destroy();
		header('location:/music_room/');
	}
}

?>
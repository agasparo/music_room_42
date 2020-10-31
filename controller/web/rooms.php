<?php
date_default_timezone_set('Europe/Paris');

class Rooms {

	const TEMPLATE_INDEX = "template/rooms/index.html";
	const TEMPLATE_MEMBERS = "template/rooms/members.html";
	const TEMPLATE_PARAMS = "template/rooms/params.html";

	private $render;
	private $allvars;
	private $bdd;
	private $users;
	private $device;

	public function __Construct() {

		$e = new Errors();
		$this->render = new Render($e);
		$this->allvars = new Globale($e);
		$this->bdd = new Bdd($e);
		$this->device = new Device();
	}

	public function show($id) {

		if (!isset($_SESSION['id']) || empty($_SESSION['id']))
			header("Location: /music_room/");

		$rooms_data = $this->bdd->AddRequest("SELECT * FROM room WHERE id = ?", [$id], 2);
		if (empty($rooms_data))
			header("Location: /music_room/");

		$members = json_decode($rooms_data["members"], true);
		$leader = $rooms_data['leader'];
		if ($leader == 0)
			$leader = $_SESSION['id'];
		$admin = false;
		if ($leader == $_SESSION['id'])
			$admin = true;
		if (strlen($rooms_data['loc']) > 10) {
			$loc_data = explode(" ", $rooms_data['loc']);
			$userLoc = $this->device->geolocalisation();
			if ($userLoc['status'] != "success")
				header("Location: /music_room/");
			if ($this->getDistance($loc_data[1], $loc_data[0], $userLoc['lat'], $userLoc['lon']) > 100)
				header("Location: /music_room/");
		}
		if ($rooms_data['h_debut'] > 0 && $rooms_data['h_fin'] > 0) {

			$now = new DateTime(date('H:i:s'));
			$cmp_max = new DateTime(date($rooms_data['h_fin'] . ':0:0'));
			$cmp_min = new DateTime(date($rooms_data['h_debut'] . ':0:0'));
			if ($now < $cmp_min || $now > $cmp_max)
				header("Location: /music_room/");
		}
		if ($rooms_data["public"]) {

			if (!$this->check_members($members)) {
				if (!($this->add_member($_SESSION['id'], $members, $id, $admin, $leader)))
					header("Location: /music_room/");
			}
		} else {

			if (!$this->check_members($members)) {
				if (!$this->check_inv(json_decode($rooms_data["inv"], true), $_SESSION['id'])) {
					header("Location: /music_room/");
				} else {
					$this->goToRoom($members, json_decode($rooms_data["inv"], true), $id);
				}
			}
		}
	
		$array = $this->allvars->GetFor("rooms/index", 0);
		$array["gol"] = $rooms_data['begin_m'];
		$array["room_datas"] = $id;
		$array["id_user"] = $_SESSION["id"];
		$array["parameters"] = $this->render->run([
			"room_name" => $rooms_data["name"],
			"world_views" => $rooms_data["public"] ? null : "checked",
			"room_h_d" => $rooms_data["h_debut"] == 0 ? null : $rooms_data["h_debut"],
			"room_d_f" => $rooms_data["h_fin"] == 0 ? null : $rooms_data["h_fin"],
			"room_loc" => $rooms_data["loc"] == null ? null : $rooms_data["loc"],
			"disabled" => $admin ? "" : "disabled",
			"rooms_details_p" => $this->room_status($rooms_data),
		], self::TEMPLATE_PARAMS);
		$array["membres"] = $this->show_room_members($members, $admin, $rooms_data['leader']);
		echo $this->render->run($array, self::TEMPLATE_INDEX);
	}

	private function getDistance(float $lat1, float $lon1, float $lat2, float $lon2) {

		if (($lat1 == $lat2) && ($lon1 == $lon2))
    		return 0;
    	$theta = $lon1 - $lon2;
    	$dist = sin(deg2rad($lat1)) * sin(deg2rad($lat2)) +  cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * cos(deg2rad($theta));
    	$dist = acos($dist);
    	$dist = rad2deg($dist);
    	$miles = $dist * 60 * 1.1515;
    	return ($miles * 1.609344);
	}

	private function goToRoom(array $members, array $inv, int $id) {

		foreach ($inv as $key => $value) {
			
			if ($key == $_SESSION['id']) {
				unset($inv[$key]);
				$members[$_SESSION['id']] = [
					"vote_playlist" => 1,
					"edit_playlist" => 0,
					"sound_control" => 0
				];
			}
		}
		$this->bdd->AddRequest("UPDATE room SET members = ?, inv = ? WHERE id = ?", [json_encode($members), json_encode($inv), $id], 3);
	}

	private function check_inv(array $inv, int $id) : bool {

		foreach ($inv as $key => $value) {
			
			if ($key == $id)
				return (true);
		}
		return (false);
	}

	public function updateUsers() {

		if (!isset($_SESSION['id']) || empty($_SESSION['id'])) {
			echo ($this->response(404, "not rights"));
			return;
		}
		if (!isset($_POST['id_room']) || empty($_POST['id_room'])) {
			echo ($this->response(403, "bad arguments"));
			return;
		}
		$id_room = htmlspecialchars($_POST['id_room']);
		$rooms_data = $this->bdd->AddRequest("SELECT * FROM room WHERE id = ?", [$id_room], 2);
		$admin = false;
		if ($rooms_data['leader'] == $_SESSION['id'])
			$admin = true;
		echo $this->response(200, $this->show_room_members(json_decode($rooms_data["members"], true), $admin, $rooms_data['leader']));
	}

	public function leaveUser() {

		if (!isset($_SESSION['id']) || empty($_SESSION['id'])) {
			echo ($this->response(404, "not rights"));
			return;
		}
		if (!isset($_POST['id_room']) || empty($_POST['id_room'])) {
			echo ($this->response(403, "bad arguments"));
			return;
		}
		if (!isset($_POST['pos_user'])) {
			echo ($this->response(403, "bad arguments"));
			return;
		}
		$id_room = htmlspecialchars($_POST['id_room']);
		$pos_user = htmlspecialchars($_POST['pos_user']);

		$rooms_data = $this->bdd->AddRequest("SELECT * FROM room WHERE id = ? AND leader = ?", [$id_room, $_SESSION['id']], 2);
		if (empty($rooms_data)) {
			echo ($this->response(403, "bad arguments"));
			return;
		}
		$users = json_decode($rooms_data["members"], true);
		$i = 0;
		foreach ($users as $key => $value) {
			
			if ($i == $pos_user)
				unset($users[$key]);
			$i++;
		}
		$this->bdd->AddRequest("UPDATE room SET members = ? WHERE id = ? AND leader = ?", [json_encode($users), $id_room, $_SESSION['id']], 2);
		echo ($this->response(200, "ok"));
	}

	public function Invite() {

		if (!isset($_SESSION['id']) || empty($_SESSION['id'])) {
			echo ($this->response(404, "not rights"));
			return;
		}

		if (!isset($_POST['room_id']) || empty($_POST['room_id'])) {
			echo ($this->response(403, "bad arguments"));
			return;
		}

		if (!isset($_POST['user_name_invite']) || empty($_POST['user_name_invite'])) {
			echo ($this->response(403, "bad arguments"));
			return;
		}

		$id_room = htmlspecialchars($_POST['room_id']);
		$user_name_invite = explode('#', htmlspecialchars($_POST['user_name_invite']));

		if (!isset($user_name_invite[1])) {
			echo ($this->response(403, "bad arguments"));
			return;
		}
		$new_user = $this->bdd->AddRequest("SELECT * FROM users WHERE nom = ? AND prenom = ?", [$user_name_invite[0], $user_name_invite[1]], 2);
		if (empty($new_user)) {
			echo ($this->response(403, "utilisateur non trouve"));
			return;
		}
		$rooms_data = $this->bdd->AddRequest("SELECT * FROM room WHERE id = ? AND leader = ?", [$id_room, $_SESSION['id']], 2);
		if (empty($rooms_data)) {
			echo ($this->response(403, "not rights"));
			return;
		}
		$users = json_decode($rooms_data["inv"], true);
		$users[$new_user['id']] = [
			"vote_playlist" => 1,
			"edit_playlist" => 0,
			"sound_control" => 0,
		];
		$this->bdd->AddRequest("UPDATE room SET inv = ? WHERE id = ?", [json_encode($users), $id_room], 3);
		echo ($this->response(200, "utilisateur invite"));
	}

	public function RemoveUser() {

		if (!isset($_SESSION['id']) || empty($_SESSION['id'])) {
			echo ($this->response(404, "not rights"));
			return;
		}
		if (!isset($_POST['id_room']) || empty($_POST['id_room'])) {
			echo ($this->response(403, "bad arguments"));
			return;
		}
		$id_room = htmlspecialchars($_POST['id_room']);
		$rooms_data = $this->bdd->AddRequest("SELECT * FROM room WHERE id = ?", [$id_room], 2);
		if (empty($rooms_data)) {
			echo ($this->response(403, "bad arguments"));
			return;
		}
		$users = json_decode($rooms_data["members"], true);
		$leader = $rooms_data['leader'];
		foreach ($users as $key => $value) {
			
			if ($_SESSION['id'] == $key) {
				unset($users[$key]);
				if ($key == $leader) {
					if (isset($users[0]))
						$leader = $users[0];
					else
						$leader = 0;
				}
			}
			$i++;
		}
		if (empty($users))
			$this->bdd->AddRequest("DELETE FROM room WHERE id = ?", [$id_room], 3);
		else
			$this->bdd->AddRequest("UPDATE room SET members = ?, leader = ? WHERE id = ?", [json_encode($users), $leader, $id_room], 3);
		echo ($this->response(200, "ok"));
	}

	public function IsInRoom() {

		if (!isset($_SESSION['id']) || empty($_SESSION['id'])) {
			echo ($this->response(404, "not rights"));
			return;
		}
		if (!isset($_POST['id_room']) || empty($_POST['id_room'])) {
			echo ($this->response(403, "bad arguments"));
			return;
		}
		$id_room = htmlspecialchars($_POST['id_room']);
		$rooms_data = $this->bdd->AddRequest("SELECT * FROM room WHERE id = ?", [$id_room], 2);
		$users = json_decode($rooms_data["members"], true);
		$in = 0;
		foreach ($users as $key => $value) {
			
			if ($_SESSION['id'] == $key)
				$in = 1;
		}
		if ($in == 1) {
			echo ($this->response(200, "is in"));
			return;
		}
		echo ($this->response(200, "leave"));
	}

	public function room_change() {

		if (!isset($_SESSION['id']) || empty($_SESSION['id'])) {
			echo ($this->response(404, "not rights"));
			return;
		}

		if (!isset($_POST['id_room']) || empty($_POST['id_room'])) {
			echo ($this->response(403, "bad arguments"));
			return;
		}

		if (!isset($_POST['value'])) {
			echo ($this->response(403, "bad arguments"));
			return;
		}

		if (!isset($_POST['name']) || empty($_POST['name'])) {
			echo ($this->response(403, "bad arguments"));
			return;
		}

		$id_room = htmlspecialchars($_POST['id_room']);
		$value = htmlspecialchars($_POST['value']);
		$name = htmlspecialchars($_POST['name']);

		$rooms_data = $this->bdd->AddRequest("SELECT * FROM room WHERE id = ? AND leader = ?", [$id_room, $_SESSION['id']], 2);
		if (empty($rooms_data)) {
			echo ($this->response(403, "bad arguments"));
			return;
		}
		if ($name == "public") {
			$v = $value == "true" ? 1 : 0;
			$this->bdd->AddRequest("UPDATE room SET public = ? WHERE id = ?", [$v, $id_room], 3);
		}
		if ($name == "h_debut") {
			$v = intval($value);
			$this->bdd->AddRequest("UPDATE room SET h_debut = ? WHERE id = ?", [$v, $id_room], 3);
		}
		if ($name == "h_fin") {
			$v = intval($value);
			$this->bdd->AddRequest("UPDATE room SET h_fin = ? WHERE id = ?", [$v, $id_room], 3);
		}
		$rooms_data[$name] = $v;
		echo $this->response(200, $this->room_status($rooms_data));
	}

	public function getLoc() {

		if (!isset($_SESSION['id']) || empty($_SESSION['id'])) {
			echo ($this->response(404, "not rights"));
			return;
		}

		if (!isset($_POST['id_room']) || empty($_POST['id_room'])) {
			echo ($this->response(403, "bad arguments"));
			return;
		}

		$id_room = htmlspecialchars($_POST['id_room']);
		$rooms_data = $this->bdd->AddRequest("SELECT * FROM room WHERE id = ? AND leader = ?", [$id_room, $_SESSION['id']], 2);
		if (empty($rooms_data)) {
			echo ($this->response(403, "bad arguments"));
			return;
		}

		if (isset($_POST['rm']) && !empty($_POST['rm']) && intval($_POST['rm']) == 1) {
			$this->bdd->AddRequest("UPDATE room SET loc = ? WHERE id = ?", ["", $id_room], 3);
			$rooms_data["loc"] = "";
			echo ($this->response(200, $this->room_status($rooms_data)));
			return;
		}

		$content = $this->device->geolocalisation();
		if ($content["status"] == "success") {
			$v = $content["lon"] . " " . $content["lat"] . " " . $content["city"] . "(". $content["country"] . ")";
			$this->bdd->AddRequest("UPDATE room SET loc = ? WHERE id = ?", [$v, $id_room], 3);
			$rooms_data["loc"] = $v;
		}
		echo $this->response(200, $this->room_status($rooms_data), $v);
	}

	private function room_status(array $rooms_data) : string {

		$str = "";
		$rooms_data["public"] ? $str .= "Tout le monde peut acceder a votre room" : $str .= "Seul les personnes invites peuvent acceder a votre room";
		$rooms_data["h_debut"] != 0 ? $str .= " entre " . $rooms_data["h_debut"] . " h et " . $rooms_data["h_fin"] . " h" : $str .= "";
		($rooms_data["loc"] != null && $rooms_data["loc"] != "") ? $str .= " et a max 200 metres de vous" : $str .= "";
		return ($str);
	}

	private function show_room_members(array $members, bool $is_admin, int $leader) : string {

		$allusers = '<table class="table table-hover"><tr class="table-active"><td>utilisateur</td><td>peut voter</td><td>peut modifier playlist</td><td>Sound Control</td>';
		if ($is_admin)
			$allusers .= "<td>supprimer</td>";
		$allusers .= "</tr>";
		$index = 0;

		foreach ($members as $key => $value) {
			
			$data_user = [];
			$member_data = $this->bdd->AddRequest("SELECT * FROM users WHERE id = ?", [$key], 2);
			if (!empty($member_data)) {
				$data_user["pseudo"] = $member_data["prenom"] . " " . $member_data["nom"];
				$data_user["have_w"] = $value["vote_playlist"] == 0 ? "badge-danger" : "badge-success";
				$data_user["w_c"] = $value["vote_playlist"] == 0 ? '<i class="fa fa-times" aria-hidden="true"></i>' : '<i class="fa fa-check" aria-hidden="true"></i>';
				$data_user["have_w1"] = $value["edit_playlist"] == 0 ? "badge-danger" : "badge-success";
				$data_user["w_c1"] = $value["edit_playlist"] == 0 ? '<i class="fa fa-times" aria-hidden="true"></i>' : '<i class="fa fa-check" aria-hidden="true"></i>';
				$data_user["have_w2"] = $value["sound_control"] == 0 ? "badge-danger" : "badge-success";
				$data_user["w_c2"] = $value["sound_control"] == 0 ? '<i class="fa fa-times" aria-hidden="true"></i>' : '<i class="fa fa-check" aria-hidden="true"></i>';
				$data_user["btn_admin"] = $is_admin && $key != $leader ? '<td><span class="badge badge-pill badge-dark clickable leave_room" data-pos="'.$index.'"><i class="fa fa-times" aria-hidden="true"></i></span></td>' : "";
				$data_user["modify_rigths_vote"] = $is_admin ? "modify_rights clickable rights_change" : "rights_change";
				$data_user["modify_rigths_playlist"] = $is_admin ? "modify_playlist clickable playlist_change" : "playlist_change";
				$data_user["modify_rigths_sounds"] = $is_admin ? "modify_sound clickable sound_change" : "sound_change";
				$data_user["uniq_id"] = $index;
				$allusers .= $this->render->run($data_user, self::TEMPLATE_MEMBERS);
				$index++;
			}
		}
		$allusers .= "</table>";
		return ($allusers);
	}

	private function add_member(int $id, array &$members, int $room_id, bool $admin, int $leader) : bool {

		if (empty($members) || $admin) {
			$members[$id] = [
				"vote_playlist" => 1,
				"edit_playlist" => 1,
				"sound_control" => 1
			];
		} else {
			$members[$id] = [
				"vote_playlist" => 1,
				"edit_playlist" => 0,
				"sound_control" => 0
			];
		}
		$res = $this->bdd->AddRequest("UPDATE room SET members = ?, leader = ? WHERE id = ?", [json_encode($members), $leader, $room_id], 3);
		if ($res == 0)
			return (false);
		return (true);
	}

	private function check_members(array $membres) : bool {

		foreach ($membres as $key => $value) {
			
			if ($key == $_SESSION['id'])
				return (true);
		}
		return (false);
	}

	private function response(int $status, string $response, string $text = "") : string {

		return (json_encode([
			"status" => $status,
			"response" => $response,
			"text" => $text
		]));
	}
}

?>
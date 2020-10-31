<?php

Class UsersShow {

	private $render;
	private $allvars;
	private $bdd;
	private $users;

	const TEMPLATE_BASE = "template/user/index.html";
	const TEMPLATE_USERINFOS = "template/user/userinfos.html";
	const TEMPLATE_USERPARAMS = "template/user/user_params.html";
	const TEMPLATE_USERIMG = "template/user/user_img.html";
	const TEMPLATE_MUSIC = "template/user/music.html";
	const TEMPLATE_MUSIC_PANEL = "template/user/panel_music.html";

	public function __Construct() {

		$e = new Errors();
		$this->render = new Render($e);
		$this->allvars = new Globale($e);
		$this->bdd = new Bdd($e);
		$this->users = new Users();
	}

	public function show($id) {

		if (!$this->users->isConnected()) {
			header('location:/music_room/');
		}
		if (!is_numeric($id)) {
			header('location:/music_room/');
		}
		$this->users->GetDataBaseUsers($id, $this->bdd);
		$userinfos = $this->users->getUserData();
		if (empty($userinfos)) {
			header('location:/music_room/');
		}
		$array = $this->allvars->GetFor("user/index", 0);
		$array["img_user"] = preg_match("#http#", $userinfos["picture"]) ? $userinfos["picture"] : "../" . $userinfos["picture"];
		$array["user_full_name"] = $userinfos["name"] . " " . $userinfos["surname"];
		$ct = $this->generateMusic($this->bdd->AddRequest("SELECT * FROM user_music WHERE id_user = ?", [$id], 1));
		$array["music_user"] = $ct[0];
		$array["music_panel"] = $ct[1];
		$permissions = $this->bdd->AddRequest("SELECT * FROM perms WHERE id_user = ?", [$id], 2);
		if (empty($permissions))
			$permissions = 3;
		else
			$permissions = intval($permissions["perm"]);
		$sp = $permissions;
		$friends = $this->bdd->AddRequest("SELECT * FROM friends WHERE id_user = ? AND id_user_1 = ?", [$id, $_SESSION['id']], 2);
		if (empty($friends))
			$friends = $this->bdd->AddRequest("SELECT * FROM friends WHERE id_user = ? AND id_user_1 = ?", [$_SESSION['id'], $id], 2);
		if (!empty($friends) && $permissions == 1) {
			$permissions = 3;
		}

		$deezer_co = $this->bdd->AddRequest("SELECT * FROM other_count WHERE id_user = ? AND api = ?", [$id, 3], 3);
		$classdeezer = "icon_user";
		if ($deezer_co == 1)
			$classdeezer = "icon_user_attach";

		$google_co = $this->bdd->AddRequest("SELECT * FROM other_count WHERE id_user = ? AND api = ?", [$id, 1], 3);
		$classgoogle = "icon_user";
		if ($google_co == 1)
			$classgoogle = "icon_user_attach";

		$array["change_p"] = ($id == $_SESSION['id']) ? 'id="change_picture"' : "";
		$array["userinfos"] = $this->render->run([
			"nom" => ($permissions == 3 || $_SESSION['id'] == $id) ? $userinfos["name"] : "non visible",
			"prenom" => ($permissions == 3 || $_SESSION['id'] == $id) ? $userinfos["surname"] : "non visible",
			"mail" => ($permissions == 3 || $_SESSION['id'] == $id) ? $userinfos["mail"] : "non visible",
			"disabled" => $_SESSION['id'] == $id ? "" : "disabled"
		], self::TEMPLATE_USERINFOS);
		$permissions = $sp;
		$array["user_params"] = $this->render->run([
			"save_params" => $_SESSION['id'] == $id ? '<button class="btn btn-primary prof" id="save_data_show">Sauvegarder</button>' : "",
			"change" => $_SESSION['id'] == $id ? "" : 'disabled',
			"value_perms_p" => $permissions == 3 ? "selected" : "",
			"value_perms_a" => $permissions == 1 ? "selected" : "",
			"value_perms_pr" => $permissions == 2 ? "selected" : "",
			"img_google" => $google_co > 0 ? json_decode($this->bdd->AddRequest("SELECT * FROM other_count WHERE id_user = ? AND api = ?", [$id, 1], 2)["count"], true)["img"] : "../assets/images/user_banner.jpg",
			"img_deezer" => $deezer_co > 0 ? json_decode($this->bdd->AddRequest("SELECT * FROM other_count WHERE id_user = ? AND api = ?", [$id, 3], 2)["count"], true)["img"] : "../assets/images/user_banner.jpg",
		], self::TEMPLATE_USERPARAMS);
		$array["user_img"] = $this->render->run([
			"deezer" => ($_SESSION['id'] == $id && $deezer_co == 0) ? '<img src="../assets/images/logo_deezer.png" class="co_logo" id="deezer_connexion">' : '<font class="'.$classdeezer.'">deezer</font>',
			"google" => ($_SESSION['id'] == $id && $google_co == 0) ? '<img src="../assets/images/logo_google.png" class="co_logo" id="google_connexion">' : '<font class="'.$classgoogle.'">google</font>',
			"img_google" => $google_co > 0 ? json_decode($this->bdd->AddRequest("SELECT * FROM other_count WHERE id_user = ? AND api = ?", [$id, 1], 2)["count"], true)["img"] : "../assets/images/user_banner.jpg",
			"img_deezer" => $deezer_co > 0 ? json_decode($this->bdd->AddRequest("SELECT * FROM other_count WHERE id_user = ? AND api = ?", [$id, 3], 2)["count"], true)["img"] : "../assets/images/user_banner.jpg",
		], self::TEMPLATE_USERIMG);
		$array["add_music"] = $_SESSION['id'] == $id ? '<input type="text" id="music_pref_add" placeholder="votre musique" class="form-control prof_in"><button class="btn btn-primary prof" id="add_music_btn">Ajouter</button>' : "";
		$btn = !empty($friends) ? '<i class="fa fa-user-times follow" aria-hidden="true" id="add_friend"></i>' : '<i class="fa fa-user-plus follow" aria-hidden="true" id="add_friend"></i>'; 
		$array["add_btn"] = $_SESSION['id'] == $id ? '' : $btn;
		$array["user_id"] = $id;

		$userpayed = $this->bdd->AddRequest("SELECT * FROM abonnement WHERE id_user = ? AND type = ?", [$id, 1], 2);
		$payed = 0;
		if (!empty($userpayed))
			$payed = 1;
		$array["premium"] = $payed > 0 ? 'Premium' : "";
		echo $this->render->run($array, self::TEMPLATE_BASE);
	}

	private function generateMusic($data) : array {

		$content = "";
		$panel = "";
		$i = 1;

		foreach ($data as $key => $value) {
			
			$content .= $this->render->run([
				"id" => $value["id_music"],
				"name" => $this->rmUselessPart($value["titre_music"]),
			], self::TEMPLATE_MUSIC);
			$panel .= $this->render->run([
				"id" => $i,
				"img" => $value["img_music"]
			], self::TEMPLATE_MUSIC_PANEL);
			$i++;
		}

		while ($i < 9) {
			$panel .= $this->render->run([
				"id" => $i,
				"img" => "../assets/images/user_banner.jpg"
			], self::TEMPLATE_MUSIC_PANEL);
			$i++;
		}
		return ([$content, $panel]);
	}

	private function rmUselessPart(string $name) : string {

		$name = html_entity_decode($name);
		if (($pos = strpos($name, "(")) !== false)
			return (substr($name, 0, $pos));
		if (($pos = strpos($name, "[")) !== false)
			return (substr($name, 0, $pos));
		return ($name);
	}

	public function addMusic() {

		if (!$this->users->isConnected()) {
			echo ($this->response(404, "not rights"));
			return;
		}
		if (!isset($_POST['id']) || empty($_POST['id'])) {
			echo ($this->response(403, "bad arguments"));
			return;
		}
		if (!isset($_POST['title']) || empty($_POST['title'])) {
			echo ($this->response(403, "bad arguments"));
			return;
		}
		if (!isset($_POST['img']) || empty($_POST['img'])) {
			echo ($this->response(403, "bad arguments"));
			return;
		}
		$id = htmlspecialchars($_POST['id']);
		$title = htmlspecialchars($_POST['title']);
		$img = htmlspecialchars($_POST['img']);

		$title = $this->rmUselessPart($title);
		$getVals = $this->bdd->AddRequest("SELECT * FROM user_music WHERE id_user = ?", [$_SESSION['id']], 1);
		if (!empty($getVals) && count($getVals) > 7)
			$this->bdd->AddRequest("DELETE FROM user_music WHERE id_user = ? AND id = ?", [$_SESSION['id'], $getVals[rand(1, 8)]["id"]], 3);
		$this->bdd->AddRequest("INSERT INTO user_music(id_user, titre_music, id_music, img_music) VALUES(?, ?, ?, ?)", [$_SESSION['id'], $title, $id, $img], 3);
		$resp = $this->bdd->AddRequest("SELECT * FROM user_music WHERE id_user = ? ORDER BY id DESC", [$_SESSION['id']], 2);
		$getVals = $this->bdd->AddRequest("SELECT * FROM user_music WHERE id_user = ?", [$_SESSION['id']], 1);
		echo ($this->response(200, "added", $this->getIndex($resp, $getVals)));
	}

	private function getIndex($new, $olds) {

		$id = $new["id"];
		$index = 1;

		foreach ($olds as $key => $value) {
			if ($value["id"] == $id)
				return ($index);
			$index++;
		}
		return ("");
	}

	public function ModifyPerms() {

		if (!$this->users->isConnected()) {
			echo ($this->response(404, "not rights"));
			return;
		}
		if (!isset($_POST['new_perm']) || empty($_POST['new_perm'])) {
			echo ($this->response(403, "bad arguments"));
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

		$name = htmlspecialchars($_POST['name']);
		$surname = htmlspecialchars($_POST['surname']);
		$new_perm = htmlspecialchars($_POST['new_perm']);

		$this->bdd->AddRequest("UPDATE users SET prenom = ?, nom = ? WHERE id = ?", [$name, $surname, $_SESSION['id']], 3);

		$rc = $this->bdd->AddRequest("SELECT * FROM perms WHERE id_user = ?", [$_SESSION['id']], 3);
		if ($rc == 1) {
			$this->bdd->AddRequest("UPDATE perms set perm = ? WHERE id_user = ?", [intval($new_perm), $_SESSION['id']], 3);
		} else {
			$this->bdd->AddRequest("INSERT INTO perms(id_user, perm) VALUES(?, ?)", [$_SESSION['id'], intval($new_perm)], 3);
		}
	} 

	public function AddFriend() {

		if (!$this->users->isConnected()) {
			echo ($this->response(404, "not rights"));
			return;
		}
		if (!isset($_POST['user']) || empty($_POST['user'])) {
			echo ($this->response(403, "bad arguments"));
			return;
		}
		$user = intval(htmlspecialchars($_POST['user']));

		$is_friends = $this->bdd->AddRequest("SELECT * FROM friends WHERE id_user = ? AND id_user_1 = ?", [$user, $_SESSION['id']], 2);
		if (empty($is_friends))
			$is_friends = $this->bdd->AddRequest("SELECT * FROM friends WHERE id_user = ? AND id_user_1 = ?", [$_SESSION['id'], $user], 2);
		if (!empty($is_friends)) {
			$this->bdd->AddRequest("DELETE FROM friends WHERE id = ?", [$is_friends['id']], 3);
			echo ($this->response(200, "good"));
			return;
		}
		$this->bdd->AddRequest("INSERT INTO friends(id_user, id_user_1) VALUES(?, ?)", [$user, $_SESSION['id']], 3);
		echo ($this->response(200, "good"));
	}

	public function UpdateImg() {

		if (!isset($_SESSION['id']) || empty($_SESSION['id'])) {
			echo ($this->response(404, "not rights"));
			return;
		}
		if (empty($_FILES)) {
			echo ($this->response(403, "bad arguments"));
			return;
		}
		$info = pathinfo($_FILES['files']['name']);
		$accept_ext = [
			"image/jpeg",
			"image/png"
		];
		if (!in_array(mime_content_type($_FILES['files']['tmp_name']), $accept_ext)) {
			echo ($this->response(406, "bad file"));
			return;
		}
		$ext = $info['extension'];
		$newname = uniqid()."_room.".$ext;
		$target = "user_image/" . $newname;
		move_uploaded_file($_FILES['files']['tmp_name'], $target);
		$this->bdd->AddRequest("UPDATE users SET img = ? WHERE id = ?", [$target, $_SESSION['id']], 3);
		echo ($this->response(200, "good"));
	}

	private function response(int $status, string $response, string $id = "") : string {

		return (json_encode([
			"status" => $status,
			"response" => $response,
			"id" => $id
		]));
	}
}
?>
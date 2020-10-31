<?php

class Chat {

	const TEMPLATE_MSG = "template/rooms/chat.html";
	const TEMPLATE_GIF = "template/rooms/chat_gif.html";
	const TEMPLATE_PROPOSITION = "template/rooms/chat_prop.html";

	const YOUTUBE_KEY = "TOKEN";

	const YOUTUBE_ENPOINT = "https://www.googleapis.com/youtube/v3/search";

	private $req;

	private $bdd;
	private $render;
	private $controls = [
		"vote",
		"propose"
	];

	public function __Construct() {

		$e = new Errors();
		$this->bdd = new Bdd($e);
		$this->render = new Render($e);
	}

	public function getChat() {

		if (!isset($_SESSION['id']) || empty($_SESSION['id'])) {
			echo ($this->response(404, "not rights"));
			return;
		}
		if (!isset($_POST['room_id']) || empty($_POST['room_id'])){
			echo ($this->response(403, "bad arguments"));
			return;
		}
		$room_id = htmlspecialchars($_POST['room_id']);
		$getChat = $this->bdd->AddRequest("SELECT * FROM chat WHERE room_id = ?", [$room_id], 1);
		$content = "";
		foreach ($getChat as $key => $value) {
			
			$msg = [];
			$template = self::TEMPLATE_MSG;
			if ($value['type'] == "bot")
				$template = self::TEMPLATE_PROPOSITION;
			if ($value['type'] == "gif")
				$template = self::TEMPLATE_GIF;
			if ($value['type'] == "text_unicode")
				$value['message'] = htmlentities(base64_decode($value['message']));
			$msg['user_id'] = $value['id_user'];
			$msg['msg'] = $value['message'];

			$userinfos = $this->bdd->AddRequest("SELECT * FROM users WHERE id = ?", [$value['id_user']], 2);
			if (!empty($userinfos))
				$msg['img_user'] = preg_match("#http#", $userinfos['img']) ? $userinfos['img'] : "../" . $userinfos['img'];
			else
				$msg['img_user'] = $value['env_img'];
			
			$msg['username'] = $value['env_pseudo'];
			$msg['msg_date'] = $value['date_env'];
			$msg['msg'] = $value['message'];
			$content .= $this->render->run($msg, $template);
		}
		echo $this->response(200, $content);
	}

	public function refreshVote() {

		if (!isset($_SESSION['id']) || empty($_SESSION['id'])) {
			echo ($this->response(404, "not rights"));
			return;
		}
		if (!isset($_POST['id_room']) || empty($_POST['id_room'])){
			echo ($this->response(403, "bad arguments"));
			return;
		}
		$id_room = htmlspecialchars($_POST['id_room']);

		$room_details = $this->bdd->AddRequest("SELECT * FROM room WHERE id = ?", [$id_room], 2);
		if (empty($room_details)) {
			echo ($this->response(404, "not rights"));
			return;
		}
		$users = json_decode($room_details['members'], true);
		$is_prop = $this->bdd->AddRequest("SELECT * FROM proposition WHERE id_room = ?", [$id_room], 2);
		if (empty($is_prop)) {
			echo ($this->response(404, "pas de proposition en cours ..."));
			return;
		}
		$getVote = $this->bdd->AddRequest("SELECT * FROM vote WHERE id_room = ? AND id_proposition = ?", [$id_room, $is_prop['id']], 1);
		$voteur = (count($users) - $this->getVoteur($users)) / 2;
		$userinfos = $this->bdd->AddRequest("SELECT * FROM users WHERE id = ?", [$_SESSION['id']], 2);

		if ($this->countVote($voteur, $getVote, 0, 0)) {
			$this->bdd->AddRequest("INSERT INTO chat(room_id, env_pseudo, env_img, date_env, message, type, id_user) VALUES(?, ?, ?, ?, ?, ?, ?)", [$id_room, "bot music_room", "https://support.upwork.com/hc/article_attachments/360040474034/chatbot-data.png", date('Y-m-d'), "musique admise " . $is_prop['name'], "bot", 0], 3);
			echo ($this->response(200, "admise", $is_prop['name']));
		} else {
			$this->bdd->AddRequest("INSERT INTO chat(room_id, env_pseudo, env_img, date_env, message, type, id_user) VALUES(?, ?, ?, ?, ?, ?, ?)", [$id_room, "bot music_room", "https://support.upwork.com/hc/article_attachments/360040474034/chatbot-data.png", date('Y-m-d'), "musique non admise : " . $is_prop['name'], "bot", 0], 3);
			echo ($this->response(200, "non admise"));
		}
		$this->bdd->AddRequest("DELETE FROM vote WHERE id_room = ? AND id_proposition = ?", [$id_room, $is_prop['id']], 3);
		$this->bdd->AddRequest("DELETE FROM proposition WHERE id_room = ?", [$id_room], 3);
	}

	private function countVote(int $min_vote, array $votes, int $yes, int $no) : bool {

		foreach ($votes as $key => $value) {
			
			if ($value['value'] == "oui")
				$yes++;
			else
				$no++;
		}
		if ($yes >= $min_vote && $yes >= $no)
			return (true);
		return (false);
	}

	public function message() {

		if (!isset($_SESSION['id']) || empty($_SESSION['id'])) {
			echo ($this->response(404, "not rights"));
			return;
		}
		if (!isset($_POST['type']) || empty($_POST['type'])){
			echo ($this->response(403, "bad arguments"));
			return;
		}
		if (!isset($_POST['message']) || empty($_POST['message'])){
			echo ($this->response(403, "bad arguments"));
			return;
		}
		if (!isset($_POST['alt'])) {
			echo ($this->response(403, "bad arguments"));
			return;
		}
		if (!isset($_POST['room_id']) || empty($_POST['room_id'])){
			echo ($this->response(403, "bad arguments"));
			return;
		}

		$type = htmlspecialchars($_POST['type']);
		$message = htmlspecialchars($_POST['message']);
		$alt = htmlspecialchars($_POST['alt']);
		$room_id = htmlspecialchars($_POST['room_id']);
		$userinfos = $this->bdd->AddRequest("SELECT * FROM users WHERE id = ?", [$_SESSION['id']], 2);
		$commande = str_replace("/", "", $message);
		$content = explode(" ", $commande);
		
		if (in_array($content[0], $this->controls)) {
			$resp = explode("|", $this->{ $content[0] }($content, $userinfos, $room_id, $type));
			if ($resp[0] == "music admise") {
				unset($content[0]);
				echo $this->response(200, "music admise", $resp[1]);
			} else if ($resp[0] == "proposition admise" || $resp[0] == "vote admis" || $resp[0] == "proposition non admise") {
				echo $this->response(200, $resp[0]);
			} else {
				echo $this->response(400, $resp[0]);
			}
		} else {
			if ($type == "text_unicode")
				$message = base64_encode($message);
			$datas = $this->bdd->AddRequest("INSERT INTO chat(room_id, env_pseudo, env_img, date_env, message, type, id_user) VALUES(?, ?, ?, ?, ?, ?, ?)", [$room_id, $userinfos['prenom'] . " " . $userinfos['nom'], $userinfos['img'], date('Y-m-d'), $message, $type, $_SESSION['id']], 3);
			if ($datas == 0) {
				echo $this->response(400, "msg non envoye");
				return;
			}
			echo $this->response(200, "msg envoye");
		}
	}

	private function vote(array $content, array $userinfos, int $room_id, string $type) : string {

		unset($content[0]);
		$search = implode(" ", $content);

		$room_details = $this->bdd->AddRequest("SELECT * FROM room WHERE id = ?", [$room_id], 2);
		$users = json_decode($room_details['members'], true);
		if (!$this->haveRights($users, $_SESSION['id']))
			return ("not rights");

		$is_prop = $this->bdd->AddRequest("SELECT * FROM proposition WHERE id_room = ?", [$room_id], 2);
		if (!empty($is_prop)) {
			if ($search != "oui" && $search != "non")
				return ("le vote doit etre oui ou non");
			$isvalid = $this->bdd->AddRequest("SELECT * FROM vote WHERE id_voteur = ?", [$_SESSION['id']], 3);
			if ($isvalid > 0)
				return ("vous pouvez voter qu'une seule fois");

			$getVote = $this->bdd->AddRequest("SELECT * FROM vote WHERE id_room = ? AND id_proposition = ?", [$room_id, $is_prop['id']], 1);
			$voteur = (count($users) - $this->getVoteur($users)) / 2;
			$yes = 0;
			$no = 0;
			if ($search == "oui")
				$yes = 1;
			else
				$no = 1;
			if ($this->countVote($voteur, $getVote, $yes, $no)) {
				$this->bdd->AddRequest("INSERT INTO chat(room_id, env_pseudo, env_img, date_env, message, type, id_user) VALUES(?, ?, ?, ?, ?, ?, ?)", [$room_id, "bot music_room", "https://support.upwork.com/hc/article_attachments/360040474034/chatbot-data.png", date('Y-m-d'), "musique admise : " . $is_prop['name'], "bot", 0], 3);
				$supprVote = $this->bdd->AddRequest("DELETE FROM vote WHERE id_room = ?", [$room_id], 3);
				$supprProp = $this->bdd->AddRequest("DELETE FROM proposition WHERE id_room = ?", [$room_id], 3);
				return ("music admise|".$is_prop['name']);
			}

			if ($getVote >= count($users) - 1) {
				$this->bdd->AddRequest("INSERT INTO chat(room_id, env_pseudo, env_img, date_env, message, type, id_user) VALUES(?, ?, ?, ?, ?, ?, ?)", [$room_id, "bot music_room", "https://support.upwork.com/hc/article_attachments/360040474034/chatbot-data.png", date('Y-m-d'), "musique non admise : " . $is_prop['name'], "bot", 0], 3);
				$supprVote = $this->bdd->AddRequest("DELETE FROM vote WHERE id_room = ?", [$room_id], 3);
				$supprProp = $this->bdd->AddRequest("DELETE FROM proposition WHERE id_room = ?", [$room_id], 3);
				return ("proposition non admise");
			}

			$insertvote = $this->bdd->AddRequest("INSERT INTO vote(id_room, id_voteur, id_proposition, value) VALUES(?, ?, ?, ?)", [$room_id, $_SESSION['id'], $is_prop['id'], $search], 3);
			$message = $userinfos['prenom'] . " " . $userinfos['nom'] . "a voté '" . $search . "' pour mettre '" . $is_prop['name'] . "' a la playlist";
			$insertchat = $this->bdd->AddRequest("INSERT INTO chat(room_id, env_pseudo, env_img, date_env, message, type, id_user) VALUES(?, ?, ?, ?, ?, ?, ?)", [$room_id, $userinfos['prenom'] . " " . $userinfos['nom'], $userinfos['img'], date('Y-m-d'), $message, "bot", $_SESSION['id']], 3);
			return ("vote admis");
		}
		return ("pas de proposition en cours ...");
	}

	private function getVoteur(array $users) : int {

		$counter = 1;

		foreach ($users as $key => $value) {
			
			if ($value['vote_playlist'] == 0)
				$counter++;
		}
		return ($counter);
	}

	private function propose(array $content, array $userinfos, int $room_id, string $type) {

		unset($content[0]);
		$search = implode(" ", $content);

		$is_prop = $this->bdd->AddRequest("SELECT * FROM proposition WHERE id_room = ?", [$room_id], 2);
		if (!empty($is_prop))
			return ("une proposition est deja en cours ...");
		if (empty($content))
			return ("une proposition ne peut pas etre vide ...");
		$response = json_decode(file_get_contents("https://api.deezer.com/search/track?q=" . urlencode($search)), true);
		if (!isset($response["data"][0]))
			return ("pas de musique trouvee");
		$this->req = new Request("GET", [
			"key" => self::YOUTUBE_KEY,
			"q" => $response["data"][0]["title"],
			"part" => "snippet",
			"maxResults" => 1,
			"type" => "video",
			"format" => 5 
		]);
		$getVideoData = json_decode($this->req->send_request(self::YOUTUBE_ENPOINT, "none"), true);
		if (!isset($getVideoData["items"])) {;
			return ("pas de musique trouvee");
		}
		$room_details = $this->bdd->AddRequest("SELECT * FROM room WHERE id = ?", [$room_id], 2);
		$rooms_users = json_decode($room_details['members'], true);
		if (count($rooms_users) == 1 || count($rooms_users) == 2) {
			$return = "music admise|".$response["data"][0]["artist"]["name"] . " - " .$response["data"][0]["title"];
		} else {
			$return = "proposition admise";
			$insertProp = $this->bdd->AddRequest("INSERT INTO proposition(id_room, name, img) VALUES(?, ?, ?)", [$room_id, $response["data"][0]["title"], $response["data"][0]["album"]["cover_medium"]], 3);
			if ($insertProp == 0)
				return ("erreur en important la proposition");
			$is_prop = $this->bdd->AddRequest("SELECT * FROM proposition WHERE id_room = ?", [$room_id], 2);
			$insertvote = $this->bdd->AddRequest("INSERT INTO vote(id_room, id_voteur, id_proposition, value) VALUES(?, ?, ?, ?)", [$room_id, $_SESSION['id'], $is_prop['id'], "oui"], 3);
		}
		$message = '
			<i>Proposition : </i><br>
			<div class="player_preview_frame" data-video="'.$getVideoData["items"][0]["id"]["videoId"].'">
				<img src="'.$response["data"][0]["album"]["cover_medium"].'" class="player_preview_frame_img"/>
			</div><br><i>vous pouvez voter oui ou non</i>';
		$insertchat = $this->bdd->AddRequest("INSERT INTO chat(room_id, env_pseudo, env_img, date_env, message, type, id_user) VALUES(?, ?, ?, ?, ?, ?, ?)", [$room_id, $userinfos['prenom'] . " " . $userinfos['nom'], $userinfos['img'], date('Y-m-d'), $message, "bot", $_SESSION['id']], 3);
		return ($return);
	}

	private function haveRights(array $content, int $user_id) : bool {

		foreach ($content as $key => $value) {
			
			if ($key == $user_id && $value['vote_playlist'] == 1)
				return (true);
		}
		return (false);
	}

	private function response(int $status, string $response, string $name = "") : string {

		return (json_encode([
			"status" => $status,
			"response" => $response,
			"name_song" => $name
		]));
	}
}

?>
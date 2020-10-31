<?php

Class mcdo {

	const CATEG = [
		"burgers",
		"salades",
		"petite-faim",
		"boissons",
		"desserts",
		"sauces",
		"grandes-salades"
	];

	const ENDPOINT = "https://www.mcdonalds.fr/";

	const CATEG_ENDPOINT = "produits/";

	private $bdd;
	private $users;

	public function __Construct() {

		$e = new Errors();
		$this->bdd = new Bdd($e);
		$this->users = new Users();
	}

	public function getCateg() {

		if (!isset($_SESSION['id']) || empty($_SESSION['id'])) {
			echo ($this->response(404, "not rights"));
			return;
		}
		if (!isset($_POST['categ']) || empty($_POST['categ'])) {
			echo ($this->response(403, "bad arguments"));
			return;
		}
		if (!isset($_POST['id_room']) || empty($_POST['id_room'])) {
			echo ($this->response(403, "bad arguments"));
			return;
		}
		$categ = htmlspecialchars($_POST['categ']);
		$index = array_search($categ, self::CATEG);
		if (!in_array($categ, self::CATEG)) {
			echo $this->showComm($_POST['id_room']);
			return;
		}
		$dom = new DOMDocument();
		@ $dom->loadHTML(file_get_contents(self::ENDPOINT.self::CATEG_ENDPOINT.self::CATEG[$index]));
		$finder = new DomXPath($dom);
		$classname = "view-content";
		$nodes = $finder->query("//*[contains(@class, '$classname')]");
		$elems = $nodes[0]->getElementsByTagName('li');
		foreach($elems as $elem) {
			$datas[] = [
				"img" => self::ENDPOINT.$elem->getElementsByTagName('img')[0]->getAttribute('src'),
				"name" => $elem->getElementsByTagName('h4')[0]->nodeValue
			];
		}
		echo $this->response(200, json_encode($datas));
	}

	public function showComm($id_room) {

		$id_room = htmlspecialchars($_POST['id_room']);
		$res = $this->bdd->AddRequest("SELECT * FROM mcdo_comm WHERE id_room = ?", [$id_room], 1);
		if (empty($res)) {
			return ($this->response(200, "pas de commande en cours ..."));
		}
		$data = [];
		foreach ($res as $key => $value) {
			
			$data[$value["id_user"]][] = [
				"name" => $value["commande"],
				"img" => $value["img_commande"],
				"pseudo" => $value["pseudo"]
			];
		}
		return ($this->response(200, json_encode(array_values($data))));
	}

	public function add() {

		if (!isset($_SESSION['id']) || empty($_SESSION['id'])) {
			echo ($this->response(404, "not rights"));
			return;
		}
		if (!isset($_POST['commande_img']) || empty($_POST['commande_img'])) {
			echo ($this->response(403, "bad arguments"));
			return;
		}
		if (!isset($_POST['commande_name']) || empty($_POST['commande_name'])) {
			echo ($this->response(403, "bad arguments"));
			return;
		}
		if (!isset($_POST['id_room']) || empty($_POST['id_room'])) {
			echo ($this->response(403, "bad arguments"));
			return;
		}
		$id_room = htmlspecialchars($_POST['id_room']);
		$commande_img = htmlspecialchars($_POST['commande_img']);
		$commande_name = htmlspecialchars($_POST['commande_name']);

		$this->users->GetDataBaseUsers($_SESSION['id'], $this->bdd);
		$user = $this->users->getUserData();
		$res = $this->bdd->AddRequest("INSERT INTO mcdo_comm(id_room, id_user, commande, pseudo, img_commande) VALUES(?, ?, ?, ?, ?)", [$id_room, $_SESSION['id'], $commande_name, $user["name"] . " " .$user["surname"], $commande_img], 3);
		if ($res == 0) {
			$this->response(200, "add error");
			return;
		}
		echo $this->response(200, "add success", $commande_name);
	}

	public function sendCommande() {

		if (!isset($_SESSION['id']) || empty($_SESSION['id'])) {
			echo ($this->response(404, "not rights"));
			return;
		}
		if (!isset($_POST['commande']) || empty($_POST['commande'])) {
			echo ($this->response(403, "bad arguments"));
			return;
		}
		if (!isset($_POST['phoneNumber']) || empty($_POST['phoneNumber'])) {
			echo ($this->response(403, "bad arguments"));
			return;
		}
		if (!isset($_POST['id_room']) || empty($_POST['id_room'])) {
			echo ($this->response(403, "bad arguments"));
			return;
		}

		$id_room = htmlspecialchars($_POST['id_room']);
		$sPhoneNum = htmlspecialchars($_POST['phoneNumber']);
		$sPhoneNum = "+33" . substr($sPhoneNum, 1);
		$commande = json_decode($_POST['commande'], true);

		$data = [
		    'phone' => $sPhoneNum,
		    'body' => $this->msg($commande),
		];
		$json = json_encode($data);
		$token = 'TOKEN';
		$instanceId = 'INSTANCEID';
		$url = 'https://api.chat-api.com/instance'.$instanceId.'/message?token='.$token;
		$options = stream_context_create(['http' => [
		        'method'  => 'POST',
		        'header'  => 'Content-type: application/json',
		        'content' => $json
		    ]
		]);
		$res = $this->bdd->AddRequest("DELETE FROM mcdo_comm WHERE id_room = ?", [$id_room], 1);
		echo $result = file_get_contents($url, false, $options);
	}

	private function msg(array $message) : string {

		$msg = "Commande Mcdo a prendre : \n";

		foreach ($message as $key => $value) {
			
			$msg .= "pour : " . $value[0]["pseudo"] . " : \n";
			foreach ($value as $keys => $values)
				$msg .= " - " . $values["name"] . "\n";
		}
		return ($msg);
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
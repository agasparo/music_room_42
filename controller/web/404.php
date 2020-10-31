<?php

class NotFound {

	const TEMPLATE_INDEX = "template/404/index.html";

	const TEMPLATE_USER = "template/404/blind.html"; 

	private $render;
	private $allvars;

	public function __Construct() {

		$e = new Errors();
		$this->render = new Render($e);
		$this->allvars = new Globale($e);
		$this->bdd = new Bdd($e);
	}

	public function show(string $url) {
		
		if (!isset($_SESSION['id']) || empty($_SESSION['id'])) {
			echo $this->render->run([], self::TEMPLATE_USER);
			return;
		}

		$user_payed = $this->bdd->addRequest("SELECT * FROM abonnement WHERE id_user = ? AND type = ?", [$_SESSION['id'], 1], 3);
		if ($user_payed == 0) {
			echo $this->render->run([], self::TEMPLATE_USER);
			return;
		}

		if ($url[strlen($url) - 1] == "/")
			header("Location: /music_room/" . substr($url, 0, -1));

		$array = $this->allvars->GetFor("404/index", 0);
		echo $this->render->run($array, self::TEMPLATE_INDEX);
	}
}

?>
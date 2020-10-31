<?php

Class Prime {

	const TEMPLATE_BASE = "template/prime/index.html";
	const TEMPLATE_PAYE = "template/prime/paypal.html";

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

		if (isset($_SESSION['id']) && !empty($_SESSION['id'])) {
			$array = $this->allvars->GetFor("prime/index", 0);
			echo $this->render->run($array, self::TEMPLATE_BASE);
		} else {
			header("Location: /music_room/");
		}
	}

	public function go_paye() {

		if (isset($_SESSION['id']) && !empty($_SESSION['id'])) {
			echo $this->render->run([], self::TEMPLATE_PAYE);
		} else {
			echo "redirect";
		}
	}

	public function payed() {

		if (isset($_SESSION['id']) && !empty($_SESSION['id'])) {
			$this->bdd->AddRequest("UPDATE abonnement set type = ? WHERE id_user = ?", [1, $_SESSION['id']], 3);
		}
		header("Location: /music_room/");
	}
} 

?>
<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

Class Login_out {

	const TEMPLATE_INSCRIPTION = "template/home/inscription.html";

	private $bdd;
	private $render;
	private $allvars;

	public function __Construct() {

		$e = new Errors();
		$this->bdd = new Bdd($e);
		$this->render = new Render($e);
		$this->allvars = new Globale($e);
	}

	public function valid(string $token) {

		$res = $this->bdd->AddRequest("SELECT * FROM users WHERE validator = ? AND valid = ? AND api = ?", [$token, 0, 0], 3);
		if ($res == 0)
			header('location:/music_room/');
		$this->bdd->AddRequest("UPDATE users SET valid = ? WHERE validator = ? AND api = ?", [1, $token, 0], 3);
		header('location:/music_room/');
	}

	public function connect() {

		$args = $_POST;

		// check if data are here

		if (!isset($args['mail']) || !isset($args['password']) || empty($args['mail']) || empty($args['password'])) {
			echo ($this->response(400, "Tous les champs doivent etres completes"));
			return;
		}

		$mail = htmlspecialchars($args['mail']);

		//check if user exist
		
		$count = $this->bdd->AddRequest("SELECT * FROM users WHERE mail = ? AND api = ?", [$mail, 0], 3);
		if ($count == 0) {
			echo ($this->response(200, "new user |" . $mail));
			return;
		}

		// if user exist check mdp
		
		$password = hash('whirlpool', $args['password']);
		$response = $this->bdd->AddRequest("SELECT * FROM users WHERE mail = ? AND password = ? AND valid = ? AND api = ?", [$mail, $password, 1, 0], 2);
		if (empty($response)) {
			echo ($this->response(400, "mail ou mot de passe incorrecte ou profil non valide"));
			return;
		}
		$_SESSION['id'] = $response['id'];
		$this->Offres($_SESSION["id"]);
		echo ($this->response(200, "login"));
	}

	public function inscription_form() {

		$form = $this->allvars->GetFor("home/inscription", 0);
		echo $this->render->run($form, self::TEMPLATE_INSCRIPTION);
	}

	public function inscription() {

		$args = $_POST;

		if (!isset($args['nom']) || empty($args['nom']) || !isset($args['prenom']) || empty($args['prenom']) || !isset($args['mail']) || empty($args['mail']) || !isset($args['mdp1']) || empty($args['mdp1']) || !isset($args['mdp2']) || empty($args['mdp2'])) {
			echo ($this->response(400, "Tous les champs doivent etres completes"));
			return;
		}

		$nom = htmlspecialchars($args['nom']);
		$prenom = htmlspecialchars($args['prenom']);

		if (strlen($nom) < 3 || strlen($nom) > 25) {
			echo ($this->response(400, "votre nom doit faire entre 3 et 25 caracteres"));
			return;
		}

		if (strlen($prenom) < 3 || strlen($prenom) > 15) {
			echo ($this->response(400, "votre prenom doit faire entre 3 et 15 caracteres"));
			return;
		}

		$mail = htmlspecialchars($args['mail']);

		if (!filter_var($mail, FILTER_VALIDATE_EMAIL)) {
			echo ($this->response(400, "votre mail doit etre valide"));
			return;
		}

		$res = $this->bdd->AddRequest("SELECT * FROM users WHERE mail = ? AND api = ?", [$mail, 0], 3);
		if ($res != 0) {
			echo ($this->response(400, "Ce mail est deja utilise"));
			return;
		}

		if (!preg_match('/^(?=.*[!@#$%^&*-])(?=.*[0-9])(?=.*[A-Z]).{8,20}$/', $args['mdp1'])) {
			echo ($this->response(400, "votre mot de passe doit contenir au mons un chifre une lettre et un caractere special et faire 8 caracteres"));
			return;
		}

		$mdp1 = hash('whirlpool', $args['mdp1']);
		$mdp2 = hash('whirlpool', $args['mdp2']);

		if ($mdp1 != $mdp2) {
			echo ($this->response(400, "vos mots de passes sont differents"));	
			return;
		}

		$token = bin2hex(random_bytes(rand(38, 98)));
		$response = $this->bdd->AddRequest("INSERT INTO users(prenom, nom, img, mail, password, valid, view, api, validator) VALUES(?, ?, ?, ?, ?, ?, ?, ?, ?)", [
			$prenom,
			$nom,
			"user_image/5f85af8cd8419_room.jpeg",
			$mail,
			$mdp1,
			0,
			true,
			0,
			$token
		], 3);

		if ($response != 1) {
			echo ($this->response(400, "utilisateur non cree"));	
			return;
		}

		$getUsers = $this->bdd->AddRequest("SELECT id from users WHERE mail = ? And api = ?", [$mail, 0], 2);
		$this->bdd->AddRequest("INSERT INTO abonnement(id_user, type) VALUES(?, ?)", [$getUsers['id'], 0], 3);
		echo $this->response(200, "login");
		$this->sendMail($mail, $prenom . " " . $nom, $token);
	}

	private function sendMail(string $mailto, string $nameto, string $token) {

		$mail = new PHPMailer(true);

		//$mail->SMTPDebug = SMTP::DEBUG_SERVER;
	    $mail->isSMTP();
	    $mail->Host       = 'smtp.gmail.com';
	    $mail->SMTPAuth   = true;
	    $mail->Username   = '42.projets.agasparo@gmail.com';
	    $mail->Password   = 'arthur3103';
	    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
	    $mail->Port       = 587;

	    $mail->setFrom('42.projets.agasparo@gmail.com', 'Bienvenue sur music Room');
	    $mail->addAddress($mailto, $nameto);

	    $mail->isHTML(true);
	    $mail->Subject = 'Bienvenue sur Music Room';
	    $mail->Body    = 'Pour valider ton inscription click <a href="localhost/music_room/valid_user/'.$token.'">ici</a>';
	    $mail->AltBody = 'ne pas repondre a ce mail';

	    $mail->send();
	}

	private function Offres(int $id) {

		$this->bdd->AddRequest("INSERT INTO connect(id_user) VALUES(?)", [$id], 3);
	}

	private function response(int $status, string $response) : string {

		return (json_encode([
			"status" => $status,
			"response" => $response
		]));
	}
}

?>
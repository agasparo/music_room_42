<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

Class Reset {

	const TEMPLATE_BASE = "template/reset/index.html";

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

	public function mdpForgot(string $token) {

		$array = $this->allvars->GetFor("reset/index", 0);
		$array["hidden"] = $token == "new" ? "hidden" : "";
		$array["value_submit"] = $token == "new" ? "envoyer mon code" : "Modifier";
		$array["token"] = $token;
		echo $this->render->run($array, self::TEMPLATE_BASE);
	}

	public function mdpChange() {

		if (!isset($_POST['mail'])){
			echo ($this->response(403, "bad arguments"));
			return;
		}
		if (!isset($_POST['mdp_ref'])) {
			echo ($this->response(403, "bad arguments"));
			return;
		}
		if (!isset($_POST['mdp_cmp'])) {
			echo ($this->response(403, "bad arguments"));
			return;
		}
		if (!isset($_POST['token']) || empty($_POST['token'])) {
			echo ($this->response(403, "bad arguments"));
			return;
		}

		$mail = htmlspecialchars($_POST['mail']);
		$mdp_ref = htmlspecialchars($_POST['mdp_ref']);
		$mdp_cmp = htmlspecialchars($_POST['mdp_cmp']);
		$token = htmlspecialchars($_POST['token']);

		$user_exist = $this->bdd->AddRequest("SELECT * FROM users WHERE mail = ? AND api = ? AND valid = ?", [$mail, 0, 1], 3);
		if ($user_exist == 0) {
			echo $this->response(404, "pas d'utilisateur trouve");
			return;
		}
		if ($token == "new") {
			$token = bin2hex(random_bytes(rand(38, 98)));
			$this->sendMail($mail, "mot de passe", $token);
			$this->bdd->AddRequest("UPDATE users SET validator = ? WHERE mail = ? AND api = ? AND valid = ?", [$token, $mail, 0, 1], 3);
			echo $this->response(200, "mail envoye");
			return;
		}
		$user = $this->bdd->AddRequest("SELECT * FROM users WHERE validator = ? AND mail = ? AND api = ? AND valid = ?", [$token, $mail, 0, 1], 3);
		if ($user == 0) {
			echo $this->response(404, "pas d'utilisateur trouve");
			return;
		}

		if (!preg_match('/^(?=.*[!@#$%^&*-])(?=.*[0-9])(?=.*[A-Z]).{8,20}$/', $mdp_ref)) {
			echo ($this->response(400, "votre mot de passe doit contenir au mons un chifre une lettre et un caractere special et faire 8 caracteres"));
			return;
		}

		$mdp1 = hash('whirlpool', $mdp_ref);
		$mdp2 = hash('whirlpool', $mdp_cmp);

		if ($mdp1 != $mdp2) {
			echo ($this->response(400, "vos mots de passes sont differents"));	
			return;
		}
		$this->bdd->AddRequest("UPDATE users SET password = ? WHERE mail = ? AND api = ? AND valid = ? AND validator = ?", [$mdp1, $mail, 0, 1, $token], 3);
		$this->bdd->AddRequest("UPDATE users SET validator = ? WHERE mail = ? AND api = ? AND valid = ?", ["", $mail, 0, 1], 3);
		echo $this->response(200, "mot de passe changer avec succes");
	}

	private function response(int $status, string $response) : string {

		return (json_encode([
			"status" => $status,
			"response" => $response
		]));
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

	    $mail->setFrom('42.projets.agasparo@gmail.com', 'music Room - mot de passe');
	    $mail->addAddress($mailto, $nameto);

	    $mail->isHTML(true);
	    $mail->Subject = 'Changer son mot de passe -  Music Room';
	    $mail->Body    = 'Pour valider le changement de ton mot de passe click <a href="localhost/music_room/reset_mdp/'.$token.'">ici</a>';
	    $mail->AltBody = 'ne pas repondre a ce mail';

	    $mail->send();
	}
}

?>
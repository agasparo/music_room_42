<?php

Class Google {

	const clientID = '362961974393-ua6eb46qg6vv0qnkohl9ov620olso7iv.apps.googleusercontent.com';
	const clientSecret = 'PDZjUQ6fVuySMLFIuDAq1BtK';
	const redirectUri = 'http://lvh.me/music_room/google_connexion';
	const redirectUriAttach = 'http://lvh.me/music_room/google_attach';

	private $client;

	private $bdd;

	public function __Construct() {

		$this->client = new Google_Client();
		$this->client->setClientId(self::clientID);
		$this->client->setClientSecret(self::clientSecret);
		$this->client->addScope("email");
		$this->client->addScope("profile");

		$e = new Errors();
		$this->bdd = new Bdd($e);
	}

	public function connexion() {

		$this->client->setRedirectUri(self::redirectUri);

		if (isset($_GET['code'])) {

			$token = $this->client->fetchAccessTokenWithAuthCode($_GET['code']);
  			$this->client->setAccessToken($token['access_token']);
  
  			// get profile info
  			$google_oauth = new Google_Service_Oauth2($this->client);
  			$google_account_info = $google_oauth->userinfo->get();
  			
  			$email = $google_account_info->email;
  			$name = $google_account_info->name;
  			$surname = $google_account_info->familyName;
  			$img = $google_account_info->picture;

  			$isSaved = $this->bdd->AddRequest("SELECT * FROM users WHERE mail = ? AND api = ?", [$email, 1], 2);
  			if (!empty($isSaved)) {
  				$_SESSION['id'] = $isSaved['id'];
  			} else {
  				$this->bdd->AddRequest("INSERT INTO users(prenom, nom, img, mail, password, valid, view, api, validator) VALUES(?, ?, ?, ?, ?, ?, ?, ?, ?)",
  				[
					$name,
					$surname,
					$img,
					$email,
					"1",
					1,
					true,
					1,
					"1"
				], 3);
  				$userinfo = $this->bdd->AddRequest("SELECT * FROM users WHERE mail = ? AND api = ?", [$email, 1], 2);
  				$_SESSION['id'] = $userinfo['id'];
  				$this->bdd->AddRequest("INSERT INTO abonnement(id_user, type) VALUES(?, ?)", [$_SESSION['id'], 0], 3);
  			}
  			$this->bdd->AddRequest("INSERT INTO connect(id_user) VALUES(?)", [$_SESSION['id']], 3);
  			header("Location: /music_room/");
		} else {
			echo ($this->response(200, $this->client->createAuthUrl()));
		}
	}

	public function attach() {

		if (!isset($_SESSION['id']) || empty($_SESSION['id'])) {
			echo ($this->response(404, "not rights"));
			return;
		}

		$this->client->setRedirectUri(self::redirectUriAttach);

		if (isset($_GET['code'])) {

			$token = $this->client->fetchAccessTokenWithAuthCode($_GET['code']);
  			$this->client->setAccessToken($token['access_token']);
  
  			// get profile info
  			$google_oauth = new Google_Service_Oauth2($this->client);
  			$google_account_info = $google_oauth->userinfo->get();
  			
  			$email = $google_account_info->email;
  			$name = $google_account_info->name;
  			$surname = $google_account_info->familyName;
  			$img = $google_account_info->picture;

  			$atta = $this->bdd->AddRequest("INSERT INTO other_count(id_user, count, api) VALUES(?, ?, ?)",
			[
				$_SESSION['id'],
				json_encode([
					"name" => $name,
					"surname" => $surname,
					"img" => $img,
					"email" => $email
				]),
				1
			], 3);
			header("Location: /music_room/user/" . $_SESSION['id']);
		} else {
			echo ($this->response(200, $this->client->createAuthUrl()));
		}
	}

	private function response(int $status, string $response) : string {

		return (json_encode([
			"status" => $status,
			"response" => $response
		]));
	}
}

?>
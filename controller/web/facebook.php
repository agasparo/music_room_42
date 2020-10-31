<?php

Class Facebook {

	const Id = '711409119029037';

	const Secret = '875ac0d5eba6a860954179d4646d7520';

	const RedirectUri = 'https://lvh.me/music_room/facebook_connexion';

	const RedirectUriAttach = 'https://lvh.me/music_room/facebook_attach';

	private $fb;

	private $token;

	private $bdd;

	public function __Construct() {

		$this->fb = new \Facebook\Facebook([
  			'app_id' => self::Id,
  			'app_secret' => self::Secret,
  			'graph_api_version' => 'v5.0',
		]);

		$e = new Errors();
		$this->bdd = new Bdd($e);
	}

	public function connexion() {

		$helper = $this->fb->getRedirectLoginHelper();
		if (isset($_GET['state']))
			$_SESSION['FBRLH_state'] = $_GET['state'];

		if (!isset($_GET['code'])) {
			
			$permissions = ['email'];
			$loginUrl = $helper->getLoginUrl(self::RedirectUri, $permissions);

			echo ($this->response(200, $loginUrl));
		} else {
			if (!isset($_SESSION['fb_token']))
				$accessToken = $helper->getAccessToken();
			else
				$accessToken = $_SESSION['fb_token'];
			if (!isset($accessToken))
				return;
			if (!isset($_SESSION['fb_token']))
				$_SESSION['fb_token'] = $accessToken;
			$response = $this->fb->get('/me?fields=id,name,email,picture', $accessToken);
			$me = $response->getGraphUser();
			if (empty($me))
				return;

			$user = explode(' ', $me['name']);
			$email = $me['email'];
			$name = $user[0];
			$surname = $user[1];
			$img = $me['picture']['url'];

			$isSaved = $this->bdd->AddRequest("SELECT * FROM users WHERE mail = ? AND api = ?", [$email, 2], 2);
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
					2,
					"1"
				], 3);
  				$userinfo = $this->bdd->AddRequest("SELECT * FROM users WHERE mail = ? AND api = ?", [$email, 2], 2);
  				$_SESSION['id'] = $userinfo['id'];
  				$this->bdd->AddRequest("INSERT INTO abonnement(id_user, type) VALUES(?, ?)", [$_SESSION['id'], 0], 3);
  			}
  			$this->bdd->AddRequest("INSERT INTO connect(id_user) VALUES(?)", [$_SESSION['id']], 3);
  			header("Location: http://lvh.me/music_room/");
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
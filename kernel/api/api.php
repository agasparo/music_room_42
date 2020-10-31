<?php

Class CheckToken {

	private $bdd;

	public function __Construct() {

		$e = new Errors();
		$this->bdd = new Bdd($e);
	}

	public function isValid(string $token, string $api_key) : bool {

		$user = $this->bdd->AddRequest("SELECT * FROM api WHERE id_key = ? AND token = ?", [$api_key, $token], 3);
		if ($user == 0)
			return (false);
		return (true);
	}
}

?>
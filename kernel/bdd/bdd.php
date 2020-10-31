<?php

Class Bdd {

	private $bdd;
	private $param;
	private $exeption;

	const PARAMS = "panel/bdd.json";
	const SQL_FILE = "kernel/bdd/data/music_room.sql";

	public function __Construct(object $e) {

		$this->exeption = $e;
		$this->param = json_decode(file_get_contents(self::PARAMS), true);
		$this->Connect();
	}

	private function Connect() {

		try {

			$this->bdd = new PDO($this->param['DB_DSN'] , $this->param['DB_USER'], $this->param['DB_PASSWORD']);
		} catch (Exception $error) {

			if (preg_match("#Unknown database '" . $this->param['DB_NAME'] . "'#", $error->getMessage())) {

				$db_error = new PDO("mysql:host=" . $this->param['DB_HOST'] . ";charset=utf8", $this->param['DB_USER'], $this->param['DB_PASSWORD']);
				$db_error->exec('CREATE DATABASE ' . $this->param['DB_NAME']);
				$this->bdd = new PDO($this->param['DB_DSN'] , $this->param['DB_USER'], $this->param['DB_PASSWORD']);
				if ($this->setUp(self::SQL_FILE) != 0) {
					$this->exeption->BddMistake("table can't be created");
				}
			} else {
				$this->exeption->BddMistake($error->getMessage());
			}
		}
	}

	public function AddRequest(string $request, array $data, int $type) {

		$request = $this->bdd->prepare($request);
		$request->execute($data);
		if ($type == 1) {
			return ($request->fetchAll());
		}
		if ($type == 2) {
			return ($request->fetch());
		}
		return ($request->rowCount());
	}

	private function setUp(string $filesql) {

		$query = file_get_contents($filesql);
		$array = explode(";\n", $query);
		$b = true;
		for ($i = 0; $i < count($array) ; $i++) {
			$str = $array[$i];
			if ($str != '') {
				$str .= ';';
				$b = $this->bdd->exec($str);  
			}  
		}
		return ($b);
	}
}

?>
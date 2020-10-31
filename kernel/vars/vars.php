<?php

Class Globale {

	const FILE = "panel/val.json";

	private $exeption;

	public function __Construct(object $e) {

		$this->exeption = $e;
		$this->vars = json_decode(file_get_contents(self::FILE), true);
	}

	public function GetFor(string $chemin, int $w) : array {

		if (!file_exists("template/" .$chemin . ".html")) {

			$this->exeption->GlobalMistake("'" . $chemin . "' doesn't exist");
			return ([]);
		}

		$route = explode("/", $chemin);
		if ($w == 0) {
			$node = $this->vars["Template"];
		} else {
			$node = $this->vars["Site"];
		}
		for ($i = 0; $i < count($route); $i++) { 
			
			if (!isset($node)) {
				$this->exeption->IndexMistake("'" . $route[$i] . "' doesn't exist");
				return ([]);
			}
			$node = $node[$route[$i]];
		}
		return ($node);
	}
}

?>
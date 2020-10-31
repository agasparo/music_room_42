<?php

Class Errors {

	private $logs;

	public function __Construct() {

		$this->logs = new Logs();
	}

	public function RenderMistake(string $error) {

		$this->logs->Write("[Template Error] : " . $error, 0);
		throw new Exception("[Template Error] : " . $error);
	}

	public function GlobalMistake(string $error) {

		$this->logs->Write("[Variable Error] : " . $error, 0);
		throw new Exception("[Variable Error] : " . $error);
	}

	public function IndexMistake(string $error) {

		$this->logs->Write("[Index Error] : " . $error, 0);
		throw new Exception("[Index Error] : " . $error);
	}

	public function BddMistake(string $error) {

		$this->logs->Write("[Bdd Error] : " . $error, 1);
		throw new Exception("[Bdd Error] : " . $error);
	}
}

?>
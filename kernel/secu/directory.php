<?php

Class Directorys {

	private $archit = [];

	public function __Construct(array $archi) {

		$this->archi = $archi;
		if (!file_exists("var"))
			mkdir("var");
	}

	public function run() {

		foreach ($this->archi as $path => $dirs) {
			
			foreach ($dirs as $key => $dir) {
				
				$this->createDir($path . "/" . $dir);
			}
		}
	}

	private function createDir(string $dirname) {

		if (!file_exists($dirname))
			mkdir($dirname);
	}
}

?>
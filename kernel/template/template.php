<?php

class Render {

	private $exeption;

	public function __Construct(object $e) {

		$this->exeption = $e;
	}

	public function run(array $vars, string $file) : string {

		if (!file_exists($file)) {
			
			$this->exeption->RenderMistake("'" . $file . "' doesn't exist");
			return ("");		
		}

		$content = file_get_contents($file);

		foreach ($vars as $key => $value) {
			
			$content = str_replace("{{ ." . $key . " }}", $value, $content);
		}

		return ($content);
	}
}
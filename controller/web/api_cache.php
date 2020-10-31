<?php

class api_cache {

	public function __Construct() {

	}

	public function save() {
		
		if (!isset($_POST['content']) || empty($_POST['content']))
			return;
		if (!isset($_POST['file']) || empty($_POST['file']))
			return;
		$index = htmlspecialchars($_POST['file']);
		if ($index == 1)
			file_put_contents("var/api_cache/api.save_music", json_encode($_POST['content']));
		if ($index == 2)
			file_put_contents("var/api_cache/api.save_playlist", json_encode($_POST['content']));
	}

	public function get() {

		if (!isset($_POST['file']) || empty($_POST['file']))
			return;
		$index = htmlspecialchars($_POST['file']);
		if ($index == 1)
			echo file_get_contents("var/api_cache/api.save_music");
		if ($index == 2)
			echo file_get_contents("var/api_cache/api.save_playlist");
	}
}

?>
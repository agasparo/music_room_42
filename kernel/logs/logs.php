<?php

Class Logs {

	const LOG_FILES = [
		"var/logs/errors.log",
		"var/logs/bdd.log",
		"var/logs/connection.log"
	];

	public function Write(string $log, int $file) {

		$device = new Device();
		if (isset($_SERVER['HTTP_USER_AGENT']))
			$data = "[app_version : 1.0][" . date("Y-m-d H:i:s") . "][" . $device->GetIp() . "][" . $_SERVER['HTTP_USER_AGENT'] . "]" . $log . "\r\n";
		else
			$data = "[app_version : 1.0][" . date("Y-m-d H:i:s") . "][" . $device->GetIp() . "][" . "cli" . "]" . $log . "\r\n";
		if (!file_exists(self::LOG_FILES[$file])) {
			file_put_contents(self::LOG_FILES[$file], "");
		}
		file_put_contents(self::LOG_FILES[$file], $data, FILE_APPEND);
	}
}

?>
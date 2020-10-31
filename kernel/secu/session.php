<?php

Class SessionSecu {

	const File = "var/session/sessions.logs";

	private $session_on = false;

	private $device;

	public function __Construct() {

		if (isset($_SESSION['id']) && !empty($_SESSION['id']))
			$this->session_on = true;
		$this->device = new Device();
	}

	public function isStoll() : bool {

		if ($this->session_on) {
			$this->insertData();
			if (!$this->checker()) {
				$this->removeData($_SESSION['id']);
				return (true);
			}
		}
		return (false);
	}

	private function removeData(int $id) {

		$content = file(self::File);

		foreach ($content as $key => $value) {
			
			$value = json_decode($value, true);
			if (isset($value['id']) && $value['id'] == $id)
				unset($content[$key]);
		}
		file_put_contents(self::File, implode("\n", $content));
	}

	private function checker() : bool {

		if (!file_exists(self::File))
			file_put_contents(self::File, "");

		$content = file(self::File);
		$ip = [];
		foreach ($content as $key => $line) {
			
			$line = json_decode($line, true);
			if (isset($line["id"]) && $line["id"] == $_SESSION["id"]) {

				if (isset($ip[$_SESSION['id']]) && $line['ip'] != $ip[$_SESSION['id']])
					return (false);

				$ip[$_SESSION['id']] = $line['ip'];
			}
		}
		return (true);
	}

	private function insertData() {

		$device = $this->device($_SERVER["HTTP_USER_AGENT"]);
		$ip = $this->device->GetIp();
		file_put_contents(self::File, json_encode([
			"device" => $device,
			"ip" => $ip,
			"id" => $_SESSION['id']
		])."\n", FILE_APPEND);
	}

	private function device($unix) {

        $device = "Computer";
        $unix = strtoupper($unix);

        if (strstr($unix,     'ANDROID'))    {$device="Phone";}
        elseif (strstr($unix, 'IPHONE'))     {$device="Phone";}
        elseif (strstr($unix, 'BLACKBERRY')) {$device="Phone";}
        elseif (strstr($unix, 'WEBOS'))      {$device="Phone";}
        elseif (strstr($unix, 'SYMBIAOS'))   {$device="Phone";}
        elseif (strstr($unix, 'TABLET'))     {$device="Tablet";}
        elseif (strstr($unix, 'IPAD'))       {$device="Tablet";}
        elseif (strstr($unix, 'IPOD'))       {$device="Portable media players ";}

        return ($device);
    }
}

?>
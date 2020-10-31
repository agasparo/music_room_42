<?php

date_default_timezone_set('Europe/Paris');

Class Fail2ban {

	const LOGS_ENDPOINT = "var/fail2ban/";

    const DATE_FORMAT = "Y-m-d H:i:s";

	private $params = [];

	public function __Construct(array $params) {

		$this->params = $params;
	}

	public function checkLogs(string $jail_name) {

		if (!isset($this->params[$jail_name]))
			return;
		$param = $this->params[$jail_name];
		if (!file_exists(self::LOGS_ENDPOINT . $param["lookup_logs"]))
			file_put_contents(self::LOGS_ENDPOINT . $param["lookup_logs"], "", FILE_APPEND);
		$this->addDataToFile(self::LOGS_ENDPOINT . $param["lookup_logs"]);
        return (
            [
                "status" => $this->banStatus(
                                self::LOGS_ENDPOINT . $jail_name,
                                self::LOGS_ENDPOINT . $param["lookup_logs"],
                                $param["max_try"],
                                isset($_SERVER["HTTP_USER_AGENT"]) ? $this->getOsClient().$this->device($_SERVER["HTTP_USER_AGENT"]).$this->architecture() : "cli",
                                $param["jail_time"]
                            ) ? 'true' : 'false',
                "jail_time" => $param["jail_time"]
            ]
        );
	}

	private function banStatus(string $jail_file, string $file_name, int $max_try, string $device_id, int $time_ban) : bool {

        if (!file_exists($file_name))
            file_put_contents($file_name, "", FILE_APPEND);
        if ($this->checkInJail($jail_file, $device_id))
            return (true);
        $content = file($file_name);
        $deviceTry = [];

        foreach ($content as $nb => $line) {
            
            $line = json_decode($line, true);
            $id = $line["os"].$line["device"].$line["archi"];

            if (isset($deviceTry[$id]) && $this->isToday($line["date"])) {
                $deviceTry[$id]["nb_try"] = intval($deviceTry[$id]["nb_try"]) + 1;
                $deviceTry[$id]["last_date"] = $line["date"];
            } else {
                $deviceTry[$id] = [
                    "nb_try" => 1,
                    "last_date" => $line["date"],
                ];
            }
            if ($max_try < $deviceTry[$id]["nb_try"] && $id == $device_id) {
                file_put_contents($jail_file, json_encode([
                    "id" => $device_id,
                    "date" => date(self::DATE_FORMAT),
                    "time" => $time_ban
                ]) . "\n", FILE_APPEND);
                return (true);
            }
        }
		return (false);
	}

    private function checkInJail(string $file_name, string $device_id) : bool {

        if (!file_exists($file_name))
            file_put_contents($file_name, "", FILE_APPEND);
        $content = file($file_name);

        foreach ($content as $key => $line) {
            
            $line = json_decode($line, true);
            if ($line["id"] == $device_id) {

                $date_ban = new DateTime($line["date"]);
                $cmpDate = new DateTime(date(self::DATE_FORMAT));

                $interval = $cmpDate->diff($date_ban);
                if ($interval->format("%i") >= $line["time"]) {
                    unset($content[$key]);
                    file_put_contents($file_name, implode("\n", $content));
                    return (false);
                }
                return (true); 
            }
        }
        return (false);
    }

    private function isToday(string $current_date) {

        $n_c = new DateTime($current_date);
        $n_l = new DateTime(date(self::DATE_FORMAT));

        $interval = $n_l->diff($n_c);
        if ($interval->format("%y") == 0 && $interval->format("%m") == 0 && $interval->format("%d") == 0 && $interval->format("%h") == 0 && $interval->format("%i") == 0 && $interval->format("%s") == 0)
            return (true);
        return (false);
    }

	private function addDataToFile(string $file_name) {

		file_put_contents(
			$file_name,
			json_encode([
				"date" => $this->getdate(),
				"os" => $this->getOsClient(),
				"nav" => $this->browser(),
				"device" => isset($_SERVER["HTTP_USER_AGENT"]) ? $this->device($_SERVER["HTTP_USER_AGENT"]) : "test",
				"archi" => $this->architecture(),
				"width" => $this->java("width"),
				"height" => $this->java("height"),
				"lang" => $this->language(),
				"provetor" => $this->provetor(),
				"agent" => $this->agent(),
				"referer" => $this->referer()
			]) . "\n",
			FILE_APPEND
		);
	}

	private function getOsClient() {

        $system = "unknow";

        $os = [
            '/Windows NT 10.0/i'    =>  'Windows 10'            , '/windows nt 6.4/i'     =>  'Windows 10'   , '/windows nt 6.3/i'     =>  'Windows 8.1',
            '/windows nt 6.2/i'     =>  'Windows 8'             , '/windows nt 6.1/i'     =>  'Windows 7'    , '/windows nt 6.0/i'     =>  'Windows Vista',
            '/windows nt 5.2/i'     =>  'Windows Server 2003/XP', '/windows nt 5.1/i'     =>  'Windows XP'   , '/windows me/i'         =>  'Windows ME',
            '/windows nt 5.0/i'     =>  'Windows 2000'          , '/win98/i'              =>  'Windows 98'   , '/win95/i'              =>  'Windows 95',
            '/windows nt 4.0/i'     =>  'Windows NT 4.0'        , '/windows nt 3.51/i'    =>  'Windows NT 3.51', '/windows nt 3.5/i'   =>  'Windows NT 3.5',
            '/windows nt 3.1/i'     =>  'Windows NT 3.1'        , '/windows nt 3.11/i'    =>  'Windows 3.11' ,  '/linux/i'             =>  'Linux',
            '/android/i'            =>  'Android'               , '/android 1.6/i'        =>  'Android 1.6'  , '/android 2.0/i'        =>  'Android 2.0',
            '/android 2.0.1/i'      =>  'Android 2.0.1'         , '/android 2.1/i'        =>  'Android 2.1' , '/android 2.2/i'         =>  'Android 2.2',
            '/android 2.2.1/i'      =>  'Android 2.2.1'         , '/android 2.2.2/i'      =>  'Android 2.2.2', '/android 2.2.3/i'      =>  'Android 2.2.3',
            '/android 2.2.4/i'      =>  'Android 2.2.4'         , '/android 2.3/i'        =>  'Android 2.3', '/android 2.3.0/i'        =>  'Android 2.0.3',
            '/android 2.3.1/i'      =>  'Android 2.3.1'         , '/android 2.3.3/i'      =>  'Android 2.3.3', '/android 2.3.4/i'      =>  'Android 2.3.4',
            '/android 2.3.5/i'      =>  'Android 2.3.5'         , '/android 2.3.6/i'      =>  'Android 2.3.6', '/android 2.3.7/i'      =>  'Android 2.3.7',
            '/android 3.0/i'        =>  'Android 3.0'           , '/android 3.1/i'        =>  'Android 3.1', '/android 3.2/i'          =>  'Android 3.1',
            '/android 3.2.1/i'      =>  'Android 3.2.1'         , '/android 3.2.2/i'      =>  'Android 3.2.2', '/android 3.2.3/i'      =>  'Android 3.2.3',
            '/android 3.2.4/i'      =>  'Android 3.2.4'         , '/android 4.0/i'        =>  'Android 4.0', '/android 4.0.0/i'        =>  'Android 4.0.0',
            '/android 4.0.1/i'      =>  'Android 4.0.1'         , '/android 4.0.2/i'      =>  'Android 4.0.2', '/android 4.0.3/i'      =>  'Android 4.0.3',
            '/android 4.0.4/i'      =>  'Android 4.0.4'         , '/android 4.2.1/i'      =>  'Android 4.2.1', '/android 4.2.2/i'      =>  'Android 4.2.2',
            '/android 4.3/i'        =>  'Android 4.3'           , '/android 4.4/i'        =>  'Android 4.4', '/android 4.4.1/i'        =>  'Android 4.4.1',
            '/android 4.4.2/i'      =>  'Android 4.4.2'         , '/android 4.4.3/i'      =>  'Android 4.4.3', '/android 4.4.4/i'      =>  'Android 4.4.4',
            '/android 5.0/i'        =>  'Android 5.0'           , '/macintosh|mac os x/i' =>  'Mac OS X', '/mac_powerpc/i'             =>  'Mac OS 9',
            '/ubuntu/i'             =>  'Ubuntu'                , '/SymbianOS/i'          =>  'SymbianOS', '/iphone/i'                 =>  'iPhone',
            '/ipod/i'               =>  'iPod'                  , '/ipad/i'               =>  'iPad', '/tablet os/i'                   =>  'Table OS',
            '/blackberry/i'         =>  'BlackBerry'            , '/bb/i'                 =>  'BlackBerry', '/webos/i'                 =>  'Mobile'
        ];

        foreach($os as $regex => $value) {
        	
            if (!isset($_SERVER['HTTP_USER_AGENT']))
                return ("tests");
        	if (preg_match($regex, $_SERVER['HTTP_USER_AGENT'])) {
        		$system = $value;
        	}
        }
        return ($system);
    }

    private function browser() {

        $navegator = "unknow";

        $browser = [
            '/msie/i'       =>  'Internet Explorer','/firefox/i'    =>  'Firefox'  , '/safari/i'     =>  'Safari',
            '/chrome/i'     =>  'Chrome'           ,'/opera/i'      =>  'Opera'    , '/netscape/i'   =>  'Netscape',
            '/maxthon/i'    =>  'Maxthon'          ,'/BrowserNG/i'  =>  'BrowserNG', '/konqueror/i'  =>  'Konqueror',
            '/ie/i'         =>  'Internet Explorer','/mobile/i'     =>  'Handheld Browser'
        ];            
        
        foreach($browser as $regex => $value) {
            if (!isset($_SERVER['HTTP_USER_AGENT']))
                return ("tests");
        	if (preg_match($regex, $_SERVER['HTTP_USER_AGENT'])) {
        		$navegator = $value;
        	}
        }
        return ($navegator);
    }

    private function architecture() {
        
        $arqui = "64Bits";
        $architecture = [
            '/x86_64/i'     => '64Bits', '/amd64/i'     => '64Bits',
            '/x86-64/i'     => '64Bits', '/x64_64/i'    => '64Bits',
            '/x64/i'        => '64Bits', '/WOW64/i'     => '64Bits'
        ];
        
        foreach($architecture as $regex => $value) { 
            if (!isset($_SERVER['HTTP_USER_AGENT']))
                return ("tests");
        	if (preg_match($regex, $_SERVER['HTTP_USER_AGENT'])) {
        		$arqui = $value;
        	}
        }
        return ($arqui);
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

    private function java($get) {
        
        $data = '<script type="text/javascript">';
        
        switch ($get) {
            case 'height':
                $data.='document.write(window.screen.height);</script>';
                break;
            case 'width':
                $data.='document.write(window.screen.width);</script>';
                break;
        }

        return ($data);
    }

    private function language() {

        if (!isset($_SERVER['HTTP_ACCEPT_LANGUAGE']))
            return ("cli");
    	return ($_SERVER['HTTP_ACCEPT_LANGUAGE']);
    }

    private function provetor() {
    	
    	return (gethostbyaddr($_SERVER['REMOTE_ADDR']));
    }

    private function agent() {
    	
        if (!isset($_SERVER['HTTP_USER_AGENT']))
                return ("tests");
    	return ($_SERVER['HTTP_USER_AGENT']);
    }

    private function referer() {
    
    	if (isset($_SERVER['HTTP_REFERER']))
    		return ($_SERVER['HTTP_REFERER']);
    	return ("none");
 	}

    private function getdate() {

    	return (date(self::DATE_FORMAT));
    }
}

?>
<?php

Class Music {

	private $bdd;

	private $response;

	private $content = [];

	private $checker;

	const DEL_FEATURING = [
		"ft",
		"feat"
	];

	public function __Construct() {

		$e = new Errors();
		$this->response = new Responses();
		$this->bdd = new Bdd($e);
		$this->content = $this->setContent();
		$this->checker = new CheckToken();
	}

	public function getAll() {

		$t0 = microtime(true);
		if (!isset($_GET["myToken"]) || !isset($_GET["apiKey"])  || !$this->checker->isValid(htmlspecialchars($_GET["myToken"]), htmlspecialchars($_GET["apiKey"])))
			return ($this->response->resp(["bad token or bad api key"], 406, ["type" => "none"], number_format(microtime(true) - $t0, 5)));
		return ($this->response->resp($this->content, 200, ["type" => "base64"], number_format(microtime(true) - $t0, 5)));
	}

	public function getByArtist(string $name) {

		$t0 = microtime(true);
		if (!isset($_GET["myToken"]) || !isset($_GET["apiKey"])  || !$this->checker->isValid(htmlspecialchars($_GET["myToken"]), htmlspecialchars($_GET["apiKey"])))
			return ($this->response->resp(["bad token or bad api key"], 406, ["type" => "none"], number_format(microtime(true) - $t0, 5)));
		if (empty($name))
			return ($this->response->resp(["bad name"], 404, ["type" => "none"], number_format(microtime(true) - $t0, 5)));
		return ($this->response->resp($this->search("artist", $name), 404, ["type" => "none"], number_format(microtime(true) - $t0, 5)));
	}

	public function getById(string $id) {

		$t0 = microtime(true);
		if (!isset($_GET["myToken"]) || !isset($_GET["apiKey"])  || !$this->checker->isValid(htmlspecialchars($_GET["myToken"]), htmlspecialchars($_GET["apiKey"])))
			return ($this->response->resp(["bad token or bad api key"], 406, ["type" => "none"], number_format(microtime(true) - $t0, 5)));
		if (empty($id))
			return ($this->response->resp(["bad id"], 404, ["type" => "none"], number_format(microtime(true) - $t0, 5)));
		return ($this->response->resp($this->search("id", $id), 404, ["type" => "none"], number_format(microtime(true) - $t0, 5)));
	}

	public function getByTitle(string $title) {

		$t0 = microtime(true);
		if (!isset($_GET["myToken"]) || !isset($_GET["apiKey"])  || !$this->checker->isValid(htmlspecialchars($_GET["myToken"]), htmlspecialchars($_GET["apiKey"])))
			return ($this->response->resp(["bad token or bad api key"], 406, ["type" => "none"], number_format(microtime(true) - $t0, 5)));
		if (empty($title))
			return ($this->response->resp(["bad id"], 404, ["type" => "none"], number_format(microtime(true) - $t0, 5)));
		return ($this->response->resp($this->search("title", $title), 404, ["type" => "none"], number_format(microtime(true) - $t0, 5)));
	}

	public function getStatistic() {

		$t0 = microtime(true);
		if (!isset($_GET["myToken"]) || !isset($_GET["apiKey"])  || !$this->checker->isValid(htmlspecialchars($_GET["myToken"]), htmlspecialchars($_GET["apiKey"])))
			return ($this->response->resp(["bad token or bad api key"], 406, ["type" => "none"], number_format(microtime(true) - $t0, 5)));
		return ($this->response->resp($this->setStats($this->content, $this->bdd->AddRequest("SELECT * FROM playlist", [], 1)), 200, ["type" => "base64"], number_format(microtime(true) - $t0, 5)));
	}

	private function setStats($content, $all_datas) : array {

		$stats = [];

		foreach ($content as $key => $value) {
			
			$stats[] = $this->searchStats($value["id"], $value["long_name"], $all_datas);
		}

		return ($stats);
	}

	private function searchStats($id, $title, $datas) {

		$res = [];
		$count = 0;
		$music_number = count($datas);

		foreach ($datas as $key => $value) {
			
			if (trim($value["song_name"]) == trim($title) || $value["song_id"] == $id)
				$count++;
		}
		$other_data = json_decode(file_get_contents("https://api.deezer.com/search/track?q=" . urlencode($title)), true);
		return ([
			"music_long_title" => $title,
			"counter" => $count,
			"percent" => intval($count / $music_number * 100),
			"duration_s" => intval($other_data["data"][0]["duration"]),
			"duration_m" => number_format(intval($other_data["data"][0]["duration"]) / 60, 2),
			"rank" => $other_data["data"][0]["rank"]
		]);
	}

	private function search($categ, $search_val) : array {

		$response = [];

		foreach ($this->content as $key => $value) {
			
			if (preg_match("#".strtolower($search_val)."#", strtolower($value[$categ]))) {

				$response[] = $this->content[$key]; 
			}
		}
		if (empty($response))
			return (["no results"]);
		return ($response);
	}

	private function setContent() : array {

		$bddVals = $this->bdd->AddRequest("SELECT * FROM playlist", [], 1);
		$data = [];

		foreach ($bddVals as $key => $value) {
		
			$details = $this->getDetails($value["song_name"]);
			if (!$this->is_in($value["song_name"], $value["song_id"], $data)) {
				$data[] = [
					"id" => $value["song_id"],
					"long_name" => trim($value["song_name"]),
					"artist" => trim($details[0]),
					"title" => trim($details[1]),
					"img" => $value["song_img"]
				];
			}
		}
		return ($data);
	}

	private function is_in(string $name, string $id, array $data) : bool {

		foreach ($data as $key => $value) {
			
			if ($value["long_name"] == $name || $value["id"] == $id)
				return (true);
		}
		return (false);
	}

	private function getDetails(string $long_name) : array {

		$short = explode("-", $long_name);
		if (!isset($short[1]))
			$short = explode(" – ", $long_name);
		if (preg_match("#/#", $short[1])) {
			$tmp = $short[1];
			$short[1] = $short[0];
			$short[0] = $tmp;
		}
		foreach (self::DEL_FEATURING as $key => $value) {
			
			if (preg_match("#".$value."#", strtolower($short[1]))) {
				$ex = explode($value, strtolower($short[1]));
				$short[1] = ucwords($ex[0]);
				unset($ex[0]);
				$short[0] = $short[0] . " " . trim($value) . " " . trim(implode(" ", $ex));
			}
		}
		return ($short);
	}
}

?>
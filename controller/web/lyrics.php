<?php

class lyrics {


	const API_ROUTE = "http://api.genius.com/search";
	const TOKEN = "TOKEN";

	private $request;
	private $bdd;

	public function __Construct() {

		$e = new Errors();
		$this->bdd = new Bdd($e);
	}

	public function search() {

		if (!isset($_SESSION['id']) || empty($_SESSION['id'])) {
			echo ($this->response(404, "not rights"));
			return;
		}
		if (!isset($_POST['search']) || empty($_POST['search'])){
			echo ($this->response(403, "bad arguments"));
			return;
		}
		$search = htmlspecialchars($_POST['search']);
		$res = $this->bdd->AddRequest("SELECT * FROM lyrics WHERE name = ?", [$search], 2);
		if (!empty($res)) {
			echo $this->response(200, base64_decode($res["lyrics"]));
			return;
		}
		
		$this->request = new Request("GET", [
			"q" => $search
		]);
		$content = json_decode($this->request->send_request(self::API_ROUTE, self::TOKEN), true);
		if (empty($content["response"]["hits"])) {
			echo $this->response(404, "pas de lyrics trouve");
			return;
		}
		$url_to_go = $content["response"]["hits"][0]["result"]["url"];
		$res = file_get_contents($url_to_go);
		preg_match('#<div class="Lyrics(.*)#', $res, $e);
		if (isset($e[0])) {
			preg_match("#(.*)>About#", $e[0], $a);
			if (isset($a[0])) {
				$breaks = ["<br />","<br>","<br/>"];
				$text = str_ireplace($breaks, "[space]", $a[0]);
				$text = str_replace("About<", "", $text);
				$index = strpos($text, "About");
				if ($index !== false)
					$n_text = substr($text, 0, $index - 1);
				else
					$n_text = $text;
				$text = strip_tags($n_text);
				$text = str_replace("[space]", "<br>", $text);
				$res = $this->bdd->AddRequest("INSERT INTO lyrics(name, lyrics) VALUES(?, ?)", [$search, base64_encode($text)], 3);
				if ($res == 0) {
					echo $this->response(400, "pas de lyrics trouve");
					return;
				}
				echo $this->response(200, $text);
				return;
			}
			echo $this->response(404, "pas de lyrics trouve");
			return;
		}
		echo $this->response(404, "pas de lyrics trouve");
		return;
	}

	private function response(int $status, string $response) : string {

		return (json_encode([
			"status" => $status,
			"response" => $response
		]));
	}
}

?>
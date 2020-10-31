<?php

Class mcdoApi {

	const CATEG = [
		"burgers",
		"salades",
		"petite-faim",
		"boissons",
		"desserts",
		"sauces",
		"grandes-salades"
	];

	const ENDPOINT = "https://www.mcdonalds.fr/";

	const CATEG_ENDPOINT = "produits/";

	private $response;

	private $checker;

	public function __Construct() {

		$this->response = new Responses();
		$this->checker = new CheckToken();
	}

	public function getAll() {

		$t0 = microtime(true);
		if (!isset($_GET["myToken"]) || !isset($_GET["apiKey"])  || !$this->checker->isValid(htmlspecialchars($_GET["myToken"]), htmlspecialchars($_GET["apiKey"])))
			return ($this->response->resp(["bad token or bad api key"], 406, ["type" => "none"], number_format(microtime(true) - $t0, 5)));
		return ($this->response->resp($this->getAllContent(), 200, ["type" => "none"], number_format(microtime(true) - $t0, 5)));
	}

	public function getByCateg(string $categ) {

		$t0 = microtime(true);
		if (!isset($_GET["myToken"]) || !isset($_GET["apiKey"])  || !$this->checker->isValid(htmlspecialchars($_GET["myToken"]), htmlspecialchars($_GET["apiKey"])))
			return ($this->response->resp(["bad token or bad api key"], 406, ["type" => "none"], number_format(microtime(true) - $t0, 5)));
		if (!in_array($categ, self::CATEG))
			return ($this->response->resp(["bad categorie"], 404, ["type" => "none"], number_format(microtime(true) - $t0, 5)));
		return ($this->response->resp($this->getCateg($categ), 200, ["type" => "none"], number_format(microtime(true) - $t0, 5)));
	}

	private function getAllContent() : array {

		$content = [];

		foreach (self::CATEG as $key => $value) {
			
			$content[$value] = $this->getCateg($value);
		}

		return ($content);
	}

	public function getCateg(string $categ) {

		$dom = new DOMDocument();
		@ $dom->loadHTML(file_get_contents(self::ENDPOINT.self::CATEG_ENDPOINT.$categ));
		$finder = new DomXPath($dom);
		$classname = "view-content";
		$nodes = $finder->query("//*[contains(@class, '$classname')]");
		$elems = $nodes[0]->getElementsByTagName('li');
		foreach($elems as $elem) {
			$datas[] = [
				"img" => substr(self::ENDPOINT, 0, -1).$elem->getElementsByTagName('img')[0]->getAttribute('src'),
				"name" => $elem->getElementsByTagName('h4')[0]->nodeValue
			];
		}
		return ($datas);
	}

}

?>
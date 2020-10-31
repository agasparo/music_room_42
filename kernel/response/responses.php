<?php

Class Responses {

	public function resp(array $content, int $status, array $encode, $time) {

		$this->show_json([
			"status" => $status,
			"number_results" => count($content),
			"response_time" => $time . " seconde(s)",
			"encode" => $encode,
			"response" => $content
		]);
	}

	private function show_json(array $response) {

		echo json_encode($response);
	}
}

?>
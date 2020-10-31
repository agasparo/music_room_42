class api_request {

	senGetRequest = (url, params) => {

		document.getElementById('response_test').style.display = "none";
		document.querySelector('.loader').style.display = "block";

		$.get(url, params, function(content) {

			document.querySelector('.loader').style.display = "none";
			document.getElementById('response_test').style.display = "block";
			content = JSON.parse(content);
			$('#response_test').jsonViewer(content);
		});
	}
}

export default new api_request();
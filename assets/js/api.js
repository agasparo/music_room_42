import apiRequest from '/music_room/assets/js/class/api_request.js';

window.onload = () => {

	let token = null;
	let api_key = null;

	$.post('/music_room/api_token_get', {}, function (data) {

		data = JSON.parse(data);
		token = data.token;
		api_key = data.api_key;
		document.getElementById('token_visu').value = token;
		document.getElementById('api_id_visu').value = api_key;
	});

	document.getElementById('generateKeys').onclick = () => {

		$.post('/music_room/api_token', {}, function (data) {

			data = JSON.parse(data);
			token = data.token;
			api_key = data.api_key;
			document.getElementById('token_visu').value = token;
			document.getElementById('api_id_visu').value = api_key;
		});
	}

	// ************************* api call ***********************//

	const params = document.getElementById('params_response');
	
	document.getElementById('lyrics_all').onclick = () => {

		params.innerHTML = "";
		const text = document.createElement('p');
		text.innerText = "Pas besoin de parametres";
		params.append(text);
		addSender(sendParamsLyricsAll, params);
	}

	document.getElementById('lyrics_name').onclick = () => {

		params.innerHTML = "";
		const input = document.createElement('input');
		input.value = "dababy";
		input.id = "name_lyrics";
		input.type = "text";
		input.placeholder = "titre de la musique";
		input.classList.add('form-control');
		input.classList.add('input_test');
		params.append(input);
		addSender(sendParamsLyricsName, params);
	}

	document.getElementById('mcdo_name').onclick = () => {

		params.innerHTML = "";
		const input = document.createElement('input');
		input.value = "salades";
		input.id = "name_mcdo";
		input.type = "text";
		input.placeholder = "categorie";
		input.classList.add('form-control');
		input.classList.add('input_test');
		params.append(input);
		addSender(sendParamsMcdoName, params);
	}

	document.getElementById('mcdo_all').onclick = () => {

		params.innerHTML = "";
		const text = document.createElement('p');
		text.innerText = "Pas besoin de parametres";
		params.append(text);
		addSender(sendParamsMcdoAll, params);
	}

	document.getElementById('music_stats').onclick = () => {

		params.innerHTML = "";
		const text = document.createElement('p');
		text.innerText = "Pas besoin de parametres";
		params.append(text);
		addSender(sendParamsMusicAllStats, params);
	}

	document.getElementById('music_all').onclick = () => {

		params.innerHTML = "";
		const text = document.createElement('p');
		text.innerText = "Pas besoin de parametres";
		params.append(text);
		addSender(sendParamsMusicAll, params);
	}

	document.getElementById('music_id').onclick = () => {

		params.innerHTML = "";
		const input = document.createElement('input');
		input.value = "83xBPCw5hh4";
		input.id = "id_music";
		input.type = "text";
		input.placeholder = "categorie";
		input.classList.add('form-control');
		input.classList.add('input_test');
		params.append(input);
		addSender(sendParamsMusicId, params);
	}

	document.getElementById('music_title').onclick = () => {

		params.innerHTML = "";
		const input = document.createElement('input');
		input.value = "a";
		input.id = "title_music";
		input.type = "text";
		input.placeholder = "categorie";
		input.classList.add('form-control');
		input.classList.add('input_test');
		params.append(input);
		addSender(sendParamsMusicTitle, params);
	}

	document.getElementById('music_artist').onclick = () => {

		params.innerHTML = "";
		const input = document.createElement('input');
		input.value = "naps";
		input.id = "artist_music";
		input.type = "text";
		input.placeholder = "categorie";
		input.classList.add('form-control');
		input.classList.add('input_test');
		params.append(input);
		addSender(sendParamsMusicArtist, params);
	}

	function sendParamsMusicId(e) {

		e.preventDefault();
		const name = document.getElementById('id_music').value;

		if (!name)
			return;

		apiRequest.senGetRequest(
			"/music_room/api/music/id/" + name,
			{
				myToken: token,
				apiKey: api_key
			}
		);
	}

	function sendParamsMusicTitle(e) {

		e.preventDefault();
		const name = document.getElementById('title_music').value;

		if (!name)
			return;

		apiRequest.senGetRequest(
			"/music_room/api/music/title/" + name,
			{
				myToken: token,
				apiKey: api_key
			}
		);
	}

	function sendParamsMusicArtist(e) {

		e.preventDefault();
		const name = document.getElementById('artist_music').value;

		if (!name)
			return;

		apiRequest.senGetRequest(
			"/music_room/api/music/artist/" + name,
			{
				myToken: token,
				apiKey: api_key
			}
		);
	}

	function sendParamsMusicAll(e) {

		e.preventDefault();
		apiRequest.senGetRequest(
			"/music_room/api/music/all",
			{
				myToken: token,
				apiKey: api_key
			}
		);
	}

	function sendParamsMusicAllStats(e) {

		e.preventDefault();
		apiRequest.senGetRequest(
			"/music_room/api/music/statistic",
			{
				myToken: token,
				apiKey: api_key
			}
		);
	}

	function sendParamsMcdoName(e) {

		e.preventDefault();
		const name = document.getElementById('name_mcdo').value;

		if (!name)
			return;

		apiRequest.senGetRequest(
			"/music_room/api/mcdo/categ/" + name,
			{
				myToken: token,
				apiKey: api_key
			}
		);
	}

	function sendParamsMcdoAll(e) {

		e.preventDefault();
		apiRequest.senGetRequest(
			"/music_room/api/mcdo/all",
			{
				myToken: token,
				apiKey: api_key
			}
		);
	}

	function sendParamsLyricsName(e) {

		e.preventDefault();
		const name = document.getElementById('name_lyrics').value;

		if (!name)
			return;

		apiRequest.senGetRequest(
			"/music_room/api/lyrics/get_by_name/" + name,
			{
				myToken: token,
				apiKey: api_key
			}
		);
	}

	function sendParamsLyricsAll(e) {

		e.preventDefault();
		apiRequest.senGetRequest(
			"/music_room/api/lyrics/all",
			{
				myToken: token,
				apiKey: api_key
			}
		);
	}

	function addSender(functions, parent) {

		const sender = document.createElement('button');
		sender.type = "submit";
		sender.classList.add('btn');
		sender.classList.add('btn-primary');
		sender.innerText = "Tester";
		sender.onclick = functions;
		parent.append(sender);
	}
}
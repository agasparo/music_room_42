import Deezer from '/music_room/assets/js/class/deezer.js';

window.onload = () => {

	const api_key = "TOKEN";
	const btn_add = document.getElementById('add_music_btn');

	if (btn_add) {
		btn_add.onclick = () => {

			const music_val = document.getElementById('music_pref_add').value;
			if (music_val === "")
				return;

			$.get('https://www.googleapis.com/youtube/v3/search', {
				key: api_key,
			    q: music_val,
			    part: "snippet",
			    maxResults: 1,
			    type: "video",
			    format: 5
			}, function (data) {

				if (!data.items)
					return;
				addToBdd(data.items[0].id.videoId, data.items[0].snippet.title, data.items[0].snippet.thumbnails.high.url);
			});
		}
	}

	function addToBdd(id_music, title_music, image_music) {

		$.post('/music_room/user/music_add', {
			id: id_music,
			title: title_music,
			img: image_music
		}, function (content) {
			content = JSON.parse(content);
			if (content.response === "added") {
				updateImg(image_music, content.id)
				createElement(id_music, renameTitle(title_music));
			}
		});
	}

	function renameTitle(title) {

		title = title.replace("/", "").replace("Clip Officiel", "");
		if (title.indexOf("(") !== -1) {
			title = title.substr(0, title.indexOf("("));
			return (title);
		}
		if (title.indexOf("[") !== -1) {
			title = title.substr(0, title.indexOf("["));
			return (title);
		}
		return (title);

	}

	function updateImg(image_music, id) {

		document.getElementById("img_" + id).src = image_music;
	}

	function createElement(id_music, title_music) {

		const tr = document.createElement('tr');
		const th = document.createElement('th');
		const td = document.createElement('td');

		tr.classList.add("table-active");
		th.scope = "row";
		th.innerText = id_music;
		td.innerText = title_music;
		tr.append(th);
		tr.append(td);
		document.getElementById("recept_music").append(tr);
	}

	const change_show = document.getElementById("save_data_show");

	if (change_show) {

		change_show.onclick = (e) => {

			$.post('/music_room/user/user_show', {
				new_perm : document.getElementById('setShow').value,
				name: document.getElementById('prenom_user').value,
				surname: document.getElementById('nom_user').value
			});
		}
	}

	const add_friend = document.getElementById("add_friend");

	if (add_friend) {

		add_friend.onclick = () => {

			$.post('/music_room/user/add_friend', {
				user: document.getElementById('user_here').innerText
			}, function (data) {
				location.reload();
			});
		}
	}

	const deezerConnexion = document.getElementById("deezer_connexion");
	const googleConnexion = document.getElementById("google_connexion");

	if (deezerConnexion) {

		deezerConnexion.onclick = () => {

			Deezer.attach();
		}
	}

	if (googleConnexion) {

		googleConnexion.onclick = () => {

			$.get('/music_room/google_attach', {}, function (data) {
				data = JSON.parse(data);
				window.location = data.response;
			});
		}
	}

	const change_img  = document.getElementById('change_picture');

	if (change_img) {

		change_img.onclick = () => {
			const target = document.createElement('input');
			target.type = 'file';
			target.onchange = changeImg;
			target.style.display = 'none';
			document.body.append(target);
			target.click();
		}
	}

	function changeImg(e) {

		const form_data = new FormData();
		form_data.append("files", e.target.files[0]);
		e.target.remove();

		const request = new XMLHttpRequest();
		request.open("POST", "/music_room/user/update_image");
		request.send(form_data);

		request.onreadystatechange = () => {

    		if (request.readyState === 4) {
      			const data = JSON.parse(request.response);
      			if (data.response === "good") {
      				location.reload();
      			} else {
      				document.getElementById('img_error').innerText = data.response;
      				document.getElementById('img_error').style.display = "block";
      				setTimeout(function() {
      					if (document.getElementById('img_error')) {
      						document.getElementById('img_error').innerText = "";
      						document.getElementById('img_error').style.display = "none";
      					}
      				}, 2000);
      			}
    		}
  		}
	}
}
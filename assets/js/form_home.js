import Response from "/music_room/assets/js/class/response.js";
import MusicTask from "/music_room/assets/js/class/music_home.js";
import DeezerI from "/music_room/assets/js/class/deezer.js";

window.onload = () => {

	MusicTask.search("playlist", 8, "music 2020", "", "music_top");
	MusicTask.search("playlist", 8, "music electro", "", "playlist_top");

	const form = document.getElementById('form_ins_co');
	if (form) {

		document.getElementById('post_form_co').onclick = (e) => {

			e.preventDefault();
			$.post("/music_room/connect", {
				mail: document.getElementById('mail_co').value,
				password: document.getElementById('pass_co').value
			}, (data) => {

				Response.parse(JSON.parse(data));
			});
		}
	}

	const ab_off = document.getElementById('abonnement_off');

	if (ab_off) {

		ab_off.onclick = (e) => {

			e.preventDefault();
			location.reload();
		}

		document.getElementById('abonnement_on').onclick = (e) => {

			e.preventDefault();
			window.location = "/music_room/prime";
		}
	}

	const list_room = document.querySelectorAll(".room_object");
	if (list_room) {
		for (let i = 0; i < list_room.length; i++) {
		
			list_room[i].onclick = room_goOn;
		}
	}

	const google_co = document.getElementById("google_connexion");
	if (google_co) {

		google_co.onclick = () => {

			$.get('google_connexion', {}, function(content) {

				content = JSON.parse(content);
				if (content.response !== "login")
					window.location = content.response;
			});
		}
	}

	const fb_co = document.getElementById("facebook_connexion");
	if (fb_co) {

		fb_co.onclick = () => {

			$.get('facebook_connexion', {}, function(content) {

				content = JSON.parse(content);
				if (content.response !== "login")
					window.location = content.response;
			});
		}
	}

	const de_co = document.getElementById("deezer_connexion");
	if (de_co) {

		de_co.onclick = () => {

			DeezerI.login();
		}
	}

	document.getElementById("new_room").onclick = () => {
		
		document.getElementById("room_new_add").style.display = "block";
	}

	document.getElementById('stop_room_form').onclick = () => {

		document.getElementById("room_new_add").style.display = "none";
	}

	document.getElementById('create_new_room').onclick = () => {

		const form_data = new FormData();
		form_data.append("files", document.getElementById('room_image').files[0]);
		form_data.append("room_name", document.getElementById('room_name').value);
		form_data.append("room_type", document.getElementById('room_type').value);
		form_data.append("room_music", document.getElementById('room_music').value);

		const request = new XMLHttpRequest();
		request.open("POST", "/music_room/rooms_create");
		request.send(form_data);

		request.onreadystatechange = () => {

    		if (request.readyState === 4) {
      			const data = JSON.parse(request.response);
      			if (data.response === "room created") {
      				window.location = '/music_room/room/' + data.room_id;
      			} else {
      				document.getElementById('room_created_error').innerText = data.response;
      				setTimeout(function() {
      					if (document.getElementById('room_created_error'))
      						document.getElementById('room_created_error').innerText = data.response;
      				}, 2000);
      			}
    		}
  		}
	}
}

window.room_goOn = (e) => {

	let elem = e.target;
	if (!elem.dataset.id_room)
		elem = elem.parentElement;
	window.location = "/music_room/room/" + parseInt(elem.dataset.id_room); 
}
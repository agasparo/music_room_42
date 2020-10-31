class sockets {

	constructor(conn) {

		this.conn = conn;
		this.ok = 0;
		this.conn.onopen = this.connectionEstablished;
		this.conn.onmessage = this.getMessage;
		this.Chat = null;
		this.Playlist = null;
		this.Lecteur = null;
		this.uniqId = Date.now() + Math.random();
		this.isparams = 0;
		this.saveparams = null;
	}

	setClasses = (Chat, Playlist, Lecteur) => {

		this.Chat = Chat;
		this.Playlist = Playlist;
		this.Lecteur = Lecteur;
	}

	closeConnection = () => {

		this.sendMessage({
			message: "leave room",
			room_id: document.getElementById('rooms_members').dataset.room
		}, true);
	}

	connectionEstablished = () => {

		this.ok = 1;
		this.sendMessage({
			message: "comming into room",
			room_id: document.getElementById('rooms_members').dataset.room,
			to: this.uniqId
		}, true);
	}

	sendMessage = (message, type = false) => {

		message.type = type;
		if (this.ok === 1)
			this.conn.send(JSON.stringify(message));
	}

	sendLecteurParams = (params) => {

		this.sendMessage({
			message: "send params",
			room_id: params.room_id,
			to: params.to,
			state: this.Lecteur.getPlayerS(),
			music: this.Lecteur.current_song,
			time: this.Lecteur.getPlayerT(),
		}, true);
	}

	setLecteurParams = (params) => {

		if (this.saveparams && params.to === this.uniqId) {
			const content = this.Playlist.getVideo(parseInt(this.saveparams.music));
			this.Lecteur.setVideoData(content);
			this.Lecteur.loadVideo(content.id, this.saveparams.time + 2, parseInt(this.saveparams.music))
			if (this.saveparams.state < 1 || this.saveparams.state === 2)
				this.Lecteur.pauseVideo();
		} else {
			this.saveparams = params;
		}
	}

	reloadMembers = (room_id) => {

		const that = this;

		$.post('/music_room/user_update', {
			id_room: room_id
		}, function (content) {
			content = JSON.parse(content);
			if (content.status === 200) {
				$('#rooms_members').html('<i class="fa fa-arrow-left close_members" aria-hidden="true" id="close_m"></i><br><br>' + content.response).promise().done(function(){
    				window.RightsInit();
    				document.getElementById('close_m').onclick = () => { $("#rooms_members").toggle("slide"); }
				});
			}
		});
	}

	refreshvote = (room_id) => {

		const that = this;

		$.post('/music_room/refresh_vote', {
			id_room: room_id
		}, function (data) {
			data = JSON.parse(data);
			if (data.status === 200) {
				if (data.response === "admise") {
					that.Playlist.search(data.name_song, room_id);
				}
				that.Chat.getChat();
			}
		});
	}

	getMessage = (event) => {

		const response = JSON.parse(event.data);
		const current_id = document.getElementById('rooms_members').dataset.room;
		const that = this;

		if (current_id === response.room_id) {

			if (response.message === "leave now") {
				this.refreshvote(response.room_id);
				this.leaveOnUser(response.room_id);
			}
			if (response.message === "new chat")
				this.Chat.getChat();
			if (response.message === "comming into room") {
				this.sendLecteurParams(response);
				this.reloadMembers(response.room_id);
				document.getElementById('AlertMembers').innerText = "Dites Bonjour ! un nouveau membre vient d'arriver sur la room";
				$('#AlertMembers').fadeToggle();
				setTimeout(function(){ 
					$('#AlertMembers').fadeToggle();
				}, 2000);
			}
			if (response.message === "leave room") {
				setTimeout(function() {
					that.reloadMembers(response.room_id);
					that.refreshvote(response.room_id);
				}, 2000);
				document.getElementById('AlertMembers').innerText = "Dites Au revoir ! un membre vient de quitter la room";
				$('#AlertMembers').fadeToggle();
				setTimeout(function(){ 
					$('#AlertMembers').fadeToggle();
				}, 2000);
			}
			if (response.message === "new playlist song") {
				const myNode = document.getElementById("room_playlist_current");
				while (myNode.firstChild) {
					myNode.lastChild.removeEventListener('click', this.Playlist.otherSong);
					myNode.removeChild(myNode.lastChild);
				}
				this.Playlist.list = [];
				this.Playlist.init("" ,current_id, this.Lecteur);
			}
			if (response.message === "change sound rights") {

				const elems = document.querySelectorAll(".sound_change");
				this.setItems(elems, response);
			}
			if (response.message === "change vote rights") {
				
				const elems = document.querySelectorAll(".rights_change");
				this.setItems(elems, response);
			}
			if (response.message === "change playlist rights") {
				
				const elems = document.querySelectorAll(".playlist_change");
				this.setItems(elems, response);
			}
			if (response.message === "send params" || response.message === "playerReady") {
				this.setLecteurParams(response);
			}
			if (response.message === "soundcontrol") {
				$("#" + response.click).trigger('click', true);
			}
			if (response.message === "playlistcontrol") {
				this.Lecteur.loadVideo(response.video, response.time, response.target);
			}
			if (response.message === "change_params") {
				if (response.name_input === "h_fin") {
					document.getElementById('params_h_f').value = response.value_input;
				} else if (response.name_input === "h_debut") {
					document.getElementById('params_h_d').value = response.value_input;
				} else if (response.name_input === "public") {
					document.getElementById('public_privee').checked = !response.value_input;
				} else if (response.name_input === "loc") {
					document.getElementById('loc_input').value = response.value_input;
				}
				document.getElementById('params_status').innerText = response.value_text;
			}
			if (response.message === "playlist_change") {
				this.Playlist.createFromSocket(response.room_id);
			}
		}
	}

	leaveOnUser = (room) => {

		$.post('/music_room/user_can_stay', {
			id_room: room
		}, function(data) {
			data = JSON.parse(data);
			if (data.response === "leave")
				window.location = "/music_room/";
		});
	}

	setItems = (elems, response) => {

		const current_change = elems[parseInt(response.change)];
		if (!response.rights) {
			current_change.classList.remove("badge-success");
			current_change.classList.add("badge-danger");
			current_change.removeChild(current_change.firstChild);
			current_change.innerHTML = '<i class="fa fa-times" aria-hidden="true"></i>';
		} else {
			current_change.classList.remove("badge-danger");
			current_change.classList.add("badge-success");
			current_change.removeChild(current_change.firstChild);
			current_change.innerHTML = '<i class="fa fa-check" aria-hidden="true"></i>';
		}
	}
}

export default new sockets(new WebSocket('ws://localhost:4242'));
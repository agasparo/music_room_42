const keytoken = "TOKEN";

let home = "0.0.0.0:8080";
if (window.location.hostname !== "lvh.me")
	home = "cors-anywhere.herokuapp.com";

class playlist {

	constructor() {

		this.list = [];
		this.parent = document.getElementById("room_playlist_current");
		this.lecteur = null;
		this.Sockets = null;
		this.SoundControl = null;
		this.PlaylistControl = null;
	}

	initSockets = (Sockets, SoundControl, PlaylistControl) => {

		const that = this;

		this.SoundControl = SoundControl;
		this.Sockets = Sockets;
		this.PlaylistControl = PlaylistControl;
	}

	init = (videoId, room_id, lecteur) => {

		const that = this;

		this.lecteur = lecteur;

		if (this.PlaylistControl.asRights() > 0) {
    		$("#room_playlist_current").sortable({
    			revert: true,
    			update: function(e, ui) {
    				that.changePlaylist(e.target.querySelectorAll(".div_playlist"));
			    }
    		});
    	}

		$.post('/music_room/playlistiscreated', {
			id_room: room_id
		}, function (data) {
			data = JSON.parse(data);
			if (data.status === 200) {

				if (data.response === "no playlist")
					that.getDataOfVideo(videoId, room_id);
				else
					that.createElementFomBdd(data);
			}
		});
	}

	changeItem = (list, id) => {

		for (let i = 0; i < list.length; i++) {

			if (list[i].id === id)
				return (list[i]);
		}
		return (0);
	}

	changePlaylist = (elems) => {

		const new_playlist = [];
		const that = this;
		const current_list = this.list;
		const n_list = []

		for (let i = 0; i < elems.length; i++) {
			const playlistItems = {
				video: elems[i].dataset.video,
				pos: i
			}
			if (elems[i].dataset.video !== this.list[i].id)
				n_list[i] = this.changeItem(current_list, elems[i].dataset.video);
			else
				n_list[i] = this.list[i];
			elems[i].dataset.pos = i;
			new_playlist[i] = playlistItems;
		}
		this.list = n_list;
		$.post('/music_room/playlistchange', {
			room_id: document.getElementById('rooms_members').dataset.room,
			n_playlist: JSON.stringify(new_playlist),
		}, function (data) {
			that.Sockets.sendMessage({
				message: "playlist_change",
				room_id: document.getElementById('rooms_members').dataset.room,
			}, true);
		});
	}

	createFromSocket = (room_id) => {

		const that = this;

		const myNode = document.getElementById('room_playlist_current');
		while (myNode.firstChild) {
    		myNode.removeChild(myNode.lastChild);
  		}
		$.post('/music_room/playlistiscreated', {
			id_room: room_id
		}, function (data) {
			data = JSON.parse(data);
			if (data.status === 200) {
				that.list = [];
				that.createElementFomBdd(data, 1);
			}
		});
	}

	createElementFomBdd = (data, type = 0) => {

		data = JSON.parse(data.response);
		for (let i = 0; i < data.length; i++) {
			const elem = document.createElement("div");
			const para = document.createElement("p");
			const img = document.createElement("img");
			
			this.addVideoToList({
				name: data[i].song_name,
				id: data[i].song_id,
				img: data[i].song_img,
				timer: data[i].song_timer
			});
			if (i === 0)
				this.lecteur.SetImgOfFisrt(this.list[0]);

			para.innerText = data[i].song_name;
			para.classList.add("txt_playlist");
			img.src = data[i].song_img;
			img.classList.add("img_playlist");
			elem.dataset.video = data[i].song_id;
			elem.classList.add("div_playlist");
			elem.dataset.pos = document.querySelectorAll('.txt_playlist').length;
			elem.append(para);
			elem.append(img);
			elem.addEventListener('click', this.otherSong);
			if (this.PlaylistControl.asRights() > 0) {
				$(".div_playlist").draggable({
					containment: "parent",
					cursor: "crosshair",
					connectToSortable: "#room_playlist_current",
				});
			}
			this.parent.append(elem);
		}
		if (this.Sockets && type == 0)
			this.Sockets.sendMessage({
				message: "playerReady",
				room_id: document.getElementById('rooms_members').dataset.room,
				to: this.Sockets.uniqId
			});
	}

	addSoundWave = (elem) => {

		const removeElem = document.querySelector('.playing');
		if (removeElem)
			removeElem.remove();
		const playing = document.createElement('div');
		playing.classList.add('playing');
		for (let i = 0; i < 3; i++) {
			const bar = document.createElement('div');
			bar.classList.add('bar_playing');
			playing.append(bar)	
		}
		elem.append(playing);
	}

	otherSong = (e) => {

		if (this.SoundControl.asRights() < 1)
			return;
		let elem = e.target;
		if (e.target.dataset.length === undefined)
			elem = e.target.parentElement;
		this.addSoundWave(elem);
		this.lecteur.loadVideo(elem.dataset.video, 0, elem.dataset.pos);
		this.Sockets.sendMessage({
			message: "playlistcontrol",
			room_id: document.getElementById('rooms_members').dataset.room,
			video: elem.dataset.video,
			time: 0,
			target: elem.dataset.pos
		});
	}

	getDataOfVideo = (videoId, room_id) => {

		const that = this;

		$.get('https://www.googleapis.com/youtube/v3/videos', {
			key : keytoken,
			part: "snippet",
			id: videoId
		}, function(data) {

			that.createElement(data, videoId, room_id);
		});
	}

	createElement = (data, videoId, room_id) => {

		if (!data.items)
			return;

		const elem = document.createElement("div");
		const para = document.createElement("p");
		const title = this.renameTitle(data.items[0].snippet.title);
		
		para.innerText = title;
		elem.dataset.video = videoId;
		elem.dataset.pos = document.querySelectorAll('.txt_playlist').length;
		elem.classList.add("div_playlist");
		para.classList.add("txt_playlist");

		elem.append(para);
		elem.addEventListener('click', this.otherSong);
		if (this.PlaylistControl.asRights() > 0) {
			$(".div_playlist").draggable({
				containment: "parent",
				cursor: "crosshair",
				connectToSortable: "#room_playlist_current",
			});
		}
		this.getPochetteImg(elem, title, videoId, room_id);
	}

	renameTitle = (title) => {

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

	getPochetteImg = (elem, title, videoId, id_room) => {

		const img = document.createElement("img");
		const that = this;

		$.get('http://'+home+'/https://api.deezer.com/search/track?q=' + title, function(content) {

			if (!content.data[0]) {
				that.getPochetteImg(elem, title.split("-")[1], videoId, id_room);
				return;
			}
			img.src = content.data[0].album.cover_medium;
			img.classList.add("img_playlist");
			that.addVideoToList({
				name: title,
				id: videoId,
				img: content.data[0].album.cover_medium,
				timer: 0
			});
			if (that.list.length === 1)
				that.lecteur.SetImgOfFisrt(that.list[0]);
			$.post('/music_room/playlistadd', {
				song_name: title,
				song_id: videoId,
				song_img: content.data[0].album.cover_medium,
				room_id: id_room
			}, function() {
				that.Sockets.sendMessage({
					message: "new playlist song",
					room_id: document.getElementById('rooms_members').dataset.room
				}, true);
			});
			elem.append(img);
			that.parent.append(elem);
		});
	}

	addVideoToList = (videoData) => {

		this.list[this.list.length] = videoData;
	}

	getVideo = (pos) => {

		return (this.list[pos]);
	}

	getSize = () => {

		return (this.list.length);
	}

	search = (song_name_s, id_room) => {

		const that = this;

		$.get('https://www.googleapis.com/youtube/v3/search', {
			key: keytoken,
		    q: song_name_s,
		    part: "snippet",
		    maxResults: 1,
		    type: "video",
		    format: 5
		}, function (data) {

			if (!data.items)
				return;
			that.createElement(data, data.items[0].id.videoId, id_room);
		});
	}
}

export default new playlist();
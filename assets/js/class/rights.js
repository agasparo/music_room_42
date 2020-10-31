class rights {

	constructor() {
		this.SoundControl = null;
		this.Sockets = null;
		this.PlaylistControl = null;
	}

	init = (SoundControl, Sockets, PlaylistControl, Playlist) => {

		this.Sockets = Sockets;
		this.SoundControl = SoundControl;
		this.PlaylistControl = PlaylistControl;
		this.Playlist = Playlist;

		this.addEvent(document.querySelectorAll(".modify_rights"), 1);
		this.addEvent(document.querySelectorAll(".modify_playlist"), 2);
		this.addEvent(document.querySelectorAll(".leave_room"), 3);
		this.addEvent(document.querySelectorAll(".modify_sound"), 4);
	}
	addEvent = (data, type) => {

		for (let i = 0; i < data.length; i++) {

			if (type === 1)
				data[i].addEventListener('click', this.ChangeVote);
			if (type === 2)
				data[i].addEventListener('click', this.ChangePlaylistEdit);
			if (type === 3)
				data[i].addEventListener('click', this.LeaveSomebody);
			if (type === 4)
				data[i].addEventListener('click', this.ChangeSoundControl);
		}
	}

	LeaveSomebody = (e) => {

		let elem = null;
		if (e.target.classList.contains('fa'))
			elem = e.target.parentElement;
		else
			elem = e.target;
		$.post('/music_room/leave_user/', {
			id_room: document.getElementById("rooms_members").dataset.room,
			pos_user: elem.dataset.pos
		});
		this.Sockets.sendMessage({
			message: "leave now",
			room_id: document.getElementById("rooms_members").dataset.room
		}, true);
	}

	ChangeSoundControl = (e) => {

		const that = this;

		let elem = null;
		if (e.target.classList.contains('fa'))
			elem = e.target.parentElement;
		else
			elem = e.target;
		let right_state = false;
		if (elem.classList.contains('badge-success'))
			right_state = true;
		$.post('/music_room/modify_rights', {
			type: 'vote',
			elem_state: right_state,
			room_id: document.getElementById("rooms_members").dataset.room,
			currentUser: elem.dataset.pos,
			replace: 'sound_control'
		}, function (data) {
			data = JSON.parse(data);
			if (data.status === 200) {
				if (right_state) {
					elem.classList.remove("badge-success");
					elem.classList.add("badge-danger");
					elem.removeChild(elem.firstChild);
					elem.innerHTML = '<i class="fa fa-times" aria-hidden="true"></i>';
				} else {
					elem.classList.remove("badge-danger");
					elem.classList.add("badge-success");
					elem.removeChild(elem.firstChild);
					elem.innerHTML = '<i class="fa fa-check" aria-hidden="true"></i>';
				}
				that.SoundControl.setRights(!right_state);
				that.Sockets.sendMessage({
					message: "change sound rights",
					room_id: document.getElementById('rooms_members').dataset.room,
					rights: !right_state,
					change: elem.dataset.pos
				}, true);
			}
		});
	}

	ChangeVote = (e) => {

		const that = this;

		let elem = null;
		if (e.target.classList.contains('fa'))
			elem = e.target.parentElement;
		else
			elem = e.target;
		let right_state = false;
		if (elem.classList.contains('badge-success'))
			right_state = true;
		$.post('/music_room/modify_rights', {
			type: 'vote',
			elem_state: right_state,
			room_id: document.getElementById("rooms_members").dataset.room,
			currentUser: elem.dataset.pos,
			replace: 'vote_playlist'
		}, function (data) {
			data = JSON.parse(data);
			if (data.status === 200) {
				if (right_state) {
					elem.classList.remove("badge-success");
					elem.classList.add("badge-danger");
					elem.removeChild(elem.firstChild);
					elem.innerHTML = '<i class="fa fa-times" aria-hidden="true"></i>';
				} else {
					elem.classList.remove("badge-danger");
					elem.classList.add("badge-success");
					elem.removeChild(elem.firstChild);
					elem.innerHTML = '<i class="fa fa-check" aria-hidden="true"></i>';
				}
				that.Sockets.sendMessage({
					message: "change vote rights",
					room_id: document.getElementById('rooms_members').dataset.room,
					rights: !right_state,
					change: elem.dataset.pos
				}, true);
			}
		});
	}

	ChangePlaylistEdit = (e) => {

		const that = this;

		let elem = null;
		if (e.target.classList.contains('fa'))
			elem = e.target.parentElement;
		else
			elem = e.target;
		let right_state = false;
		if (elem.classList.contains('badge-success'))
			right_state = true;
		$.post('/music_room/modify_rights', {
			type: 'vote',
			elem_state: right_state,
			room_id: document.getElementById("rooms_members").dataset.room,
			currentUser: elem.dataset.pos,
			replace: 'edit_playlist'
		}, function (data) {
			data = JSON.parse(data);
			if (data.status === 200) {
				if (right_state) {
					elem.classList.remove("badge-success");
					elem.classList.add("badge-danger");
					elem.removeChild(elem.firstChild);
					elem.innerHTML = '<i class="fa fa-times" aria-hidden="true"></i>';
				} else {
					elem.classList.remove("badge-danger");
					elem.classList.add("badge-success");
					elem.removeChild(elem.firstChild);
					elem.innerHTML = '<i class="fa fa-check" aria-hidden="true"></i>';
				}
				that.PlaylistControl.setRights(!right_state);
				if (that.PlaylistControl.asRights() > 0) {
		    		$("#room_playlist_current").sortable({
		    			revert: true,
		    			update: function(e, ui) {
		    				that.Playlist.changePlaylist(e.target.querySelectorAll(".div_playlist"));
					    }
		    		});
		    		$(".div_playlist").draggable({
						containment: "parent",
						cursor: "crosshair",
						connectToSortable: "#room_playlist_current",
					});
		    	} else {
		    		$('.div_playlist').draggable("disable");
		    		$('#room_playlist_current').sortable("disable");
		    	}
				that.Sockets.sendMessage({
					message: "change playlist rights",
					room_id: document.getElementById('rooms_members').dataset.room,
					rights: !right_state,
					change: elem.dataset.pos
				}, true);
			}
		});
	}
}

export default new rights();
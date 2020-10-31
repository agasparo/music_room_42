class params {

	constructor() {
		this.Sockets = null;
	}

	init = (Sockets) => {

		this.Sockets = Sockets;
		document.getElementById('public_privee').onchange = this.visibilite;
		document.getElementById('params_h_d').onchange = this.date_debut;
		document.getElementById('params_h_f').onchange = this.date_fin;
		document.getElementById('params_loca').onclick = this.getGeoloc;
		document.getElementById('params_loca_r').onclick = this.getGeolocRemove;
	}

	getGeolocRemove = () => {

		const that = this;

		$.post('/music_room/update_loc', {
			id_room: document.getElementById('rooms_members').dataset.room,
			rm: 1
		}, function(data) {
			data = JSON.parse(data);
			if (data.status === 200) {
				document.getElementById('params_status').innerText = data.response;
				document.getElementById('loc_input').value = data.text;
				that.Sockets.sendMessage({
					room_id: document.getElementById('rooms_members').dataset.room,
					message: "change_params",
					name_input: "loc",
					value_input: data.text,
					value_text: data.response
				}, true);
			}
		});
	}

	getGeoloc = () => {

		const that = this;

		$.post('/music_room/update_loc', {
			id_room: document.getElementById('rooms_members').dataset.room
		}, function(data) {
			data = JSON.parse(data);
			if (data.status === 200) {
				document.getElementById('params_status').innerText = data.response;
				document.getElementById('loc_input').value = data.text;
				that.Sockets.sendMessage({
					room_id: document.getElementById('rooms_members').dataset.room,
					message: "change_params",
					name_input: "loc",
					value_input: data.text,
					value_text: data.response
				}, true);
			}
		});
	}

	date_debut = (e) => {

		let f = document.getElementById('params_h_f').value;
		let current_v = e.target.value;

		if (parseInt(current_v) === 0 && parseInt(f) === 0) {
			this.pushchange(0, "h_debut");
			this.pushchange(0, "h_fin");
			return;
		}

		if (parseInt(current_v) >= parseInt(f)) {
			document.getElementById('params_h_f').value = parseInt(current_v) + 1;
			this.pushchange(parseInt(current_v) + 1 , "h_fin");
			this.pushchange(current_v, "h_debut");
		}

		if (parseInt(current_v) > 23)
			e.target.value = 23;
		if (parseInt(current_v) < 1)
			e.target.value = 1;

		if (parseInt(f) > parseInt(e.target.value) && parseInt(e.target.value) <= 23 && parseInt(e.target.value) >= 1)
			this.pushchange(e.target.value, "h_debut");
	}

	date_fin = (e) => {

		let d = document.getElementById('params_h_d').value;
		let current_v = e.target.value;

		if (parseInt(current_v) === 0 && parseInt(d) === 0) {
			this.pushchange(0, "h_debut");
			this.pushchange(0, "h_fin");
			return;
		}

		if (parseInt(current_v) <= parseInt(d)) {
			document.getElementById('params_h_d').value = parseInt(current_v) - 1;
			this.pushchange(parseInt(current_v) - 1, "h_debut");
			this.pushchange(current_v, "h_fin");
		}

		if (parseInt(current_v) > 24)
			e.target.value = 24;
		if (parseInt(current_v) < 2)
			e.target.value = 2;

		if (parseInt(d) < parseInt(e.target.value) && parseInt(e.target.value) <= 24 && parseInt(e.target.value) >= 2)
			this.pushchange(e.target.value , "h_fin");
	}

	visibilite = (e) => {

		this.pushchange(!e.target.checked, "public");
	}

	pushchange = (new_val, name_change) => {

		const that = this;

		$.post('/music_room/update_params', {

			id_room:document.getElementById('rooms_members').dataset.room,
			value: new_val,
			name: name_change
		}, function(data) {
			data = JSON.parse(data);
			if (data.status === 200) {
				document.getElementById('params_status').innerText = data.response;
				that.Sockets.sendMessage({
					room_id: document.getElementById('rooms_members').dataset.room,
					message: "change_params",
					name_input: name_change,
					value_input: new_val,
					value_text: data.response
				}, true);
			}
		});
	}
}

export default new params();
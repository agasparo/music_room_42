class SoundControl {


	constructor() {

		this.SoundControls = false;
	}

	getSoundRigth = () => {

		const that = this;

		$.post('/music_room/get_sound_rights', {
			room_id: document.getElementById('rooms_members').dataset.room
		}, function (data) {
			data = JSON.parse(data);
			that.SoundControls = data.response;
		});
	}

	setRights = (new_r) => {
		this.SoundControls = new_r;
	}

	asRights = () => {
		return (this.SoundControls);
	}
}

export default new SoundControl();
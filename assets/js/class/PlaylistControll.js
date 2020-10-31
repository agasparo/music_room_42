class PlaylistControll {


	constructor() {

		this.PlaylistControlls = false;
		this.ready = false;
	}

	getPlaylistRigth = (callable) => {

		const that = this;

		$.post('/music_room/get_playlist_edit_rights', {
			room_id: document.getElementById('rooms_members').dataset.room
		}, function (data) {
			data = JSON.parse(data);
			that.PlaylistControlls = data.response;
			callable();
		});
	}

	setRights = (new_r) => {
		this.PlaylistControlls = new_r;
	}

	asRights = () => {

		return (this.PlaylistControlls);
	}
}

export default new PlaylistControll();
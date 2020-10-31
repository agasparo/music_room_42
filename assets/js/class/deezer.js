class Deezer {

	constructor() {
		DZ.init({
			appId  : '421942',
			channelUrl : 'http://lvh.me/music_room/extrats/deezer.php'
		});
	}

	login = () => {
		DZ.login(function(response) {

		    if (response.authResponse && response.authResponse.accessToken) {
		       	DZ.api('/user/me', function (response) {
		           	$.post('/music_room/deezer_connexion', {
		           		name: response.firstname,
		           		surname: response.lastname,
		           		mail: response.email,
		           		img: response.picture_big ? response.picture_big : response.picture
		           	}, function (data) {
		           		data = JSON.parse(data);
		           		if (data.response === "login")
		           			location.reload();
		           	});
		        });
		    }
		}, { perms: 'basic_access,email' });
	}

	attach = () => {
		DZ.login(function(response) {

		    if (response.authResponse && response.authResponse.accessToken) {
		       	DZ.api('/user/me', function (response) {
		           	$.post('/music_room/deezer_attach', {
		           		name: response.firstname,
		           		surname: response.lastname,
		           		mail: response.email,
		           		img: response.picture_big ? response.picture_big : response.picture
		           	}, function (data) {
		           		data = JSON.parse(data);
		           		if (data.response === "attach")
		           			location.reload();
		           	});
		        });
		    }
		}, { perms: 'basic_access,email' });
	}
}

export default new Deezer();
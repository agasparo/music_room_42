class response {

	parse(data) {

		if (data.status == 200) {

			if (data.response.indexOf("|") >= 0) {
				
				this.newuser(data.response.split('|')[1]);
			} else {

				location.reload();
			}
		}
		if (data.status == 400) {

			this.error(data.response);		
		}
	}

	error(error) {

		document.getElementById('error_form').innerText = error;
		setTimeout(function(){
			document.getElementById('error_form').innerText = "";
		}, 3000);
	}

	newuser(mail_user) {

		$.post("/music_room/inscription_form", {}, (data) => {

			document.getElementById('form_co').innerHTML = data;
			document.getElementById('mail_insc').value = mail_user;
			document.getElementById('post_form_ins').onclick = (e) => {
				e.preventDefault();
				this.inscription();
			}
		});
	}

	inscription() {

		$.post("/music_room/inscription", {

			nom: document.getElementById('nom_insc').value,
			prenom: document.getElementById('prenom_insc').value,
			mail: document.getElementById('mail_insc').value,
			mdp1: document.getElementById('pass_insc').value,
			mdp2: document.getElementById('pass2_insc').value,

		}, (data) => {

			this.parse(JSON.parse(data));
		});
	}
}

export default new response();
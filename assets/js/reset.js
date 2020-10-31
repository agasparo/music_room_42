window.onload = () => {

	const sender = document.getElementById('send_new_pass');

	sender.onclick = () => {

		const email = document.getElementById('email_user').value;
		const mdp1 = document.getElementById('mdp1_user').value;
		const mdp2 = document.getElementById('mdp2_user').value;

		$.post('/music_room/update_mdp', {
			mail: email,
			mdp_ref: mdp1,
			mdp_cmp: mdp2,
			token: document.getElementById('token_user').innerText
		}, function(data) {
			data = JSON.parse(data);
			if (data.status === 200) {
				document.getElementById('success').innerText = data.response;
				setTimeout(function() {
					document.getElementById('success').innerText = "";
				}, 3000);
			} else {
				document.getElementById('errors').innerText = data.response;
				setTimeout(function() {
					document.getElementById('errors').innerText = "";
				}, 3000);
			}
		});
	}
}
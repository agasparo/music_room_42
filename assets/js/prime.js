window.onload = () => {

	const btn = document.getElementById('prime_ok');
	if (btn) {

		btn.onclick = (e) => {

			e.preventDefault();
			$.post('/music_room/go_paye', {}, (data) => {

				if (data == "redirect") {
					window.location = "/music_room/";
				} else {
					document.getElementById('frg').innerHTML = data;
					document.getElementById('sub_prime').click();
				}
			});
		}
	}
}
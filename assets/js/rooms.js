import SoundControl from "/music_room/assets/js/class/SoundControl.js";
import PlaylistControl from "/music_room/assets/js/class/PlaylistControll.js";
import Lecteur from "/music_room/assets/js/class/lecteur.js";
import Playlist from "/music_room/assets/js/class/playlist.js";
import Rights from "/music_room/assets/js/class/rights.js";
import Chat from "/music_room/assets/js/class/chat.js";
import Sockets from "/music_room/assets/js/class/sockets.js";
import Params from "/music_room/assets/js/class/params.js";
import MCDO from "/music_room/assets/js/class/Mcdo.js";

let PageControl = {

	Sound: false,
	Rights: false,
	YoutubeApi: false,
};
let intervalCursor;

window.onmousemove = () => {

	document.body.style.cursor = 'default';
	clearTimeout(intervalCursor);
	intervalCursor = setTimeout(hideCursor, 5000);
}

window.hideCursor = () => {

	document.body.style.cursor = 'none';
}

window.onload = () => {

	document.getElementById('soundok').onclick = () => {

		document.getElementById('soundCollector').style.display = 'none';
		soundEnable();
	}
}

window.soundEnable = () => {

	PageControl.Sound = true;

	MCDO.init();
	SoundControl.getSoundRigth();
	Sockets.setClasses(Chat, Playlist, Lecteur);
	RightsInit();
	Chat.initSockets(Sockets, Lecteur);
	Playlist.initSockets(Sockets, SoundControl, PlaylistControl);
	Chat.setPlaylist(Playlist);
	Params.init(Sockets);

	const tool_bar = document.querySelectorAll(".tool_icon_bar");
	$('.tool_icon_bar').fadeToggle();
	let hide = true;

	document.getElementById('chache_player').onmousemove = (event) => {

		if (event.clientX <= 50 && hide) {
			$('.tool_icon_bar').fadeToggle();
			hide = false;
		} else if (!hide && event.clientX > 50) {
			$('.tool_icon_bar').fadeToggle();
			hide = true;
		}
	}
	if (PageControl.YoutubeApi)
		onYouTubePlayerAPIReady();
	if (PageControl.Rights)
		OnRightReady();
}

window.RightsInit = () => {

	Rights.init(SoundControl, Sockets, PlaylistControl, Playlist);
} 

window.onbeforeunload = () => {

	$.post('/music_room/user_delete/', { id_room: document.getElementById('rooms_members').dataset.room });
	Sockets.closeConnection();
}

window.OnRightReady = () => {

	if (!PageControl.Rights)
		PageControl.Rights = true;
	if (!PageControl.Sound)
		return;

	const id_base = document.getElementById('player').dataset.begin;

	Playlist.init(id_base, document.getElementById('rooms_members').dataset.room, Lecteur);
   	Lecteur.init(id_base, Playlist.getSize(), Playlist);
} 

window.onYouTubePlayerAPIReady = () => {

	if (!PageControl.YoutubeApi)
		PageControl.YoutubeApi = true;
	if (!PageControl.Sound)
		return;

	const id_base = document.getElementById('player').dataset.begin;

	const btnLecteur = document.getElementById('lecteur_state');
	const next = document.getElementById('lecteur_next');
	const prev = document.getElementById('playlist_prev');
	const audio = document.getElementById('lecteur_audio');
	const audio_state = document.getElementById('autio_st');

	const close_mem = document.getElementById('close_m');
	const open_mem = document.getElementById('members_icon');
	const open_params = document.getElementById('para_icon');
	const close_params = document.getElementById('close_params');

	const oc_chat = document.getElementById('chat_icon');
	const oc_playlist = document.getElementById('playlist_icon');
	const oc_video = document.getElementById('video_icon');

	const chat_input = document.getElementById('chat_room_input');
	const img_gif = document.getElementById('img_gif');
	const img_emoji = document.getElementById('img_emoji');
	const s_gif = document.getElementById('gifs_search');
	const s_emoji = document.getElementById('emojis_search');

	const lyrics_icon = document.getElementById('lyrics_icon');
	const fast_food_icon = document.getElementById('food_icon');
	const close_fast_food = document.getElementById('close_fast_food');

	PlaylistControl.getPlaylistRigth(OnRightReady);

	Chat.getChat();

	$( "#room_chat" ).draggable({
		containment: "parent"
	});
	$( "#room_lyrics" ).draggable({
		containment: "parent"
	});
	$( ".playlist_room" ).draggable({
		containment: "parent",
	});
	$( ".playlist_room" ).resizable();
	$( "#room_chat" ).resizable();
	$( "#room_lyrics" ).resizable();

	oc_video.onclick = () => {
		$('#player').fadeToggle();
	}

	lyrics_icon.onclick = () => {
		document.getElementById('room_chat').style.display = "none";
		$('#room_lyrics').toggle();
	}

	oc_chat.onclick = () => {
		document.getElementById('room_lyrics').style.display = "none";
		$('#room_chat').toggle();
	}

	oc_playlist.onclick = () => {
		$('.playlist_room').toggle();
	}

	img_gif.onclick = () => {

		document.getElementById('emoji_show').style.display = "none";
		Chat.getGifs("random", 0);
	}

	img_emoji.onclick = () => {

		document.getElementById('gif_show').style.display = "none";
		Chat.getEmoji();
	}

	s_emoji.onkeypress = (event) => {

		const keys = event.which || event.keyCode;
		if (keys == 13) {
			event.preventDefault();
			Chat.searchEmoji(event.target.value);
			event.target.value = null;
		}
	}

	s_gif.onkeypress = (event) => {

		const keys = event.which || event.keyCode;
		if (keys == 13) {
			event.preventDefault();
			Chat.getGifs(event.target.value, 1);
			event.target.value = null;
		}
	}

	chat_input.onkeypress = (event) => {

		const keys = event.which || event.keyCode;
		if (keys == 13) {
			event.preventDefault();
			if (event.target.value === "/help")
				Chat.help();
			else
				Chat.sendOptions(event.target.value, Sockets);
			event.target.value = null;
		} else {
			const res = Chat.propose(event.target.value, String.fromCharCode(keys));
			if (res !== "") {
				event.preventDefault();
				event.target.value = res;
			}
		}
	}

	open_params.onclick = () => {
		$("#rooms_params").toggle("slide");
	}

	open_mem.onclick = () => {
		
		$("#rooms_members").toggle("slide");
	}

	fast_food_icon.onclick = () => {

		$("#rooms_fats_food").toggle("slide");
	}

	close_params.onclick = () => {
		$("#rooms_params").toggle("slide");
	}

	close_mem.onclick = () => {
		$("#rooms_members").toggle("slide");
	}

	close_fast_food.onclick = () => {
		$("#rooms_fats_food").toggle("slide");
	}

	btnLecteur.onclick = (e, wasTriggered) => {

		let st = "play";

		if (SoundControl.asRights() > 0 || wasTriggered) {
			if (btnLecteur.classList.contains("fa-play"))
				Lecteur.startVideo();
			else
				Lecteur.pauseVideo();
			if (!wasTriggered) {
				Sockets.sendMessage({
					message: "soundcontrol",
					room_id: document.getElementById('rooms_members').dataset.room,
					click: "lecteur_state"
				}, true);
			}
		}
	}

	next.onclick = (e, wasTriggered) => {

		if (SoundControl.asRights() > 0 || wasTriggered) {
			Lecteur.nextSong(Playlist);
			if (!wasTriggered) {
				Sockets.sendMessage({
					message: "soundcontrol",
					room_id: document.getElementById('rooms_members').dataset.room,
					click: "lecteur_next"
				}, true);
			}
		}
	}

	prev.onclick = (e, wasTriggered) => {

		if (SoundControl.asRights() > 0 || wasTriggered) {
			Lecteur.prevSong(Playlist);
			if (!wasTriggered) {
				Sockets.sendMessage({
					message: "soundcontrol",
					room_id: document.getElementById('rooms_members').dataset.room,
					click: "playlist_prev"
				}, true);
			}
		}
	}


	audio.onclick = () => {

		if (audio.classList.contains('fa-volume-up') || audio.classList.contains('fa-volume-down'))
			Lecteur.mutePlayer();
		else
			Lecteur.unmutePlayer();
	}

	autio_st.onchange = (event) => {

		Lecteur.setVol(event.target.value);
	}
};
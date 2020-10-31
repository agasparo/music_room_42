class chat {


	constructor() {

		this.commande = [
			{ word: "/vote " },
			{ word: "/propose " },
			{ word: "/invite " },
			{ word: "/help" }
		];
		this.Playlist = null;
		this.Sockets = null;
		this.Lecteur = null;
		this.mute_player = false;
	}

	initSockets = (Sockets, Lecteur) => {

		this.Sockets = Sockets;
		this.Lecteur = Lecteur;
	}

	propose = (text, currentkeys) => {

		const search = text + currentkeys;

		for (let i = 0; i < this.commande.length; i++) {
			
			if (this.commande[i].word.substr(0, search.length) === search && search !== "/")
				return (this.commande[i].word);
		}
		return ("");
	}

	help = () => {
		const chathelp = document.createElement('div');
		const img = document.createElement('img');
		img.src = "https://support.upwork.com/hc/article_attachments/360040474034/chatbot-data.png";
		img.id = "imginfos";
		const para = document.createElement('p');
		para.innerHTML = "Bonjour, besoin d'aide ? <br/> /vote : permet de voter <br/> /propose permet de proposer une musique <br /> /invite permet d'inviter dans la room <br /> elle doit etre de la forme nom#prenom";
		chathelp.append(img);
		chathelp.append(para);
		const chatBody = document.getElementById('chat_body');
		chat_body.append(chathelp);
		chatBody.scrollTop = chatBody.scrollHeight - chatBody.clientHeight;
	}

	invite = (username) => {

		if (username.indexOf("#") === -1) {
			document.getElementById("err_msg_chat").innerText = "invitation non valide";
			setTimeout(function(){ document.getElementById("err_msg_chat").innerText = "" }, 3000);
			return;
		}
		$.post("/music_room/user_add", {
			room_id: document.getElementById('rooms_members').dataset.room,
			user_name_invite: username
		}, function (data) {

			data = JSON.parse(data);
			if (data.status === 200)
				document.getElementById("err_msg_chat").style.color = "green";
			else
				document.getElementById("err_msg_chat").style.color = "red";
			document.getElementById("err_msg_chat").innerText = data.response;
			setTimeout(function(){
				document.getElementById("err_msg_chat").innerText = "";
				document.getElementById("err_msg_chat").style.color = "red";
			}, 3000);
		});
	}

	getGifs = (search_sub, type) => {

		const that = this;

		const view = document.getElementById('gif_show').style.display;
		if (((view === "none" || view === "") && type === 0) || type === 1) {
			this.removegifs(document.getElementById('gif_show').childNodes, 3, document.getElementById('gif_show'));
			$.get('https://api.giphy.com/v1/gifs/search?api_key=NKLtkOAhNnbGk5A5nBamlYC9PgJwOMa8&q='+ search_sub +'&limit=25&offset=0&rating=g&lang=en', function(data) {

				for (let i = 0; i < data.data.length; i++) {

					const fig = document.createElement("figure");
					const img = document.createElement("img");
					img.src = data.data[i].images.downsized.url;
					img.alt = data.data[i].title;
					img.dataset.type = "gif";
					img.classList.add('img_gif');
					fig.appendChild(img);
					fig.classList.add("gif_box");
					fig.dataset.url = data.data[i].images.downsized.url;
					fig.dataset.ti = data.data[i].title;
					fig.dataset.type = "gif";
					document.getElementById('gif_show').append(fig);
					fig.addEventListener('click', that.sendMessage);
				}
				if (type === 0)
					$("#gif_show").toggle();
			});
		} else
			$("#gif_show").toggle();
	}

	removegifs = (childs, index, elem) => {

		while (childs.length > index) {

			for (let i = index; i < childs.length; i++) {

				elem.removeChild(childs[i]);
			}
		}
	}

	getEmoji = () => {

		$('#emoji_show').toggle();
		const that = this;
		const view = document.getElementById('emoji_show').style.display;
		if (view === "block" || view === "") {
			this.removegifs(document.getElementById('emoji_show').childNodes, 3, document.getElementById('emoji_show'));
			$.get('https://emoji-api.com/emojis?access_key=2bc9909f9127420e720b63aec2d73e084c0c9711', function (content) {

				for (let i = 0; i < content.length; i++) {
					const font = document.createElement("font");
					font.innerText = content[i].character;
					font.addEventListener('click', that.sendMessage);
					font.classList.add('emojis');
					font.dataset.type = 'text_unicode';
					font.dataset.url = content[i].character;
					font.dataset.ti = content[i].unicodeName;
					document.getElementById('emoji_show').append(font);
				}
			});
		}
	}

	searchEmoji = (search_sub) => {

		const that = this;

		this.removegifs(document.getElementById('emoji_show').childNodes, 3, document.getElementById('emoji_show'));
		$.get('https://emoji-api.com/emojis?search='+ search_sub +'&access_key=2bc9909f9127420e720b63aec2d73e084c0c9711', function (content) {

			for (let i = 0; i < content.length; i++) {
				const font = document.createElement("font");
				font.innerText = content[i].character;
				font.addEventListener('click', that.sendMessage);
				font.classList.add('emojis');
				font.dataset.type = 'text_unicode';
				font.dataset.url = content[i].character;
				font.dataset.ti = content[i].unicodeName;
				document.getElementById('emoji_show').append(font);
			}
		});
	}

	addEventForIframe = () => {

		const list = document.querySelectorAll('.player_preview_frame');

		for (let i = 0; i < list.length; i++) {
			if ('ontouchstart' in window)
				list[i].addEventListener('touchstart', this.frameControls);
			else
				list[i].addEventListener('mouseenter', this.frameControls);
		}
	}

	updateEventFrame = (e) => {

		let parent = e.target;

		if (!parent.dataset.video)
			parent = parent.parentElement;

		this.removeAllChild(parent);
		this.mutePlayerFrame(1);
		parent.append(this.save_child);
		if ('ontouchstart' in window) {
			parent.addEventListener('touchstart', this.frameControls);
		} else {
			parent.addEventListener('mouseenter', this.frameControls);
			parent.removeEventListener('mouseleave', this.updateEventFrame);
		}
		clearInterval(this.intervalPreview);
	}

	frameControls = (e) => {

		let parent = e.target;
		const that = this;

		if (!parent.dataset.video)
			parent = parent.parentElement;

		this.mutePlayerFrame(0);
		if ('ontouchstart' in window) {
			parent.removeEventListener('touchstart', this.frameControls);
		} else {
			parent.removeEventListener('mouseenter', this.frameControls);
			parent.addEventListener('mouseleave', this.updateEventFrame);
		}

		const preview_recep = document.createElement('div');
		preview_recep.id = "yt_player_preview";
		preview_recep.classList.add('player_preview_frame_body');

		this.save_child = parent.childNodes[1] ? parent.childNodes[1] : parent.childNodes[0];
		this.removeAllChild(parent);
		parent.append(preview_recep);

		const preview = new YT.Player('yt_player_preview', {
      		videoId: parent.dataset.video,
      		playerVars: {
      			playsinline: 1,
      			controls: 0,
          		rel: 0,
          		showinfo: 0,
          		fs: 0,
          		ecver: 2,
          		origin:'http://lvh.me/music_room/',
          		disablekb: 1,
          		start: 30,
          		autoplay: 1
      		},
      		events: {
        		'onReady': this.phonePlayer,
      		},
      		height: '100%',
          	width: '100%',
      	});

		this.intervalPreview = setInterval(function() {
			that.removeAllChild(parent);
			parent.append(that.save_child);
			if ('ontouchstart' in window) {
				parent.addEventListener('touchstart', that.frameControls);
			} else {
				parent.addEventListener('mouseenter', that.frameControls);
				parent.removeEventListener('mouseleave', that.updateEventFrame);
			}
			that.mutePlayerFrame(1);
			clearInterval(that.intervalPreview);
		}, 15000);
	}

	phonePlayer(event) {
		if ('ontouchstart' in window) {
			event.target.mute();
    		event.target.playVideo();

    		setTimeout(function() {
    			event.target.unMute();
    		}, 500);
    	}
	}

	removeAllChild = (list) => {

		while (list.firstChild) {

			list.removeChild(list.lastChild);
		}
	}

	mutePlayerFrame = (state) => {

		const audio = document.getElementById('lecteur_audio');

		if (state == 0) {

			if (audio.classList.contains('fa-volume-up') || audio.classList.contains('fa-volume-down'))
				this.mute_player = false;
			else
				this.mute_player = true;
		}
		
		if (state === 0 && !this.mute_player) {
			this.Lecteur.mutePlayer();
		}

		if (state === 1 && !this.mute_player) {
			this.Lecteur.unmutePlayer();
		}
	}

	sendMessage = (e) => {

		const that = this;

		if (e.target.dataset.type === "gif")
			$("#gif_show").toggle();
		if (e.target.dataset.type === 'text_unicode')
			$('#emoji_show').toggle();
		const elem = e.target;

		$.post('/music_room/chat', {
			type: elem.dataset.type,
			message: elem.dataset.url ? elem.dataset.url : elem.src,
			alt: elem.dataset.ti ? elem.dataset.ti : elem.alt,
			room_id: document.getElementById('rooms_members').dataset.room
		}, function (data) {
			data = JSON.parse(data);
			if (data.status === 400) {
				document.getElementById("err_msg_chat").innerText = data.response;
				setTimeout(function(){ document.getElementById("err_msg_chat").innerText = "" }, 3000);
			} else {
				that.Sockets.sendMessage({
					message: "new chat",
					room_id: document.getElementById('rooms_members').dataset.room
				});
				that.addEventForIframe();
			}
		});
	}

	setPlaylist = (p) => {
		this.Playlist = p;
	}

	sendOptions = (data) => {

		const that = this;

		if (data.split(" ")[0] && data.split(" ")[0] == "/invite") {
			this.invite(data.split(" ")[1]);
			return;
		}

		$.post('/music_room/chat', {
			type: 'text',
			message: data,
			alt: '',
			room_id: document.getElementById('rooms_members').dataset.room
		}, function (data) {
			data = JSON.parse(data);
			if (data.response == "music admise")
				that.Playlist.search(data.name_song, document.getElementById('rooms_members').dataset.room);
			if (data.status === 400) {
				document.getElementById("err_msg_chat").innerText = data.response;
				setTimeout(function(){ document.getElementById("err_msg_chat").innerText = "" }, 3000);
			} else {
				that.Sockets.sendMessage({
					message: "new chat",
					room_id: document.getElementById('rooms_members').dataset.room
				});
				that.addEventForIframe();
			}
		});
	}

	getChat = () => {

		const that = this;
		const body_chat = document.getElementById('chat_body');
		$.post('/music_room/get_chat', {
			room_id: document.getElementById('rooms_members').dataset.room
		}, function(data) {
			data = JSON.parse(data);
			body_chat.innerHTML = data.response;
			const chatBody = document.getElementById('chat_body');
			chatBody.scrollTop = chatBody.scrollHeight - chatBody.clientHeight;
			that.addEventForIframe();
		});
	}
}

export default new chat();
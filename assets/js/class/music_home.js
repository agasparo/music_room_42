const keytoken = "TOKEN";

let home = "0.0.0.0:8080";
if (window.location.hostname !== "lvh.me")
	home = "cors-anywhere.herokuapp.com";

class music_home {

	search = (searchType = "music", max_res = 10, ToSearch = "", orderType = "", elem_id) => {

		const that = this;

		$.get('https://www.googleapis.com/youtube/v3/search', {
			key: keytoken,
		    q: ToSearch,
		    part: "snippet",
		    maxResults: max_res,
		    type: searchType,
		    format: 5
		}, function (data) {
			$.post('/music_room/api_cache', { content:data, file:2 });
			if (searchType === "playlist") {
				that.getvideosOnPlaylist(data.items[that.getRandomInt(max_res)].id.playlistId, elem_id);
			}
		}).fail(function(xhr, status, error) {
        	
			$.post('/music_room/api_cache_get', { file:2 }, function(data) {
				data = JSON.parse(data);
				if (searchType === "playlist") {
					that.getvideosOnPlaylist(data.items[that.getRandomInt(max_res)].id.playlistId, elem_id);
				}
			});
    	});
	}

	getvideosOnPlaylist = (id, id_div) => {

		const that = this;

		$.get('https://www.googleapis.com/youtube/v3/playlistItems', {
			key: keytoken,
			maxResults: 15,
			part: 'snippet,contentDetails',
			playlistId: id,
			format: 5
		}, function (data) {
			$.post('/music_room/api_cache', { content:data, file:1 });
			for (let i = 0; i < data.items.length; i++) {
				$.get('http://'+home+'/https://api.deezer.com/search/playlist?q=' + that.renameTitle(data.items[i].snippet.title), function(content) {
					if (content.data) {
						let img = content.data[0] ? content.data[0].picture_medium : data.items[i].snippet.thumbnails.medium.url;
						that.createDiv(
							that.renameTitle(data.items[i].snippet.title),
							img,
							data.items[i].snippet.resourceId.videoId,
							id_div
						);
					}
				});
			}
		}).fail(function(xhr, status, error) {
        	
			$.post('/music_room/api_cache_get', { file:1 }, function(data) {
				data = JSON.parse(data);
				for (let i = 0; i < data.items.length; i++) {
					$.get('http://'+home+'/https://api.deezer.com/search/playlist?q=' + that.renameTitle(data.items[i].snippet.title), function(content) {
						if (content.data) {
							let img = content.data[0] ? content.data[0].picture_medium : data.items[i].snippet.thumbnails.medium.url;
							that.createDiv(
								that.renameTitle(data.items[i].snippet.title),
								img,
								data.items[i].snippet.resourceId.videoId,
								id_div
							);
						}
					});
				}

			});
    	});
	}

	renameTitle = (title) => {

		title = title.replace("/", "").replace("Clip Officiel", "");
		if (title.indexOf("(") !== -1) {
			title = title.substr(0, title.indexOf("("));
			return (title);
		}
		if (title.indexOf("[") !== -1) {
			title = title.substr(0, title.indexOf("["));
			return (title);
		}
		return (title);

	}

	createDiv = (title, img_url, id, div_id) => {
		
		const elem = document.createElement("div");
		elem.classList.add("bloc");
		elem.dataset.music_id = id;
		elem.dataset.create = true;
		elem.dataset.music_img = img_url;

		const img = document.createElement("img");
		img.classList.add("img_bloc");
		img.src = img_url;
		img.title = title;
		img.dataset.music_id = id;
		img.dataset.create = true;
		img.dataset.music_img = img_url;

		const para = document.createElement("p");
		let tmp;
		if (title.length > 15) {
			tmp = title.substr(0, 16);
			tmp += "...";
		}
		else
			tmp = title;
		para.innerText = tmp;
		para.classList.add("bloc_para");
		para.title = title;
		para.dataset.music_id = id;
		para.dataset.music_img = img_url;
		para.dataset.create = true; 

		elem.append(img);
		elem.append(para);
		elem.addEventListener("click", this.gotToRooms);
		document.getElementById(div_id).append(elem);
	}

	gotToRooms = (e) => {
		
		e.preventDefault();
		$.post('/music_room/check_rooms', {
			img: e.target.dataset.music_img,
			tocreate: e.target.dataset.create,
			name: e.target.title,
			id: e.target.dataset.music_id
		}, function(data) {
			data = JSON.parse(data);
			if (data.status !== 200)
				location.reload();
			else
				location.href = "/music_room/room/" + data.response;
		});
	}

	getRandomInt = (max) => {
  		return Math.floor(Math.random() * Math.floor(max));
	}
}

export default new music_home();
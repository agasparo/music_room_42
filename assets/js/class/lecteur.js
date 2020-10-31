let player;
let timer;

class lecteur {

	constructor() {
    this.current_song = 0;
    this.playlistlength = null;
    this.stateBtn = document.getElementById("lecteur_state");
    this.audioBtn = document.getElementById('lecteur_audio');
    this.playlistClass = null;
	}

	init = (id, playlistSize, Playlist) => {

    this.playlistlength = playlistSize;
    this.playlistClass = Playlist;
		player = new YT.Player('player', {

        videoId: id,
        playerVars: { 
          playsinline: 1,
          controls: 0,
          rel: 0,
          showinfo: 0,
          fs: 0,
          ecver: 2,
          origin:'http://lvh.me/music_room/',
        },
        events: {
          'onReady': this.onPlayerReady,
          'onStateChange': this.onPlayerStateChange
        }
    });
	}

  onPlayerStateChange = (state) => {

    const that = this;

    if (state.data === 0) {
      this.nextSong(this.playlistClass);
    }

    if (state.data === 1) {
      timer = setInterval(function() {

        const playerTotalTime = player.getDuration();
        const playerTimeDifference = (player.getCurrentTime() / playerTotalTime) * 100;
        document.getElementById("lecteur_timer").innerText = that.secondsToHms(player.getCurrentTime()) + " / " + that.secondsToHms(playerTotalTime);
        document.getElementById("progress_bar").style.width = playerTimeDifference + "%";
      }, 1000);
    } else {
      clearTimeout(timer);
    }
  }

  secondsToHms(d) {

    d = Number(d);
    const h = Math.floor(d / 3600);
    const m = Math.floor(d % 3600 / 60);
    const s = Math.floor(d % 3600 % 60);

    const hDisplay = h > 0 ? (h < 10 ? "0" + h : h) + ":" : "00:";
    const mDisplay = m > 0 ? (m < 10 ? "0" + m : m) + ":" : "00:";
    const sDisplay = s > 0 ? (s < 10 ? "0" + s : s) : "00";
    return (hDisplay + mDisplay + sDisplay); 
  }

  onPlayerReady = () => {

    this.startVideo();
  }

  getPlayerS = () => {
    if (!player)
      return;
    return (player.getPlayerState());
  }

  getPlayerT = () => {
    if (!player)
      return;
    return (player.getCurrentTime());
  }

  SetImgOfFisrt = (content) => {
    this.setVideoData(content);
  }

  setVideoData = (content) => {

    document.getElementById('img_current_music').src = content.img;
    document.getElementById('title_current_music').innerText = content.name;
    $.post('/music_room/music_lyrics', {
      search: content.name
    }, function (data) {
      data = JSON.parse(data);
      document.getElementById('lyrics_text').innerHTML = data.response;
    });
  }

  setSoundWave = (pos) => {

    const elem = document.querySelectorAll('.div_playlist')[pos];
    if (elem)
      this.playlistClass.addSoundWave(elem);
  }

  nextSong = (Playlist) => {

    this.current_song++;
    this.playlistlength = Playlist.getSize();
    if (this.current_song >= this.playlistlength)
      this.current_song = 0;
    const video_data = Playlist.getVideo(this.current_song);
    this.setVideoData(video_data);
    if (video_data) {
      this.setSoundWave(this.current_song);
      this.stateBtn.classList.remove("fa-play");
      this.stateBtn.classList.add("fa-pause");
      this.loadVideo(video_data.id, video_data.timer, this.current_song);
    }

  }

  prevSong = (Playlist) => {

    this.current_song--;
    this.playlistlength = Playlist.getSize();
    if (this.current_song < 0)
      this.current_song = this.playlistlength - 1;
    const video_data = Playlist.getVideo(this.current_song);
    this.setVideoData(video_data);
    if (video_data) {
      this.setSoundWave(this.current_song);
      this.stateBtn.classList.remove("fa-play");
      this.stateBtn.classList.add("fa-pause");
      this.loadVideo(video_data.id, video_data.timer, this.current_song);
    }
  }

  loadVideo = (video_id, start_time, cs) => {

    if (cs !== undefined) {
      this.pauseVideo();
      this.current_song = cs;
      const video_data = this.playlistClass.getVideo(this.current_song);
      this.setVideoData(video_data);
      player.cueVideoById(video_id, start_time);
      this.startVideo();
    }
  }

  startVideo = () => {
    player.playVideo();
    this.stateBtn.classList.remove("fa-play");
    this.stateBtn.classList.add("fa-pause");
  }

  pauseVideo = () => {
    if (!player)
      return;
    player.pauseVideo();
    this.stateBtn.classList.remove("fa-pause");
    this.stateBtn.classList.add("fa-play");
  }

  mutePlayer = () => {

    player.mute();
    this.audioBtn.classList.remove(this.setIconSound(-1));
    this.audioBtn.classList.add("fa-volume-off");
  }

  unmutePlayer = () => {

    player.unMute();
    this.audioBtn.classList.remove("fa-volume-off");
    this.audioBtn.classList.add(this.setIconSound(-1));
  }

  setIconSound = (vol) => {

    let current_vol;
    if (vol >= 0)
      current_vol = vol;
    else
      current_vol = player.getVolume();
    if (current_vol < 50 && current_vol > 0)
      return ("fa-volume-down")
    if (current_vol >= 50)
      return ("fa-volume-up");
    return ("fa-volume-off");
  }

  setVol(vol) {

    this.audioBtn.classList.remove("fa-volume-up");
    this.audioBtn.classList.remove("fa-volume-down");
    player.setVolume(vol);
    this.audioBtn.classList.add(this.setIconSound(vol));
  }
}

export default new lecteur();
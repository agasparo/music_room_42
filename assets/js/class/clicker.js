class Clicker {

	constructor() {

		this.music = 0;
		this.parentMusic = null;
		this.clickListen = null;
		this.shop = null;
		this.shopList = [
			{ note_ps: 1, total: 0, price: 12 },
			{ note_ps: 5, total: 0, price: 120 },
			{ note_ps: 10, total: 0, price: 1200 },
			{ note_ps: 15, total: 0, price: 12000 }
		];
		this.upgrade = null;
		this.upgradeList = [
			{ note_ps: 10, total: 0, price: 10000 },
			{ note_ps: 50, total: 0, price: 25000 },
			{ note_ps: 100, total: 0, price: 30000 },
			{ note_ps: 150, total: 0, price: 50000 }
		];
		this.totalnote = 0;
		this.parentNotes = null;
	}

	init = () => {

		this.music = 0;
		this.parentMusic = document.getElementById('clicker_content');
		this.clickListen = document.getElementById('clicker');
		this.parentNotes = document.getElementById('current_per_s');
		this.shop  = document.querySelectorAll('.shop_buy');
		this.addEventShop(this.shop);
		this.upgrade  = document.querySelectorAll('.buy_upgrade');
		this.addEventUpgrade(this.upgrade);
		this.clickListen.onclick = this.listenClick;

		const that = this;

		setInterval(function(){ 
			that.addClicks();
		}, 1000);
	}

	addClicks = () => {

		let add = 0;
		this.totalnote = 0;

		for (let i = 0; i < this.shopList.length; i++) {

			add += this.shopList[i].total * this.shopList[i].note_ps;
			this.totalnote += this.shopList[i].total * this.shopList[i].note_ps;
		}
		this.addMusic(add);
	}

	addEventShop = (list) => {

		for (let i = 0; i < list.length; i++) {
			list[i].addEventListener('click', this.listenShop);
		}
	}

	addEventUpgrade = (list) => {

		for (let i = 0; i < list.length; i++) {
			list[i].addEventListener('click', this.listenUpgrade);
		}
	}

	listenUpgrade = (e) => {

		const w = e.target.dataset.id;
		const elem = this.upgradeList[parseInt(w) - 1];
		if (this.music >= elem.price && elem.total === 0) {
			this.upgradeList[parseInt(w - 1)].total = 1; 
			this.shopList[parseInt(w) - 1].note_ps += this.upgradeList[parseInt(w - 1)].note_ps;
			this.music -= this.upgradeList[parseInt(w) - 1].price;
			this.updateFrontMusic();
			document.getElementById("up_" + w).innerText = "possede";
		}
		
	}

	listenShop = (e) => {

		const w = e.target.dataset.id;
		const elem = this.shopList[parseInt(w) - 1];
		if (elem.price <= this.music) {
			this.shopList[parseInt(w) - 1].total++;
			this.music -= this.shopList[parseInt(w) - 1].price;
			this.updateFrontMusic();
			document.getElementById(w + "_many").innerText = this.shopList[parseInt(w) - 1].total;
		} 
	}

	listenClick = () => {

		this.addMusic(1);
	}

	addMusic = (add_notes) => {

		this.music += add_notes;
		this.updateFrontMusic();
	}

	updateFrontMusic = () => {

		this.parentMusic.innerText = this.music;
		this.parentNotes.innerText = this.totalnote;
	}
}

export default new Clicker();
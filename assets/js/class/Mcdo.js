class Mcdo {

	constructor() {
		this.categ = null;
		this.commande = null;
	}

	init = () => {
		this.categ = document.querySelectorAll('.mcdo_menu');

		for (let i = 0; i < this.categ.length; i++) {
			
			this.categ[i].addEventListener('click', this.changeCateg)
		}
		document.querySelectorAll('.mcdo_menu')[0].click();
	}

	changeCateg = (e) => {

		const parent = document.getElementById('menu_content_mcdo');
		const that = this;

		parent.innerText = "en cours ...";
		$.post('/music_room/mcdo', {
			categ: e.target.innerText.toLowerCase(),
			id_room: document.getElementById('rooms_members').dataset.room
		}, function(data) {
			data = JSON.parse(data);
			if (data.status === 200) {
				parent.innerHTML = "";
				const w = data.response[0];
				if (w !== "[") {
					parent.innerText = data.response;
					return;
				}
				data = JSON.parse(data.response);
				if (e.target.innerText.toLowerCase() !== "commande")
					that.constructHtml(data, parent);	
				else {
					that.commande = data;
					that.constructCommande(data, parent);
				}
			}
		});
	}

	constructCommande = (data, parent) => {

		const div = document.createElement('div');
		const input = document.createElement('input');
		input.type = "number";
		input.id = "phone_number_mcdo";
		input.classList.add('input_macdo_commande');
		input.classList.add('form-control');
		div.append(input);
		const btn = document.createElement('button');
		btn.id = "send_commande_mcdo";
		btn.classList.add('btn');
		btn.classList.add('btn-primary');
		btn.innerText = "envoyer";
		btn.onclick = this.sendCommande;
		div.append(btn);
		const tab = document.createElement('table');
		tab.classList.add("table");
		tab.classList.add("table-hover");

		for (let i = 0; i < data.length; i++) {
  			
  			const tr = document.createElement('tr');
  			tr.classList.add('table-active');
  			const td = document.createElement('td');
  			td.innerText = data[i][0].pseudo;
  			tr.append(td);
  			const content = document.createElement('td');
  			for (let j = 0; j < data[i].length; j++) {

  				const li = document.createElement('li');
  				const img = document.createElement('img');
  				li.innerText = data[i][j].name;
  				img.src = data[i][j].img;
  				img.classList.add("img_commande_mcdo");
  				li.classList.add("li_commande_mcdo");
  				li.append(img);
  				content.append(li);
			}
			tr.append(content);
			tab.append(tr);
		}
		div.append(tab)
		parent.append(div);
	}

	sendCommande = (e) => {

		const that = this;
		const value = document.getElementById('phone_number_mcdo').value;
		
		$.post('/music_room/mcdo_comm_send', {
			commande: JSON.stringify(that.commande),
			phoneNumber: value,
			id_room: document.getElementById('rooms_members').dataset.room
		}, function(data) {
			data = JSON.parse(data);
			if (data.sent === true) {
				document.getElementById('mcdo_send_val').innerText = value;
				$('#AlertCommandeMcdoSendS').fadeToggle();
				setTimeout(function(){ $('#AlertCommandeMcdoSendS').fadeToggle(); }, 2000);
			} else {
				$('#AlertCommandeMcdoSendF').fadeToggle();
				setTimeout(function(){ $('#AlertCommandeMcdoSendF').fadeToggle(); }, 2000);
			}
			that.commande = null;
		});
	}

	constructHtml = (data, parent) => {

		for (let i = 0; i < data.length; i++) {
			const div = document.createElement('div');
			const img = document.createElement('img');
			const para = document.createElement('p');
			img.src = data[i].img;
			img.classList.add("img_mcdo_food");
			para.innerText = data[i].name.toLowerCase();
			para.classList.add("para_mcdo_food");
			div.classList.add("div_mcdo_food");
			div.onclick = this.addArticle;
			div.append(img);
			div.append(para);
			parent.append(div);
		}
	}

	addArticle = (e) => {

		const elem = e.target.parentElement;
		$.post('/music_room/mcdo_comm_add', {
			id_room: document.getElementById('rooms_members').dataset.room,
			commande_img: elem.childNodes[0].src,
			commande_name: elem.childNodes[1].innerText
		}, function(data) {
			data = JSON.parse(data);
			if (data.status === 200) {
				if (data.response === "add success") {
					document.getElementById('mcdo_comm_val').innerText = data.text;
					$('#AlertCommandeMcdoS').fadeToggle();
					setTimeout(function(){ $('#AlertCommandeMcdoS').fadeToggle(); }, 2000);
				} else {
					$('#AlertCommandeMcdoF').fadeToggle();
					setTimeout(function(){ $('#AlertCommandeMcdoF').fadeToggle(); }, 2000);
				}
			}
		});
	}
}

export default new Mcdo();
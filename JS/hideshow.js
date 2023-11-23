function hide(hider, shower, conteneur) {
	function wrapped() {
		shower.style.display = conteneur.style.display;
		conteneur.style.display = "none";
		hider.style.display = "none";
	}
	return wrapped;
}

function show(hider, shower, conteneur) {
	function wrapped() {
		conteneur.style.display = shower.style.display;
		hider.style.display = shower.style.display;
		shower.style.display = "none";
	}
	return wrapped;
}

function con(str1, str2) {
	return "".concat(str1, str2);;
}

var conteneurs = document.getElementsByClassName('conteneur');
for (var i = 0; i < conteneurs.length; i++) {
	var identifiant = conteneurs[i].id
	var hider = document.getElementById(con("hider-", identifiant));
	var shower = document.getElementById(con("shower-", identifiant));
	var conteneur = conteneurs[i];
	
	hider.onclick = hide(hider, shower, conteneur);
	shower.onclick = show(hider, shower, conteneur);
}
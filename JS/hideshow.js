function hider() {
	document.getElementById("barre").style.display = "none";
	document.getElementById("hider").style.display = "none";
	document.getElementById("shower").style.display = "block";
}
function shower() {
	document.getElementById("barre").style.display = "block";
	document.getElementById("hider").style.display = "block";
	document.getElementById("shower").style.display = "none";
}

document.getElementById('hider').onclick = hider;
document.getElementById('shower').onclick = shower;
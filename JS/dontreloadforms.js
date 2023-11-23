var body = document.getElementsByClassName("content")[0];

window.onload = function() {
    body.scrollTop = sessionStorage.getItem("scroll");
}


body.addEventListener("scroll", (event) => {
	sessionStorage.setItem("scroll", body.scrollTop);
});
function aktualizujOdkazy() {
  if(!document.getElementsByTagName) return false;
  var links = document.getElementsByTagName("a");
  for (var i=0; i < links.length; i++) {
    if(links[i].className.match("popup")) {
      links[i].onclick = function () {
        return !window.open(this.href);
      }
    }
  }
}
window.onload=aktualizujOdkazy;

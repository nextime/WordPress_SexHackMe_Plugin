
function shmChangeGif(o){
   //console.log(o);
   o.src = o.getAttribute('data-gsrc');
   console.log("loading "+o.src);
}

function createCookieSeconds(name,value,seconds) {
   if(seconds) {
      var date = new Date();
      date.setTime(date.getTime()+(seconds*1000));
      var expires = "; expires="+date.toGMTString();
   }
   else var expires = "";
   document.cookie = name+"="+value+expires+"; path=/";
}

function createCookie(name,value,days) {
	if (days) {
		var date = new Date();
		date.setTime(date.getTime()+(days*24*60*60*1000));
		var expires = "; expires="+date.toGMTString();
	}
	else var expires = "";
	document.cookie = name+"="+value+expires+"; path=/";
}

function readCookie(name) {
	var nameEQ = name + "=";
	var ca = document.cookie.split(';');
	for(var i=0;i < ca.length;i++) {
		var c = ca[i];
		while (c.charAt(0)==' ') c = c.substring(1,c.length);
		if (c.indexOf(nameEQ) == 0) return c.substring(nameEQ.length,c.length);
	}
	return null;
}

function eraseCookie(name) {
	createCookie(name,"",-1);
}
//window.onload = function(){
   //var thumbhovers = document.getElementsByClassName("sexhack_thumb_hover");
   //for (var i = 0; i < thumbhovers.length; i++) {
   //   thumbhovers[i].src = thumbhovers[i].getAttribute('data-src'); //second console output
   //}
//}

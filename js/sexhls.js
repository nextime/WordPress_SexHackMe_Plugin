//var vtag = document.getElementById('vtag');

function SexHLSPlayer(url, vuid){
  var vtag = document.getElementById(vuid);
  if(Hls.isSupported()) {
      vtag.volume = 0.3;
      var hls = new Hls({autoStartLoad:false});
      var m3u8Url = decodeURIComponent(url)
      hls.loadSource(m3u8Url);
      hls.attachMedia(vtag);
      hls.on(Hls.Events.MANIFEST_PARSED,function() {
        hls.autoLevelEnabled = true;
        //hls.loadLevel = 4;
        hls.startLoad();
        //vtag.play(); // XXX Autoplay doesn't work apparently atm
      });
      //document.title = url
    }
	else if (vtag.canPlayType('application/vnd.apple.mpegurl')) {
		vtag.src = url;
      // XXX Autoplay doesn't work apparently atm
		//vtag.addEventListener('canplay',function() {
		//  vtag.play();
		//});
		vtag.volume = 0.3;
		//document.title = url;
  	}
}

function SexHLSplayPause(vuid) {
    vtag = document.getElementById(vuid);
    vtag.paused?vtag.play():vtag.pause();
}

function SexHLSvolumeUp(vuid) {
    vtag = document.getElementById(vuid);
    if(vtag.volume <= 0.9) vtag.volume+=0.1;
}

function SexHLSvolumeDown(vuid) {
    vtag = document.getElementById(vuid);
    if(vtag.volume >= 0.1) vtag.volume-=0.1;
}

function SexHLSseekRight(vuid) {
    vtag = document.getElementById(vuid);
    vtag.currentTime+=5;
}

function SexHLSseekLeft(vuid) {
    vtag = document.getElementById(vuid);
    vtag.currentTime-=5;
}

function SexHLSvidFullscreen(vuid) {
    vtag = document.getElementById(vuid);
    if (vtag.requestFullscreen) {
      vtag.requestFullscreen();
  } else if (vtag.mozRequestFullScreen) {
      vtag.mozRequestFullScreen();
  } else if (vtag.webkitRequestFullscreen) {
      vtag.webkitRequestFullscreen();
    }
}

//playM3u8(window.location.href.split("#")[1])
/*
$(window).on('load', function () {
    $('#vtag').on('click', function(){this.paused?this.play():this.pause();});
    Mousetrap.bind('space', SexHLSplayPause);
    Mousetrap.bind('up', SexHLSvolumeUp);
    Mousetrap.bind('down', SexHLSvolumeDown);
    Mousetrap.bind('right', SexHLSseekRight);
    Mousetrap.bind('left', SexHLSseekLeft);
    Mousetrap.bind('f', SexHLSvidFullscreen);
});
*/

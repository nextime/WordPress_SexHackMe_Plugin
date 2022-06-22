function SexVideoPlayer(vuid){
  var vtag = videojs(vuid);
  vtag.ready(function() {
      myPlayer.volume(0.5); 
  });
  return vtag;
}


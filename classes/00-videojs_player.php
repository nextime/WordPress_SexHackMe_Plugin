<?php
namespace wp_SexHackMe;

if(!class_exists('SexhackVideoJSPlayer')) {
   class SexhackVideoJSPlayer
   {
      public function __construct()
      {
         sexhack_log('SexhackVideoJSPlayer() Instanced');
         add_action('wp_enqueue_scripts', array( $this, 'add_js' ));
         add_action('wp_enqueue_scripts', array( $this, 'add_css' ));
         add_shortcode("sexvideo", array( $this, "sexvideo_shortcode"));
      }

      public function add_js()
      {
         wp_enqueue_script('sexvideo_baseplayer', plugin_dir_url(__DIR__).'js/video.min.js');
			//wp_enqueue_script('sexvideo_vrplayer', plugin_dir_url(__DIR__).'js/videojs-vr.js');
			wp_enqueue_script('sexvideo_xrplayer', plugin_dir_url(__DIR__).'js/videojs-xr.min.js');
         //wp_enqueue_script('sexvideo_player_controls', plugin_dir_url(__DIR__).'js/sexvideo.js');
			//wp_enqueue_script('sexvideo_vrplayer', plugin_dir_url(__DIR__).'js/deovr.js');
      }

      public function add_css()
      {  
		   wp_enqueue_style ('videojs', plugin_dir_url(__DIR__).'css/video-js.min.css');
         wp_enqueue_style ('sexhack_videojs', plugin_dir_url(__DIR__).'css/sexhackme_videojs.css');
         //wp_enqueue_style ('videojs-vr', plugin_dir_url(__DIR__).'css/videojs-vr.css');
			wp_enqueue_style ('videojs-xr', plugin_dir_url(__DIR__).'css/videojs-xr.css');
         //wp_enqueue_style ('videojs_forest', plugin_dir_url(__DIR__).'css/videojs_forest.css');
			//wp_enqueue_style ('videojs', plugin_dir_url(__DIR__).'css/deovr.css');
      }

      public function addPlayer($vurl, $posters="", $projection="180_LR")
      {
         $uid = uniqid('sexvideo_');
			//$uid = "antani";
			$html  = "<video id='$uid' class='video-js vjs-default-skin vjs-2-1 vjs-big-play-centered' style='width: 100%; height: 100%;' controls poster='$posters'>\n";
			//$html.= '<script src="https://s3.deovr.com/version/1/js/bundle.js" async></script>';
			//$html .= "<deo-video id='$uid'>\n";
  			//$html .= '	<source src="'.$vurl.'" quality="1080p" type="application/x-mpegURL">'."\n";
			$html .= '</video>'."\n";
			//$html .= "</deo-video>\n";
			$html .= "<script language='javascript'>\n";
			$html .= "$(window).on('load', function() {\n";
			//$html .= "   videojs.log.level('debug');\n";
			$html .= "   var player = videojs('$uid', {\n";
			$html .= "				html5: {\n";
    		$html .= "               vhs: {\n";
         $html .= "                overrideNative: !videojs.browser.IS_SAFARI\n";
   	   $html .= "               },\n";
         $html .= "           nativeAudioTracks: false,\n";
         $html .= "           nativeVideoTracks: false\n";
         $html .= "           }});\n";
         $html .= "   player.src({ src: '$vurl', type: 'application/x-mpegURL'});\n";
         //if($_GET['antani']) {
            $html .= "   player.xr();";
         //} else {
		   //   $html .= "   player.mediainfo = player.mediainfo || {};\n";
			//   $html .= "   player.mediainfo.projection = '$projection';\n";
         //   $html .= "   player.vr({projection: '$projection', debug: false, forceCardboard: true});\n";
         //}
			//$html .= "   player.xr();";
			$html .= '});'."\n";
			$html .= "</script>";
         return $html;
      }

      public function sexvideo_shortcode($attr, $cont)
      {
         extract( shortcode_atts(array(
            "url" => '',
            "posters" => '',
         ), $attr));
         return "<div class='sexvideo_videojs'>" . $this->addPlayer($url, $posters) . "</div>";
      }

   }
}




$SEXHACK_SECTION = array(
   'class' => 'SexhackVideoJSPlayer', 
   'description' => 'Add VideoJS Video Player', 
   'name' => 'sexhackme_videojs_player'
);

?>

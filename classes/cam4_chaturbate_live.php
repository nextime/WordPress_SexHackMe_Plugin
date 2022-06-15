<?php
namespace wp_SexHackMe;

if(!class_exists('Cam4ChaturbateLive')) {
   class Cam4ChaturbateLive
   {
      public function __construct()
      {
			add_shortcode( 'sexhacklive', array( $this, 'sexhack_live' ));
         sexhack_log('Cam4ChaturbateLive() Instanced');
      }

      public function parse_chaturbate($html)
      {
         $dom = new DOMDocument;
         @$dom->loadHTML($html);
         foreach ($dom->getElementsByTagName('script') as $node) {
            preg_match( '/initialRoomDossier\s*=\s*(["\'])(?P<value>(?:(?!\1).)+)\1/', $node->textContent, $res);
            if(count($res) > 2)
            {
               $j = json_decode(str_replace("\u0022", '"', str_replace("\u005C", "\\", $res[2])));
               if(property_exists($j, 'hls_source'))
               {
                  return $j->{'hls_source'};
               }
            }
         }
         return FALSE;
      }


      public function parse_cam4($html)
      {
         $dom = new DOMDocument;
         @$dom->loadHTML($html);
         foreach ( $dom->getElementsByTagName('video') as $node) {
            return $node->getAttribute('src');
         }
         return FALSE;
      }


		public function sexhacklive_getChaturbate($model)
		{
			$vurl = false; //$this->parse_chaturbate(sexhack_getURL('https://chaturbate.com/'.$model.'/'));
         if(!$vurl) {
            return '<p>Chaturbate '.$model."'s cam is OFFLINE</p>";
         }
         return '<a href="https://chaturbate.com/'.$model.'/" target="_black" >Chaturbate '.$model.':</a> '.SexhackHlsPlayer::addPlayer($vurl);

		}

		public function sexhacklive_getCam4($model)
		{
         $vurl = false; //$this->parse_cam4(sexhack_getURL('https://www.cam4.com/'.$model));
         if(!$vurl) {
            return '<p>Cam4 '.$model."'s cam is OFFLINE</p>";
         }
         return '<a href="https://chaturbate.com/'.$model.'/" target="_blank" >Cam4 '.$model.":</a> ".SexhackHlsPlayer::addPlayer($vurl);

		}

      public function sexhack_live($attributes, $content)
      {
         extract( shortcode_atts(array(
            'site' => 'chaturbate',
            'model' => 'sexhackme',
         ), $attributes));
         if($site=='chaturbate') {
            return $this->sexhacklive_getChaturbate($model);
         } else if($site=='cam4') {
            return $this->sexhacklive_getCam4($model);
         }
         return '<p>CamStreamDL Error: wrong site option '.$site.'</p> ';

      }
   }
}




$SEXHACK_SECTION = array(
   'class' => 'Cam4ChaturbateLive', 
   'description' => 'Add shortcodes for retrieve cam4 and/or chaturbate live streaming (it needs HLS player active!!) Shortcuts: [sexhacklive site="chaturbate|cam4" model="modelname"] ', 
   'name' => 'sexhackme_cam4chaturbate_live'
);

?>

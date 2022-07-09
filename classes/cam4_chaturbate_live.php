<?php
/**
 * Copyright: 2022 (c)Franco (nextime) Lanza <franco@nexlab.it>
 * License: GNU/GPL version 3.0
 *
 * This file is part of SexHackMe Wordpress Plugin.
 *
 * SexHackMe Wordpress Plugin is free software: you can redistribute it and/or modify it 
 * under the terms of the GNU General Public License as published 
 * by the Free Software Foundation, either version 3 of the License, 
 * or (at your option) any later version.
 *
 * SexHackMe Wordpress Plugin is distributed in the hope that it will be useful, 
 * but WITHOUT ANY WARRANTY; without even the implied warranty of 
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. 
 * See the GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License 
 * along with SexHackMe Wordpress Plugin. If not, see <https://www.gnu.org/licenses/>.
 */

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

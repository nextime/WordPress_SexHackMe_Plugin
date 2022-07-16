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

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;


if(!class_exists('ChaturbateLive')) {
   class ChaturbateLive
   {
      public static function parseSite($html)
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

      public static function getStream($model)
      {
         $vurl = false; //$this->parse_chaturbate(sexhack_getURL('https://chaturbate.com/'.$model.'/'));
         if(!$vurl) {
            return '<p>Chaturbate '.$model."'s cam is OFFLINE</p>";
         }
         return '<a href="https://chaturbate.com/'.$model.'/" target="_black" >Chaturbate '.$model.':</a> '.sh_hls_player($vurl);

      }


   }
}

if(!class_exists('Cam4Live')) {
   class Cam4Live
   {
      public static function parseSite($html)
      {
         $dom = new DOMDocument;
         @$dom->loadHTML($html);
         foreach ( $dom->getElementsByTagName('video') as $node) {
            return $node->getAttribute('src');
         }
         return FALSE;
      }

      public static function getStream($model)
      {
         $vurl = false; //$this->parse_cam4(sexhack_getURL('https://www.cam4.com/'.$model));
         if(!$vurl) {
            return '<p>Cam4 '.$model."'s cam is OFFLINE</p>";
         }
         return '<a href="https://chaturbate.com/'.$model.'/" target="_blank" >Cam4 '.$model.":</a> ".sh_hls_player($vurl);

      }

   }
}

if(!class_exists('LiveCamSite')) {
   class LiveCamSite
   {   
      public static function getCamStream($site, $model)
      {
         if($site=='chaturbate') return ChaturbateLive::getStream($model);
         else if($site=='cam4') return Cam4Live::getStream($model);
         return false;
      }
   }

}




?>

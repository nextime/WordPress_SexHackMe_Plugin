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

if(!class_exists('SexhackHlsPlayer')) {
   class SexhackHlsPlayer
   {
      public function __construct()
      {
         sexhack_log('SexhackHlsPlayer() Instanced');
         add_action('wp_enqueue_scripts', array( $this, 'add_js' ));
         add_shortcode("sexhls", array( $this, "sexhls_shortcode"));
      }

      public function add_js()
      {
         wp_enqueue_script('sexhls_baseplayer', plugin_dir_url(__DIR__).'js/hls.js');
         wp_enqueue_script('sexhls_player_controls', plugin_dir_url(__DIR__).'js/sexhls.js');
         wp_enqueue_script('sexhls_mousetrap', plugin_dir_url(__DIR__).'js/mousetrap.min.js');
      }

      public function addPlayer($vurl, $posters="")
      {
         $uid = uniqid('sexhls_');
         $html = '<video id="'.$uid.'" style="width: 100%; height: 100%;" controls poster="'.$posters.'"></video>'."\n";
         $html .= '<script language="javascript">'."\n";
         $html .= '$(window).on(\'load\', function() {'."\n";
         $html .= '   SexHLSPlayer(\''.$vurl.'\', \''.$uid.'\');'."\n";
         $html .= '   $(\'#'.$uid.'\').on(\'click\', function(){this.paused?this.play():this.pause();});'."\n";
         $html .= '   Mousetrap(document.getElementById(\''.$uid.'\')).bind(\'space\', function(e, combo) { SexHLSplayPause(\''.$uid.'\'); });'."\n";
         $html .= '   Mousetrap(document.getElementById(\''.$uid.'\')).bind(\'up\', function(e, combo) { SexHLSvolumeUp(\''.$uid.'\'); });'."\n";
         $html .= '   Mousetrap(document.getElementById(\''.$uid.'\')).bind(\'down\', function(e, combo) { SexHLSvolumeDown(\''.$uid.'\'); });'."\n";
         $html .= '   Mousetrap(document.getElementById(\''.$uid.'\')).bind(\'right\', function(e, combo) { SexHLSseekRight(\''.$uid.'\'); });'."\n";
         $html .= '   Mousetrap(document.getElementById(\''.$uid.'\')).bind(\'left\', function(e, combo) { SexHLSseekLeft(\''.$uid.'\'); });'."\n";
         $html .= '   Mousetrap(document.getElementById(\''.$uid.'\')).bind(\'f\', function(e, combo) { SexHLSvidFullscreen(\''.$uid.'\'); });'."\n";
         $html .= '});'."\n";
         $html .= '</script>'."\n";
         return $html;
      }

      public function sexhls_shortcode($attr, $cont)
      {
         extract( shortcode_atts(array(
            "url" => '',
            "posters" => '',
         ), $attr));
         return "<div class='sexhls_video'>" . $this->addPlayer($url, $posters) . "</div>";
      }

   }
}




$SEXHACK_SECTION = array(
   'class' => 'SexhackHlsPlayer', 
   'description' => 'Add HLS Video Player for progressive and live streaming support', 
   'name' => 'sexhackme_hls_player'
);

?>

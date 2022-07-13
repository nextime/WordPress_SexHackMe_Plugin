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

if(!class_exists('SH_Shortcodes')) {
   class SH_Shortcodes
   {

		public static function init() {

         $shortcodes = array(
            'sexhls'         => __CLASS__ . '::video_hls',
				'sexvideo'       => __CLASS__ . '::video_xr',
				'sh_videoxr'     => __CLASS__ . '::video_xr',
				'sh_videohls'    => __CLASS__ . '::video_hls',
         );

         foreach( $shortcodes as $shortcode_tag => $shortcode_func ) {
            add_shortcode( $shortcode_tag, $shortcode_func );
         }

		}

      public static function video_hls($attr, $cont)
      {
         extract( shortcode_atts(array(
            "url" => '',
            "posters" => '',
         ), $attr));
         return "<div class='sexhls_video'>" . sh_hls_player($url, $posters) . "</div>";
      }

      public static function video_xr($attr, $cont)
      {
         extract( shortcode_atts(array(
            "url" => '',
            "posters" => '',
         ), $attr));
         return "<div class='sexvideo_videojs'>" . sh_xr_player($url, $posters) . "</div>";
      }


	}
}


?>

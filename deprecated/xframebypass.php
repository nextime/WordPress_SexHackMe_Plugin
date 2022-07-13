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

if(!class_exists('XFrameByPass')) {
   class XFrameByPass
   {
      public function __construct()
      {
         sexhack_log('XFrameByPass() Instanced');
         add_shortcode( 'xfbp', array( $this, 'xfbp_shortcode_fn' ));
      }

      public function xfbp_shortcode_fn($attributes, $content)
      {
         extract( shortcode_atts(array(
            'url' => 'https://www.sexhack.me',
         ), $attributes));
         return '<iframe is="x-frame-bypass" src="'.$url.'"></iframe>';
      }
   }
}




$SEXHACK_SECTION = array(
   'class' => 'XFrameByPass', 
   'description' => 'Bypass iframe limitation with x-frame-bypass', 
   'name' => 'sexhackme_xframebypass'
);

?>

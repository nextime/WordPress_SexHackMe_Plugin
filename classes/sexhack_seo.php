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

if(!class_exists('SexHackSEO')) {
   class SexHackSEO
   {
      public function __construct()
      {
         do_action( 'wp_head', array($this, 'add_meta_tags' ));
         sexhack_log('SexHackSEO() Instanced');
      }

      public function add_meta_tags() 
      {
         global $post;
         // XXX Use post tags for meta keywords, if not available like in pages?
         // What to use for description?
      }
   }
}




$SEXHACK_SECTION = array(
   'class' => 'SexHackSEO', 
   'description' => 'SEO functionality for SexHack', 
   'name' => 'sexhackme_seo'
);

?>

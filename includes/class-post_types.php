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


if(!class_exists('SH_PostTypes')) {
   class SH_PostTypes
   {

      public static function init()
      {

         // Advertising
         register_post_type('sexhackadv', array(
            'label' => 'Advertising','description' => '',
            'public' => true,
            'show_ui' => true,
            'show_in_menu' => true, // Visibility in admin menu.
            'menu_position' => 32,
            'capability_type' => 'post',
            'hierarchical' => false,
            'publicly_queryable' => true,
            'rewrite' => false,
            'query_var' => true,
            'has_archive' => true,
            'supports' => array('title','editor','page-attributes', 'shortcode'),
            'taxonomies' => array(),
            // there are a lot more available arguments, but the above is plenty for now

         ));
         add_filter( 'manage_sexhackadv_posts_columns', 'wp_SexHackMe\advert_add_id_column', 5 );
         add_action( 'manage_sexhackadv_posts_custom_column', 'wp_SexHackMe\advert_id_column_content', 5, 2 );

      }


	}
}

?>

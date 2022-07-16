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
			// For some pages we need to add also rewrite rules
			global $wp_rewrite;

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


         // SexHack Videos 
      	// TODO: the idea is to have custom post type for models profiles and for videos.
      	//       Ideally /$DEFAULTSLUG/nomevideo/ finisce sul corrispettivo prodotto woocommerce, 
      	//       /$DEFAULTSLUG/modelname/nomevideo/ finisce sul corrispettivo page sexhackme_video quando show_in_menu e' attivo.
     	 	//
      	//       Devo pero' verificare le varie taxonomy e attributi della pagina, vedere come creare un prodotto in wordpress
      	//       per ogni pagina sexhack_video che credo, sincronizzare prodotti e video pagine, gestire prodotti con lo stesso nome
      	//       ( credo si possa fare dandogli differenti slugs ) 
         register_post_type('sexhack_video', array(
             'labels'        => array(
                'name'                  => 'Videos',
                'singular_name'         => 'Video',
                'add_new'               => 'Add New',
                'add_new_item'          => 'Add New Video',
                'edit_item'             => 'Edit Video',
                'not_found'             => 'There are no videos yet',
                'not_found_in_trash'    => 'Nothing found in Trash',
                'search_items'          => 'Search videos',
               ),
            'description' => 'Videos for SexHack.me gallery',
            'public' => true,
            //'register_meta_box_cb' => array($this, 'sexhack_video_metaboxes'), // XXX BUG We need this NOW!!
            'show_ui' => true,
            'show_in_menu' => true,
            'show_in_rest' => true,
            'menu_position' => 32,
            'capability_type' => 'post', // TODO Maybe  We should create our own cap type?
            // 'capabilities' => array(), // Or just select specific capabilities here
            'hierarchical' => true,
            'publicly_queryable' => true,
            'rewrite' => false,
            'query_var' => true,
            'has_archive' => true,
            'supports' => array('title'), // 'thumbnail', 'editor','excerpt','trackbacks','custom-fields','comments','revisions','author','page-attributes'),
            'taxonomies' => array('category','post_tag'), // TODO  Shouldn't we have a "video_type" taxonomy for VR or flat?
         ));
			$DEFAULTSLUG = get_option('sexhack_gallery_slug', 'v');
         $projects_structure = '/'.$DEFAULTSLUG.'/%wooprod%/';
         $rules = $wp_rewrite->wp_rewrite_rules();
         if(array_key_exists($DEFAULTSLUG.'/([^/]+)/?$', $rules)) {
            sexhack_log("REWRITE: rules OK: ".$DEFAULTSLUG.'/([^/]+)/?$ => '.$rules[$DEFAULTSLUG.'/([^/]+)/?$']);
         } else {
            sexhack_log("REWRITE: Need to add and flush our rules!");
            $wp_rewrite->add_rewrite_tag("%wooprod%", '([^/]+)', "post_type=sexhack_video&wooprod=");
            $wp_rewrite->add_rewrite_tag("%videoaccess%", '([^/]+)', "videoaccess=");
            $wp_rewrite->add_permastruct($DEFAULTSLUG, $projects_structure, false);
            $wp_rewrite->add_permastruct($DEFAULTSLUG, $projects_structure."%videoaccess%/", false);
            update_option('need_rewrite_flush', 1);

         }


      }


	}
}

?>

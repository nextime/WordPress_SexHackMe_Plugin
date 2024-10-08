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
            'menu_icon' => SH_PLUGIN_DIR_URL . 'img/adv_icon.png',
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

         // Fix the permalink for video pages
         add_filter( 'post_type_link', __CLASS__."::replace_permalink", 10, 4 );

         // if the post get called instead of the video page, redirect!
         add_action( 'template_redirect', __CLASS__."::post_to_video" );

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
            'register_meta_box_cb' => 'wp_SexHackMe\SH_MetaBox::add_video_metaboxes', // XXX BUG We need this NOW!!
            'show_ui' => true,
            'show_in_menu' => true,
            'show_in_rest' => true,
            'menu_position' => 32,
            'menu_icon' => SH_PLUGIN_DIR_URL . 'img/porn_icon.png',
            'capability_type' => 'post', // TODO Maybe  We should create our own cap type?
            // 'capabilities' => array(), // Or just select specific capabilities here
            'hierarchical' => true,
            'publicly_queryable' => true,
            'rewrite' => false,
            'query_var' => true,
            'has_archive' => true,
            'supports' => array('title', 'thumbnail'), //'editor','excerpt','trackbacks','custom-fields','comments','revisions','author','page-attributes'),
            'taxonomies' => array(), //'category','post_tag'), // TODO  Shouldn't we have a "video_type" taxonomy for VR or flat?
         ));

         $statuses = array('creating','uploading','queue','processing','ready','published','error');
         foreach($statuses as $status) {
            register_post_status( $status, array(
                    'label'                     => $status,
                    'public'                    => true,
                    'label_count'               => _n_noop( $status.' <span class="count">(%s)</span>', $status.' <span class="count">(%s)</span>', 'sexhackme-domain' ),
                    'post_type'                 => array( 'sexhack_video' ), // Define one or more post types the status can be applied to.
                    'show_in_admin_all_list'    => true,
                    'show_in_admin_status_list' => true,
                    'show_in_metabox_dropdown'  => true,
                    'show_in_inline_dropdown'   => true,
                    'dashicon'                  => 'dashicons-businessman',
				));
         }
         $rules = $wp_rewrite->wp_rewrite_rules();

			$DEFAULTSLUG = get_option('sexhack_gallery_slug', 'v');


         //sexhack_log($rules);
         if(!array_key_exists($DEFAULTSLUG.'/([^/]+)/?$', $rules)) {
				update_option('need_rewrite_flush', 1);
				sexhack_log("REWRITE: Need to add and flush our rules!");
         } else {
            sexhack_log("WE DON'T NEED REWRITE!!!!!!!!!!!!!!!!!!!!!!");
            //sexhack_log($rules);
         }

      } 

	   public static function replace_permalink( $post_link, $post, $leavename, $sample ) 
		{
    		 if ( 'sexhack_video' === $post->post_type ) {
				 $video = sh_get_video_from_post($post);
				 $post_link=get_site_url()."/".get_option('sexhack_gallery_slug', 'v')."/".$video->slug;
          }
          return $post_link;
      }

      public static function post_to_video()
      {
         $vpslug=get_query_var('sexhack_video', false);
         if($vpslug) {
            $video = sh_get_video_from_post(get_post());
            wp_redirect(get_site_url()."/".get_option('sexhack_gallery_slug', 'v')."/".$video->slug);
         }
      }


		public static function add_rewrites()
		{
				global $wp_rewrite;


				$DEFAULTSLUG = get_option('sexhack_gallery_slug', 'v');
            sexhack_log("REWRITE: ADDING RULES");
            //flush_rewrite_rules();

            $pid = get_option('sexhack_video_page', false);
            if($pid) $redir = "page_id=$pid";
            else 
               $redir = "pagename=$DEFAULTSLUG";


            add_rewrite_rule($DEFAULTSLUG.'/([^/]+)/([^/]+)/page/?([0-9]{1,})/?$', 
                                  'index.php?'.$redir.'&sh_video=$matches[1]&videoaccess=$matches[2]&paged=$matches[3]', 'top');
            add_rewrite_rule($DEFAULTSLUG.'/([^/]+)/([^/]+)/?$',
                                  'index.php?'.$redir.'&sh_video=$matches[1]&videoaccess=$matches[2]', 'top');
            add_rewrite_rule($DEFAULTSLUG.'/([^/]+)/page/?([0-9]{1,})/?$',
                                  'index.php?'.$redir.'&sh_video=$matches[1]&paged=$matches[2]', 'top');
            add_rewrite_rule($DEFAULTSLUG.'/([^/]+)/?$',
                                  'index.php?'.$redir.'&sh_video=$matches[1]','top');


            $vg_id = get_option('sexhack_video404_page', false);
            //if(is_int($vg_id) && $vg_id>0)
               add_rewrite_rule($DEFAULTSLUG.'/?$',
                  'index.php?page_id='.strval($vg_id), 'top');


            //update_option('need_rewrite_flush', 1);
            //sexhack_log($wp_rewrite->wp_rewrite_rules());

      }
	

   }
}

?>

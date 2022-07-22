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


if(!class_exists('SH_MetaBox')) {
   class SH_MetaBox
   {

      public static function add_video_metaboxes($post=false)
      {

         // Main configuration for Video pages
         add_meta_box( 'sh-mbox-videodescription', 'Video details', 'wp_SexHackMe\SH_MetaBox::main_metabox_video', 'sexhack_video', 'normal','default');

         // Remove Thumbnail featured image
         remove_meta_box( 'postimagediv', 'sexhack_video', 'side' );

         // Model selection
         add_meta_box('video_model', 'Model', 'wp_SexHackMe\SH_MetaBox::model_select_meta_box', 'sexhack_video', 'side', 'default');

         // Video categories
         add_meta_box('video_category', 'Video categories', 'wp_SexHackMe\SH_MetaBox::video_categories_meta_box', 'sexhack_video', 'side', 'default');
     
         // Video tags
         add_meta_box('video_tags', 'Video tags', 'wp_SexHackMe\SH_MetaBox::video_tags_meta_box', 'sexhack_video', 'side', 'default');

         // Re-add featured image thumbnail
         add_meta_box('video_postimagediv', 'Video Thumbnail', 'post_thumbnail_meta_box', 'sexhack_video', 'side', 'default');

         // XXX Remove Paid Member Subscription meta boxes
         remove_meta_box( 'pms_post_content_restriction', 'sexhack_video', 'normal');

         // XXX Remove Members plugin meta box
         remove_meta_box( 'members-cp', 'sexhack_video', 'advanced');

      }

      public static function main_metabox_video($post)
      {
         wp_nonce_field('video_description_nonce','sh_video_description_nonce');

         $video = sh_get_video_from_post($post->ID);
         if(!$video) 
         {
            $video = new SH_Video();
            $video->post_id = $post->ID;
            $video->post = $post;
         }
         sh_get_template("admin/metabox_video.php", array('video' => $video, 'post' => $post));   

      }

      public static function model_select_meta_box( $post, $box ) {
         $video = sh_get_video_from_post($post->ID);
         if(!$video) 
         {
            $video = new SH_Video();
            $video->post_id = $post->ID;
            $video->post = $post;
         }
         sh_get_template("admin/metabox_model.php", array('video' => $video, 'post' => $post));
      }

      public static function video_categories_meta_box( $post, $box ) {
         $video = sh_get_video_from_post($post->ID);
         if(!$video)
         {
            $video = new SH_Video();
            $video->post_id = $post->ID;
            $video->post = $post;
         }
         $cats = sh_get_categories();
         sh_get_template("admin/metabox_videocategories.php", 
                              array('video' => $video, 
                                    'post' => $post,
                                    'cats' => $cats,
                                    ));
      }

      

		public static function video_tags_meta_box( $post, $box ) {
         $video = sh_get_video_from_post($post->ID);
         if(!$video)
         {
            $video = new SH_Video();
            $video->post_id = $post->ID;
            $video->post = $post;
         }
			sh_get_template("admin/metabox_videotags.php" , array('video' => $video, 'post' => $post));
		}


      public static function save_meta_box_data($post_id)
      {
         return save_sexhack_video_meta_box_data($post_id);
      }

   }
   add_action('save_post', 'wp_SexHackMe\SH_MetaBox::save_meta_box_data' );
}


?>

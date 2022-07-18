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
         add_meta_box( 'sh-mbox-videodescription', 'Videos', 'wp_SexHackMe\SH_MetaBox::load_metabox_video', 'sexhack_video', 'normal','default');
         remove_meta_box( 'postimagediv', 'sexhack_video', 'side' );
         add_meta_box('postimagediv', 'Video Thumbnail', 'post_thumbnail_meta_box', 'sexhack_video', 'side', 'default');
      }

      public static function load_metabox_video($post)
      {
         wp_nonce_field('video_description_nonce','sh_video_description_nonce');

         $video = sh_get_video_from_post($post->ID);
         if(!$video) $video = new SH_Video();
         $video->post_id = $post->ID;
         $video->post = $post;
         sh_get_template("admin/metabox_video.php", array('video' => $video, 'post' => $post));   

      }


      public static function save_meta_box_data($post_id)
      {
         return SH_MetaBox::save_sexhack_video_meta_box_data($post_id);
      }

      public static function save_sexhack_video_meta_box_data( $post_id ) 
      {


         // Verify that the nonce is set and valid.
         if (!isset( $_POST['sh_video_description_nonce'])
            || !wp_verify_nonce( $_POST['sh_video_description_nonce'], 'video_description_nonce' ) ) {
            return;
         }

         // If this is an autosave, our form has not been submitted, so we don't want to do anything.
         if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
            return;
         }

         // Check the user's permissions.
         if ( isset( $_POST['post_type'] ) && 'page' == $_POST['post_type'] ) {

            if ( ! current_user_can( 'edit_page', $post_id ) ) {
               return;
            }

         }
          else {
            if ( ! current_user_can( 'edit_post', $post_id ) ) {
               return;
            }
         }
         /* OK, it's safe for us to save the data now. */

         // Make sure that it is set.
         if ( ! isset( $_POST['video_description'] ) ) {
            return;
         }
         
         $video = sh_get_video_from_post($post_id);
         if(!$video) $video = new SH_Video();
         $video->post_id = $post_id;
         $post = $video->get_post();
         
         $video->title = $post->post_title;
         $video->slug = $post->post_name;

         sexhack_log($post);
            
         // Sanitize user input.
         $video->description = sanitize_text_field( $_POST['video_description'] );

         // Update the meta field in the database.
         //update_post_meta( $post_id, 'video_description', $my_data );
         sh_save_video($video);

      }

   }
   add_action('save_post', 'wp_SexHackMe\SH_MetaBox::save_meta_box_data' );
}


?>

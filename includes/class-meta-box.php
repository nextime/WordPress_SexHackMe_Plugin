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

         // XXX Remove Paid Member Subscription meta boxes
         remove_meta_box( 'pms_post_content_restriction', 'sexhack_video', 'default');

         // XXX Remove Members plugin meta box
         remove_meta_box( 'members-cp', 'sexhack_video', 'default');

         // Re-add featured image thumbnail
         add_meta_box('video_postimagediv', 'Video Thumbnail', 'post_thumbnail_meta_box', 'sexhack_video', 'side', 'default');
      }

      public static function main_metabox_video($post)
      {
         wp_nonce_field('video_description_nonce','sh_video_description_nonce');

         $video = sh_get_video_from_post($post->ID);
         if(!$video) $video = new SH_Video();
         $video->post_id = $post->ID;
         $video->post = $post;
         sh_get_template("admin/metabox_video.php", array('video' => $video, 'post' => $post));   

      }

      public static function model_select_meta_box( $post, $box ) {
         sh_get_template("admin/metabox_model.php");
      }

      public static function video_categories_meta_box( $post, $box ) {
         sh_get_template("admin/metabox_videocategories.php");
      }

      

		public static function video_tags_meta_box( $post, $box ) {
    		$user_can_assign_terms = true; //current_user_can( $taxonomy->cap->assign_terms );
    		$comma                 = _x( ',', 'tag delimiter' );
    		$terms_to_edit         = "prova,prova2,antani"; //get_terms_to_edit( $post->ID, $tax_name );
    		if ( ! is_string( $terms_to_edit ) ) {
        		$terms_to_edit = '';
    		}
			sh_get_template("admin/metabox_videotags.php", array('terms_to_edit' => $terms_to_edit, "comma" => $comma, 'user_can_assign_terms' => $user_can_assign_terms));
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

         // XXX TODO Sanitize inputs!
         //
         // Title and slug 
         $video->title = $post->post_title;
         $video->slug = $post->post_name;


         // TODO Remove debug
         sexhack_log("SAVE post object:");
         sexhack_log($post);
         sexhack_log('   - $POST:');
         sexhack_log($_POST);

         // Video description
         $video->description = sanitize_text_field( $_POST['video_description'] );

         // Video thumbnail
         if(array_key_exists('video_thumbnail', $_POST) && sanitize_text_field($_POST['video_thumbnail']))
            $video->thumbnail = sanitize_text_field( $_POST['video_thumbnail'] );
         else if(array_key_exists('_thumbnail_id', $_POST)
            && is_numeric($_POST['_thumbnail_id'])
            && intval($_POST['_thumbnail_id']) > 0)
         {
            $video->thumbnail = intval($_POST['_thumbnail_id']);
         }
         else
            $video->thumbnail = false;



         // Save the video data in the database.
         sh_save_video($video);

      }

   }
   add_action('save_post', 'wp_SexHackMe\SH_MetaBox::save_meta_box_data' );
}


?>

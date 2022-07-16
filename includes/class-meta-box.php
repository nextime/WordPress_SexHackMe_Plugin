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
         add_meta_box( 'sh-mbox-videodescription', 'Video Description', array(__CLASS__, '::load_metabox_videodescription'), 'sexhack_video', 'normal','default');
         add_meta_box( 'sh-mbox-video', 'Video locations', array( __CLASS__, '::load_metabox_videolocations' ), 'sexhack_video', 'normal', 'default' );
         //remove_meta_box( 'postimagediv', 'sexhack_video', 'side' );
         add_meta_box('postimagediv', 'Video Thumbnail', 'post_thumbnail_meta_box', 'sexhack_video', 'side', 'default');
      }

      public static function load_metabox_videodescription($post)
      {
         wp_nonce_field('video_description_nonce','sh_video_description_nonce');
         $value = get_post_meta( $post->ID, 'video_description', true );
         echo '<textarea style="width:100%" id="video_description" name="video_description">' . esc_attr( $value ) . '</textarea>';

      }

      public static function load_metabox_videolocations($post) //($object, $box)
      {
         wp_nonce_field( 'global_notice_nonce', 'global_notice_nonce' );

         $value = get_post_meta( $post->ID, '_global_notice', true );

         echo '<textarea style="width:100%" id="global_notice" name="global_notice">' . esc_attr( $value ) . '</textarea>';
      }


      public static function save_meta_box_data($post_id)
      {
         return $this->save_sexhack_video_meta_box_data($post_id);
      }

      public function save_sexhack_video_meta_box_data( $post_id ) 
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

         // Sanitize user input.
         $my_data = sanitize_text_field( $_POST['video_description'] );

         // Update the meta field in the database.
         update_post_meta( $post_id, 'video_description', $my_data );
      }

   }
   add_action('save_post', array('wp_SexHackMe\SH_MetaBox', '::save_meta_box_data' ));
}


?>

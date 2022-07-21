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


if(!class_exists('SH_PostType_Video')) {
   class SH_PostType_Video
   {
      public function __construct()
      {
         add_action('delete_post', array($this, 'delete_post'), 10, 2);
         add_action('transition_post_status', array($this, 'change_post_status'), 10, 3);
      }

      public function delete_post($post_id, $post)
      {

         // Make sure is the right post_type or exit
         if(!is_object($post) || ($post->post_type!='sexhack_video'))
            return;

         sh_delete_video_from_post($post_id);
      }

      public function change_post_status($new, $old, $post)
      {
         // Only sexhack_video posts...
         if(!is_object($post) || ($post->post_type!='sexhack_video')) 
            return;

         //sexhack_log("STATUS CHANGE: post ".$post->ID." changed from $old to $new");
         if($old===$new) return;

         $video = sh_get_video_from_post($post);
         if($video)
         {
            $vold = $video->status;
            if($new=='publish' && $video->status == 'ready') $video->status = 'published';
            else if($new!='publish' && $video->status == 'published') $video->status = 'ready';

            //sexhack_log("    *  video ".$video->id." is ".$video->status." (was $vold)");

            if($vold!=$video->status) sh_save_video($video);
         }
      }

   }

   // run
   new SH_PostType_Video;
}


?>

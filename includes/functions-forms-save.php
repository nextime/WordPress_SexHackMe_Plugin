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

function save_sexhack_video_forms( $post_id)
{

   // Verify that the nonce is set and valid.
   
   if ((!isset( $_POST['sh_video_description_nonce']) || !wp_verify_nonce( $_POST['sh_video_description_nonce'], 'video_description_nonce' )) 
      && (!isset( $_POST['sh_editvideo_nonce']) || !wp_verify_nonce( $_POST['sh_editvideo_nonce'], 'sh_editvideo')))
   {
      return;
   }

   $admin=false;
   if(isset( $_POST['sh_video_description_nonce']) && wp_verify_nonce( $_POST['sh_video_description_nonce'], 'video_description_nonce' )) $admin=true;

   // We need to be executed only when post_type is set...
   if(!isset($_POST['post_type'])) return;
   // ... ant it's set to sexhack_video
   if($_POST['post_type']!='sexhack_video') return;

   // ... what if we are saving the wrong post?
   if(get_post_type($post_id) != 'sexhack_video') return;


   // Make sure we don't get caught in any loop
   if($admin) unset($_POST['sh_video_description_nonce']);
   if(!$admin) unset($_POST['sh_editvideo_nonce']);

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
   else { // XXX Add more specific permission for our pages?
      if ( ! current_user_can( 'edit_post', $post_id ) ) {
          return;
      }
   }
   /* OK, it's safe for us to save the data now. */


   // **** VIDEO OBJECT CREACTION **** //


   // Make sure that it is set.
   if ( ! isset( $_POST['video_description'] ) ) {
      return;
   }

   // Get $video object
   $setslug = false;
   $newvideo = true;
   $video = sh_get_video_from_post($post_id);
   if(!$video) sexhack_log("Video object not initialized, new video?? (form passed \"$post_id\" \$post_id");
   if(!$video) sexhack_log($video);
   if(!$video) $setslug = true;
	if(!$video) $newvideo = true;
   if(!$video) $video = new SH_Video();


	// **** VIDEO OBJECT BASIC SETTINGS **** //	

   // Set post_id
   $video->post_id = $post_id;

   // set post
   $post = $video->get_post();

   // XXX TODO Sanitize inputs!
   //
   // Title and slug 
   $video->title = $post->post_title;
   if(!$video->slug || $setslug) $video->slug = uniqidReal()."-".$post->post_name;

   // Model
   if($admin) {
   	if(array_key_exists('video_model', $_POST) && is_numeric($_POST['video_model']) && intval($_POST['video_model']) > 0)
         $video->user_id = intval($_POST['video_model']);
   } else {
      $video->user_id = get_current_user_id();
   }

   // Video description
 	$video->description = sanitize_text_field( $_POST['video_description'] );


	
   // Save video status as "creating" to get video id
	if($newvideo) 
	{
		$video->status = "creating";
		$video = sh_save_video($video, 'FORM');	
	}

   // Video status
   $validstatuses = array('creating','uploading','queue','processing','ready','published','error');
   if(array_key_exists('video_status', $_POST) && in_array(sanitize_text_field($_POST['video_status']), $validstatuses))
      $video->status = sanitize_text_field($_POST['video_status']);
   else if(!$admin)  $video->status = get_post_status($post_id);

   // Video private
   if(array_key_exists('video_private', $_POST) && in_array($_POST['video_private'], array('Y','N')))
      $video->private = $_POST['video_private'];

   // Video visible
   if(array_key_exists('video_visible', $_POST) && in_array($_POST['video_visible'], array('Y','N')))
      $video->visible = $_POST['video_visible'];

	// Video price
	if(array_key_exists('video_price', $_POST) && is_numeric($_POST['video_price']) && (floatval($_POST['video_price']) >= 0))
      $video->price = floatval($_POST['video_price']);
   else $video->price = 0;

	// Video type
	if(array_key_exists('video_type', $_POST) && in_array($_POST['video_type'], array('VR', 'FLAT')))
		$video->video_type = $_POST['video_type'];

	// VR Projection
	if(array_key_exists('video_vr_projection', $_POST) && in_array($_POST['video_vr_projection'], array('VR180_LR','VR360_LR')))
		$video->vr_projection = $_POST['video_vr_projection'];

	// **** VIDEO ASSOCIATIVE ARRAYS **** //


   // Video Guests
   if(array_key_exists('vguests', $_POST) && is_array($_POST['vguests']))
   {
      foreach($_POST['vguests'] as $guest_id)
      {
         if(is_numeric($guest_id) && intval($guest_id) > 0)
         {
            if($admin || (!$admin && intval($guest_id) != get_current_user_id())) {
               $guest = get_userdata(intval($guest_id));
               if($guest) $video->add_guest($guest);
            }
         }
      }
   }
   // Make sure the guestss array is initialized
   $video->get_guests(false);


   // Video Categories
   if(array_key_exists('vcategory', $_POST) && is_array($_POST['vcategory']))
   {
      foreach($_POST['vcategory'] as $cat_id)
      {
         if(is_numeric($cat_id) && intval($cat_id) > 0)
         {
            $cat = sh_get_categories(intval($cat_id));
            if($cat) $video->add_category($cat);
         }
      }
   }
   // Make sure the categories array is initialized
   $video->get_categories(false);

   // Video Tags
   if(array_key_exists('video_tags', $_POST) && is_array($_POST['video_tags']))
   {
      foreach($_POST['video_tags'] as $tag_name)
      {
         $tag_name = str_replace("#", "", $tag_name);
         $vtags = $video->get_tags(false);
         if(sanitize_text_field(strtolower($tag_name)))
         {
            $tag_name = sanitize_text_field(strtolower($tag_name));
            $tag = sh_get_tag_by_name($tag_name, true);
            if($tag) $video->add_tag($tag);
         }  
         
      }  
   }  
   // Make sure the tags array is initialized
   $video->get_tags(false);


	// **** HLS AND DOWNLOAD VIDEO PROCESSING **** //

	// Differenciated content for access levels
	$public_exist=false;
   $members_exists=false;
   $selectedv=false;
	foreach(array('public','members','premium') as $vt)
   {
      if(!$admin &&  array_key_exists('video_'.$vt, $_POST) &&
         sanitize_text_field($_POST['video_'.$vt]) && !$selectedv)
      {
         $selectedv = get_option('sexhack_video_tmp_path', '/tmp')."/".sanitize_text_field($_POST['video_'.$vt]);
      }

		// HLS playlist 
		if($admin &&array_key_exists('video_hls_'.$vt, $_POST) && 
			check_url_or_path(sanitize_text_field($_POST['video_hls_'.$vt])) &&
			(strncasecmp(strrev(sanitize_text_field($_POST['video_hls_'.$vt])), '8u3m', 4) === 0)) 
		{
			$video->__set('hls_'.$vt, sanitize_text_field($_POST['video_hls_'.$vt]));
      }
      else if(!$admin &&  array_key_exists('video_'.$vt, $_POST) && 
         sanitize_text_field($_POST['video_'.$vt])) 
      {
         //$video->__set('hls_'.$vt, get_option('sexhack_video_tmp_path', '/tmp')."/".sanitize_text_field($_POST['video_'.$vt]));
			sh_add_video_job($video->id, 'process_hls_'.$vt, get_option('sexhack_video_tmp_path', '/tmp')."/".sanitize_text_field($_POST['video_'.$vt]));
			if($vt=='public') $public_exists=true;
			if($vt=='members') 
			{
				$members_exists=true;
				if(array_key_exists('video_createPublic_'.$vt, $_POST) && 
					array_key_exists('video_createPublicStart_'.$vt, $_POST) &&
					\DateTime::createFromFormat('H:i:s',$_POST['video_createPublicStart_'.$vt]) &&
					array_key_exists('video_createPublicDuration_'.$vt, $_POST) &&
					is_numeric($_POST['video_createPublicDuration_'.$vt]) && intval($_POST['video_createPublicDuration_'.$vt]) > 0 &&
					in_array($_POST['video_createPublic_'.$vt], array('Y','N')) && !$public_exists)
				{
					$file=get_option('sexhack_video_tmp_path', '/tmp')."/".sanitize_text_field($_POST['video_'.$vt]);
					$start=$_POST['video_createPublicStart_'.$vt];
					$duration=$_POST['video_createPublicDuration_'.$vt];
					sh_add_video_job($video->id, 'create_hls_public', json_encode(array('file' => $file, 'start' => $start, 'duration' => $duration )));
					$public_exists=true;
				}
			}
			if($vt=='premium') 
			{
            if(array_key_exists('video_createPublic_'.$vt, $_POST) &&
               array_key_exists('video_createPublicStart_'.$vt, $_POST) && 
               \DateTime::createFromFormat('H:i:s',$_POST['video_createPublicStart_'.$vt]) &&
               array_key_exists('video_createPublicDuration_'.$vt, $_POST) &&
               is_numeric($_POST['video_createPublicDuration_'.$vt]) && intval($_POST['video_createPublicDuration_'.$vt]) > 0 &&
               in_array($_POST['video_createPublic_'.$vt], array('Y','N')) && !$public_exists)
            {
               $file=get_option('sexhack_video_tmp_path', '/tmp')."/".sanitize_text_field($_POST['video_'.$vt]);
               $start=$_POST['video_createPublicStart_'.$vt];
               $duration=$_POST['video_createPublicDuration_'.$vt];
               sh_add_video_job($video->id, 'create_hls_public', json_encode(array('file' => $file, 'start' => $start, 'duration' => $duration )));
               $public_exists=true;
            }  
            if(array_key_exists('video_createMembers_'.$vt, $_POST) &&
               array_key_exists('video_createMembersStart_'.$vt, $_POST) && 
               \DateTime::createFromFormat('H:i:s',$_POST['video_createMembersStart_'.$vt]) &&
               array_key_exists('video_createMembersDuration_'.$vt, $_POST) &&
               is_numeric($_POST['video_createMembersDuration_'.$vt]) && intval($_POST['video_createMembersDuration_'.$vt]) > 0 &&
               in_array($_POST['video_createMembers_'.$vt], array('Y','N')) && !$members_exists)
            {
               $file=get_option('sexhack_video_tmp_path', '/tmp')."/".sanitize_text_field($_POST['video_'.$vt]);
               $start=$_POST['video_createMembersStart_'.$vt];
               $duration=$_POST['video_createMembersDuration_'.$vt];
               sh_add_video_job($video->id, 'create_hls_members', json_encode(array('file' => $file, 'start' => $start, 'duration' => $duration )));
               $members_exists=true;
            }  

			}
			$video->__set('hls_'.$vt, false);
      }
      else $video->__set('hls_'.$vt, false);
	
      // Download 
      if($admin && array_key_exists('video_download_'.$vt, $_POST) &&
         check_url_or_path(sanitize_text_field($_POST['video_download_'.$vt])))
      {  
         $video->__set('download_'.$vt, sanitize_text_field($_POST['video_download_'.$vt]));
      }
      else if(!$admin && array_key_exists($vt.'_isdownload', $_POST) &&  
         in_array($_POST[$vt.'_isdownload'], array('Y','N')) && array_key_exists('video_'.$vt, $_POST) && 
         sanitize_text_field($_POST['video_'.$vt])) 
		{
            //$video->__set('download_'.$vt, get_option('sexhack_video_tmp_path', '/tmp')."/".sanitize_text_field($_POST['video_'.$vt]));
				sh_add_video_job($video->id, 'process_download_'.$vt, get_option('sexhack_video_tmp_path', '/tmp')."/".sanitize_text_field($_POST['video_'.$vt]));
		}
      else $video->__set('download_'.$vt, false);
  
		// Text only data
      if($admin) {
		   foreach(array('size','format','codec','acodec','duration','resolution') as $key)
		   {
      	   if(array_key_exists('video_'.$key.'_'.$vt, $_POST) &&
         	   sanitize_text_field($_POST['video_'.$key.'_'.$vt]))
      	   {  
         	   $video->__set($key.'_'.$vt, sanitize_text_field($_POST['video_'.$key.'_'.$vt]));
      	   } else $video->__set($key.'_'.$vt, false);
         }
		} 
	
	}

	// **** VIDEO EXTRACTIONS PROPERTIES (XXX those NEEDS to be AFTER video processing) **** //

   // Video thumbnail
   if($admin) {
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
   } else {
      // Shoudn't we move it somewhere?
      if(isset($_POST['video_thumb'])
         && !empty($_POST['video_thumb'])) 
      {
         //$video->thumbnail = get_option('sexhack_video_tmp_path', '/tmp')."/".sanitize_text_field($_POST['video_thumb']);
         sh_add_video_job($video->id, 'process_thumb', get_option('sexhack_video_tmp_path', '/tmp')."/".sanitize_text_field($_POST['video_thumb'])); 
         $video->thumbnail = false;
      }
      else
      {
         if($selectedv) sh_add_video_job($video->id, 'create_thumb', $selectedv); 
         $video->thumbnail = false;
      }
   }


   // Animated gif path
   if($admin &&  array_key_exists('video_gif', $_POST) && check_url_or_path(sanitize_text_field($_POST['video_gif'])))
      $video->gif = sanitize_text_field($_POST['video_gif']);
   elseif(!$admin &&  array_key_exists('video_gif', $_POST) &&
      sanitize_text_field($_POST['video_gif'])) 
   {
      //$video->gif = sanitize_text_field(get_option('sexhack_video_tmp_path', '/tmp')."/".$_POST['video_gif']);
		sh_add_video_job($video->id, 'process_gif', get_option('sexhack_video_tmp_path', '/tmp')."/".sanitize_text_field($_POST['video_gif']));
		$video->gif = false;
	}
	elseif(!$admin && 
		array_key_exists('video_createStart_gif', $_POST) &&
		\DateTime::createFromFormat('H:i:s',$_POST['video_createStart_gif']) && 
		array_key_exists('video_createDuration_gif', $_POST) &&
		is_numeric($_POST['video_createDuration_gif']) && intval($_POST['video_createDuration_gif']) > 0 &&
		array_key_exists('video_createFPS_gif', $_POST) &&
		is_numeric($_POST['video_createFPS_gif']) && intval($_POST['video_createFPS_gif']) > 0 )
	{
		$duration=$_POST['video_createDuration_gif'];
		$start=$_POST['video_createStart_gif'];
		$fps=$_POST['video_createFPS_gif'];
		sh_add_video_job($video->id, 'create_gif', json_encode(array('start' => $start, 'fps' => $fps, 'duration' => $duration, 'file' => $selectedv )));
		$video->gif = false;
	}
   else
      $video->gif = false;

   // Small Animated gif path
   if($admin && array_key_exists('video_gif_small', $_POST) && check_url_or_path(sanitize_text_field($_POST['video_gif_small'])))
      $video->gif_small = sanitize_text_field($_POST['video_gif_small']);
   elseif(!$admin && array_key_exists('video_gif_small', $_POST) &&
      sanitize_text_field($_POST['video_gif_small']))
	{
      //$video->gif_small = sanitize_text_field(get_option('sexhack_video_tmp_path', '/tmp')."/".$_POST['video_gif_small']);
		sh_add_video_job($video->id, 'process_gif_small', get_option('sexhack_video_tmp_path', '/tmp')."/".sanitize_text_field($_POST['video_gif_small']));
		$video->gif_small = false;
	}
	elseif(!$admin &&
      array_key_exists('video_createStart_gif_small', $_POST) &&
      \DateTime::createFromFormat('H:i:s',$_POST['video_createStart_gif_small']) &&
      array_key_exists('video_createDuration_gif_small', $_POST) &&
      is_numeric($_POST['video_createDuration_gif_small']) && intval($_POST['video_createDuration_gif_small']) > 0 &&
      array_key_exists('video_createFPS_gif_small', $_POST) &&
      is_numeric($_POST['video_createFPS_gif_small']) && intval($_POST['video_createFPS_gif_small']) > 0 )
   {
      $duration=$_POST['video_createDuration_gif_small'];
      $start=$_POST['video_createStart_gif_small'];
      $fps=$_POST['video_createFPS_gif_small'];
      sh_add_video_job($video->id, 'create_gif_small', json_encode(array('start' => $start, 'fps' => $fps, 'duration' => $duration, 'file' => $selectedv )));
      $video->gif_small = false;
   }
   else
      $video->gif_small = false;

   // Preview video
   if($admin && array_key_exists('video_preview', $_POST) && check_url_or_path(sanitize_text_field($_POST['video_preview'])))
      $video->preview = sanitize_text_field($_POST['video_preview']);
   elseif(!$admin && array_key_exists('video_preview', $_POST) &&
      sanitize_text_field($_POST['video_preview']))
   {
      //$video->preview = sanitize_text_field(get_option('sexhack_video_tmp_path', '/tmp')."/".$_POST['video_preview']);
      sh_add_video_job($video->id, 'process_preview', sanitize_text_field(get_option('sexhack_video_tmp_path', '/tmp')."/".$_POST['video_preview']));
   }
   else
      $video->preview = false;

   // Socialpost
   if(!$admin)
   {

      //sh_add_video_job($video->id, 'socialpost_text'); // Not needed. shmproc will give you the text to do if it doesn't exists already
      sh_add_video_job($video->id, 'socialpost_media', $selectedv);
   }


   // Save the video data in the database.
   //sexhack_log("SAVING VIDEO FROM FORM");
   //sexhack_log($video);
   sh_save_video($video, 'FORM');

}



?>

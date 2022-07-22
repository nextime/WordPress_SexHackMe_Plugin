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

function save_sexhack_video_meta_box_data( $post_id )
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

   // Get $video object
   $video = sh_get_video_from_post($post_id);
   if(!$video) $video = new SH_Video();


   // Set post_id
   $video->post_id = $post_id;

   // set post
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

	// Model
	if(array_key_exists('video_model', $_POST) && is_numeric($_POST['video_model']) && intval($_POST['video_model']) > 0)
		$video->user_id = intval($_POST['video_model']);

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

   // Video status
   $validstatuses = array('creating','uploading','queue','processing','ready','published','error');
   if(array_key_exists('video_status', $_POST) && in_array(sanitize_text_field($_POST['video_status']), $validstatuses))
      $video->status = sanitize_text_field($_POST['video_status']);

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

	// Preview video
	if(array_key_exists('video_preview', $_POST) && check_url_or_path(sanitize_text_field($_POST['video_preview'])))
      $video->preview = sanitize_text_field($_POST['video_preview']);
   else
      $video->preview = false;

	// Animated gif path
	if(array_key_exists('video_gif', $_POST) && check_url_or_path(sanitize_text_field($_POST['video_gif'])))
      $video->gif = sanitize_text_field($_POST['video_gif']);
   else
      $video->gif = false;

   // Small Animated gif path
   if(array_key_exists('video_gif_small', $_POST) && check_url_or_path(sanitize_text_field($_POST['video_gif_small'])))
      $video->gif_small = sanitize_text_field($_POST['video_gif_small']);
   else
      $video->gif_small = false;


	// Differenciated content for access levels
	foreach(array('public','members','premium') as $vt)
	{
		// HLS playlist 
		if(array_key_exists('video_hls_'.$vt, $_POST) && 
			check_url_or_path(sanitize_text_field($_POST['video_hls_'.$vt])) &&
			(strncasecmp(strrev(sanitize_text_field($_POST['video_hls_'.$vt])), '8u3m', 4) === 0)) 
		{
			$video->__set('hls_'.$vt, sanitize_text_field($_POST['video_hls_'.$vt]));
		} else $video->__set('hls_'.$vt, false);
	
      // Download 
      if(array_key_exists('video_download_'.$vt, $_POST) &&
         check_url_or_path(sanitize_text_field($_POST['video_download_'.$vt])))
      {  
         $video->__set('download_'.$vt, sanitize_text_field($_POST['video_download_'.$vt]));
      } else $video->__set('download_'.$vt, false);
  
		// Text only data
		foreach(array('size','format','codec','acodec','duration','resolution') as $key)
		{
      	if(array_key_exists('video_'.$key.'_'.$vt, $_POST) &&
         	sanitize_text_field($_POST['video_'.$key.'_'.$vt]))
      	{  
         	$video->__set($key.'_'.$vt, sanitize_text_field($_POST['video_'.$key.'_'.$vt]));
      	} else $video->__set($key.'_'.$vt, false);
		} 
	
	}

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


   // Save the video data in the database.
   sh_save_video($video);

}



?>

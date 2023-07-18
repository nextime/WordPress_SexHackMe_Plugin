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

if(!class_exists('SH_VideoUpload')) {

   require_once(SH_PLUGIN_DIR_PATH."vendor/autoload.php");

   class SH_VideoUpload
   {
      public function __construct()
      {
         add_action('wp_ajax_file_upload', array($this, 'file_upload_callback'));
         add_action('wp_ajax_nopriv_file_upload', array($this, 'file_upload_callback'));

         add_action('wp_ajax_sh_editvideo', array($this, 'edit_video_callback'));
         add_action('wp_ajax_nopriv_sh_editvideo', array($this, 'edit_video_callback'));

      }

      public function file_upload_callback()
      {
    		check_ajax_referer('sh_video_upload', 'security');
    		//$arr_ext = array('image/png', 'image/jpeg', 'image/jpg', 'image/gif');
         //$arr_ext = array('video/mp4', 'video/webm','video/mov','video/m4v','video/mpg','video/flv');
			$config = new \Flow\Config();
			$config->setTempDir("/tmp");
         $request = new \Flow\Request();
         if(isset($_POST['uniqid'])) $uniqid = $_POST['uniqid'];
         else $uniqid = uniqid();

         $uploadFolder = get_option('sexhack_video_tmp_path', '/tmp');
			$uploadFileName = $uniqid . "_" . $request->getFileName();
         $uploadPath = $uploadFolder."/".$uploadFileName;

			if (\Flow\Basic::save($uploadPath, $config, $request)) {
            sexhack_log("Hurray, file was saved in " . $uploadPath);

			} else {
            sexhack_log("UPLOADING...");
			} 
         /*
    		if (in_array($_FILES['file']['type'], $arr_ext)) {
        		$upload = wp_upload_bits($_FILES["file"]["name"], null, file_get_contents($_FILES["file"]["tmp_name"]));
        		//$upload['url'] will gives you uploaded file path
    		}
         */
    		//wp_die();
      }

      public function edit_video_callback()
      {
         sexhack_log("PORCODIOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOO");
         sexhack_log($_POST);
         sexhack_log($_GET);
			
			// XXX Sanitize $_POST['title']
			if(!isset($_POST['title'])) return;
			$title = $_POST['title'];

			$post_id = wp_insert_post(array (
   			'post_type' => 'sexhack_video',
   			'post_title' => $title,
   			'post_status' => 'queue',
			));
      }

   }

   new SH_VideoUpload;
}


?>

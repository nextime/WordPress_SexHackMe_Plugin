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
   class SH_VideoUpload
   {
      public function __construct()
      {
         add_action('wp_ajax_file_upload', array($this, 'file_upload_callback'));
         add_action('wp_ajax_nopriv_file_upload', array($this, 'file_upload_callback'));
      }

      public function file_upload_callback()
      {
    		check_ajax_referer('sh_video_upload', 'security');
    		$arr_img_ext = array('image/png', 'image/jpeg', 'image/jpg', 'image/gif');
    		if (in_array($_FILES['file']['type'], $arr_img_ext)) {
        		$upload = wp_upload_bits($_FILES["file"]["name"], null, file_get_contents($_FILES["file"]["tmp_name"]));
        		//$upload['url'] will gives you uploaded file path
    		}
    		wp_die();
      }

   }

   new SH_VideoUpload;
}


?>

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


if(!class_exists('SH_RClone')) {
   class SH_RClone
   {
      protected $rclone;

      function __construct()
      {

         if(get_option('sexhack_rclone_path', false)) $this->rclone = get_option('sexhack_rclone_path');
         else $this->rclone = false;

         if(!$this->rclone && is_callable('shell_exec') && false === stripos(ini_get('disable_functions'), 'shell_exec'))
         {
            $rclone_path = str_replace("\n", "", shell_exec("which rclone"));
            if($rclone_path && is_executable($rclone_path))
            {
               $this->rclone = $rclone_path;
               //set_option('sexhack_rclone_path', $this->rclone);
            }
         }

         if($this->rclone && is_callable('shell_exec') && false === stripos(ini_get('disable_functions'), 'shell_exec')) {
            add_filter('sh_download_url_filter', array($this, 'get_download_url'));
         }
      } 

      function is_enabled()
      {
         return ((bool)$this->rclone && is_callable('shell_exec') && false === stripos(ini_get('disable_functions'), 'shell_exec'));
      }

      function get_remotes()
      {
         $res = array();
         if($this->is_enabled())
         {
            $res =  explode("\n", shell_exec($this->rclone." -q listremotes"));
         }
         return $res;
      }

      function get_path()
      {
         if(!$this->rclone) return '';
         return $this->rclone;
      }

      function get_download_url($file)
      {

			if(!($file && is_string($file) && strlen($file)>3)) return $file;

         $drivename = get_option('sexhack_rclone_gdrive_name', false);
         $shared = get_option('sexhack_rclone_gdrive_shared', false);

         if(!$drivename) return $file;

			$add="";
         if($this->is_enabled()) 
         {
            
            if((strlen($file) > 9) && (strncmp($file, "gdrive://", 9) === 0))
            {
               $gpath=substr($file, 9);
               if(!strncmp($gpath, '/', 1)===0) $gpath='/'.$gpath;
					if($shared) $add = "--drive-shared-with-me";
					$shres = shell_exec($this->rclone." -q  lsf $add ".$drivename.$gpath." -F ip");
					if($shres)
					{
						$split = explode(";", $shres);
						if(count($split) > 1)
						{
							if($split[0]) return $file="https://drive.google.com/uc?export=download&confirm=t&id=".$split[0];
						}
					}

            }
         }
         return $file;
      }
   }

   $GLOBALS['sh_rclone'] = new SH_RClone();
}
?>

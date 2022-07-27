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

use \IGD\Account;
use \IGD\App;


// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;


if(!class_exists('SH_GDrive')) {
   class SH_GDrive
   {

      function __construct()
      {
         add_filter('sh_download_url_filter', array($this, 'get_download_url'));
      } 

      function get_download_url($file)
      {
         $active = Account::get_active_account();
         if(!$active) return $file;

         $account_id = $active['id'];

         if(!($file && is_string($file) && strlen($file)>3)) return $file;
         if ( function_exists( 'igd_fs' ) )
         {
            // Integrate Google Drive Plugin is installed and active, so, filter it!
            
            if((strlen($file) > 9) && (strncmp($file, "gdrive://", 9) === 0))
            {
               $gpath=substr($file, 9);
               if(strncmp($gpath, '/', 1)===0) $gpath=substr($file, 10);
               $gparts = explode('/', $gpath);
               if(count($gparts) > 0)
               {
                  
                  $parent=false;
                  $gfile=false;
                  $success=false; 
                  $igd = App::instance($account_id);
                  // Try root first
                  foreach($gparts as $k => $part)
                  {
                     if($k == 0) 
                     {
                        $gdo = $igd->get_files(array('q'=> "{$part} in name"), 'root', false);
                     } else {
                        $gdo = $igd->get_files(array('q'=> "name='{$part}"), $parent, false);
                     }

                     if(!is_array($gdo) || (count($gdo) < 1) || array_key_exists('error', $gdo)) break;

                     $parent=false;
                     foreach($gdo as $g)
                     {
                        if($g['name']==$part)
                        {
                           $parent=$g['id'];
                           $gf=$g;
                        }
                     }
                  
                     if(!$parent) break;

                     if(count($gparts)-1 == $k) $success=true;
                  } 
                        

                  // Then try on the shared with me folder
                  if(!$success)
                  {
                     foreach($gparts as $k => $part)
                     {
                        if($k == 0)
                        {
                           $gdo = $igd->get_files(array('q'=> "parents='' and  sharedWithMe=true and '{$part}' in name"), 'shared', false);
                        } else {
                           $gdo = $igd->get_files(array('q'=> "name='{$part}"), $parent, false);
                        }
                        if(!is_array($gdo) || (count($gdo) < 1) || array_key_exists('error', $gdo))
                        {
                           if($k == 0)
                           {
                              $gdo = $igd->get_files(array('q'=> "parents='' and  sharedWithMe=true and '{$part}' in name"), 'shared', true);
                           } else {
                              $gdo = $igd->get_files(array('q'=> "name='{$part}"), $parent, true);

                           }
                        }
                        if(!is_array($gdo) || (count($gdo) < 1) || array_key_exists('error', $gdo)) break;

								$parent=false;
                     	foreach($gdo as $g)
                     	{  
                        	if($g['name']==$part)
                        	{
                           	$parent=$g['id'];
                              $gf=$g;
                        	}
								}
                     }  
						}

                  if(count($gparts)-1 == $k) $success=true;

                  if($success) $gfile = $gf;

                  if($gfile && ($gfile['type'] != 'application/vnd.google-apps.folder'))
                  {
                    $file="https://drive.google.com/uc?export=download&confirm=t&id=".$gfile['id'];
                    //$file="https://drive.google.com/open?action=igd-wc-download&id=".$gfile['id'];
                  }

               }
            }
         }
         return $file;
      }
   }

   new SH_Gdrive;
}


?>

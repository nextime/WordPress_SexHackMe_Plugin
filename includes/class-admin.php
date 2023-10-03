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


if(!class_exists('SH_Admin')) {
   class SH_Admin
   {

      public static function init()
      {
         // Add general settings section     
         add_settings_section('sexhackme-settings', ' ', 'wp_SexHackMe\sexhackme_settings_section', 'sexhackme-settings');

         // Add General settings section
         if( file_exists(SH_PLUGIN_DIR_PATH . 'includes/admin/functions-sexhackme.php') )
         {
            include_once(SH_PLUGIN_DIR_PATH . 'includes/admin/functions-sexhackme.php');
            register_setting('sexhackme-settings', 'sexhack-model-role');
         }

         // Add WC-PMS_Integration settings
         if( file_exists(SH_PLUGIN_DIR_PATH . 'includes/admin/functions-wcpms.php') )
         {
            include_once SH_PLUGIN_DIR_PATH . 'includes/admin/functions-wcpms.php';
            $plans = sh_get_subscription_plans();
            add_settings_section('sexhackme-wcpms-settings', ' ', 'wp_SexHackMe\settings_wcpms_section', 'sexhackme-wcpms-settings');
            register_setting('sexhackme-wcpms-settings', 'sexhack-wcpms-checkout');
            foreach($plans as $plan)
            {
               if($plan->price > 0)
               {
                  register_setting('sexhackme-wcpms-settings', 'sexhack-wcpms-'.strval($plan->id));
               }
            }
            add_settings_section('sexhackme-wcpms-settings', ' ', 'wp_SexHackMe\settings_wcpms_section_email', 'sexhackme-wcpms-settings-email');
            register_setting('sexhackme-wcpms-settings', 'sexhack_registration_mail_endpoint');

            add_settings_section('sexhackme-wcpms-settings', ' ', 'wp_SexHackMe\settings_wcpms_section_prodcat', 'sexhackme-wcpms-settings-prodcat');
            register_setting('sexhackme-wcpms-settings', 'sexhack_wcpms-prodcat');
            register_setting('sexhackme-wcpms-settings', 'sexhack_wcpms-prodvisible');


         }

         // Add Advertising settings
         if( file_exists(SH_PLUGIN_DIR_PATH . 'includes/admin/functions-advert.php') )
         {
            include_once SH_PLUGIN_DIR_PATH . 'includes/admin/functions-advert.php';
            add_settings_section('sexhackme-advert-settings', ' ', 'wp_SexHackMe\settings_advert_section', 'sexhackme-advert-settings');
            register_setting('sexhackme-advert-settings', 'sexadv_video_top');
            register_setting('sexhackme-advert-settings', 'sexadv_video_bot');
            register_setting('sexhackme-advert-settings', 'sexadv_video_native');
         }

         // Gallery settings
         if( file_exists(SH_PLUGIN_DIR_PATH . 'includes/admin/functions-gallery.php') )
         {
             include_once SH_PLUGIN_DIR_PATH . 'includes/admin/functions-gallery.php';
             add_settings_section('sexhackme-gallery-settings', ' ','wp_SexHackMe\gallery_settings_section', 'sexhackme-gallery-settings');
             register_setting('sexhackme-gallery-settings', 'sexhack_video_page');
             register_setting('sexhackme-gallery-settings', 'sexhack_gallery_page');
             register_setting('sexhackme-gallery-settings', 'sexhack_video404_page');
             register_setting('sexhackme-gallery-settings', 'sexhack_shmdown');
             register_setting('sexhackme-gallery-settings', 'sexhack_shmdown_uri');
             register_setting('sexhackme-gallery-settings', 'sexhack_video_tmp_path');
             register_setting('sexhackme-gallery-settings', 'sexhack_video_flat_path');
             register_setting('sexhackme-gallery-settings', 'sexhack_video_vr_path');
             register_setting('sexhackme-gallery-settings', 'sexhack_video_hls_storage');
             register_setting('sexhackme-gallery-settings', 'sexhack_video_hls_uri');
             register_setting('sexhackme-gallery-settings', 'sexhack_video_video_storage');
             register_setting('sexhackme-gallery-settings', 'sexhack_video_video_uri');
             register_setting('sexhackme-gallery-settings', 'sexhack_video_photo_storage');
             register_setting('sexhackme-gallery-settings', 'sexhack_video_photo_uri');
             register_setting('sexhackme-gallery-settings', 'sexhack_video_gif_storage');
             register_setting('sexhackme-gallery-settings', 'sexhack_video_gif_uri');
             register_setting('sexhackme-gallery-settings', 'sexhack_video_vr_storage');
             register_setting('sexhackme-gallery-settings', 'sexhack_video_vr_uri');
             register_setting('sexhackme-gallery-settings', 'sexhack_thumbnail_storage');
             register_setting('sexhackme-gallery-settings', 'sexhack_thumbnail_uri');
             register_setting('sexhackme-gallery-settings', 'sexhack_socialpost_storage');
             add_action('update_option', '\wp_SexHackMe\SH_Admin::update_gallery_slug', 10, 3);
             //register_setting('sexhackme-gallery-settings', 'sexhack_gallery_slug');
         }

         // RClone settings
         if( file_exists(SH_PLUGIN_DIR_PATH . 'includes/admin/functions-rclone.php') )
         {
            include_once SH_PLUGIN_DIR_PATH . 'includes/admin/functions-rclone.php';
            add_settings_section('sexhackme-rclone-settings', ' ','wp_SexHackMe\rclone_settings_section', 'sexhackme-rclone-settings');
            register_setting('sexhackme-rclone-settings', 'sexhack_rclone_path'); 
            register_setting('sexhackme-rclone-settings', 'sexhack_rclone_gdrive_name'); 
            register_setting('sexhackme-rclone-settings', 'sexhack_rclone_gdrive_shared');
         }
      }

      public static function update_gallery_slug($option, $old, $new)
      {
         switch($option)
         {
            case 'sexhack_video_page':
               if(!is_int($new)) break;
               $page = get_post($new);
               set_option('sexhack_gallery_slug', $page->post_name);
               update_option('need_rewrite_flush', 1);
               break;

            case 'sexhack_gallery_page':
               update_option('need_rewrite_flush', 1);
               break;
            case 'sexhack_video404_page':
               update_option('need_rewrite_flush', 1);
               break;

            default:
               break;
         }


      }

      public static function menu()
      {

         add_menu_page('SexHackMe Settings', 'SexHackMe', 'manage_options', 'sexhackme-settings',
               'wp_SexHackMe\sexhackme_admin_page', SH_PLUGIN_DIR_URL .'img/admin_icon.png', 31);

         // Add The main page again cause with multiple subpages apparently we need to do it.
          add_submenu_page( 'sexhackme-settings', 'SexHackMe Settings', 'General Settings',
                  'manage_options', 'sexhackme-settings');

         // TODO We don't have a main page yet, so, remove it.
         //remove_submenu_page( 'sexhackme-settings', 'sexhackme-settings' );


         // Add Gallery settings page
         if( file_exists(SH_PLUGIN_DIR_PATH . 'includes/admin/functions-gallery.php') )
         {
               add_submenu_page( 'sexhackme-settings',             // root slug
                           'Gallery',                              // title
                           'Gallery',                              // title
                           'manage_options',                       // capabilities
                           'gallery',                              // slug
                           'wp_SexHackMe\gallery_adminpage',       // callback
                           20);

         }

         // Add RClone interface
         if (file_exists(SH_PLUGIN_DIR_PATH . 'includes/admin/functions-rclone.php') )
         {
               add_submenu_page( 'sexhackme-settings',
                           'RClone',
                           'RClone',
                           'manage_options',
                           'rclone',
                           'wp_SexHackMe\rclone_adminpage',
                           50);
         }

         // Add page WC-PMS_Integration
         if( file_exists(SH_PLUGIN_DIR_PATH . 'includes/admin/functions-wcpms.php') )
         {

               add_submenu_page( 'sexhackme-settings',             // root slug
                           'WC-PMS Integration',                   // title
                           'WC-PMS Integration',                   // title
                           'manage_options',                       // capabilities
                           'pmswc-integration',                    // slug
                           'wp_SexHackMe\wcpms_adminpage',         // callback
                           60);                                    // position
         }

         // Add Advertising settings
         if( file_exists(SH_PLUGIN_DIR_PATH . 'includes/admin/functions-advert.php') )
         {
               add_submenu_page( 'sexhackme-settings',            // root slug
                           'Advertising',                         // title
                           'Advertising',                         // title
                           'manage_options',                      // capabilities
                           'advert',                              // slug
                           'wp_SexHackMe\advert_adminpage',       // callback
                           80);

         }

         // Add Video tags and categories subpages to Video edit menu
         //if(in_array('sexhack_video', get_post_types())
         //{
         //}
      }

   }
}


?>

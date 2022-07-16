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
			}

         // Add Advertising settings
         if( file_exists(SH_PLUGIN_DIR_PATH . 'includes/admin/functions-advert.php') )
         {
            include_once SH_PLUGIN_DIR_PATH . 'includes/admin/functions-advert.php';
			   add_settings_section('sexhackme-advert-settings', ' ', 'wp_SexHackMe\settings_advert_section', 'sexhackme-advert-settings');
            register_setting('sexhackme-advert-settings', 'sexadv_video_top');
			   register_setting('sexhackme-advert-settings', 'sexadv_video_bot');
         }

         // Gallery settings
         if( file_exists(SH_PLUGIN_DIR_PATH . 'includes/admin/functions-gallery.php') )
         {
             include_once SH_PLUGIN_DIR_PATH . 'includes/admin/functions-gallery.php';
             add_settings_section('sexhackme-gallery-settings', ' ','wp_SexHackMe\gallery_settings_section', 'sexhackme-gallery-settings');
             register_setting('sexhackme-gallery-settings', 'sexhack_gallery_slug');
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
         remove_submenu_page( 'sexhackme-settings', 'sexhackme-settings' );

		   // Add page WC-PMS_Integration
			if( file_exists(SH_PLUGIN_DIR_PATH . 'includes/admin/functions-wcpms.php') )
			{
			
         		add_submenu_page( 'sexhackme-settings', 				// root slug
									'WC-PMS Integration', 						// title
                           'WC-PMS Integration', 						// title
									'manage_options', 							// capabilities
									'pmswc-integration',						   // slug
                           'wp_SexHackMe\wcpms_adminpage');			// callback
			}

         // Add Advertising settings
			if( file_exists(SH_PLUGIN_DIR_PATH . 'includes/admin/functions-advert.php') )
      	{
               add_submenu_page( 'sexhackme-settings',            // root slug
                           'Advertising',                         // title
                           'Advertising',                         // title
                           'manage_options',                      // capabilities
                           'advert',                              // slug
                           'wp_SexHackMe\advert_adminpage');      // callback

      	}


         // Add Gallery settings page
         if( file_exists(SH_PLUGIN_DIR_PATH . 'includes/admin/functions-gallery.php') )
         {
               add_submenu_page( 'sexhackme-settings',             // root slug
                           'Gallery',                              // title
                           'Gallery',                              // title
                           'manage_options',                       // capabilities
                           'gallery',                              // slug
                           'wp_SexHackMe\gallery_adminpage');      // callback

         }

      }

   }
}


?>
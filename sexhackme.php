<?php
/**
 * Plugin Name: SexHackMe
 * Plugin URI: https://www.sexhack.me/SexHackMe_Wordpress
 * Description: Cumulative plugin for https://www.sexhack.me modifications to wordpress, woocommerce and storefront theme
 * Version: 0.0.1
 * Author: Franco Lanza
 *
 * ----------------------
 *
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

// XXX TODO: should we run only if woocommerce is installed?
//           look at https://woocommerce.com/document/create-a-plugin/#section-1


// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;


if(!class_exists('SexHackMe_Plugin')) {

   class SexHackMe_Plugin
   {

      public $prefix;

      public function __construct()
      {

          define( 'SH_VERSION', '0.0.1' );
          define( 'SH_PLUGIN_DIR_PATH', plugin_dir_path( __FILE__ ) );
          define( 'SH_PLUGIN_DIR_URL', plugin_dir_url( __FILE__ ) );
          define( 'SH_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );

          // The prefix of the plugin
          $this->prefix = 'sh_';
          define( 'SH_PREFIX', $this->prefix);

          // Install needed components on plugin activation
          register_activation_hook( __FILE__, array( $this, 'install' ) );

          register_deactivation_hook(__FILE__, array($this, 'uninstall') );

          add_action( 'plugins_loaded', array( $this, 'register_custom_meta_tables' ) );

          // Check if this is a newer version
          add_action( 'plugins_loaded', array( $this, 'update_check' ) );

          // Include dependencies
          $this->include_dependencies();

          // Initialize the components
          $this->init();


      }


      /*
       * Method that gets executed on plugin activation
       *
       */
      public function install( $network_activate = false ) 
      {

          // Handle multi-site installation
          if( function_exists( 'is_multisite' ) && is_multisite() && $network_activate ) {

              global $wpdb;

              $blog_ids = $wpdb->get_col( "SELECT blog_id FROM $wpdb->blogs" );

              foreach( $blog_ids as $blog_id ) {

                 switch_to_blog( $blog_id );

                 // Create needed tables
                 $this->create_tables();

                 // Add default settings
                 //$this->add_default_settings();

                 restore_current_blog();

              }

           // Handle single site installation
           } else {

              // Create needed tables
              $this->create_tables();

              // Add default settings
              //$this->add_default_settings();

           }

          // Add a cron job to be executed daily
          //$this->cron_job();
      }


      /*
       * Method that gets executed on plugin deactivation
       *
       */
      public function uninstall() 
      {

         // Clear cron job
         //$this->clear_cron_job();

      }


      /*
       * Method that checks if the current version differs from the one saved in the db
       *
       */
      public function update_check() 
      {

         $db_version = get_option( 'sh_version', '' );

         if( SH_VERSION != $db_version ) {

             $this->create_tables();

             do_action('sh_update_check');

             update_option( 'sh_version', SH_VERSION );
         }

      }

      /*
      * Create or update the database tables needed for the plugin to work
      * as needed
      *
      */
      public function create_tables() {

         global $wpdb;

         // Add / Update the tables as needed
         $charset_collate = $wpdb->get_charset_collate();
         $sql_query = "CREATE TABLE {$wpdb->prefix}{$this->prefix}videos (
             id bigint(20) AUTO_INCREMENT NOT NULL,
             user_id bigint(20) NOT NULL,
             post_id bigint(20) NOT NULL,
             product_id bigint(20) NOT NULL DEFAULT '0',
             status ENUM('creating', 'uploading', 'queue', 'processing', 'ready','published','error') NOT NULL DEFAULT 'creating',
             private ENUM('Y', 'N') NOT NULL DEFAULT 'N',
             visible ENUM('Y', 'N') NOT NULL DEFAULT 'Y',
             title varchar(256) NOT NULL,
             description varchar(1024) NOT NULL,
             created datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
             updated datetime NOT NULL ON UPDATE CURRENT_TIMESTAMP,
             slug varchar(256) NOT NULL,
             price float(10) NOT NULL DEFAULT '0',
             video_type ENUM('FLAT','VR') NOT NULL DEFAULT 'FLAT',
             vr_projection ENUM('VR180_LR', 'VR360_LR') NOT NULL DEFAULT 'VR180_LR',
             preview varchar(1024) DEFAULT NULL,
             hls_public varchar(1024) DEFAULT NULL,
             hls_members varchar(1024) DEFAULT NULL,
             hls_premium varchar(1024) DEFAULT NULL,
             thumbnail varchar(1024) DEFAULT NULL,
             gif varchar(1024) DEFAULT NULL,
             download_public varchar(1024) DEFAULT NULL,
             download_members varchar(1024) DEFAULT NULL,
             download_premium varchar(1024) DEFAULT NULL,
             size_public varchar(256) DEFAULT NULL,
             size_members varchar(256) DEFAULT NULL,
             size_premium varchar(256) DEFAULT NULL,
             format_public varchar(256) DEFAULT 'mp4',
             format_members varchar(256) DEFAULT 'mp4',
             format_premium varchar(256) DEFAULT 'mp4',
             codec_public varchar(256) DEFAULT 'h264',
             codec_members varchar(256) DEFAULT 'h264',
             codec_premium varchar(256) DEFAULT 'h264',
             acodec_public varchar(256) DEFAULT 'AAC',
             acodec_members varchar(256) DEFAULT 'AAC',
             acodec_premium varchar(256) DEFAULT 'AAC',
             duration_public varchar(256) DEFAULT NULL,
             duration_members varchar(256) DEFAULT NULL,
             duration_premium varchar(256) DEFAULT NULL,
             resolution_public varchar(256) DEFAULT NULL,
             resolution_members varchar(256) DEFAULT NULL,
             resolution_premium varchar(256) DEFAULT NULL,            
             views_public bigint(32) NOT NULL DEFAULT '0',
             views_members bigint(32) NOT NULL DEFAULT '0',
             views_premium bigint(32) NOT NULL DEFAULT '0',
             sells bigint(32) NOT NULL DEFAULT '0',
             PRIMARY KEY  (id),
             KEY user_id (user_id),
             KEY post_id (post_id),
             KEY slug (slug),
             KEY price (price),
             KEY video_type (video_type),
             KEY product_id (product_id)
         ) {$charset_collate};
         CREATE TABLE {$wpdb->prefix}{$this->prefix}video_meta (
             meta_id bigint(20) AUTO_INCREMENT NOT NULL,
             video_id bigint(20) NOT NULL DEFAULT '0',
             meta_key varchar(191),
             meta_value longtext,
             PRIMARY KEY  (meta_id),
             KEY video_id (video_id),
             KEY meta_key (meta_key)
         ) {$charset_collate};
         CREATE TABLE {$wpdb->prefix}{$this->prefix}videocategory (
             id bigint(20) AUTO_INCREMENT NOT NULL,
             category varchar(32) NOT NULL,
             PRIMARY KEY  (id),
             KEY category( category)
         ) {$charset_collate};
         CREATE TABLE {$wpdb->prefix}{$this->prefix}videotags (
             id bigint(20) AUTO_INCREMENT NOT NULL,
             tag varchar(32) NOT NULL,
             PRIMARY KEY (id),
             UNIQUE KEY tag (tag)
         ) {$charset_collate};
         CREATE TABLE {$wpdb->prefix}{$this->prefix}videoguests_assoc (
             id bigint(20) AUTO_INCREMENT NOT NULL,
             user_id bigint(20) NOT NULL,
             video_id bigint(20) NOT NULL,
             PRIMARY KEY (id),
             KEY user_id (user_id),
             KEY video_id (video_id)
         ) {$charset_collate};
         CREATE TABLE {$wpdb->prefix}{$this->prefix}videocategory_assoc (
             id bigint(20) AUTO_INCREMENT NOT NULL,
             cat_id bigint(20) NOT NULL,
             video_id bigint(20) NOT NULL,
             PRIMARY KEY  (id),
             KEY cat_id (cat_id),
             KEY video_id (video_id)
         ) {$charset_collate};
         CREATE TABLE {$wpdb->prefix}{$this->prefix}videoaccess_assoc (
             id bigint(20) AUTO_INCREMENT NOT NULL,
             user_id bigint(20) NOT NULL,
             video_id bigint(20) NOT NULL,
             PRIMARY KEY  (id),
             KEY user_id (user_id),
             KEY video_id (video_id)
         ) {$charset_collate};
         CREATE TABLE {$wpdb->prefix}{$this->prefix}videotags_assoc (
             id bigint(20) AUTO_INCREMENT NOT NULL,
             tag_id bigint(20) NOT NULL,
             video_id bigint(20) NOT NULL,
             PRIMARY KEY  (id),
             KEY video_id (video_id),
             KEY tag_id (tag_id)
         ) {$charset_collate};";

         require_once( ABSPATH . '/wp-admin/includes/upgrade.php' );

         dbDelta( $sql_query );

      }

      /*
       * Function that schedules a hook to be executed daily (cron job)
       *
       */
      public function cron_job() 
      {

         // Process payments for custom member subscriptions
         //if( !wp_next_scheduled( 'sh_cron_process_member_subscriptions_payments' ) )
         //    wp_schedule_event( time(), 'daily', 'sh_cron_process_member_subscriptions_payments' );
      }


      /*
       * Function that cleans the scheduler on plugin deactivation:
       *
       */
      public function clear_cron_job() 
      {

         //wp_clear_scheduled_hook( 'pms_cron_process_member_subscriptions_payments' );

      }


      /*
       * Add the default settings if they do not exist
       *
       */
      public function add_default_settings() 
      {
         $already_installed = get_option( 'sh_already_installed' );



         if ( !$already_installed )
            update_option( 'sh_already_installed', 'yes', false );

      }


      private function file_include($file)
      {
			
         if(isset($_GET['SHDEV']) || isset($_POST['SHDEV']))
			{
				$devar = explode('/', $file);
				$devar[count($devar)-1] = 'dev-'.$devar[count($devar)-1];
				$devfile = implode('/', $devar);
 				if (file_exists( SH_PLUGIN_DIR_PATH . $devfile  ))
            	return include_once SH_PLUGIN_DIR_PATH . $devfile;
			}
         if(file_exists( SH_PLUGIN_DIR_PATH . $file ) ) 
            return include_once SH_PLUGIN_DIR_PATH . $file;
         return false;
      }

      /*
       * Function to include the files needed
       *
       */
      public function include_dependencies() 
      {
   
         /* Manage Plugin Dependencies */
         $this->file_include('includes/class-tgm-plugin-activation.php');

         /* Utils  */
         $this->file_include('includes/functions-utils.php');

         /* Core functions  */
         $this->file_include('includes/functions-core.php');

         /* Custom Post Types declarations */
         $this->file_include('includes/class-post_types.php');

         /* Meta Boxes */
         $this->file_include('includes/class-meta-box.php');

         /* DB Query */
         $this->file_include('includes/class-query.php');

         /* Admin interface */
         $this->file_include('includes/class-admin.php');

         /* Hooks compatibility/translation */
         $this->file_include('includes/functions-hooks.php');

         /* Cryptocurrencies utils */
         $this->file_include('includes/functions-crypto.php');

         /* Paid Member Subscription utils */
         $this->file_include('includes/class-paid-member-subscriptions-integration.php');

         /* Video Players */
         $this->file_include('includes/class-video-players.php');

         /* Advertising support */
         $this->file_include('includes/functions-advert.php');

         /* Cam4 and Chaturbate support */
         $this->file_include('includes/class-livecam-site-support.php');

         /* WooCommerce support functions */
         $this->file_include('includes/functions-woocommerce-support.php');

         /* WooCommerce support class */
         $this->file_include('includes/class-woocommerce-support.php');

         /* Storefront customization support */
         $this->file_include('includes/class-storefront.php');

         /* Unlock integration class */
         $this->file_include('includes/class-unlock-support.php');

         /* Video */
         $this->file_include('includes/class-video.php');
         $this->file_include('includes/functions-video.php');
         $this->file_include('includes/class-post_type-video.php');


         /* Video Gallery */
         $this->file_include('includes/class-videogallery.php');

         /* Form posts functions */
         $this->file_include('includes/functions-forms-save.php');

         /* Shortcodes */
         $this->file_include('includes/class-shortcodes.php');

         /* Widgets */
         $this->file_include('includes/class-widgets.php');

         /* Hook to include needed files */
         do_action( 'pms_include_files' );


         /* Testing code */
         foreach( glob(dirname(__FILE__) . '/testing/*.php') as $class_path ) {
            try {
               include_once($class_path);
            } catch(\Throwable $e) {
               sexhack_log($e);
            }
         }

      }


      /**
       * Registers custom meta tables with WP's $wpdb object
       *
       */
      public function register_custom_meta_tables() 
      {

          global $wpdb;

          $wpdb->sh_video_meta = $wpdb->prefix . $this->prefix . 'video_meta';

      }



      /*
       * Initialize the plugin
       *
       */
      public function init() 
      {

         // Check plugin dependencies
         add_action( 'tgmpa_register', array($this, 'plugin_dependencies' ));

         // Set the main menu page
         add_action('admin_menu', array('wp_SexHackMe\SH_Admin', 'menu'));
         add_action('admin_init', array('wp_SexHackMe\SH_Admin', 'init'));


         // Check if we need to flush rewrite rules
         add_action('init', array($this, 'register_flush'), 10);
         add_action('init', array($this, 'flush_rewrite'), 900);

         // Enqueue scripts on the front end side. Priority 200 because of WooCommerce.
         add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_front_end_scripts' ), 200 );



         // Enqueue scripts on the admin side
         if( is_admin() )
             add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_scripts' ) );

         // Initialize Custom post_types 
         add_action( 'init', array( 'wp_SexHackMe\SH_PostTypes', 'init'));

         // Initialize shortcodes
         add_action( 'init', array( 'wp_SexHackMe\SH_Shortcodes', 'init' ) );
         //add_action( 'init', array( $this, 'init_dependencies' ), 1 );

         // Initialize storefront fixes/personalizations
         add_action( 'init', array( 'wp_SexHackMe\SH_StoreFront', 'init' ) );

         //Show row meta on the plugin screen (used to add links like Documentation, Support etc.).
         add_filter( 'plugin_row_meta', array( $this, 'plugin_row_meta' ), 10, 2 );

         // Hook to be executed on a specific interval, by the cron job (wp_schedule_event); used to check if a subscription has expired
         //add_action('pms_check_subscription_status','pms_member_check_expired_subscriptions');

         // Hook to be executed on a daily interval, by the cron job (wp_schedule_event); used to remove the user activation key from the db (make it expire) every 24 hours
         //add_action('pms_remove_activation_key','pms_remove_expired_activation_key');

         // Add new actions besides the activate/deactivate ones from the Plugins page
         add_filter( 'plugin_action_links_' . plugin_basename(__FILE__), array( $this, 'add_plugin_action_links' ) );
         

         //sexhack_log("SexHackMe PLUGIN Loaded!");

      }

      // XXX There are so many dependencies to add here...
      public function plugin_dependencies() 
      {
         $plugins = array(
            array(
               'name'      => 'WooCommerce',
               'slug'      => 'woocommerce',
               'required'  => false,
               //'is_callable' => 'wpseo_init',
            )
         );
         $config = array(
            'id'           => 'sexhackme',             // Unique ID for hashing notices for multiple instances of TGMPA.
            'default_path' => '',                      // Default absolute path to bundled plugins.
            'menu'         => 'tgmpa-install-plugins', // Menu slug.
            'parent_slug'  => 'plugins.php',           // Parent menu slug.
            'capability'   => 'manage_options',        // Capability needed to view plugin install page, should be a capability associated with the parent menu used.
            'has_notices'  => true,                    // Show admin notices or not.
            'dismissable'  => true,                    // If false, a user cannot dismiss the nag message.
              'dismiss_msg'  => '',                      // If 'dismissable' is false, this message will be output at top of nag.
            'is_automatic' => false,                   // Automatically activate plugins after installation or not.
            'message'      => '',                      // Message to output right before the plugins table.
         );
      }


      public function register_flush() 
      {
          register_setting('sexhackme-settings', 'need_rewrite_flush');
      }

      public function flush_rewrite()
      {
         if( get_option('need_rewrite_flush'))
         {
             sexhack_log("FLUSHING REWRITE RULES");
             flush_rewrite_rules(false);
             update_option('need_rewrite_flush', 0);
         }

      }

      public function enqueue_admin_scripts()
      {

         // Admin pages CSS
         wp_enqueue_style('sexhackme_admin', SH_PLUGIN_DIR_URL.'css/sexhackme_admin.css', array(), SH_VERSION);

         // Admin pages js 
         wp_enqueue_script('sexadmin_js', SH_PLUGIN_DIR_URL.'js/sexhackme_admin.js', array('jquery-ui-tabs'), SH_VERSION);
      }

      public function enqueue_front_end_scripts()
      {
         // HLS Player
         wp_enqueue_script('sexhls_baseplayer', SH_PLUGIN_DIR_URL.'js/hls.js', array(), SH_VERSION);
         wp_enqueue_script('sexhls_player_controls', SH_PLUGIN_DIR_URL.'js/sexhls.js', array('sexhls_baseplayer'), SH_VERSION);
         wp_enqueue_script('sexhls_mousetrap', SH_PLUGIN_DIR_URL.'js/mousetrap.min.js', array('sexhls_baseplayer'), SH_VERSION);


         // VideoJS Player (for 3D)
         wp_enqueue_script('sexvideo_baseplayer', SH_PLUGIN_DIR_URL.'js/video.min.js', array(), SH_VERSION);
         wp_enqueue_script('sexvideo_xrplayer', SH_PLUGIN_DIR_URL.'js/videojs-xr.min.js', array('sexvideo_baseplayer'), SH_VERSION);

         wp_enqueue_style ('videojs', SH_PLUGIN_DIR_URL.'css/video-js.min.css', array(), SH_VERSION);
         wp_enqueue_style ('sexhack_videojs', SH_PLUGIN_DIR_URL.'css/sexhackme_videojs.css', array(), SH_VERSION);
         wp_enqueue_style ('videojs-xr', SH_PLUGIN_DIR_URL.'css/videojs-xr.css', array('videojs'), SH_VERSION);

         // Sexhack Video Gallery
         wp_enqueue_style ('sexhackme_gallery', SH_PLUGIN_DIR_URL.'css/sexhackme_gallery.css', array(),  SH_VERSION);

         // Sexhack Fix Header
         wp_enqueue_style ('sexhackme_header', SH_PLUGIN_DIR_URL.'css/sexhackme_header.css', array(), SH_VERSION);

         // Fix Woocommerce Checkout
         wp_enqueue_style ('sexhackme_checkout', SH_PLUGIN_DIR_URL.'css/sexhackme_checkout.css', array(), SH_VERSION);

         // XFrame Bypass
         wp_enqueue_script('xfbp_poly', SH_PLUGIN_DIR_URL.'js/custom-elements-builtin.js', array(),  SH_VERSION);
         wp_enqueue_script('xfbp_js', SH_PLUGIN_DIR_URL.'js/x-frame-bypass.js', array(), SH_VERSION);


      }


      /**
       * Show row meta on the plugin screen. (Used to add links like Documentation, Support etc.)
       *
       * @param  mixed $links Plugin Row Meta
       * @param  mixed $file  Plugin Base file
       * @return array
       *
       */
      public static function plugin_row_meta( $links, $file ) {
          if ( $file == SH_PLUGIN_BASENAME ) {

              $row_meta = array(
                 'get_support'    => '<a href="' . esc_url( 
                        apply_filters( 'sh_docs_url', 'https://git.nexlab.net/SexHackMe/sexhackme/issues' ) 
                     ) . '" title="' . esc_attr( 'Get Support' ) . '" target="_blank">Get Support</a>',
                  );

              return array_merge( $links, $row_meta );
          }

          return (array) $links;
      }


      public function add_plugin_action_links( $links ) {

        if ( current_user_can( 'manage_options' ) ) {
           $links[] = '<span class="delete"><a href="' . wp_nonce_url( 
                  add_query_arg( array( 'page' => 'sh-uninstall-page' ) , 
                  admin_url( 'admin.php' ) 
              ), 
             'sh_uninstall_page_nonce' ) . '">Uninstall</a></span>';

           $settings_url = sprintf( '<a href="%1$s">%2$s</a>', 
              menu_page_url( 'sh-settings-page', false ), 
              esc_html( 'Settings' ) 
           );

           $docs_url = sprintf( '<a href="%1$s" target="_blank">%2$s</a>', 
              esc_url( 'https://www.sexhack.me/SexHackMe_Wordpress' ), 
              esc_html('Docs' ) 
           ); 

             array_unshift( $links, $settings_url, $docs_url );
          }   

          return $links;

      }

   }

   // Let's run the plugin!
   new SexHackMe_Plugin;
}



// DEBUG REWRITE RULES
if( WP_DEBUG === true ){
   // only matched?
   //add_action("the_post", 'wp_SexHackMe\debug_rewrite_rules');
   //sexhack_log("REQUEST: ".$_SERVER['REQUEST_URI']." QUERY: ".$_SERVER['QUERY_STRING']. "POST:");
   //sexhack_log($_POST);
}




?>

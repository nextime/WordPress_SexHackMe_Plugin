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

          // Install needed components on plugin activation
          register_activation_hook( __FILE__, array( $this, 'install' ) );

          register_deactivation_hook(__FILE__, array($this, 'uninstall') );

          //add_action( 'plugins_loaded', array( $this, 'register_custom_meta_tables' ) );

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
                 //$this->create_tables();

                 // Add default settings
                 //$this->add_default_settings();

                 restore_current_blog();

              }

           // Handle single site installation
           } else {

              // Create needed tables
              //$this->create_tables();

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

             //$this->create_tables();

             do_action('sh_update_check');

             update_option( 'sh_version', SH_VERSION );
         }

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


	   /*
       * Function to include the files needed
       *
       */
      public function include_dependencies() 
      {
	
			/*
         if( file_exists( SH_PLUGIN_DIR_PATH . 'includes/' ) )
            include_once( SH_PLUGIN_DIR_PATH . 'includes/' );
			*/		

			/* Manage Plugin Dependencies */
         if( file_exists( SH_PLUGIN_DIR_PATH . 'includes/class-tgm-plugin-activation.php' ) )
				include_once( SH_PLUGIN_DIR_PATH . 'includes/class-tgm-plugin-activation.php' );

         /* Utils  */
         if( file_exists( SH_PLUGIN_DIR_PATH . 'includes/functions-utils.php' ) )
            include_once( SH_PLUGIN_DIR_PATH . 'includes/functions-utils.php' );

			/* Cryptocurrencies utils */
         if( file_exists( SH_PLUGIN_DIR_PATH . 'includes/functions-crypto.php' ) )
            include_once( SH_PLUGIN_DIR_PATH . 'includes/functions-crypto.php' );

			/* Paid Member Subscription utils */
         if( file_exists( SH_PLUGIN_DIR_PATH . 'includes/functions-paid-member-subscriptions-integration.php' ) )
            include_once( SH_PLUGIN_DIR_PATH . 'includes/functions-paid-member-subscriptions-integration.php' );

         /* Video Players */
         if( file_exists( SH_PLUGIN_DIR_PATH . 'includes/class-video-players.php' ) )
            include_once( SH_PLUGIN_DIR_PATH . 'includes/class-video-players.php' );


         /* Shortcodes */
         if( file_exists( SH_PLUGIN_DIR_PATH . 'includes/class-shortcodes.php' ) )
            include_once SH_PLUGIN_DIR_PATH . 'includes/class-shortcodes.php';



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

          $wpdb->member_subscriptionmeta = $wpdb->prefix . $this->prefix . 'member_subscriptionmeta';

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
         add_action('admin_menu', array($this, 'admin_menu'));
         add_action('admin_init', array($this, 'initialize_plugin'));


         // Check if we need to flush rewrite rules
         add_action('init', array($this, 'register_flush'), 10);
         add_action('init', array($this, 'flush_rewrite'), 900);

         // Enqueue scripts on the front end side. Priority 200 because of WooCommerce.
         add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_front_end_scripts' ), 200 );



         // Enqueue scripts on the admin side
         //if( is_admin() )
         //    add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_scripts' ) );

         // Initialize shortcodes
         add_action( 'init', array( 'wp_SexHackMe\SH_Shortcodes', 'init' ) );
         //add_action( 'init', array( $this, 'init_dependencies' ), 1 );

         //Show row meta on the plugin screen (used to add links like Documentation, Support etc.).
         add_filter( 'plugin_row_meta', array( $this, 'plugin_row_meta' ), 10, 2 );

         // Hook to be executed on a specific interval, by the cron job (wp_schedule_event); used to check if a subscription has expired
         //add_action('pms_check_subscription_status','pms_member_check_expired_subscriptions');

         // Hook to be executed on a daily interval, by the cron job (wp_schedule_event); used to remove the user activation key from the db (make it expire) every 24 hours
         //add_action('pms_remove_activation_key','pms_remove_expired_activation_key');

         // Add new actions besides the activate/deactivate ones from the Plugins page
         add_filter( 'plugin_action_links_' . plugin_basename(__FILE__), array( $this, 'add_plugin_action_links' ) );
			

         sexhack_log("SexHackMe PLUGIN Loaded!");

			// Initialize the deprecated plugin parts 
			// XXX To be removed soon!
			$this->deprecated();

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


		/* FROM HERE IS THE DEPRECATED PART */

      public function deprecated()
      {


			$SECTIONS = array();
   		foreach( glob(dirname(__FILE__) . '/deprecated/*.php') as $class_path ) {
      		$SEXHACK_SECTION = false;
      		try {
         		include_once($class_path);
      		} catch(\Throwable $e) {
         		sexhack_log($e);
      		}
      		if(is_array($SEXHACK_SECTION)) $SECTIONS[] = $SEXHACK_SECTION; 
   		}
         $this->SECTIONS = $SECTIONS;
         $this->instances = array();
         foreach($SECTIONS as $section) {
            $class = "wp_SexHackMe\\".$section['class'];
            $this->instances[$section['name']] = new $class();
         }

      }

      /* public function settings_section() {
       *   echo "<h3>Enable following functionalities:</h3>";
       * }
       */
      
      public function initialize_plugin() 
      {
         add_settings_section('sexhackme-settings', ' ', array($this, 'settings_section'), 'sexhackme-settings');
         foreach($this->SECTIONS as $section) {
            register_setting('sexhackme-settings', $section['name']);
				if(array_key_exists('require-page', $section) && ($section['require-page']))
            { 
               if(is_array($section['require-page'])) {
                  foreach($section['require-page'] as $pagereq) {
                     if(array_key_exists('post_type', $pagereq)) {
                        if(array_key_exists('option', $pagereq)) register_setting('sexhackme-settings', $pagereq['option']);
                     }
                  }
               } else {
					   register_setting('sexhackme-settings', $section['name']."-page");
               }
				}
         }
      }

      public function admin_menu() 
      {
         add_menu_page('SexHackMe Settings', 'SexHackMe', 'manage_options', 'sexhackme-settings', 
            array($this, 'admin_page'), SH_PLUGIN_DIR_PATH .'img/admin_icon.png', 31);

			add_submenu_page( 'sexhackme-settings', 'SexHackMe Settings', 'Modules',
            'manage_options', 'sexhackme-settings');

         foreach($this->SECTIONS as $section) {
            if(get_option( $section['name'])=="1")
            {  
               if (array_key_exists('adminmenu', $section) && is_array($section['adminmenu'])) { 
                  foreach($section['adminmenu'] as $admsub) {
                     sexhack_log($admsub);
                     if(is_array($admsub) 
						      && array_key_exists('title', $admsub) 
						      && array_key_exists('callback', $admsub)
						      && array_key_exists('slug', $admsub)) {
						         add_submenu_page( 'sexhackme-settings', $admsub['title'], 
												$admsub['title'], 'manage_options', $admsub['slug'], 
												$admsub['callback']);
                     }
                  }
               }
				}
			}
      }

      public function admin_page()
      {
         if(file_exists( SH_PLUGIN_DIR_PATH . 'templates/admin/sexhackme.php'))
            include_once( SH_PLUGIN_DIR_PATH . 'templates/admin/sexhackme.php');
      }

   }

   // Let's run the plugin!
   new SexHackMe_Plugin;
}



// DEBUG REWRITE RULES
if( WP_DEBUG === true ){
   // only matched?
	//add_action("the_post", 'wp_SexHackMe\debug_rewrite_rules');
}



?>

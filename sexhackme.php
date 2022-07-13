<?php
/**
 * Plugin Name: SexHackMe
 * Plugin URI: https://www.sexhack.me/SexHackMe_Wordpress
 * Description: Cumulative plugin for https://www.sexhack.me modifications to wordpress, woocommerce and storefront theme
 * Version: 0.1
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
         //add_action( 'init', array( 'PMS_Shortcodes', 'init' ) );
         //add_action( 'init', array( $this, 'init_dependencies' ), 1 );

         //Show row meta on the plugin screen (used to add links like Documentation, Support etc.).
         //add_filter( 'plugin_row_meta', array( $this, 'plugin_row_meta' ), 10, 2 );

         // Hook to be executed on a specific interval, by the cron job (wp_schedule_event); used to check if a subscription has expired
         //add_action('pms_check_subscription_status','pms_member_check_expired_subscriptions');

         // Hook to be executed on a daily interval, by the cron job (wp_schedule_event); used to remove the user activation key from the db (make it expire) every 24 hours
         //add_action('pms_remove_activation_key','pms_remove_expired_activation_key');

         // Add new actions besides the activate/deactivate ones from the Plugins page
         //add_filter( 'plugin_action_links_' . plugin_basename(__FILE__), array( $this, 'add_plugin_action_links' ) );
			

         sexhack_log("SexHackMe PLUGIN Loaded!");

			// Initialize the deprecated plugin parts 
			// XXX To be removed soon!
			$this->deprecated();

      }

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
		      'id'           => 'sexhackme',                 // Unique ID for hashing notices for multiple instances of TGMPA.
		      'default_path' => '',                      // Default absolute path to bundled plugins.
		      'menu'         => 'tgmpa-install-plugins', // Menu slug.
		      'parent_slug'  => 'plugins.php',            // Parent menu slug.
		      'capability'   => 'manage_options',    // Capability needed to view plugin install page, should be a capability associated with the parent menu used.
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
         wp_enqueue_script('sexhls_baseplayer', SH_PLUGIN_DIR_URL.'js/hls.js');
         wp_enqueue_script('sexhls_player_controls', SH_PLUGIN_DIR_URL.'js/sexhls.js');
         wp_enqueue_script('sexhls_mousetrap', SH_PLUGIN_DIR_URL.'js/mousetrap.min.js');


         // VideoJS Player (for 3D)
         wp_enqueue_script('sexvideo_baseplayer', SH_PLUGIN_DIR_URL.'js/video.min.js');
         wp_enqueue_script('sexvideo_xrplayer', SH_PLUGIN_DIR_URL.'js/videojs-xr.min.js');

         wp_enqueue_style ('videojs', SH_PLUGIN_DIR_URL.'css/video-js.min.css');
         wp_enqueue_style ('sexhack_videojs', SH_PLUGIN_DIR_URL.'css/sexhackme_videojs.css');
         wp_enqueue_style ('videojs-xr', SH_PLUGIN_DIR_URL.'css/videojs-xr.css');

         // Sexhack Video Gallery
         wp_enqueue_style ('sexhackme_gallery', SH_PLUGIN_DIR_URL.'css/sexhackme_gallery.css');

         // Sexhack Fix Header
         wp_enqueue_style ('sexhackme_header', SH_PLUGIN_DIR_URL.'css/sexhackme_header.css');

         // Fix Woocommerce Checkout
         wp_enqueue_style ('sexhackme_checkout', SH_PLUGIN_DIR_URL.'css/sexhackme_checkout.css');

         // XFrame Bypass
         wp_enqueue_script('xfbp_poly', SH_PLUGIN_DIR_URL.'js/custom-elements-builtin.js');
         wp_enqueue_script('xfbp_js', SH_PLUGIN_DIR_URL.'js/x-frame-bypass.js');


      }

		/* FROM HERE IS THE DEPRECATED PART */

      public function deprecated()
      {


			$SECTIONS = array();
   		foreach( glob(dirname(__FILE__) . '/deprecated/*.php') as $class_path ) {
            sexhack_log($class_path);
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
            sexhack_log("Loading ".$section['name']);
            $this->instance_subclass($section);
         }

      }

      public function instance_subclass($section)
      {
         $class = "wp_SexHackMe\\".$section['class'];
         //sexhack_log($class);
         $this->instances[$section['name']] = new $class();
      }

      public function settings_section() {
         echo "<h3>Enable following functionalities:</h3>";
      }
      
      public function checkbox($res) 
      {
         if($res=="1") return "checked";
      }


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
            array($this, 'admin_page'), plugin_dir_url(__FILE__) .'/img/admin_icon.png', 31);

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
         ?>
            <div class="wrap">
               <h2>SexHackMe Plugin Settings</h2>
               <form method="post" action="/wp-admin/options.php">
               <?php settings_fields( 'sexhackme-settings' ); ?>
               <?php do_settings_sections( 'sexhackme-settings' ); ?>
               <table class="form-table">
               <?php foreach($this->SECTIONS as $section) { ?>
						<tr align="top">
               	   <th scope="row"><?php echo $section['description'];?></th>
							<td>
								<input type="checkbox" name="<?php echo $section['name'];?>" value="1" <?php echo $this->checkbox(get_option( $section['name'] )); ?>/>
								<br>
                      <?php  
                         if(array_key_exists('require-page', $section) && ($section['require-page']))
                         { 
                            $reqps = array();
                            if(is_string($section['require-page'])) 
                            {
                               $reqtitle="Select the base plugin module  page";
                               $reqpages=get_posts(array('post_type'    => $section['require-page'], 'parent' => 0));
                               $reqps[] = array('title' => $reqtitle, 'pages' => $reqpages, 'option' => $section['name']."-page");
                            } elseif(is_array($section['require-page'])) {
                               $i=0;
                               foreach($section['require-page'] as $rpage) {
                                  if(array_key_exists('post_type', $rpage)) {
                                     $reqpsa = array('title' => 'Select Page', 'option' => $section['name']."-page$i", 
                                        'pages' => get_posts(array('post_type'  => $rpage['post_type'], 'parent' => 0)));
                                     if(array_key_exists('option', $rpage)) $reqpsa['option'] = $rpage['option'];
                                     if(array_key_exists('title', $rpage)) $reqpsa['title'] = $rpage['title'];
                                     $reqps[] = $reqpsa;
                                  }
                                  $i++;

                               }
                            } else {
                               $reqtitle="Select the base plugin module  page";
                               $reqpages=get_pages();
                               $reqps[] = array('title' => $reqtitle, 'pages' => $reqpages, 'option' => $section['name']."-page");
                            }
                           foreach($reqps as $reqarr) { 
                        ?>
        						<select id="<?php echo $reqarr['option'];?>" name="<?php echo $reqarr['option']; ?>" class="widefat">
            					<option value="-1"><?php esc_html_e( 'Choose...', 'paid-member-subscriptions' ) ?></option>
            					<?php
									$opt=get_option($reqarr['option']);
            					foreach( $reqarr['pages'] as $page ) {
                					echo '<option value="' . esc_attr( $page->ID ) . '"';
										if ($opt == $page->ID) { echo "selected";}
										echo '>' . esc_html( $page->post_title ) . ' ( ID: ' . esc_attr( $page->ID ) . ')' . '</option>';
            					}  ?>
        						</select>
                        <p class="description"><?php echo $reqarr['title']; ?></p>
                        <?php } ?>
							<?php } ?>
							</td>
						</tr>
               <?php } ?>
               </table>
               <?php submit_button(); ?>
               </form>
            </div>
         <?php
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

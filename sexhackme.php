<?php
/**
 * Plugin Name: SexHackMe
 * Plugin URI: https://www.sexhack.me/SexHackMe_Wordpress
 * Description: Cumulative plugin for https://www.sexhack.me modifications to wordpress, woocommerce and storefront theme
 * Version: 0.1
 * Author: Franco Lanza
 */

/**
 * include all files from folder sites
 * Array for every classfile, format: array('description' => "<text>", 'name' => "<text>", "class" => "<classname>");
 * it MUST be present on every subclass!!!
 */


namespace wp_SexHackMe;

// XXX TODO: should we run only if woocommerce is installed?
//           look at https://woocommerce.com/document/create-a-plugin/#section-1


$SEXHACK_SECTIONS = array();
$SEXHACK_ERRORS = array();

$GLOBAL_NOSLUGS=array(
                  'wp-cron.php',
                  'wp-content',
                  'xmlrpc.php'
                );

if(!function_exists('sexhack_log')){
  function sexhack_log( $message ) {
    if( WP_DEBUG === true ){
      if( is_array( $message ) || is_object( $message ) ){
        error_log( "SexHackMe: ".print_r( $message, true ) );
      } else {
        error_log( "SexHackMe: ".$message );
      }
    }
  }
}

$FIRST_SLUG = "/";
$slug = explode("/", $_SERVER['REQUEST_URI']);
if(count($slug) > 1) {
   $FIRST_SLUG=explode("?", $slug[1])[0];
   $FIRST_SLUG=explode("#", $FIRST_SLUG)[0];
}

require_once dirname( __FILE__ ) . '/inc/class-tgm-plugin-activation.php';

add_action( 'tgmpa_register', 'sexhackme_register_required_plugins' );


function sexhackme_register_required_plugins() {
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

sexhack_log("FIRST_SLUG:".$FIRST_SLUG." REQUEST:".$_SERVER['REQUEST_URI']." QUERY:".$_SERVER['QUERY_STRING'] );

foreach( glob(dirname(__FILE__) . '/helpers/*.php') as $helper_path ) {
   sexhack_log('Loading '.$helper_path);
   require_once($helper_path);
}


if(!class_exists('SexHackMe')) {
   foreach( glob(dirname(__FILE__) . '/classes/*.php') as $class_path ) {
      $SEXHACK_SECTION = false;
      try {
         include_once($class_path);
      } catch(\Throwable $e) {
         sexhack_log($e);
      }
		//sexhack_log("Class_path:" . $class_path);
      //sexhack_log("Section:" . $SEXHACK_SECTION["name"]);
      if(is_array($SEXHACK_SECTION)) { $SEXHACK_SECTIONS[] = $SEXHACK_SECTION; }
      else { $SEXHACK_ERRORS[] = basename("/classes/".$class_path); }
   }


   class SexHackMe
   {
      public function __construct($SECTIONS)
      {
         global $FIRST_SLUG;
         //$FIRST_SLUG = \wp_SexHackMe\$FIRST_SLUG;
         sexhack_log("SexHackMe Instanciated");
         $this->SECTIONS = $SECTIONS;
         $this->instances = array();
         add_action('admin_menu', array($this, 'admin_menu'));
         add_action('admin_init', array($this, 'initialize_plugin'));
         add_action('init', array($this, 'register_flush'), 10);
         add_action('init', array($this, 'flush_rewrite'), 900);
         foreach($this->SECTIONS as $section) {
            if(get_option( $section['name'])=="1")
            {
               if (array_key_exists('noslugs', $section) && in_array($FIRST_SLUG, $section['noslugs'])) {
                  sexhack_log("NOSLUGS for ".$section['name']);
                  continue;
               }
               else {
                  if (array_key_exists('slugs', $section)) {
                     if(in_array($FIRST_SLUG, $section['slugs'])) {
                        sexhack_log("SLUGS for ".$section['name']);
                        $this->instance_subclass($section);
                     }
                  } 
                  else {
                     $this->instance_subclass($section);
                  }
               }
            }

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

      public function settings_field($name) 
      {
         echo $name;
      }

      public function checkbox($res) 
      {
         if($res=="1") return "checked";
      }

      public function register_flush() {
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

      public function initialize_plugin() 
      {
         add_settings_section('sexhackme-settings', ' ', array($this, 'settings_section'), 'sexhackme-settings');
         //register_setting('sexhackme-settings', 'need_rewrite_flush');
         foreach($this->SECTIONS as $section) {
            add_settings_field($section['name'], $section['name'], $section['name'], 
               array($this, 'settings_field'), 'sexhackme-settings', 'sexhackme-settings', $section['name']   );
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
            array($this, 'admin_page'), plugin_dir_url(__FILE__) .'/img/admin_icon.png', 150);

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
         global $SEXHACK_ERRORS;
         ?>
            <div class="wrap">
               <h2>SexHackMe Plugin Settings</h2>
               <?php if(!empty($SEXHACK_ERRORS)) { 
                  foreach($SEXHACK_ERRORS as $serr) { ?><h3 style="color: red;">Error loading <?php echo $serr ?>!</h3><?php }} ?>
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

   function sexhackme_plugin_run($SECTIONS, $SLUG)
   {
      sexhack_log("Running SexHackMe Plugins (".$SLUG.")");
      $SexHackMe = new SexHackMe($SECTIONS);
   }

   if(!in_array($FIRST_SLUG, $GLOBAL_NOSLUGS)) sexhackme_plugin_run($SEXHACK_SECTIONS, $FIRST_SLUG);
   else sexhack_log("NOSLUGS DETECTED: NOT RUNNING( ".$FIRST_SLUG." - ".$_SERVER['REQUEST_URI'].$_SERVER['QUERY_STRING'].")");
}



// DEBUG REWRITE RULES
if( WP_DEBUG === true ){
   // only matched?
	add_action("the_post", 'wp_SexHackMe\debug_rewrite_rules');
}



?>

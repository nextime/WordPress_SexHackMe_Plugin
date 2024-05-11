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

/* Sync Video Pages and Products */
if(!class_exists("SH_VideoProducts")) {
   class SH_VideoProducts
   {

      public function __construct()
      {
         //add_action('sh_save_video_after_query', array($this, 'sync_product_from_video'), 1, 10);

         // fired when saving SH videos to sync the product concurrently.
         add_filter('video_before_save', array($this, 'sync_product_from_video'));

         // fired when deleting a video
         add_action('sh_delete_video', array($this, 'delete_video_product'), 9, 1);

         // Add filter for download product uri fix
         add_filter('woocommerce_download_product_filepath', array($this, 'fix_download_uri'));
      }

      public function fix_download_uri($path)
      {
         $vr_storage = get_option('sexhack_video_vr_storage', false);
         $vr_uri = get_option('sexhack_video_vr_uri', '/VR/');
         $video_storage = get_option('sexhack_video_video_storage', false);
         $video_uri = get_option('sexhack_video_video_uri', '/Videos/');
         $gif_storage = get_option('sexhack_video_gif_storage', false);
         $gif_uri = get_option('sexhack_video_gif_uri', '/GIF/');
         $photo_storage = get_option('sexhack_video_photo_storage', false);
         $photo_uri = get_option('sexhack_video_photo_uri', '/Photos/');

         if($vr_storage && starts_with($vr_storage, $path)) return str_replace($vr_storage, $vr_uri, $path);
         if($gif_storage && starts_with($gif_storage, $path)) return str_replace($gif_storage, $gif_uri, $path);
         if($video_storage && starts_with($video_storage, $path)) return str_replace($video_storage, $video_uri, $path);
         if($photo_storage && starts_with($photo_storage, $path)) return str_replace($photo_storage, $photo_uri, $path);
         return $path;
      }

      public function delete_video_product($video)
      {
         if($video->product_id > 0) return sh_wc_deleteProduct($video->product_id, true);
         return false;
      }

      public function sync_product_from_video($video)
      {

         $prod = false;

         // Get product if already set in video
         if(intval($video->product_id) > 0)
            $prod = wc_get_product($video->product_id);

         // Create a new one if not present
         if(!$prod) {
            $prod = new \WC_Product_Simple();
         }

         // main product settings
         $prod->set_name($video->title);
         $prod->set_slug($video->slug); 

         // Product description.
			$video_link=site_url().'/'.get_option('gallery_slug', 'v')."/".$video->slug."/"; 
         $prod->set_short_description('<p>Whach me <a href="'.$video_link.'">ONLINE HERE</a></p>');
         $prod->set_description($video->description);

         // Product image: XXX TODO and if it isn't numeric?
         if(is_numeric($video->thumbnail))
            $prod->set_image_id( intval($video->thumbnail ));

         // Product status. Note we publish the product only 
         // if is there a downloadable video.
         if(($video->status == 'published') && ($video->has_downloads()))
            $prod->set_status('publish');
         else
            $prod->set_status('draft');

         // Catalog visibility
         if(get_option('sexhack_wcpms-prodvisible', false)) $prod->set_catalog_visibility('visible');
         else $prod->set_catalog_visibility('hidden');

         // Set the product as virtual and downloadable
         $prod->set_virtual(true);
         $prod->set_downloadable(true);

         // Price
         $prod->set_regular_price(floatval($video->price));

			// Prepare product attributes
			$attrs = array();

			// Videw Preview
			$attribute = new \WC_Product_Attribute();
			$attribute->set_name( 'video_preview' );
			$attribute->set_options( array($video->preview) );
			$attribute->set_visible( false );
			$attribute->set_variation( false );

			$attrs[] = $attribute;
			
			$prod->set_attributes($attrs);

         // Download links
         $download_public = apply_filters('sh_download_url_filter', $video->download_public);
         $download_members = apply_filters('sh_download_url_filter', $video->download_members);
         $download_premium = apply_filters('sh_download_url_filter', $video->download_premium);

         $wcdowns = array();

         if($download_public) 
         {
            $wcdownload = new \WC_Product_Download();
            $wcdownload->set_name(basename($video->download_public));  // XXX Do we really want to use basename here?
            $wcdownload->set_id( md5( $download_public ));
            $wcdownload->set_file( $download_public );
				$wcdowns[] = $wcdownload;
         }

         if($download_members)
         {  
            $wcdownload = new \WC_Product_Download();
            $wcdownload->set_name(basename($video->download_members));  // XXX Do we really want to use basename here?
            $wcdownload->set_id( md5( $download_members ));
            $wcdownload->set_file( $download_members );
				$wcdowns[] = $wcdownload;
         }

         if($download_premium)
         {  
            $wcdownload = new \WC_Product_Download();
            $wcdownload->set_name(basename($video->download_premium));  // XXX Do we really want to use basename here?
            $wcdownload->set_id( md5( $download_premium ));
            $wcdownload->set_file( $download_premium );
            $wcdowns[] = $wcdownload;
         }  

			$prod->set_downloads( $wcdowns );


         // Categories.
         // XXX We don't access categories of products, so, just put them in "videos"
         /*
         $catsids = array();
         if(count($video->get_categories(false)) > 0)
         {
            $cats = array();
            foreach($video->get_categories(false) as $cat) {
               $cats[$cat->id] = $cat->category;
            }
            wp_set_object_terms($prod->get_id(),$cats, 'product_cat');
         }
         */
         $cat_id = get_option('sexhack_wcpms-prodcat', false);
         if($cat_id) $prod->set_category_ids(array($cat_id));
         else {
            $cat = false;
            foreach( get_categories(array('taxonomy' => 'product_cat')) as $cat)
            {
               if($cat->name == 'Video') break;
            }
            if($cat) $prod->set_category_ids(array($cat->term_id));
         }

         // Sync tags with Video
         if(count($video->get_tags(false)) > 0) {
            $tags = array();
            foreach($video->get_tags(false) as $tag) {
               $tags[$tag->id] = $tag->tag;
            }
            wp_set_object_terms($prod->get_id(), $tags, 'product_tag');
         }

         // Save the product
		   $prod->save();
			$video->product_id = $prod->get_id();

			//sexhack_log($video);

			return $video;
      }

   }
   new SH_VideoProducts;
}


/* Class woocommerce add-to-checkout management */
if(!class_exists('SexhackWoocommerceCheckout')) {

   class SexhackWoocommerceCheckout
   {
      public function __construct()
      {
         //add_action( 'woocommerce_before_checkout_form', array($this, 'empty_cart'), 1);
			add_action( 'woocommerce_add_to_cart_validation', array($this, 'empty_cart'), 1);

         add_filter( 'woocommerce_add_to_cart_redirect', array($this, 'redirect_checkout_add_cart' ));
 
         add_action( 'woocommerce_simple_add_to_cart', array($this, 'oneclick_checkout'));
         add_action( 'woocommerce_after_shop_loop_item', array($this, 'product_loop_oneclick'), 9);

         add_action( 'template_redirect', array($this, 'header_buffer_start'), 1);
         add_action( 'wp_head', array($this, 'header_buffer_stop'), 1);

      }

      public function empty_cart() {
         if(isset($_GET['shm_direct_checkout']) && is_numeric($_GET['shm_direct_checkout'])) {
            global $woocommerce;
            $woocommerce->cart->empty_cart();
				//WC()->session->set('cart', array());
            $woocommerce->cart->add_to_cart(intval($_GET['shm_direct_checkout']), 1);
         } else {
            //$woocommerce->cart->empty_cart();
            return true;
         } 
      }

      public function redirect_checkout_add_cart() {
         return wc_get_checkout_url();
      }

      public function header_buffer_start() { ob_start(array($this, "header_buffer_callback")); }

      public function header_buffer_stop() { ob_end_flush(); }

      public function header_buffer_callback($buffer) {
         if(is_ssl()) $buffer = str_replace("http://", "https://", $buffer);
         
         return $buffer;
      }

      public function product_loop_oneclick() {
   		global $product;
         remove_action( 'woocommerce_after_shop_loop_item', 'woocommerce_template_loop_add_to_cart');
         if(!$product->is_type('variable')) {
            echo '<div class="custom-add-to-cart">';
      	   //woocommerce_template_loop_add_to_cart();
			   echo '<a class="button" href="'.wc_get_checkout_url()."?add-to-cart=".$product->get_id()."&shm_direct_checkout=".$product->get_id().'">Buy now!</a>';
            echo '</div>';
         }
      }

      public function oneclick_checkout() {
         global $product;
         
		   echo '<a class="button" href="'.wc_get_checkout_url()."?add-to-cart=".$product->get_id()."&shm_direct_checkout=".$product->get_id().'">Buy now!</a>';

         remove_action('woocommerce_'.$product->get_type().'_add_to_cart', 'woocommerce_'.$product->get_type().'_add_to_cart', 30);
      }
   }
   new SexhackWoocommerceCheckout;
}


/* Class to add Video to product page instead of image */
if(!class_exists('SexhackWoocommerceProductVideos')) {
   class SexhackWoocommerceProductVideos
   {
      public function __construct()
      {
         add_action( 'woocommerce_before_single_product', array($this, 'video_remove_default_woocommerce_image' ));
         add_filter( 'query_vars', array($this, 'themeslug_query_vars' ));

      }

      public function themeslug_query_vars( $qvars ) {
         $qvars[] = 'sexhack_forcevideo';
         return $qvars;
      }

      public function video_remove_default_woocommerce_image() {
         remove_action( 'woocommerce_before_single_product_summary', 'woocommerce_show_product_images', 20 );
         remove_action( 'woocommerce_product_thumbnails', 'woocommerce_show_product_thumbnails', 20 );
         add_action( 'woocommerce_before_single_product_summary', array($this, 'woocommerce_show_product_images_videos'), 30 );
      }

      public function woocommerce_show_product_images_videos() {
    
          // Get video and display
          $prod = wc_get_product(get_the_ID());
   
         // verify GET vars
         $bypass = get_query_var('sexhack_forcevideo', false);
   
         // Possible displays
         $disps = array('video', 'gif', 'image');
   
         // By default fallback to:
         $display='image';
   
         // detect attributes
         $video = $prod->get_attribute('video_preview');
         $gif = $prod->get_attribute('gif_preview');
   
         if(in_array($bypass, $disps)) $display=$bypass;
         else if($video) $display="video";
         else if($gif) $display="gif";
   
         switch($display) {
            case "video":
         
                 // Sanitize video URL
                 $video = esc_url( $video );

                 // Display video
                 echo '<div class="images"><div class="responsive-video-wrap"><h3>Video Preview</h3>';
               echo '<video src='."'$video'".' controls autoplay muted playsinline loop></video></div></div>';
               break;
         
            case "gif":
      
               // sanitize URL
               $gif = esc_url( $gif );
      
               // Display GIF
               echo '<div class="images"><img src="'.$gif.'" /></div>';
               break;
         
            case "image":
        
                 // No video defined so get thumbnail
                 wc_get_template( 'single-product/product-image.php' );
                break;
         }
      }

   }
   
   // Instance Product Video Support class
   new SexhackWoocommerceProductVideos;

}

/* Class to change woocommerce  account form fields */
if(!class_exists('WoocommerceAccountRemoveNameSurname')) {
   class WoocommerceAccountRemoveNameSurname
   {
      public function __construct()
      {  
         add_filter('woocommerce_save_account_details_required_fields', array($this, 'ts_hide_first_last_name'));
         add_action( 'woocommerce_edit_account_form_start', array($this, 'add_username_to_edit_account_form'));
      }

      // Add the custom field "username"
      public function add_username_to_edit_account_form()
      {  
         $user = wp_get_current_user();
         ?>
        <p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
        <label for="username"><?php _e( 'Username', 'woocommerce' ); ?> (Cannot be changed!) </label>
        <input type="text" class="woocommerce-Input woocommerce-Input--text input-text" name="username" id="username" 
               value="<?php echo esc_attr( $user->user_login ); ?>" disabled />
         </p>
         <?php
      }

      public function ts_hide_first_last_name($required_fields)
      {
         unset($required_fields["account_first_name"]);
         unset($required_fields["account_last_name"]);
         unset($required_fields["account_display_name"]);
         return $required_fields;
      }
   }
   
   // Start changing the woocommerce account form
   new WoocommerceAccountRemoveNameSurname;

}


if(!class_exists('WoocommerceEmailCheckout')) {
   class WoocommerceEmailCheckout
   {
      public function __construct()
      {
         add_filter( 'woocommerce_checkout_fields' , array($this,'simplify_checkout_virtual') );
         add_filter( 'woocommerce_login_redirect', array($this, 'fix_woocommerce_user'), 99, 2);

      }

      public function simplify_checkout_virtual( $fields ) {
    
         $only_virtual = true;
    
         foreach( WC()->cart->get_cart() as $cart_item_key => $cart_item ) {
            // Check if there are non-virtual products
            if ( (! $cart_item['data']->is_virtual()) && ($cart_item['data']->get_attribute('virtual') != 'y') ) $only_virtual = false;   
         }


          if( $only_virtual ) {
             unset($fields['billing']['billing_company']);
             unset($fields['billing']['billing_address_1']);
             unset($fields['billing']['billing_address_2']);
             unset($fields['billing']['billing_city']);
             unset($fields['billing']['billing_postcode']);
             unset($fields['billing']['billing_country']);
             unset($fields['billing']['billing_state']);
             unset($fields['billing']['billing_phone']);
             unset($fields['billing']['billing_first_name']);
             unset($fields['billing']['billing_last_name']);                
             add_filter( 'woocommerce_enable_order_notes_field', '__return_false' );
         }
     
         return $fields;
      }

      // Fix the user plan if none by add the default (usually free) one
       public function fix_woocommerce_user($redirect, $user)
        {

         if(is_object($user) && is_checkout())
         {
            sh_fix_user_with_no_plan($user->ID);
         }
         return $redirect;
      }

   }

   // Start the Woocommerce Email only on Checkout field
   new WoocommerceEmailCheckout;
}

if(!class_exists('SH_WooCommerce_Registration_Integration')) {

   class SH_WooCommerce_Registration_Integration
   {
      public function __construct()
      {                 
                     
         //$this->addcart = false;
                     

         // Register new endpoint (URL) for My Account page
         add_action( 'init', array($this, 'add_endpoints'), 300 );

         // Add new QUERY vars
         add_filter( 'query_vars', array($this, 'add_query_vars'), 0 );

         //  Insert the new endpoints into the My Account page from woocommerce and remove the unused ones
         add_filter( 'woocommerce_account_menu_items', array($this, 'modify_my_account_page_tabs'), 99 );

         /* Add content to the new tab
          *  NOTE: add_action must follow 'woocommerce_account_{your-endpoint-slug}_endpoint' format */
         add_action( 'woocommerce_account_subscriptions_endpoint', array($this, 'subscriptions_content'), 50, 6 );
         add_action( 'woocommerce_account_videomanager_endpoint', array($this, 'videomanager_content'), 50, 6 );
         add_action( 'woocommerce_account_modelsettings_endpoint', array($this, 'modelsettings_content'), 50, 6 );

         /* Inject random generate pass as we don't send it from the registration form */
         add_action( 'init', array($this, 'gen_random_pwd'), 5); // This need to happen before PMS detect the form at "10" sequence firing

         // Sending email with link to set user password 
         add_action("sh_register_form_after_create_user", array($this, "send_register_email_reset_password"), 10, 1 );


         // Manage manual payments for Paid Member Subscriptions plugin 
         // ( expecially for new users and change of subscription plan )
         add_filter('sh_get_redirect_url', array($this, 'redirect_after_manual_payment') );

      }

      // redirect 
      public function redirect_after_manual_payment($url)
      {
         $subscription_plan = sh_get_subscription_plan( absint($_POST['subscription_plans']));

         if( !isset( $subscription_plan->id ) || $subscription_plan->price == 0 )
            return $url;
         else
         {

            $prodid = get_option('sexhack-wcpms-'.strval($subscription_plan->id), false);
            $product = false;
            if($prodid) $product = wc_get_product($prodid);
            if(($product) && ($product->get_regular_price()==$subscription_plan->price)) $wcprods = array($product);
            else $wcprods = get_wc_subscription_products_priced($subscription_plan->price, $subscription_plan->id);
            if(count($wcprods) > 0)
            {
               //return wc_get_checkout_url()."?add-to-cart=".$wcprods[0]->get_id()."&quantity=1";
               return wc_get_checkout_url()."?shm_direct_checkout=".$wcprods[0]->get_id();
            }
            return $url; //home_url('/payment-info');
         }
      }

      // Register new endpoint (URL) for My Account page
      // Note: Re-save Permalinks or it will give 404 error
      function add_endpoints()
      {
         global $wp_rewrite;
         add_rewrite_endpoint( 'subscriptions', EP_ROOT | EP_PAGES );
         add_rewrite_endpoint( 'videomanager', EP_ROOT | EP_PAGES );
         add_rewrite_endpoint( 'modelsettings', EP_ROOT | EP_PAGES );
         $rules = $wp_rewrite->wp_rewrite_rules();
         if(!array_key_exists('(.?.+?)/subscriptions(/(.*))?/?$', $rules) 
            || !array_key_exists('(.?.+?)/videomanager(/(.*))?/?$', $rules)
            || !array_key_exists('(.?.+?)/modelsettings(/(.*))?/?$', $rules))
         {
            update_option('need_rewrite_flush', 1);
         }
      }

      // Add new QUERY vars
      public function add_query_vars( $vars )
      {
         $vars[] = 'subscriptions';
         return $vars;
      }

      //  Insert the new endpoint into the My Account menu
      public function modify_my_account_page_tabs( $items )
      {

         // Add CSS for icons
         wp_enqueue_style('sh_myaccount', SH_PLUGIN_DIR_URL.'css/sexhackme_myaccount.css', array(), SH_VERSION);

         $items['subscriptions'] = 'Subscriptions';
         if(array_key_exists('edit-address', $items)) unset($items['edit-address']);
         if(array_key_exists('payment-methods', $items)) unset($items['payment-methods']);

         if(user_is_model()) 
         {
            $items['videomanager'] = 'My Videos';
            $items['modelsettings'] = 'Model settings';
         }

         return $items;
      }


      // Add content to the new tab
      public function subscriptions_content()
      {
         sh_account_subscription_content();
      }

      public function videomanager_content()
      {
         sh_account_videomanager_content();
      }

      public function modelsettings_content()
      {
         sh_account_modelsettings_content();
      }

      public function send_register_email_reset_password($user_data)
      {
         $mailpage = get_option('sexhack_registration_mail_endpoint', false);
         if($mailpage) {
            $page = get_page($mailpage);
            $mailpage = $page->post_name;
         }
         $ulogin = $user_data;
         if(is_array($user_data) OR is_object($user_data)) $ulogin = $user_data["user_login"];
         send_changepwd_mail($ulogin, $mailpage);
      }

      public function gen_random_pwd()
      {
         sh_genpass_register_form();
      }
   }

   // Initilize
   new SH_WooCommerce_Registration_Integration;

}



if(!class_exists('SH_WooCommerce_Chaturbate_Payments')) {


	add_filter( 'woocommerce_payment_gateways', 'wp_SexHackMe\sh_add_payment_gateway_class' );
	function sh_add_payment_gateway_class( $gateways ) {
		$gateways[] = 'wp_SexHackMe\SH_WooCommerce_Chaturbate_Payments'; // your class name is here
		return $gateways;
	}

   add_action( 'plugins_loaded', 'wp_SexHackMe\chaturbate_payment_init' );
   function chaturbate_payment_init() {

      class SH_WooCommerce_Chaturbate_Payments extends \WC_Payment_Gateway {
         // Constructor for initializing the payment gateway
         public function __construct() {
            $this->id = 'shchaturbate';
            $this->method_title = 'Chaturbate Payment Gateway (SexHackMe)';
            $this->method_description = 'Receive payments in chaturbate.com tokens';
				$this->icon = SH_PLUGIN_DIR_URL.'/img/chaturbate_ico.png'; // URL to the icon

            $this->has_fields = true;
            $this->init_form_fields();
            $this->init_settings();

				$this->supports = array(
					'products'
				);

				$this->enabled = $this->get_option( 'enabled' );
            $this->title = $this->get_option('title');
            $this->description = $this->get_option('description');
				$this->uuid_prefix = $this->get_option('uuid_prefix');
				$this->cb_model = $this->get_option('cb_model');
				$this->cb_change = $this->get_option('cb_change');
				$this->instructions = $this->get_option('instructions');
            $this->api_passkey = $this->get_option('api_passkey');

            add_action('woocommerce_update_options_payment_gateways_' . $this->id, array($this, 'process_admin_options'));
				add_action( 'woocommerce_api_shchaturbate', array( $this, 'webhook_cb' ) );
      		//add_action( 'woocommerce_before_thankyou', array( $this, 'thankyou_page' ));
				add_filter('woocommerce_thankyou_order_received_text', array( $this, 'thankyou_page' ), 90, 2);

      		// Customer Emails.
      		add_action( 'woocommerce_email_before_order_table', array( $this, 'email_instructions' ), 10, 3 );


         }

         public function webhook() {
            if($_GET['key'] == $this->api_passkey) {
               $msg=$_GET['msg'];
               $tkns=intval($_GET['tkns']);
               if(str_starts_with($msg, $this->uuid_prefix)) {
                  $order_id = intval(str_replace($this->uuid_prefix, '', $msg));
                  $order = wc_get_order( $order_id );
                  $tktotal = $order->get_total()/$this->cb_change;
                  if($tkns >= $tktotal) {
                     $order->payment_complete();
                  }
               }
            }
	         //$order->reduce_order_stock();
         }

         // Initialize settings fields
         public function init_form_fields() {
            $this->form_fields = array(
					'enabled' => array(
						'title'       => 'Enable/Disable',
						'label'       => 'Enable Chaturbate Gateway',
						'type'        => 'checkbox',
						'description' => '',
						'default'     => 'no'
					),
               'title' => array(
                  'title' => __('Title', 'woocommerce'),
                  'type' => 'text',
                  'description' => __('This controls the title displayed during checkout.', 'woocommerce'),
                  'default' => __('Chaturbate Tokens', 'woocommerce'),
                  'desc_tip' => true,
               ),
               'description' => array(
                  'title' => __('Description', 'woocommerce'),
                  'type' => 'textarea',
                  'description' => __('This controls the description displayed during checkout.', 'woocommerce'),
                  'default' => __('Pay using Chaturbate tokens', 'woocommerce'),
                  'desc_tip' => true,
               ),
					'cb_change' => array(
						'title' => 'CB Tokens USD conversion',
						'type' => 'number',
						'custom_attributes' => array( 'step' => 'any', 'min' => '0' ),
						'description' => 'Value of 1 chaturbate token in USD',
						'desc_tip' => true,
						'default' => 0.05,
					),
               'instructions' => array(
		            'title'       => __( 'Instructions', 'woocommerce' ),
      		      'type'        => 'textarea',
            		'description' => __( 'Instructions that will be added to the thank you page and emails.', 'woocommerce' ),
            		'default'     => 'Send {TOTAL} chaturbate tokens to https://chaturbate.com/{CB_MODEL} with the message {ORDER_UUID}',
            		'desc_tip'    => true,
         		),
					'uuid_prefix' => array(
						'title' => 'Order ID Prefix',
						'type' => 'text',
						'description' => 'order ID prefix for messages included in tokens sent',
						'desc_tip' => true,
						'default' => 'SHMPAY-',
					),
					'cb_model' => array(
						'title' => 'Model name',
						'type' => 'text',
						'description' => 'Model name on chaturbate that will receive tokens',
						'desc_tip' => true,
						'default' => 'sexhackme',
               ),
               'api_passkey' => array(
                  'title' => 'API Passkey',
                  'type' => 'text',
                  'description' => 'API Key password for chaturbate bot API',
                  'desc_tip' => true,
                  'default' => 'sexhackme_key_changeme',
               ),

            );
         }

			private function print_instructions($order_id, $totalusd=-1)
			{
				$instr = str_replace('{CB_MODEL}', $this->cb_model, $this->instructions );
				$instr = str_replace('{ORDER_UUID}', $this->uuid_prefix.strval($order_id), $instr);
				if($totalusd < 0) {
					$order = wc_get_order( $order_id );
					$totalusd = $order->get_total();
				}
				$instr = str_replace('{TOTAL}', intval($totalusd/$this->cb_change), $instr);
				return $instr;
			}


   		//public function thankyou_page($order_id) {
			public function thankyou_page($msg, $order) {
				$order_id=$order->get_id();
      		if ( $this->instructions ) {
				    //$order = wc_get_order( $order_id );
					 if($order->get_payment_method() == 'shchaturbate')
						return wp_kses_post( wpautop( wptexturize( $this->print_instructions($order_id, $order->get_total()) ) ) );
        			 	//echo wp_kses_post( wpautop( wptexturize( $this->print_instructions($order_id, $order->get_total()) ) ) );
      		}
				return $msg;
   		}


   		public function email_instructions( $order, $sent_to_admin, $plain_text = false ) {
      		if ( $this->instructions && ! $sent_to_admin && 'shchaturbate' === $order->get_payment_method() && $order->has_status( apply_filters( 'woocommerce_shchaturbate_email_instructions_order_status', 'on-hold', $order ) ) ) {
         		echo wp_kses_post( wpautop( wptexturize( $this->print_instructions($order->get_id(), $order->get_total())  ) ) . PHP_EOL );
      		}
   		}


         // Process payment
         public function process_payment($order_id) {
				$order = wc_get_order( $order_id );
				
				$total = $order->get_total();		


            //
            /*
      		if ( $total > 0 ) {
         		// Mark as on-hold (we're awaiting the shchaturbate).
         		$order->update_status( 
						apply_filters( 'woocommerce_shchaturbate_process_payment_order_status', 'on-hold', $order ), 
						'Waiting for '.intval($total/$this->cb_change).' tokens to https://chaturbate.com/'.$this->cb_model.' with message '.$this->uuid_prefix.$order_id
					);
      		} else {
         		$order->payment_complete();
      		}
             */

            if($total<-0) 
               $order->payment_complete();
				//$order->update_status('on-hold', __( 'Pay by sending '.intval($total/0.05).' tokens to https://chaturbate.com/sexhackme', 'woocommerce' ));

				/*
				$order->add_order_note( 
					'Please go on https://chaturbate.com/sexhackme and tip '.intval($total/0.05).' tokens with the message SHM-'.strval($order_id), 
					true );
				*/				

				// Empty cart
				WC()->cart->empty_cart();
			

				return array(
					'result' => 'success',
					'redirect' => $this->get_return_url( $order ),
				);
         }

			/*
			// XXX We don't use anything special here.

         // Display payment fields during checkout
         public function payment_fields() {
            // Display payment fields such as credit card info or other required info
            // ...
         }
         // Validate payment fields
         public function validate_fields() {
            // Validate payment fields submitted by the customer
            // ...
         } */
      }
   }

}

?>

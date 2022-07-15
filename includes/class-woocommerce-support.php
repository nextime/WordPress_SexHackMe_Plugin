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


/* Class to add Video to product page instead of image */
if(!class_exists('SexhackWoocommerceProductVideos')) {
   class SexhackWoocommerceProductVideos
   {
      public function __construct()
      {
         sexhack_log('SexhackWoocommerceProductVideos() Instanced');
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
         sexhack_log('WoocommerceAccountRemoveNameSurname() Instanced');
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
         sexhack_log('WoocommerceEmailCheckout() Instanced');
			add_filter( 'woocommerce_checkout_fields' , array($this,'simplify_checkout_virtual') );
			add_filter( 'woocommerce_login_redirect', array($this, 'fix_woocommerce_user'), 99, 2);

      }

		public function simplify_checkout_virtual( $fields ) {
    
   		$only_virtual = true;
    
   		foreach( WC()->cart->get_cart() as $cart_item_key => $cart_item ) {
      		// Check if there are non-virtual products
      		if ( ! $cart_item['data']->is_virtual() ) $only_virtual = false;   
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
                     
         sexhack_log('SH_WooCommerce_Registration_Integration() Instanced');

         // Register new endpoint (URL) for My Account page
         add_action( 'init', array($this, 'add_subscriptions_endpoint'), 300 );

         // Add new QUERY vars
         add_filter( 'query_vars', array($this, 'subscriptions_query_vars'), 0 );

         //  Insert the new endpoint into the My Account menu
         add_filter( 'woocommerce_account_menu_items', array($this, 'add_subscriptions_link_my_account') );

         /* Add content to the new tab
          *  NOTE: add_action must follow 'woocommerce_account_{your-endpoint-slug}_endpoint' format */
         add_action( 'woocommerce_account_subscriptions_endpoint', array($this, 'subscriptions_content'), 50, 6 );

         /* Inject random generate pass as we don't send it from the registration form */
         add_action( 'init', array($this, 'gen_random_pwd'), 5); // This need to happen before PMS detect the form at "10" sequence firing

         // Sending email with link to set user password 
         add_action("sh_register_form_after_create_user", array($this, "send_register_email_reset_password") );


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
               return wc_get_checkout_url()."?add-to-cart=".$wcprods[0]->get_id()."&quantity=1";
            }
            return $url; //home_url('/payment-info');
         }
      }

      // Register new endpoint (URL) for My Account page
      // Note: Re-save Permalinks or it will give 404 error
      function add_subscriptions_endpoint()
      {
         global $wp_rewrite;
         sexhack_log("SUBSCRIPTION ENDPOINT ADDED");
         add_rewrite_endpoint( 'subscriptions', EP_ROOT | EP_PAGES );
         $rules = $wp_rewrite->wp_rewrite_rules();
         if(!array_key_exists('(.?.+?)/subscriptions(/(.*))?/?$', $rules))
         {
            sexhack_log("SUBSCRIPTION RULESS NEEDS REWRITE");
            update_option('need_rewrite_flush', 1);
         }
      }

      // Add new QUERY vars
      public function subscriptions_query_vars( $vars )
      {
         $vars[] = 'subscriptions';
         return $vars;
      }

      //  Insert the new endpoint into the My Account menu
      public function add_subscriptions_link_my_account( $items )
      {
         $items['subscriptions'] = 'Subscriptions';
         return $items;
      }


      // Add content to the new tab
      public function subscriptions_content()
      {
         sh_account_subscription_content();
      }

      public function send_register_email_reset_password($user_data)
      {
         $mailpage = get_option('sexhack_registration_mail_endpoint', false);
         if($mailpage) {
            $page = get_page($mailpage);
            $mailpage = $page->post_name;
         }
         send_changepwd_mail($user_data["user_login"], $mailpage);
      }

      public function gen_random_pwd()
      {
         sh_genpass_register_form();
      }
   }

	// Initilize
	new SH_WooCommerce_Registration_Integration;

}
?>

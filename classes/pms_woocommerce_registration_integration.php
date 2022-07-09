<?php
namespace wp_SexHackMe;

if(!class_exists('PmsWoocommerceRegistrationIntegration')) {


   function get_wc_subscription_products($spid=false)
   {  
		if($spid) $meta = array(
				'key'     => '_pms_woo_subscription_id',
				'value'   => $spid,
				'compare' => '=',
			);
	   else $meta = array(
				'key'     => '_pms_woo_subscription_id',
				'compare' => 'like',
			);
      $args = array(
         'posts_per_page' => 100,
         'post_type'      => 'product',
         'post_status'    => 'publish',
         'meta_key'   => '_pms_woo_subscription_id',
         'meta_query' => array($meta),
         );
      $query = new \WP_Query( $args );
      return $query;
   }


	function get_wc_subscription_products_priced($price, $pid=false)
	{
		$res = array();
		$pages = get_wc_subscription_products($pid);
		if ( $pages->have_posts() )
		{
			foreach($pages->posts as $post) 
			{
				$product = wc_get_product($post->ID);
				if(is_object($product) && (strval($price) == strval($product->get_regular_price())))
				{
					$res[] = $product;
				}
			}
		}
		
		return $res;
	}

   class PmsWoocommerceRegistrationIntegration
   {
      public function __construct()
      {
			
			//$this->addcart = false;

         sexhack_log('PmsWoocommerceRegistrationIntegration() Instanced');

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
			// XXX BUG! we should initialize this when is the right page and the right POST, 
			//          don't be a dick. Don't do it on every fucking page. 
			//			if you look hard enought       like really really hard...
			//			    you find a dick						pretty much 
			//                             everywhere
			//									  if you look hard 
			//											enought
			//										 like really
			//										 really hard
         //										you find a dick
			//										  pretty much
			//											everywhere
			add_action( 'init', array($this, 'gen_random_pwd'), 5); // This need to happen before PMS detect the form at "10" sequence firing

			// Sending email with link to set user password 
         add_action("pms_register_form_after_create_user", array($this, "send_register_email_reset_password") );


         // Manage manual payments for Paid Member Subscriptions plugin 
         // ( expecially for new users and change of subscription plan )
         add_action('pms_get_redirect_url', array($this, 'redirect_after_manual_payment') );

         // Manage checkout ( as we use only woocommerce for payments )
         // XXX This is a dirty hack, we are not going to filter anything!
         //add_filter("init", array($this, "redirect_to_checkout") ); 


         // Reorder membership subscription in registration page
         //add_action( 'pre_get_posts', array($this, 'sexhack_reorder_membership'), 1 );
      }

      /*
      public function sexhack_reorder_membership($query) {
         if ( ! is_admin() && in_array ( $query->get('post_type'), array('pms-subscriptions') ) ) {
            $query->set( 'order' , 'asc' );
            $query->set( 'orderby', 'price');      
         }
      }
      */
		/*
		public function add_to_cart()
		{
			sexhack_log("ADD_TO_CART()");
			if($this->addcart)
			{
				WC()->cart->add_to_cart( $this->addcart );
				wp_redirect(wc_get_checkout_url());
			}
		}
      */

      // NOTE: This is the "better way" for the checkout 
      //       redirect (look the following commented function)
      public function redirect_after_manual_payment($url)
      {
			if( !isset( $_POST['pay_gate'] ) || $_POST['pay_gate'] != 'manual' )
				return $url;
 
			$subscription_plan = pms_get_subscription_plan( $_POST['subscription_plans']);
 
			if( !isset( $subscription_plan->id ) || $subscription_plan->price == 0 )
				return $url;
			else
			{
				
				sexhack_log("CHECKOUT: ");
				//sexhack_log($subscription_plan);
				$prodid = get_option('sexhack-wcpms-'.strval($subscription_plan->id), false);
				$product = false;
				if($prodid) $product = wc_get_product($prodid);
				if(($product) && ($product->get_regular_price()==$subscription_plan->price)) $wcprods = array($product);
				else $wcprods = get_wc_subscription_products_priced($subscription_plan->price, $subscription_plan->id);
				sexhack_log($wcprods);
				if(count($wcprods) > 0)
				{
					// XXX we can't use WC() here as this is called in "init", while
               //     WC() can't be only called after wp_loaded, but here we have an
					//     immediate redirect, before wp_loaded, so, i can't do it that way.

				 	//$this->addcart = $wcprods[0]->get_id();
					//add_action('wp_loaded', array($this, 'add_to_cart'));
					//WC()->cart->add_to_cart( $this->addcart );

				 	return wc_get_checkout_url()."?add-to-cart=".$wcprods[0]->get_id()."&quantity=1";
				}
				return $url; //home_url('/payment-info');
			}
      }



      // XXX For Paid Membership subscription....
      //     Depends on it!
      //     XXX We found a better way.
      /*
      public function redirect_to_checkout() 
      {
         //pms_gateway_payment_id=NDQ&pmsscscd=c3Vic2NyaXB0aW9uX3BsYW5z&pms_gateway_payment_action=cmVnaXN0ZXI
         //$pid = 46  $pact = register  $scscd = subscription_plans
         if(isset($_GET['pms_gateway_payment_id']) 
            //&& is_integer(base64_decode($_GET['pms_gateway_payment_id']))
            && isset($_GET['pms_gateway_payment_action']) 
            && isset($_GET['pmsscscd'])
            && (base64_decode($_GET['pmsscscd'])=="subscription_plans")
            )
         {
            // Here we go we have to pay!
            $pid = base64_decode($_GET['pms_gateway_payment_id']);
            $pact = base64_decode($_GET['pms_gateway_payment_action']);
            $scscd = base64_decode($_GET['pmsscscd']);
            sexhack_log("ANTANI! $pid $pact $scscd");
            switch($pact)
            {
               case 'register':
                  $payment = pms_get_payment($pid);
                  sexhack_log("PAYMENT: ");
                  sexhack_log($payment);
                  break;
               default:
                  sexhack_log("UNMANAGED REQUEST WITH PACT: $pact in redirect_to_checkout()");

            }
         }
      } */

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
   		echo '<h3>Subscriptions</h3>';
   		echo do_shortcode( '[pms-account show_tabs="no"]' );
   		echo "<h3>Payment History:</h3>";
   		echo do_shortcode( '[pms-payment-history]');
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

		// XXX 8==D
		public function gen_random_pwd() 
		{
			$pwd = wp_generate_password();
			$_POST['pass1'] = $pwd;
			$_POST['pass2'] = $pwd;
		}
   }
}


function wcpms_adminpage()
{
	$plans = pms_get_subscription_plans();
	sexhack_log($plans);

?>
   <div class="wrap">
         <?php do_settings_sections( 'sexhackme-wcpms-settings' ); ?>
         <form method="post" action="/wp-admin/options.php">
         <?php settings_fields( 'sexhackme-wcpms-settings' ); ?>
         <table class="form-table">
				<?php 
				foreach($plans as $plan) 
				{ 
					if($plan->price > 0)
					{
				?>
            <tr align="top">
               <td>
                  <label><b><?php echo $plan->name ?> woocommerce product</b></label><br>
						<select id="sexhack-wcpms-<?php echo $plan->id;?>" name="sexhack-wcpms-<?php echo $plan->id; ?>" class="widefat">
						<?php
						$opt = get_option('sexhack-wcpms-'.strval($plan->id));
						foreach(get_wc_subscription_products_priced($plan->price, $plan->id) as $prod)
						{
							
						?>
							<option value='<?php echo $prod->id; ?>' <?php if($opt == $prod->id) echo "selected"; ?> >
								<?php echo $prod->get_title() ?> (<?php echo $prod->id; ?>)
							</option>

						<?php
						}
						?>
						</select>
               </td>
            </tr>
			   <?php 
					} 
				} 
				?>
         </table>
         <?php submit_button(); ?>
         </form>
   </div>
<?php

}

function settings_wcpms_section()
{
	echo "<h2>SexHackMe PMS - WooCommerce integration Settings</h2>";
}

function wcpms_initialize_options() 
{
	$plans = pms_get_subscription_plans();
	add_settings_section('sexhackme-wcpms-settings', ' ', 'wp_SexHackMe\settings_wcpms_section', 'sexhackme-wcpms-settings');
   register_setting('sexhackme-wcpms-settings', 'sexhack-wcpms-checkout');
	foreach($plans as $plan)
	{
		if($plan->price > 0)
		{
			register_setting('sexhackme-wcpms-settings', 'sexhack-wcpms-'.strval($plan->id));
		}
	}
}

add_action('admin_init', 'wp_SexHackMe\wcpms_initialize_options');

$SEXHACK_SECTION = array(
   'class' => 'PmsWoocommerceRegistrationIntegration', 
   'description' => 'Integrate woocommerce account page and sexhack modified registration form on pms to send password change link by email', 
   'name' => 'sexhackme_pmswooregistration',
   'require-page' => array(
                        array('post_type' => 'page', 'title' => 'Set password mail page', 'option' => 'sexhack_registration_mail_endpoint')
                     ),
   'adminmenu' => array(
                     array('title' => 'WC-PMS Integration',
                           'slug' => 'wcpms-integration',
                           'callback' => 'wp_SexHackMe\wcpms_adminpage')
                     ),

   'slugs' => array('account', 'register', 'login', 'password-reset')
);

?>

<?php
namespace wp_SexHackMe;

if(!class_exists('PmsWoocommerceRegistrationIntegration')) {
   class PmsWoocommerceRegistrationIntegration
   {
      public function __construct()
      {
			
         sexhack_log('PmsWoocommerceRegistrationIntegration() Instanced');

			// Register new endpoint (URL) for My Account page
         add_action( 'init', array($this, 'add_subscriptions_endpoint'), 300 );

			// Add new QUERY vars
         add_filter( 'query_vars', array($this, 'subscriptions_query_vars'), 0 );

			//  Insert the new endpoint into the My Account menu
			add_filter( 'woocommerce_account_menu_items', array($this, 'add_subscriptions_link_my_account') );

			/* Add content to the new tab
			 *  NOTE: add_action must follow 'woocommerce_account_{your-endpoint-slug}_endpoint' format */
			add_action( 'woocommerce_account_subscriptions_endpoint', array($this, 'subscriptions_content') );

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

      // Register new endpoint (URL) for My Account page
      // Note: Re-save Permalinks or it will give 404 error
      function add_subscriptions_endpoint() 
      {
         sexhack_log("SUBSCRIPTION ENDPOINT ADDED");
         add_rewrite_endpoint( 'subscriptions', EP_ROOT | EP_PAGES );
         //update_option('need_rewrite_flush', 1);
         //flush_rewrite_rules();
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
			send_changepwd_mail($user_data["user_login"]);
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




$SEXHACK_SECTION = array(
   'class' => 'PmsWoocommerceRegistrationIntegration', 
   'description' => 'Integrate woocommerce account page and sexhack modified registration form on pms to send password change link by email', 
   'name' => 'sexhackme_pmswooregistration',
   'slugs' => array('account', 'register', 'login', 'password-reset')
);

?>

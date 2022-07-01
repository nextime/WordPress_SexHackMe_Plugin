<?php
namespace wp_SexHackMe;

if(!class_exists('SexhackAddUnlockLogin')) {
   class SexhackAddUnlockLogin
   {
      public function __construct()
      {
         add_filter("login_form_bottom", array($this, "add_to_login"), 10, 2);
			add_action("woocommerce_after_order_notes", array($this, "add_to_checkout"));
			add_filter("pms_register_shortcode_content", array($this, "add_to_register"), 10, 2);
         sexhack_log('SexhackAddUnlockLogin() Instanced');
      }

		public function get_proto(){
			if(is_ssl()) {
				return 'https://';
			} else {
				return 'http://';
			}
		}

		public function unlock_get_login_url($redirect_url=false) {
    		$UNLOCK_BASE_URL = 'https://app.unlock-protocol.com/checkout';
			$rurl=apply_filters( 'unlock_protocol_get_redirect_uri', wp_login_url());
			if($redirect_url) {
				$rurl=$redirect_url;
			} 
			$login_url = add_query_arg(
       		array(
            	'client_id'    => apply_filters( 'unlock_protocol_get_client_id', wp_parse_url( home_url(), PHP_URL_HOST ) ),
            	'redirect_uri' => $rurl,
            	'state'        => wp_create_nonce( 'unlock_login_state' ),
       		),
       		$UNLOCK_BASE_URL
    		);
    		return apply_filters( 'unlock_protocol_get_login_url', $login_url );
		}

		public function unlock_button($string, $args, $redirect_url) 
		{
			$html="";
			if(!is_user_logged_in()) {
	   		$html="<hr><div style='text-align: center; width:100%;'><p>OR</p></div><hr>";
	   		$html.="<br><div style='text-align:left;width:100%;'<p><button onclick=\"window.location.href='".$this->unlock_get_login_url($redirect_url);
	   		$html.="'\" type='button'>Login with Crypto Wallet</button></p></div>";
			}
			return $string.$html;
		}



      // XXX Those 3 functions, hard-coded uri's that are dependent on a shortcode? that's sounds a bad idea, we 
      //     really need to implement the admin subpages for the plugin so i can setup easily more things!
		public function add_to_register($string, $args){
			return $this->unlock_button($string, $args, $this->get_proto().wp_parse_url( home_url(), PHP_URL_HOST )."/register");
		}

		public function add_to_login($string, $args){
			return $this->unlock_button($string, $args, $this->get_proto().wp_parse_url( home_url(), PHP_URL_HOST ));
		}

		public function add_to_checkout(){
			echo $this->unlock_button('', $args, $this->get_proto().wp_parse_url( home_url(), PHP_URL_HOST )."/checkout");
		}
   }
}




$SEXHACK_SECTION = array(
   'class' => 'SexhackAddUnlockLogin', 
   'description' => 'Integrate Unlock login in PMS login and registration page, as well as woocommerce checkout page', 
   'name' => 'sexhackme_addunlockbutton'
);

?>

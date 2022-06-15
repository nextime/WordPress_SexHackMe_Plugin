<?php
namespace wp_SexHackMe;

if(!class_exists('WoocommerceAccountRemoveNameSurname')) {
   class WoocommerceAccountRemoveNameSurname
   {
      public function __construct()
      {
         add_filter('woocommerce_save_account_details_required_fields', array($this, 'ts_hide_first_last_name'));
         add_action( 'woocommerce_edit_account_form_start', array($this, 'add_username_to_edit_account_form'));
         add_action('wp_enqueue_scripts', array( $this, 'add_css' ), 200);
         sexhack_log('WoocommerceAccountRemoveNameSurname() Instanced');
      }

      public function add_css()
      {
         wp_enqueue_style ('sexhackme_checkout', plugin_dir_url(__DIR__).'css/sexhackme_checkout.css');
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
}




$SEXHACK_SECTION = array(
   'class' => 'WoocommerceAccountRemoveNameSurname', 
   'description' => 'Remove Name and Surname fields from the woocommerce account details page', 
   'name' => 'sexhackme_woonamesurname'
);

?>

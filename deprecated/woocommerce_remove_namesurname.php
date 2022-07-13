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
}




$SEXHACK_SECTION = array(
   'class' => 'WoocommerceAccountRemoveNameSurname', 
   'description' => 'Remove Name and Surname fields from the woocommerce account details page', 
   'name' => 'sexhackme_woonamesurname'
);

?>

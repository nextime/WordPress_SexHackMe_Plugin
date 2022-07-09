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

if(!class_exists('WoocommerceEmailCheckout')) {
   class WoocommerceEmailCheckout
   {
      public function __construct()
      {
         sexhack_log('WoocommerceEmailCheckout() Instanced');
			add_filter( 'woocommerce_checkout_fields' , array($this,'simplify_checkout_virtual') );
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
   }
}




$SEXHACK_SECTION = array(
   'class' => 'WoocommerceEmailCheckout', 
   'description' => 'Reduce new user form on woocommerce checkout to email only for virtual/downloadable products', 
   'name' => 'sexhackme_woovirtcheckout'
);

?>

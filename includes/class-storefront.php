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


if(!class_exists('SH_StoreFront')) {
   class SH_StoreFront
   {
      public static function init()
      {
         // Remove the cart and the product search 
         remove_action( 'storefront_header', 'storefront_header_cart', 60 );
         remove_action( 'storefront_header', 'storefront_product_search', 40);

         // Remove StoreFront credits
         add_filter('storefront_credit_link', 'wp_SexHackMe\SH_StoreFront::credits');

         // add footer disclaimer
         //add_action('storefront_footer', 'wp_SexHackMe\sh_get_disclaimer')); // XXX I don't like positioning this way. Fix in CSS or sobstitute footer theme file?

         // Re add the cart in the right position
         add_action( 'storefront_header', 'storefront_header_cart', 40);

      }

      public static function credits($cred)
      {
         return '';
      }

   }
}


?>

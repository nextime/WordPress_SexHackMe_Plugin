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

if(!class_exists('StorefrontMoveHeaderCart')) {
   class StorefrontMoveHeaderCart
   {
      public function __construct()
      {
         sexhack_log('StorefrontMoveHeaderCart() Instanced');
         add_action( 'init', array($this, 'remove_header_cart' ));
         add_filter('storefront_credit_link', false);
         //add_action('storefront_footer', array($this, 'disclaimer')); // XXX I don't like positioning this way. Fix in CSS or sobstitute footer theme file?
			add_action( 'storefront_header', array($this, 'add_header_cart'), 40);
      }

      public function disclaimer()
      { ?>
         <div class="site-info">
         All pictures and videos are property of the respective models. Please copy them for personal use but do not republish any without permission.
         </div>
        <?php
      }

		public function remove_header_cart()
		{
			remove_action( 'storefront_header', 'storefront_header_cart', 60 );
			remove_action( 'storefront_header', 'storefront_product_search', 40); 
		}

		public function add_header_cart()
		{
			storefront_header_cart();
		}
   }
}




$SEXHACK_SECTION = array(
   'class' => 'StorefrontMoveHeaderCart', 
   'description' => 'Move storefront header cart and remove find products and credits', 
   'name' => 'sexhackme_sf_headercart'
);

?>

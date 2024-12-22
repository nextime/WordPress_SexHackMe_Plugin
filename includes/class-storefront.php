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
         
         // Remove primary navigation 
         //remove_action( 'storefront_header', 'storefront_primary_navigation', 50 );

         // readd primary navigation
         //add_action( 'storefront_header', 'storefront_primary_navigation', 21 );

         // Remove StoreFront credits
         add_filter('storefront_credit_link', 'wp_SexHackMe\SH_StoreFront::credits');

         // add footer disclaimer
         //add_action('storefront_footer', 'wp_SexHackMe\sh_get_disclaimer')); // XXX I don't like positioning this way. Fix in CSS or sobstitute footer theme file?

         // add footer navigation menu
         register_nav_menu('shm-footer-menu',__( 'Sexhackme Footer Menu' ));
         add_action( 'storefront_footer', 'wp_SexHackMe\SH_StoreFront::footer_menu', 15);

         // Add menu location for card widget
         register_nav_menu( 'shm-cartmenu', __( 'Cart widget Menu' ));

         // Re add the cart in the right position
         //add_action( 'storefront_header', 'storefront_header_cart', 40);
         add_action( 'storefront_header', 'wp_SexHackMe\SH_StoreFront::sexhackme_header_cart', 40);

         // Remove breadcrumb
         remove_action( 'storefront_before_content', 'woocommerce_breadcrumb', 10 );

         // Change handheld menu button text
         add_filter( 'storefront_menu_toggle_text', 'wp_SexHackMe\SH_StoreFront::storefront_menu_toggle_text' );

         // Add account button to handheld menu
         add_action( 'storefront_header', 'wp_SexHackMe\SH_StoreFront::add_handheld_account', 48); // storefront uses 50 priority for primary_navigation
         add_action( 'storefront_header', 'wp_SexHackMe\SH_StoreFront::add_handheld_cart', 49);


         // Replace 404 page if /404.php exists
         if (is_readable($_SERVER['DOCUMENT_ROOT'].'/404.php')) {
            add_action( 'template_redirect', 'wp_SexHackMe\SH_StoreFront::page404' );
         }

      }

      public static function page404() 
      {
         if(is_404()) 
         {
            wp_redirect( home_url( '/404.php' ) );
            die;
         }
      }

      // Sobstitute the function storefront_header_cart()
      public static function sexhackme_header_cart()
      {
         //storefront_header_cart();
         //echo "<ul><li/>antani</li></ul>";
         if ( storefront_is_woocommerce_activated() ) {
         	if ( is_cart() ) {
            	$class = 'current-menu-item';
         	} else {
            	$class = '';
         	}
            ?>
				<ul class="site-header-shmlogin menu">
					<li class="shmlogin">
            <?php
				if(!is_user_logged_in()) {
            ?>
                  <div class="sh_loginbutton">
                     <a href="/login">Login</a>
                     <div class="sh_loginpopup">
                     <?php
                        echo do_shortcode('[shincludepage page="login"]' );
                     ?>
                     </div>
                  </div>
                  <a class="sh_signupmenu" href="/register">Signup</a>
            <?php
				} else if(is_user_logged_in() && !user_is_premium()) {
            ?>
                   <a href="/account/">My Account</a>
						 <a class="sh_freememberacc" href="/product-category/subscriptions/">Premium</a>
            <?php
				} else if(is_user_logged_in() && user_is_premium()) {
            ?>
						<a href="/account/">My Account</a>
            <?php
				}
            ?>
						<i class="fa fa-user " style="position:relative;display:block;float:right;color:white;" aria-hidden="true"></i>
					</li>
				</ul>
      		<ul id="site-header-cart" class="site-header-cart menu"> 
               <li class="<?php echo esc_attr( $class ); ?>">
            		<?php storefront_cart_link(); ?>
         		</li>
         		<li>
            		<?php the_widget( 'WC_Widget_Cart', 'title=' ); ?>
         		</li>
      		</ul>
         <?php
      	}
      }

      public static function add_handheld_account()
      {
         // XXX set an option for the account and login page?
         if(is_user_logged_in()) $url="/account";
         else $url="/login";
         ?>
            <a href="<?php echo $url; ?>"><i class="fa fa-user fa-2x" style="margin-left:10px;position:relative;display:block;float:left;color:white;" aria-hidden="true"></i></a>
         <?php
      }

      public static function add_handheld_cart()
      {
         // XXX set an option for the account and login page?
         //$url=WC()->cart->get_cart_url();
         $url=wc_get_cart_url();
         ?>
            <a href="<?php echo $url; ?>"><i class="fa fa-shopping-cart fa-2x" style="margin-left:10px;position:relative;display:block;float:left;color:white;" aria-hidden="true"></i></a>
         <?php
      }

      // XXX Make it configurable?
      public static function storefront_menu_toggle_text( $text ) 
      {
         $text = '';
         return $text;
      }

      public static function footer_menu()
      {
         echo '<nav class=\'secondary-navigation\' role=\'navigation\' aria-label=\'Secondary Navigation\' >';
         wp_nav_menu(array('theme_location' => 'shm-footer-menu'));
         echo '</nav>';
      }

      public static function credits($cred)
      {
         return '';
      }

      

   }
}


?>

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


// XXX Add Pagination
function get_wc_products_by_meta($spid=false, $metakey='_pms_woo_subscription_id')
{
   if($spid) $meta = array(
         'key'     => $metakey,
         'value'   => $spid,
         'compare' => '=',
      );
   else $meta = array(
         'key'     => $metakey,
         'compare' => 'like',
      );
   $args = array(
      'posts_per_page' => 100,
      'post_type'      => 'product',
      'post_status'    => 'publish',
      'meta_key'   => $metakey,
      'meta_query' => array($meta),
      );
   $query = new \WP_Query( $args );
   return $query;
}

// XXX Add Pagination
function get_wc_subscription_products_priced($price, $pid=false)
{
   $res = array();

   // XXX CACHE THIS!
   $pages = get_wc_products_by_meta($pid);

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

?>

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

// Delete a product by id
/*
 * Method to delete Woo Product
 * 
 * @param int $id the product ID.
 * @param bool $force true to permanently delete product, false to move to trash.
 * @return \WP_Error|boolean
 */
function sh_wc_deleteProduct($id, $force = FALSE)
{
    $product = wc_get_product($id);

    if(empty($product))
        return new WP_Error(999, sprintf(__('No %s is associated with #%d', 'woocommerce'), 'product', $id));

    // If we're forcing, then delete permanently.
    if ($force)
    {
        if ($product->is_type('variable'))
        {
            foreach ($product->get_children() as $child_id)
            {
                $child = wc_get_product($child_id);
                $child->delete(true);
            }
        }
        elseif ($product->is_type('grouped'))
        {
            foreach ($product->get_children() as $child_id)
            {
                $child = wc_get_product($child_id);
                $child->set_parent_id(0);
                $child->save();
            }
        }

        $product->delete(true);
        $result = $product->get_id() > 0 ? false : true;
    }
    else
    {
        $product->delete();
        $result = 'trash' === $product->get_status();
    }

    if (!$result)
    {
        return new WP_Error(999, sprintf(__('This %s cannot be deleted', 'woocommerce'), 'product'));
    }

    // Delete parent product transients.
    if ($parent_id = wp_get_post_parent_id($id))
    {
        wc_delete_product_transients($parent_id);
    }
    return true;
}
?>

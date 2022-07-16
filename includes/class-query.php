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


if(!class_exists('SH_Query')) {
   class SH_Query
   {
      public static function get_Videos($vcat=false)
      {
         $filter=false;
         if(isset($_GET['sexhack_vselect']))
         {
            switch($_GET['sexhack_vselect'])
            {
               case 'premium':
               case 'members':
               case 'public':
               case 'preview':
                  $filter=$_GET['sexhack_vselect'];
                  break;
            }
         }

         $queryarr = array(

            /*
            * We're limiting the results to 100 products, change this as you
            * see fit. -1 is for unlimted but could introduce performance issues.
            */
            'posts_per_page' => 100,
            'post_type'      => 'product',
            'post_status'    => 'publish',
            'product_cat'    => 'Videos, VR180, VR360',
            'order'          => 'ASC',
            'orderby'        => 'title',
            'tax_query'    => array( array(
               'taxonomy'  => 'product_visibility',
               'terms'     => array( 'exclude-from-catalog' ),
               'field'     => 'name',
               'operator'  => 'NOT IN',
            ) )
            //'meta_query'   => array( array(
            //   'value'     => 'hls_public',
            //   'compare'   => 'like'
            //) ),
         );
         if($filter)
         {
            if($filter=="preview") {
               $queryarr['meta_query'] = array();
               $queryarr['meta_query']['relation'] = 'OR';
               $queryarr['meta_query'][] = array(
                  'value'  =>  'video_preview',
                  'compare' => 'like'
               );
               $queryarr['meta_query'][] = array(
                  'value'  =>  'hls_preview',
                  'compare' => 'like'
               );
               $queryarr['meta_query'][] = array(
                  'value'  =>  'vr_preview',
                  'compare' => 'like'
               );

            } else {
               $queryarr['meta_query'] = array();
               $queryarr['meta_query']['relation'] = 'OR';
               $queryarr['meta_query'][] = array(
                     'value'     => 'hls_'.$filter,
                     'compare'   => 'like'
               );
               $queryarr['meta_query'][] = array(
                     'value'     => 'vr_'.$filter,
                     'compare'   => 'like'
               );

            }
         }
         $products = new \WP_Query($queryarr);
         //sexhack_log(var_dump($products));
         return $products;


      }
   }
}


?>

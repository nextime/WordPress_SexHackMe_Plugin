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

      public static function get_columns($table)
      {
         global $wpdb;

         $sql = "SELECT `COLUMN_NAME`  FROM `INFORMATION_SCHEMA`.`COLUMNS`  
            WHERE `TABLE_SCHEMA`='".DB_NAME."'  AND `TABLE_NAME`='".$wpdb->_real_escape($table)."';";

         $colums = array();
         $res = $wpdb->get_results( $sql, ARRAY_N );
         foreach($res as $k => $v)
            $colums[]=$v[0];
         return $colums;
   
      }


      public static function save_Video($video)
      {
         global $wpdb;

         if(is_object($video))
         {
            $fieldsarrayraw = $video->get_sql_array();
            $fieldsarray = array();
            $fields = "";
            $keys = "";
            $values = "";
            $count=0;
            $tables = SH_Query::get_columns($wpdb->prefix.SH_PREFIX."videos");
            //sexhack_log("TABLES");
            //sexhack_log($tables);
            foreach($fieldsarrayraw as $k => $v)
            {
               if(!in_array($k, $tables)) continue;
               $fieldsarray[$k] = $v;
            }
            foreach($fieldsarray as $k => $v)
            {
               if(!in_array($k, $tables)) continue;

               $v = $wpdb->_real_escape($v);
               $adds = "\n";
               $fields .= $k." = '$v'";
               $keys .= $k;
               $values .= "'$v'";
               if($count < count($fieldsarray)-1) $adds = ",\n";
               $fields .= $adds;
               $keys .= $adds;
               $values .= $adds;

               $count++;
            }
            if((is_long($video->id) || is_numeric($video->id)) && intval($video->id) > 0)
            {
               // Save an already existing video entry
               $sql = "UPDATE {$wpdb->prefix}".SH_PREFIX."videos SET
                           {$fields}
								WHERE
									id = {$video->id};";
               $wpdb->query( $sql );

            } 
            else 
            {
               // Save a new video
               $sql = "INSERT INTO {$wpdb->prefix}".SH_PREFIX."videos 
                              ({$keys})
                           VALUES  
                              ({$values});";
					$wpdb->query( $sql );
					$video->id = $wpdb->insert_id;

            }
            //sexhack_log($sql);
            return $video;

         }
      }

      public static function delete_Video($id, $idtype='')
      {
         global $wpdb;

         if(!is_integer($id))
            return;

         $idtype=sanitize_idtype($idtype);

         if(!$idtype) return false;

         $sql = "DELETE FROM {$wpdb->prefix}".SH_PREFIX."videos WHERE {$idtype}=".intval($id);
         return $wpdb->query( $sql );

      }

      public static function get_Video($id, $idtype='')
      {
         global $wpdb;  


         if(!is_integer($id))
            return;

         $idtype=sanitize_idtype($idtype);

         if(!$idtype) return false;

         $sql = "SELECT * FROM {$wpdb->prefix}".SH_PREFIX."videos WHERE {$idtype}=".intval($id);
         $dbres = $wpdb->get_results( $sql );
         if(is_array($dbres) && count($dbres) > 0)
         {
            return new SH_Video((array)$dbres[0]);
         }
         return false;
      }

      


      public static function get_Videos($vcat=false)
      {

         global $wpdb;


         // XXX TODO This filtering using a $_GET in the query class is SHIT.
         //     Move it in the gallery interface, and pass a fucking argument
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

			$results = array();
         //$sql = $wpdb->prepare("SELECT * from {$wpdb->prefix}{$prefix}videos");
         $sql = "SELECT * from {$wpdb->prefix}".SH_PREFIX."videos";
         $dbres = $wpdb->get_results( $sql );
		
         foreach($dbres as $row)
         {
         	$results[] = new SH_Video($row);
         }

         return $results;


      }

      public static function get_Products($vcat=false)
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

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


      public static function save_Video($video, $source=false)
      {
         global $wpdb;


         if($source) sexhack_log("save_Video called from $source");
         if(is_object($video))
         {

            $video = apply_filters('video_before_save', $video);
            //sexhack_log($video);
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
            $updateid = false;
            $sqlarr = array();
            if((is_long($video->id) || is_numeric($video->id)) && intval($video->id) > 0)
            {
               // Save an already existing video entry
               $sql = "UPDATE {$wpdb->prefix}".SH_PREFIX."videos SET
                          {$fields}
								WHERE
                           id = {$video->id};";
               $sqlarr[] = "DELETE FROM {$wpdb->prefix}".SH_PREFIX."videoguests_assoc
                        WHERE
                            video_id = {$video->id};";
               $sqlarr[] = "DELETE FROM {$wpdb->prefix}".SH_PREFIX."videotags_assoc 
                        WHERE
                           video_id = {$video->id};";
               $sqlarr[] = "DELETE FROM {$wpdb->prefix}".SH_PREFIX."videocategory_assoc 
                        WHERE
                           video_id = {$video->id};\n";
            } 
            else 
            {
               // Save a new video
               $sql = "INSERT INTO {$wpdb->prefix}".SH_PREFIX."videos 
                              ({$keys})
                           VALUES  
                              ({$values});\n";

               $updateid = true;

            }

            do_action("sh_save_video_before_query", $video);

				$wpdb->query( $sql );
            if($updateid) $video->id = $wpdb->insert_id;
            if($source) sexhack_log("QUERY FOR  save_Video called from $source");
            sexhack_log($sql);

            foreach($video->get_categories() as $cat)
            {  
               $sqlarr[] = "INSERT INTO {$wpdb->prefix}".SH_PREFIX."videocategory_assoc
                              (cat_id, video_id)
                           VALUES
                              ({$cat->id}, {$video->id});\n";
            }
            foreach($video->get_tags_names() as $tagname)
            {
               $tagname = $wpdb->_real_escape($tagname);
               $tag = sh_get_tag_by_name($tagname, true);

               if($tag)
               {
                  $sqlarr[] = "INSERT INTO {$wpdb->prefix}".SH_PREFIX."videotags_assoc
                              (tag_id, video_id)
                           VALUES
                              ('{$tag->id}', '{$video->id}');";
               }
               
            }
            foreach($video->get_guests() as $guest)
            {
               $sqlarr[] =  "INSERT INTO {$wpdb->prefix}".SH_PREFIX."videoguests_assoc
                                 (user_id, video_id)
                           VALUES
                              ('{$guest->ID}', '{$video->id}');";
            }
            foreach($sqlarr as $sql)
            {
               //sexhack_log($sql);
               if($sql)
				      $wpdb->query( $sql );  
            }
            do_action("sh_save_video_after_query", $video);

            //sexhack_log($video);
            //sexhack_log($sqlarr);
            return $video;

         }
      }

      public static function delete_Video($id, $idtype='')
      {
         global $wpdb;

         if(!is_integer($id))
            return;

         $video = SH_Query::get_Video($id, $idtype);
         $idtype=sanitize_idtype($idtype);

         if(!$idtype) return false;

         do_action('sh_delete_video', $video);


         $sqlarr = array();
         $sqlarr[] = "DELETE FROM {$wpdb->prefix}".SH_PREFIX."videoguests_assoc WHERE video_id IN (
                         SELECT id FROM {$wpdb->prefix}".SH_PREFIX."videos
                            WHERE {$idtype}=".intval($id)." );";
         $sqlarr[] = "DELETE FROM {$wpdb->prefix}".SH_PREFIX."videotags_assoc WHERE video_id IN (
                         SELECT id FROM {$wpdb->prefix}".SH_PREFIX."videos
                            WHERE {$idtype}=".intval($id)." );";
         $sqlarr[] = "DELETE FROM {$wpdb->prefix}".SH_PREFIX."videocategory_assoc WHERE video_id IN (
                         SELECT id FROM {$wpdb->prefix}".SH_PREFIX."videos
                            WHERE {$idtype}=".intval($id)." );";
         $sqlarr[] = "DELETE FROM {$wpdb->prefix}".SH_PREFIX."jobs WHERE jobtype='video' AND obj_id IN (
                         SELECT id FROM {$wpdb->prefix}".SH_PREFIX."videos
                            WHERE {$idtype}=".intval($id)." );";
         $sqlarr[] = "DELETE FROM {$wpdb->prefix}".SH_PREFIX."videos WHERE {$idtype}=".intval($id);
         foreach($sqlarr as $sql)
         {
            $wpdb->query( $sql );
         }

      }

      public static function get_VideoFromSlug($slug)
      {
         global $wpdb;
         
         $slug = $wpdb->_real_escape($slug);
         $sql = "SELECT * FROM {$wpdb->prefix}".SH_PREFIX."videos WHERE slug='".$slug."'";
         $dbres = $wpdb->get_results( $sql );
         if(is_array($dbres) && count($dbres) > 0) return new SH_Video((array)$dbres[0]);
         return false;

      }

      public static function get_VideosFromHLS($vpath, $level="public")
      {
         global $wpdb;

         $vpath = $wpdb->_real_escape($vpath);
         switch($level)
         {
            case "members":
                  $level="hls_members";
               break;
            case "premium":
                  $level="hls_premium";
               break;
            default:
               $level="hls_public";
         }
         $sql = "SELECT * FROM {$wpdb->prefix}".SH_PREFIX."videos WHERE ".$level."='".$vpath."'";
         $dbres = $wpdb->get_results( $sql );
         if(is_array($dbres) && count($dbres) > 0) return new SH_Video((array)$dbres[0]);
         return false;

      }

      public static function get_Videos($id, $idtype='')
      {
         return SH_Query::_get_Videos($id, $idtype, false);
      } 

      public static function get_Video($id, $idtype='')
      {
         return SH_Query::_get_Videos($id, $idtype, true);
      }

      public static function _get_Videos($id, $idtype='', $firstres=true)
      {
         global $wpdb;  


         if(!is_integer($id))
            return;

         $idtype=sanitize_idtype($idtype);

         if(!$idtype) return false;

         $sql = "SELECT * FROM {$wpdb->prefix}".SH_PREFIX."videos WHERE {$idtype}=".intval($id)." ORDER BY created DESC";
         $dbres = $wpdb->get_results( $sql );
         if(is_array($dbres) && count($dbres) > 0)
         {
            if($firstres) return new SH_Video((array)$dbres[0]);
            else {
               $videos = array();
               foreach($dbres as $res) {
                  $videos[] = new SH_Video((array)$res);
               }
               return $videos;
            }
            return new SH_Video((array)$dbres[0]);
         }
         return false;
      }

      


      public static function get_VideosCat($vcat=false, $filtering=false)
      {

         global $wpdb;


         // XXX TODO This filtering using a $_GET in the query class is SHIT.
         //     Move it in the gallery interface, and pass a fucking argument
         $filter=false;
         if(isset($_GET['shvs']))
         {
            switch($_GET['shvs'])
            {
               case 'premium':
               case 'members':
               case 'public':
                  $filter="hls_".$_GET['shvs'];
                  break;
               case 'preview':
                  $filter=$_GET['shvs'];
                  break;
            }
         }

			$results = array();
         //$sql = $wpdb->prepare("SELECT * from {$wpdb->prefix}{$prefix}videos");
         $sql = "SELECT * FROM {$wpdb->prefix}".SH_PREFIX."videos";

         if($filter) $sql .= " WHERE ".$filter."!=''";

         if($filter && $filtering) $sql .= " AND "; 
         else if(!$filter && $filtering) $sql .= " WHERE ";

         if($filtering) $sql .= " ".$filtering." ";

         $dbres = $wpdb->get_results( $sql, ARRAY_A );
		   //sexhack_log($dbres);
         foreach($dbres as $row)
         {
         	$results[] = new SH_Video($row);
         }
         //sexhack_log($results);
         return $results;


      }


      public static function get_Video_Jobs($vid=false)
      {
         global $wpdb;
         $sql = "SELECT * FROM {$wpdb->prefix}".SH_PREFIX."jobs WHERE jobtype='video'";
         if($vid && is_numeric($vid))
            $sql .= " AND obj_id='".intval($vid)."'";
         $dbres = $wpdb->get_results( $sql );
         return $dbres;
      }


      public static function add_Video_job($vid, $command, $args)
      {
         global $wpdb;
         if(is_array($args)) $arg="JSON_QUOTE($args)";
         else $arg="'$args'";
         $sql = "INSERT IGNORE INTO {$wpdb->prefix}".SH_PREFIX."jobs (jobtype, obj_id, command, arguments) VALUES ('video','$vid','$command',$arg)";
         sexhack_log($sql);
         $wpdb->query($sql);
         return;
      }

      public static function del_Video_job($vid, $command=false)
      {
         global $wpdb;
         $sql = "DELETE FROM {$wpdb->prefix}".SH_PREFIX."jobs WHERE obj_id='$vid'";
         if($command) $sql.= " AND command='$command'";
         $wpdb->query($sql);
         return;
         
      }

      public static function del_job($id)
      {
         global $wpdb;
         $sql = "DELETE FROM {$wpdb->prefix}".SH_PREFIX."jobs WHERE id='$id'";
         $wpdb->query($sql);
         return;
      }


      public static function get_Categories($id=false)
      {
         global $wpdb;

         $sql = "SELECT * FROM {$wpdb->prefix}".SH_PREFIX."videocategory";
         if($id && is_numeric($id)) 
            $sql .= " WHERE id='".intval($id)."'";
         $dbres = $wpdb->get_results( $sql );

         if(!$id) return $dbres;
         if(is_array($dbres) && count($dbres) > 0) return $dbres[0];
      }

      public static function get_Tag_By_Name($name, $create=false)
      {
         global $wpdb;

         $sql = "SELECT * FROM {$wpdb->prefix}".SH_PREFIX."videotags WHERE tag='{$name}'";
         $res = $wpdb->get_results($sql);
         if(is_array($res))
         {
            if(count($res) > 0) return $res[0];
            if(count($res) == 0 && $create) 
            {
               $sql = "INSERT IGNORE INTO {$wpdb->prefix}".SH_PREFIX."videotags (tag) VALUES ('{$name}')";
               $wpdb->query($sql);
               return SH_Query::get_Tag_By_Name($name);
            }
         }
         return false;
      }

      public static function get_Video_Guests($vid)
      {
         global $wpdb;
         $sql = "SELECT user_id FROM {$wpdb->prefix}".SH_PREFIX."videoguests_assoc WHERE video_id=".intval($vid)." ;";
         $dbres = $wpdb->get_results( $sql );
         $guests = array();
         if($dbres && count($dbres) > 0)
         {
            foreach($dbres as $ures)
            {
               $udata = get_userdata($ures->user_id);
               if($udata) $guests[$ures->user_id] = $udata;
            }
         }
         if(count($guests) > 0) return $guests;
         return false;
      }

      public static function delete_Guests_assoc($id, $idtype)
      {
         global $wpdb;

         if(!is_integer($id))
            return;

         $idtype=sanitize_idtype($idtype);

         if(!in_array($idtype, array('id', 'user_id', 'video_id'))) return false;

         $sql = "DELETE FROM {$wpdb->prefix}".SH_PREFIX."videoguests_assoc WHERE {$idtpe}={$id}";

         return $wpdb->query( $sql );

      }

      public static function get_Video_Categories($vid)
      {
         global $wpdb;


         $sql = "SELECT * FROM {$wpdb->prefix}".SH_PREFIX."videocategory WHERE id IN ";
         $sql .= "( SELECT cat_id FROM {$wpdb->prefix}".SH_PREFIX."videocategory_assoc WHERE video_id=".intval($vid)." );";
         $dbres = $wpdb->get_results( $sql );
         return $dbres;
      }

      public static function delete_Categories_assoc($id, $idtype)
      {
         global $wpdb;

         if(!is_integer($id))
            return;

         $idtype=sanitize_idtype($idtype);

         if(!in_array($idtype, array('id', 'cat_id', 'video_id'))) return false;

         $sql = "DELETE FROM {$wpdb->prefix}".SH_PREFIX."videocategory_assoc WHERE {$idtpe}={$id}";

         return $wpdb->query( $sql );

      }

      public static function get_Video_Tags($vid)
      {
         global $wpdb;


         $sql = "SELECT * FROM {$wpdb->prefix}".SH_PREFIX."videotags WHERE id IN ";
         $sql .= "( SELECT tag_id FROM {$wpdb->prefix}".SH_PREFIX."videotags_assoc WHERE video_id=".intval($vid)." );";
         $dbres = $wpdb->get_results( $sql );
         return $dbres;
      }

      public static function delete_Tags_assoc($id, $idtype)
      {
         global $wpdb;
           
         if(!is_integer($id))
            return;

         $idtype=sanitize_idtype($idtype);

         if(!in_array($idtype, array('id', 'tag_id', 'video_id'))) return false;

         $sql = "DELETE FROM {$wpdb->prefix}".SH_PREFIX."videotags_assoc WHERE {$idtpe}={$id}";

         return $wpdb->query( $sql );

      }


      // XXX Deprecated TODO remove it
      public static function get_Products($vcat=false)
      {
         $filter=false;
         if(isset($_GET['shvs']))
         {
            switch($_GET['shvs'])
            {
               case 'premium':
               case 'members':
               case 'public':
               case 'preview':
                  $filter=$_GET['shvs'];
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

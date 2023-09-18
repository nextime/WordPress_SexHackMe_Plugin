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


if(!class_exists('SH_Video')) {


   class SH_Video
   {

      protected $attributes = array(
         'id' => 0,
         'user_id' => 0,
         'post_id' => 0, 
         'product_id' => 0,
         'status' => 'creating',
         'private' => 'N',
         'visible' => 'Y',
         'title' => '',
         'description' => '', 
         'created' => false,
         'updated' => false,
         'slug' => '', 
         'price' => 0,
         'video_type' => 'FLAT',
         'vr_projection' => 'VR180_LR',
         'preview' => false, 
         'hls_public' => false,
         'hls_members' =>  false,
         'hls_premium' =>  false,
         'thumbnail' => false,
         'gif' => false,
         'gif_small' => false,
         'download_public' => false,
         'download_members' => false,
         'download_premium' => false,
         'size_public' => false,
         'size_members' => false,
         'size_premium' => false,
         'format_public' => false,
         'format_members' => false,
         'format_premium' => false,
         'codec_public' => 'h264',
         'codec_members' => 'h264',
         'codec_premium' => 'h264',
         'acodec_public' => 'AAC',
         'acodec_members' => 'AAC',
         'acodec_premium' => 'AAC',
         'duration_public' => false,
         'duration_members' => false,
         'duration_premium' => false,
         'resolution_public' => false,
         'resolution_members' => false,
         'resolution_premium' => false,            
         'views_public' => 0,
         'views_members' => 0,
         'views_premium' => 0,
         'sales' => 0,
         'socialpost' => 0
      );
      public function __construct($attr=false)
      {
         $this->attributes['uploaded'] = date('Y-m-d H:i:s');
         if(is_array($attr)) $this->setAttributes($attr);
      }


      public function get_title()
      {
         return $this->attributes['title'];
      }

      public function has_downloads($level=false)
      {
         switch($level) {
            case 'premim':
               return $this->download_premium;
               break;
            case 'members':
               return $this->download_members;
               break;
            case 'public':
               return $this->download_public;
               break;
         }
         if($this->download_public OR $this->download_members OR $this->download_premium) return true;
         return false;
      }


      function setAttributes($attr, $post=false, $wcprod=false)
      {

			// XXX TODO Validate the $attr array 
         if(!is_array($attr)) return false;
			foreach($attr as $k => $v)
			{
				if(array_key_exists($k, $this->attributes)) $this->attributes[$k] = $v;
			}
         
         if($post && is_object($post))  $this->__set('post', $post);
         else if($post && is_array($post) && (count($post) > 0) && is_object($post[0])) $this->__set('post', $post[0]);
         //else if(array_key_exists('post_id', $attributes) && is_integer($attributes['post_id']) && ($attributes['post_id'] > 0))
         //   $this->__set('post',  get_post($attributes['post_id']);

         if($wcprod && is_object($wcprod)) $this->__set('product', $wcprod);
         else if($wcprod && is_array($wcprod) && (count($wcprod) > 0) && is_object($wcprod[0])) $this->__set('product', $wcprod[0]);
         //else if(array_key_exists('product_id', $attributes) && is_integer($attributes['product_id']) && ($attributes['product_id'] > 0))
         //   $this->__set('product', wc_get_product($attributes['product_id']);
      }

      public function __get($key){
         if (!array_key_exists($key, $this->attributes)) return null;
         return $this->attributes[$key];
      }

      public function __set($key, $value=false)
      {
         $this->attributes[$key] = $value;
      }

      public function __unset($key)
      {
         unset($this->attributes[$key]);
      }


      public function __isset($key)
      {
         return isset($this->attributes['key']);
      }

      public function get_tags($usedb=true)
      {
         if(isset($this->attributes['tags'])) return $this->tags;
         $this->attributes['tags'] = array();
         $this->attributes['tagsnames'] = array();
         if($usedb)
         {
            $tags = sh_get_video_tags($this->id);
            if($tags)
            {
               foreach($tags as $tag) {
                  $this->attributes['tags'][$tag->id] = $tag;
                  $this->attributes['tagsnames'][] = $tag->tag;
               }
            }
         }
         return $this->tags;
      }

      public function get_tags_names()
      {
         if(isset($this->attributes['tagsnames'])) return $this->tagsnames;
         $this->get_tags();
         return $this->tagsnames;
      }

      public function add_category($cat)
      {
         if(!is_object($cat)) return false;
         if(!isset($this->attributes['categories'])) $this->attributes['categories'] = array();
         $this->attributes['categories'][$cat->id] = $cat;
         return true;

      }

      public function add_tag($tag)
      {
         if(!is_object($tag)) return false;
         if(!isset($this->attributes['tags'])) $this->attributes['tags'] = array();
         if(!isset($this->attributes['tagsnames'])) $this->attributes['tagsnames'] = array();
         $this->attributes['tags'][$tag->id] = $tag;
         $this->attributes['tagsnames'][] = $tag->tag;
         return true;
      }

      public function add_guest($guest)
      {
         if(!is_object($guest)) return false;
         if(!isset($this->attributes['guests'])) $this->attributes['guests'] = array();
         $this->attributes['guests'][$guest->ID] = $guest;
      }

      public function get_guests($usedb=true)
      {
         if(isset($this->attributes['guests'])) return $this->guests;
         $this->attributes['guests'] = array();
         if($usedb)
         {
            $guests = sh_get_video_guests($this->id);
            if($guests)
            {
               foreach($guests as $guest)
                  $this->attributes['guests'][$guest->ID] = $guest;

            }
         }
         return $this->guests;
      }

      public function get_categories($usedb=true)
      {
         if(isset($this->attributes['categories'])) return $this->categories;
         $this->attributes['categories'] = array();
         if($usedb)
         {
            $cats = sh_get_video_categories($this->id);
            if($cats)
            {
               foreach($cats as $cat)
                  $this->attributes['categories'][$cat->id] = $cat;
            }
         }
         return $this->categories;
      }

      public function has_category($cat_id)
      {
         if(!isset($this->attributes['categories'])) $this->get_categories();
         if(in_array($cat_id, array_keys($this->categories))) return true;
         return false;
      }

      public function get_post()
      {
         if(isset($this->attributes['post'])) return $this->post;
         if(array_key_exists('post_id', $this->attributes)) 
         {
            $this->__set('post', get_post($this->post_id));
            return $this->post;
         }
         return false;
      }

      public function get_product()
      {
         if(isset($this->attributes['product'])) return $this->product;
         if(array_key_exists('product', $this->attributes))
         {
            $this->__set('product', wc_get_product($this->post_id));
            return $this->product;
         }
         return false;
      } 


      public function user_bought_video($uid=False)
      {
         if(!isset($this->attributes['product_id'])) return false;
         if(!$uid && get_current_user_id())
         {
            if(wc_customer_bought_product( '', get_current_user_id(), $this->attributes['product_id'])) return true;
         } 
         else if($uid && intval($uid)) 
         {
            if(wc_customer_bought_product( '', $uid, $this->attributes['product_id'])) return true;
         } 
         return false;
      }

      // Repopulate the object from $post or $post->ID
      public function  update_video_from_post($p)
      {
         $post = false;
         if(is_object($p)) $post = $p;
         else if(is_integer($p)) $post = get_post($post);
         if($post)
         {
            $this->post = clone $post;
         }
         return false;
      }

      public function get_sql_array()
      {
         $r = array();
         foreach($this->attributes as $k => $v)
         {
            if(($v!==false) && !in_array($k, array('id','post','product','tags',
               'categories','tagsnames','created','updated','sells','guests',
               'views_public','views_members','views_premium')) ) $r[$k] = $v;
         }
         return $r;
      }

   }
   $GLOBALS['sh_video'] = new SH_Video();
   do_action('sh_video_ready');
}


?>

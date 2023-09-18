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


if(!class_exists('SH_Shortcodes')) {
   class SH_Shortcodes
   {

      public static function init() {

         $shortcodes = array(
            'sexhls'         => __CLASS__ . '::video_hls',
            'sexvideo'       => __CLASS__ . '::video_xr',
            'sh_videoxr'     => __CLASS__ . '::video_xr',
            'sh_videohls'    => __CLASS__ . '::video_hls',
            'xfbp'           => __CLASS__ . '::xframe_bypass',
            'sexhacklive'    => __CLASS__ . '::sexhacklive',
            'sexadv'         => __CLASS__ . '::adv_shortcode',
            'sexgallery'     => __CLASS__ . '::videogallery_shortcode',
            'shvideomanager' => __CLASS__ . '::video_manager_shortcode',
            'shincludepage'  => __CLASS__ . '::include_page_shortcode',
         );

         foreach( $shortcodes as $shortcode_tag => $shortcode_func ) {
            add_shortcode( $shortcode_tag, $shortcode_func );
         }

      }

      public static function video_hls($attr, $cont)
      {
         extract( shortcode_atts(array(
            "url" => '',
            "posters" => '',
         ), $attr));
         return "<div class='sexhls_video'>" . sh_hls_player($url, $posters) . "</div>";
      }

      public static function video_xr($attr, $cont)
      {
         extract( shortcode_atts(array(
            "url" => '',
            "posters" => '',
         ), $attr));
         return "<div class='sexvideo_videojs'>" . sh_xr_player($url, $posters) . "</div>";
      }

      public static function xframe_bypass($attr, $cont)
      {
         extract( shortcode_atts(array(
            'url' => 'https://www.sexhack.me',
         ), $attr));
         return '<iframe is="x-frame-bypass" src="'.$url.'"></iframe>';

      }

      public static function sexhacklive($attr, $cont)
      {
         extract( shortcode_atts(array(
            'site' => 'chaturbate',
            'model' => 'sexhackme',
         ), $attr));
         $ret = LiveCamSite::getCamStream($site, $model);
         if($ret) return $ret;
         return '<p>CamStreamDL Error: wrong site option '.$site.'</p> ';

      }

      public static function adv_shortcode($attr, $cont)
      {
         global $post;

         extract( shortcode_atts(array(
            "adv" => false,
         ), $attr));
         if(!user_is_premium())
         {
            if($attr['adv'])
            {  
               $post = get_post(intval($attr['adv']));
               if(($post) && ($post->post_type == 'sexhackadv'))
               {
                  $html = $post->post_content;
                  wp_reset_postdata();

                  return $html;
               }
            }

            wp_reset_postdata();
            //return 'Error in retrieving sexhackadv post. Wrong ID?';
         }
         return;
      }

      public static function videogallery_shortcode($attr, $cont)
      {
         global $post;
         global $sh_videogallery;
         extract( shortcode_atts(array(
            "category" => "all",
         ), $attr));

         $html = "<div class='sexhack_gallery'>"; //<h3>SexHack VideoGallery</h3>";
         if(isset($_GET['SHDEV'])) $html .= '<h3>DEVELOPEMENT MODE</h3>';
         $html .= '<ul class="products columns-4">';
         $videos = $sh_videogallery->get_videos_by_cat(filtering: "status='published' ORDER BY created DESC");
         $sep=1;
         foreach($videos as $video)
         {
            //if($video->status == 'published')
            //{
               $post = $video->get_post();
               if($post) setup_postdata($post);
               $html .= $sh_videogallery->get_video_thumb($video);
               if($sep==4) $html .= '<li class="product type-product sexhack_thumbli" style="width:100% !important; margin-bottom: 1px !important;"> </li>';
               $sep=$sep+1;
               if($sep==5) $sep=1;
            //}
         }
         wp_reset_postdata();
         $html .= "</ul></div>";
         return $html;
      }

      public static function video_manager_shortcode($attr, $cont)
      {
         //$post = get_post();
         //echo $post->post_name;
         //echo get_permalink();
         //echo $_SERVER['REQUEST_URI'];
         if(isset($_GET['vedit'])) {
            if(is_numeric($_GET['vedit']) && intval($_GET['vedit']) > 0) 
            {
               $video = sh_get_video(intval($_GET['vedit']));
               if(is_object($video) && $video->user_id == get_current_user_id()) sh_get_template('edit_video.php', 
                                                                                                      array(
                                                                                                         'video' => $video,
                                                                                                         'post' => get_post($video->post_id)
                                                                                                       )
                                                                                                );
               else sh_get_template('video_access_negate.php');
               return;
            } else if($_GET['vedit'] == 'new') {
               sh_get_template('new_video.php');
               return;
            } else {
               sh_get_template('video_access_negate.php');
               return;
            }
         }
         sh_get_template('videomanager.php');
         return;
      }

      public static function include_page_shortcode($attr, $cont)
      {
         extract( shortcode_atts(array(
            "page" => '',
				'level' => 'public',
         ), $attr));
         if(!array_key_exists('level', $attr)) $attr['level'] = 'public';
			if(($attr['level'] == 'guestonly' && !is_user_logged_in()) || ($attr['level'] == 'public' or !$attr['level']) || (is_user_logged_in() && ($attr['level']=='members' || ($attr['level']=='premium' && user_is_premium()))))
			{
				$ipost = get_post($attr['page']);
				if($ipost) {
            	return $ipost->post_content;
				}	
			}
			return;
      }

   }
}


?>

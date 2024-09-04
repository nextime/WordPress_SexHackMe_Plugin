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
            'sh_register'    => __CLASS__ . '::register',
         );

         foreach( $shortcodes as $shortcode_tag => $shortcode_func ) {
            add_shortcode( $shortcode_tag, $shortcode_func );
         }

      }

      public static function register($attr, $cont)
      {
         pms_stripe_enqueue_front_end_scripts();
         $before = "<style> .pms-pass1-field, .pms-pass2-field, .pms-first-name-field, .pms-last-name-field {display: none;} </style>";
         $after = '<script>
            $(".pms-pass1-field").remove();
            $(".pms-pass2-field").remove();
            $(".pms-first-name-field").remove();
            $(".pms-last-name-field").remove();
            </script>';
         return $before.do_shortcode( '[pms-register]').$after;

      }

      public static function video_hls($attr, $cont)
      {
         extract( shortcode_atts(array(
            "url" => '',
            "posters" => '',
            "autoplay" => false
         ), $attr));
         ob_start();
         sh_hls_player($url, $posters, $autoplay);
         $player = ob_get_contents();
         ob_end_clean();
         return "<div class='sexhls_video'>" . $player . "</div>";
      }

      public static function video_xr($attr, $cont)
      {
         extract( shortcode_atts(array(
            "url" => '',
            "posters" => '',
         ), $attr));
         ob_start();
         sh_xr_player($url, $posters);
         $player = ob_get_contents();
         ob_end_clean();
         return "<div class='sexvideo_videojs'>" . $player . "</div>";
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
            "adv" => "4,16"
         ), $attr));

         $html = "<div class='sexhack_gallery'>"; //<h3>SexHack VideoGallery</h3>";
         if(isset($_GET['SHDEV'])) $html .= '<h3>DEVELOPEMENT MODE</h3>';
         $html .= '<ul class="products columns-4">';
         $videos = $sh_videogallery->get_videos_by_cat(filtering: "status='published' ORDER BY created DESC");
         $sep=1;
         $vcount=1;
         if(isset($attr['adv'])) $adv=explode(",", $attr['adv']);
         else $adv=array("4");
         $advid=get_option('sexadv_video_native', false);

         foreach($videos as $video)
         {
            if(in_array(strval($vcount), $adv) && is_numeric($advid) && intval($advid) >= 0) // && !user_is_premium() && $advid && is_numeric($advid) && intval($advid)>0)
            {
               $html .= '<li class="product type-product sexhack_thumbli">';
               $html .= do_shortcode("[sexadv adv=$advid]");
               if($sep==4) $html .= '<li class="product type-product sexhack_thumbli" style="width:100% !important; margin-bottom: 1px !important;"> </li>';
               $html .= '</li>';
               $sep=$sep+1;
               if($sep==5) $sep=1;
               $vcount=$vcount+1;
            } 
            //if($video->status == 'published')
            //{
               $post = $video->get_post();
               if($post) setup_postdata($post);
               $html .= $sh_videogallery->get_video_thumb($video);
               if($sep==4) $html .= '<li class="product type-product sexhack_thumbli" style="width:100% !important; margin-bottom: 1px !important;"> </li>';
               $sep=$sep+1;
               if($sep==5) $sep=1;
               $vcount=$vcount+1;
            //}
         }
         wp_reset_postdata();
         $html .= "</ul></div>";
         /*
         $html .= "<script>
            window.SHM_stoplazy = false;
            //$('body').on('click', function(event){
            //   // event.preventDefault();
            //   console.log('STOP CALLED');
            //   window.stop();
            //});
            
            //$(window).on('beforeunload', function() {
            //   console.log('BEFOREUNLOAD CALLED');
            //   //setInterval(window.stop, 200);
            //   window.stop();
            //   window.SHM_stoplazy = true;
            //});

            //$(window).on('lazybeforeunveil', function(event){
              //console.log(event);
              //if(window.SHM_stoplazy===true) {
              //   event.preventDefault();
              //   console.log('Event prevented');
             // }

            //});

            //$(window).on('lazybeforesizes', function(event){
            //console.log(event);
            //});

         </script>";
          */    
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
            'random' => 'false',
            'popup' => 'false',
            'class' => 'shmpopup',
            'campaign' => '',
            'expire' => false,
            'expiredays' => false,
            'delay' => '0',
            'before' => '',
            'after' => ''
         ), $attr));
         if(!array_key_exists('page', $attr)) return;
         if($attr['page']=='') return;
         $before='';
         $after='';
         $content='';
         $random=false;
         $popup=false;
         $shmclass='shmpopup';
         $delay='0';
         $campaign='';
         $expiredays=false;
         $expire=false;
         if(array_key_exists('expire', $attr) && is_numeric($attr['expire'])) $expire=$attr['expire'];
         if(array_key_exists('expiredays', $attr) && is_numeric($attr['expiredays'])) $expiredays=$attr['expiredays'];
         if(array_key_exists('campaign', $attr) && $attr['campaign'] != '') $campaign=$attr['campaign'];
         if(array_key_exists('delay', $attr) && is_numeric($attr['delay']) && intval($attr['delay']) > 0) $delay=$attr['delay'];
         if(array_key_exists('class', $attr) && $attr['class'] != '' ) $shmclass=$attr['class'];
         if(array_key_exists('popup', $attr) && $attr['popup']=='true') $popup=true;
         if(array_key_exists('before', $attr) && $attr['before'] != '') $before=$attr['before'];
         if(array_key_exists('after', $attr) && $attr['after'] != '') $after=$attr['after'];
         if(!array_key_exists('level', $attr)) $attr['level'] = 'public';

         if(!$expire && !$expiredays) $expiredays="1";


			if(($attr['level'] == 'guestonly' && !is_user_logged_in()) || ($attr['level'] == 'public' or !$attr['level']) || (is_user_logged_in() && ($attr['level']=='members' || ($attr['level']=='premium' && user_is_premium()))))
         {

            if($pos = strpos($attr['page'], ',')) { 
               $pages=explode(',', $attr['page']);
            } else {
               $pages=array($attr['page']);
            }
            if($campaign!='') $cookie=$campaign;
            else $cookie=md5($attr['page']);

            if(count($pages) > 1 && array_key_exists('random', $attr) && $attr['random']=='true') $random=true;
            if($random || $popup) {
               $class=str_replace('.', '_', uniqid('shinclude_', true));
               $script="<script language='javascript'>\n";
               if($expiredays || $expire) $script.="var strcookie = readCookie('shmpopup_".$cookie."'); if (strcookie === null) {";
               $script.="$(window).on('load', function() {\n";
            }
            if($random) {
               $script.="   shinc=$($('.".$class."')[Math.floor(Math.random()*$('.".$class."').length)]);\n";
            } else if($popup) {
               $script.="   shinc=$('.".$class."');";
            }
            if($popup) { 
               $script.="   shinc.addClass('".$shmclass."');";
               if($campaign!='') $action="function(){\$.post('/content/plugins/matomo/app/matomo.php', {'idsite':1, 'rec': '1','mtm_campaign': '".$campaign."','mtm_kwd': shinc.attr('data-shmname') })}";
               if($campaign!='') $script.="   shinc.find('form').submit(".$action.");";
               if($campaign!='') $script.="   shinc.find('a').click(".$action.");";
            }

            if($random || $popup) {
               $script.="   setTimeout(function() { shinc.show();";
               if($expiredays) $script.="  createCookie('shmpopup_".$cookie."','opened',$expiredays);";
               elseif($expire) $script.="  createCookieSeconds('shmpopup_".$cookie."','opened',$expire);";
               $script.="  }, $delay);\n";
               $script.="});\n";
               if($expiredays || $expire) $script.="}\n";
               $script.="</script>";
               $before=$script.$before;
            }
            foreach($pages as $page) {
               $popheader="<div style='float:right;width:10%;margin-left:90%;border:1px solid white;border-radius:10px;padding:5px;cursor:pointer;min-width:60px;' onClick='$(\".".$class."\").hide();'>Close X</div>";
               $randheader="<div data-shmname='".$page."' class='".$class."' style='display:none;'>";
               if(is_numeric($page)) {
				      $ipost = get_post($page);
                  if($ipost) {
                     if($random || $popup) $content.=$randheader;
                     if($popup) $content.=$popheader;
                     $content=$content.$ipost->post_content;
                     if($random || $popup) $content.="</div>";
                  }
               } else {
                  $args = array(
                     'name' => $page,
                     'post_type' => 'page',
                     'post_status' => 'publish',
                     'posts_per_page' => 1
                  );
                  $res = get_posts($args);
                  if($res) {
                     if($random || $popup) $content.=$randheader;
                     if($popup) $content.=$popheader;
                     $content=$content.$res[0]->post_content;
                     if($random || $popup) $content.="</div>";
                  }
               }
            }

            if($content && $content!='')
               return apply_filters('the_content', $before.$content.$after);   
			}
			return;
      }

   }
}


?>

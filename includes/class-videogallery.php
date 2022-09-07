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

if(!class_exists('SH_VideoGallery')) {


   class SH_VideoGallery
   {


      public function __construct()
      {

         // TODO What an horrible and inefficient way to cache the query result.
         //     Think about moving it in session and with a better data structure.
         $this->videolist = false;

         // Register Query Vars
         add_filter("query_vars", array($this, "query_vars"));
         add_filter('page_template', array($this, 'sexhack_video_template'));
         add_filter('archive_template', array($this, 'sexhack_video_template'));

         add_action('pre_get_posts', array($this, 'fix_video_query'), 1, 1);

      }


      public function query_vars($vars)
      {
         $vars[] = 'sh_video';
         $vars[] = 'videoaccess';
         return $vars;
      }

      public function sexhack_video_template($template) 
      {
         if(isset($_GET['SHDEV'])) $templ='newvideo.php';
         $is_sexhack_video = get_query_var('sh_video', false);
         if($is_sexhack_video ) {
            $templ='video.php';
            set_query_var( 'post_type', 'sexhack_video' );
            if ( file_exists( plugin_dir_path(__DIR__) . '/templates/'.$templ)) {
               return plugin_dir_path(__DIR__) . '/templates/'.$templ;
            }
         }

         return $template;
      }


      public function fix_video_query($query)
      {  
         if($query->get('post_type')=='sexhack_video') {
            $sh_video = $query->get('sh_video', false);
            if($sh_video) {
               $query->query['post_type'] = 'sexhack_video';
               $query->set('name', esc_sql($wooprod));
               $query->set('post_type', 'any');
               //$query->set('post_type', '');
            }
         }
      }

      public function get_videos_by_cat($vcat=false) {
         // XXX TODO Only published videos!

         if(!$this->videolist && !$vcat) $this->videolist = sh_get_videos_by_cat(); 
         else if($vcat) return sh_get_videos_by_cat($vcat);  

         return $this->videolist;

      }
      
      public function get_video_thumb($video=false)
      {

         $DEFAULTSLUG = get_option('sexhack_gallery_slug', 'v');

         $post_id = get_the_ID();
         if(!$video) $video=sh_get_video_from_post($post_id);
         if(is_numeric($video->thumbnail))
         {
            //$image = get_post_thumbnail_id($video->thumbnail);
            //$image = wp_get_attachment_link($video->thumbnail);
            $image=wp_get_attachment_image($video->thumbnail, "320x160"); // XXX Seriously fixed size?
         }
         else
            $image = $video->thumbnail;

         $hls_public = $video->hls_public;
         $hls_member = $video->hls_members;
         $hls_premium = $video->hls_premium;
         $video_preview = $video->video_preview;
         $gif_preview = $video->gif_small;

         sexhack_log($video);

         $categories = $video->get_categories(true);


         //print_r($categories);

         $gif = $video->gif;

         if(!$gif_preview) $gif_preview = $gif;
         if($gif_preview) $image .= "<img src='$gif_preview' class='alignleft sexhack_thumb_hover' loading='lazy' />";

         $html = '<li class="product type-product sexhack_thumbli">';
         
         $vurl = site_url().esc_url( "/".$DEFAULTSLUG."/".$video->slug )."/";
         if(isset($_GET['SHDEV'])) $vurl.="?SHDEV=true";
         $vtitle = $video->get_title();
         $vtags=array();

         $downtag ='';
         if((!$hls_public) AND (!$hls_member) AND (!$hls_premium) AND ($video_preview) ) $vtags[] = '<label class="sexhack_vtag sexhack_preview" style="*LEFT*">preview</label>';
         if($hls_public) $vtags[] = '<label class="sexhack_vtag sexhack_public" style="*LEFT*">public</label>';
         if($hls_member)$vtags[] = '<label class="sexhack_vtag sexhack_members" style="*LEFT*">members</label>';
         if($hls_premium)$vtags[] = '<label class="sexhack_vtag sexhack_premium" style="*LEFT*">premium</label>';

         if($video->has_downloads()) $html .= '<label class="sexhack_vtag sexhack_download"">download</label>';
         if($video->video_type == 'VR') $html .= '<label class="sexhack_vtag sexhack_VR"">VR/3D</label>';

         $html .= "<a href=\"$vurl\" class=\"woocommerce-LoopProduct-link woocommerce-loop-product__link\">";
         $html .= "<div class='sexhack_thumb_cont'>".$image."</div>";
         
         foreach($vtags as $vid => $vtag)
         {
            $left = intval($vid)*12;
            $vtag = str_replace("*LEFT*", "left:-".$left."px", $vtag);
            $html .= $vtag;
         }

         $html .= "<h3 class=\"sexhack_gallery_title woocommerce-loop-product__title\" alt='".$vtitle."'>".trim_text_preview($vtitle, 60, false)."</h3>";
         $html .= "</a></li>";

         return $html;
      }
   }

   $GLOBALS['sh_videogallery'] = new SH_VideoGAllery();
   do_action('sh_videogallery_ready');
}

?>

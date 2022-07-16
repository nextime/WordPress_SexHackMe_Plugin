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
         $this->productlist = false;

         // Register Query Vars
         add_filter("query_vars", array($this, "query_vars"));
         //add_filter('page_template', array($this, 'sexhack_video_template'));
         add_filter('archive_template', array($this, 'sexhack_video_template'));

         add_action('pre_get_posts', array($this, 'fix_video_query'), 1, 1);

      }


      public function query_vars($vars)
      {
         $vars[] = 'wooprod';
         $vars[] = 'videoaccess';
         return $vars;
      }

      public function sexhack_video_template($template) 
      {
         $template='video.php';
         if(isset($_GET['SEXHACKDEBUG'])) $template='newvideo.php';
         $is_sexhack_video = get_query_var('wooprod', false);
         if($is_sexhack_video ) {
            set_query_var( 'post_type', 'sexhack_video' );
            if ( file_exists( plugin_dir_path(__DIR__) . '/templates/'.$template)) {
               return plugin_dir_path(__DIR__) . '/templates/'.$template;
            }
          }
          return $template;
      }


      public function fix_video_query($query)
      {  
         if($query->get('post_type')=='sexhack_video') {
            $wooprod = $query->get('wooprod', false);
            if($wooprod) {
               $query->query['post_type'] = 'sexhack_video';
               $query->set('name', esc_sql($wooprod));
               $query->set('post_type', 'any');
               //$query->set('post_type', '');
            }
         }
      }

      public function getProducts($vcat=false) {
   
         if(!$this->productlist && !$vcat) $this->productlist = SH_Query::get_Videos($vcat); //$this->_getProducts($vcat);
         else if($vcat) return SH_Query::get_Videos($vcat); //$this->_getProducts($vcat);

         return $this->productlist;

      }
      
      public function get_video_thumb()
      {

         $DEFAULTSLUG = get_option('sexhack_gallery_slug', 'v');

         $id = get_the_ID();
         $prod = wc_get_product($id);
         $image = get_the_post_thumbnail($id, "medium", array("class" => "sexhack_thumbnail")); //array("class" => "alignleft sexhack_thumbnail"));

         $hls = $prod->get_attribute("hls_public");
         $hls_member = $prod->get_attribute("hls_members");
         $hls_premium = $prod->get_attribute("hls_premium");
         $video_preview = $prod->get_attribute("video_preview");
         $gif_preview = $prod->get_attribute("gif_preview");
         $vr_premium = $prod->get_attribute("vr_premium");
         $vr_member = $prod->get_attribute("vr_members");
         $vr_public = $prod->get_attribute("vr_public");
         $vr_preview = $prod->get_attribute("vr_preview");
         $categories = explode(", ", html2text( wc_get_product_category_list($id)));


         //print_r($categories);

         $gif = $prod->get_attribute("gif_thumbnail");
         if(!$gif) $gif = $gif_preview;
         if($gif) $image .= "<img src='$gif' class='alignleft sexhack_thumb_hover' loading='lazy' />";

         $html = '<li class="product type-product sexhack_thumbli">';
         $vurl = str_replace("/product/", "/".$DEFAULTSLUG."/", esc_url( get_the_permalink() ));
         $vtitle = esc_html( get_the_title() );
         $vtags=array();

         $downtag ='';
         if((!$hls) AND (!$hls_member) AND (!$hls_premium) AND (($video_preview) OR ($vr_preview))) $vtags[] = '<label class="sexhack_vtag sexhack_preview" style="*LEFT*">preview</label>';
         if(($hls) OR ($vr_public)) $vtags[] = '<label class="sexhack_vtag sexhack_public" style="*LEFT*">public</label>';
         if(($hls_member) OR ($vr_member))$vtags[] = '<label class="sexhack_vtag sexhack_members" style="*LEFT*">members</label>';
         if(($hls_premium) OR ($vr_premium))$vtags[] = '<label class="sexhack_vtag sexhack_premium" style="*LEFT*">premium</label>';

         if(count($prod->get_downloads()) > 0) $html .= '<label class="sexhack_vtag sexhack_download"">download</label>';
         if(($vr_premium) OR ($vr_member) OR ($vr_public) OR ($vr_preview) 
            OR ((count($prod->get_downloads()) > 0) 
            AND (in_array("VR180", $categories) 
            OR in_array("VR360", $categories)))) $html .= '<label class="sexhack_vtag sexhack_VR"">VR/3D</label>';         

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
}

?>

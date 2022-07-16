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
         add_shortcode("sexgallery", array($this, "sexgallery_shortcode"));
         //add_action('add_meta_boxes', array($this, "sexhack_video_metaboxes"));
         //add_action('admin_init', array($this, "register_settings"));
			//add_filter('page_template', array($this, 'sexhack_video_template'));
			add_filter('archive_template', array($this, 'sexhack_video_template'));
			add_action('save_post', array($this, 'save_sexhack_video_meta_box_data' ));

			add_action('pre_get_posts', array($this, 'fix_video_query'), 1, 1);
         sexhack_log('SexHackVideoGallery() Instanced');

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
         		sexhack_log("NEW TEMPLATE!: ".plugin_dir_path(__DIR__) . '/templates/'.$template);
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
         		sexhack_log($_SERVER['REQUEST_URI']." BEFORE ".print_r($query, true));
         		$query->query['post_type'] = 'sexhack_video';
         		$query->set('name', esc_sql($wooprod));
         		$query->set('post_type', 'any');
         		//$query->set('post_type', '');
         		sexhack_log("AFTER ".print_r($query, true));
      		}
   		}
		}



      public function sexhack_video_metaboxes($post=false)
      {
			add_meta_box( 'sh-mbox-videodescription', 'Video Description', array($this, 'load_metabox_videodescription'), 'sexhack_video', 'normal','default');
         add_meta_box( 'sh-mbox-video', 'Video locations', array( $this, 'load_metabox_videolocations' ), 'sexhack_video', 'normal', 'default' );
         //remove_meta_box( 'postimagediv', 'sexhack_video', 'side' );
         add_meta_box('postimagediv', 'Video Thumbnail', 'post_thumbnail_meta_box', 'sexhack_video', 'side', 'default');
      }

		public function load_metabox_videodescription($post)
		{
			wp_nonce_field('video_description_nonce','sh_video_description_nonce');
			$value = get_post_meta( $post->ID, 'video_description', true );
         echo '<textarea style="width:100%" id="video_description" name="video_description">' . esc_attr( $value ) . '</textarea>';

		}

		public function save_sexhack_video_meta_box_data( $post_id ) {


    		// Verify that the nonce is set and valid.
    		if (!isset( $_POST['sh_video_description_nonce']) 
				|| !wp_verify_nonce( $_POST['sh_video_description_nonce'], 'video_description_nonce' ) ) {
        		return;
    		}

    		// If this is an autosave, our form has not been submitted, so we don't want to do anything.
    		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
        		return;
    		}

    		// Check the user's permissions.
    		if ( isset( $_POST['post_type'] ) && 'page' == $_POST['post_type'] ) {

        		if ( ! current_user_can( 'edit_page', $post_id ) ) {
            	return;
        		}

    		}
   		 else {
        		if ( ! current_user_can( 'edit_post', $post_id ) ) {
            	return;
        		}
    		}

    		/* OK, it's safe for us to save the data now. */

    		// Make sure that it is set.
    		if ( ! isset( $_POST['video_description'] ) ) {
        		return;
    		}

    		// Sanitize user input.
    		$my_data = sanitize_text_field( $_POST['video_description'] );

    		// Update the meta field in the database.
    		update_post_meta( $post_id, 'video_description', $my_data );
		}


      public function load_metabox_videolocations($post) //($object, $box)
      {
    		wp_nonce_field( 'global_notice_nonce', 'global_notice_nonce' );

    		$value = get_post_meta( $post->ID, '_global_notice', true );

    		echo '<textarea style="width:100%" id="global_notice" name="global_notice">' . esc_attr( $value ) . '</textarea>';
      }

      public function getProducts($vcat=false) {
   
         if(!$this->productlist && !$vcat) $this->productlist = $this->_getProducts($vcat);
         else if($vcat) return $this->_getProducts($vcat);

         return $this->productlist;

      }
      

      // TODO: add pagination support
	   public function _getProducts($vcat=false) 
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

      public function sexgallery_shortcode($attr, $cont)
      {
         global $post;
         extract( shortcode_atts(array(
            "category" => "all",
         ), $attr));

         $html = "<div class='sexhack_gallery'>"; //<h3>SexHack VideoGallery</h3>";
         $html .= '<ul class="products columns-4">';
			$products = $this->getProducts();
			while( $products->have_posts() ) {
				$products->the_post();
				$html .= $this->get_video_thumb();
         }
			wp_reset_postdata();
			$html .= "</ul></div>";
         return $html;
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

   $gal = new SH_VideoGallery();
   $GLOBAL['sh_videogallery'] = $gal;
}

?>

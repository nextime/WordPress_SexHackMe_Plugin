<?php

namespace wp_SexHackMe;

if(!class_exists('SexhackWoocommerceProductVideos')) {
   class SexhackWoocommerceProductVideos
   {
      public function __construct()
      {
         sexhack_log('SexhackWoocommerceProductVideos() Instanced');
         add_action( 'woocommerce_before_single_product', array($this, 'video_remove_default_woocommerce_image' ));
         add_filter( 'query_vars', array($this, 'themeslug_query_vars' ));
      }

      public function themeslug_query_vars( $qvars ) {
         $qvars[] = 'sexhack_forcevideo';
         return $qvars;
      }

		public function video_remove_default_woocommerce_image() {
			remove_action( 'woocommerce_before_single_product_summary', 'woocommerce_show_product_images', 20 );
         remove_action( 'woocommerce_product_thumbnails', 'woocommerce_show_product_thumbnails', 20 );
         add_action( 'woocommerce_before_single_product_summary', array($this, 'woocommerce_show_product_images_videos'), 30 );
		}

		public function woocommerce_show_product_images_videos() {
    
    		// Get video and display
    		$prod = wc_get_product(get_the_ID());
   
			// verify GET vars
			$bypass = get_query_var('sexhack_forcevideo', false);
	
			// Possible displays
			$disps = array('video', 'gif', 'image');
	
			// By default fallback to:
			$display='image';
	
			// detect attributes
			$video = $prod->get_attribute('video_preview');
			$gif = $prod->get_attribute('gif_preview');
	
			if(in_array($bypass, $disps)) $display=$bypass;
			else if($video) $display="video";
			else if($gif) $display="gif";
   
			switch($display) {
				case "video":
			
        			// Sanitize video URL
        			$video = esc_url( $video );

        			// Display video
        			echo '<div class="images"><div class="responsive-video-wrap"><h3>Video Preview</h3>';
					echo '<video src='."'$video'".' controls autoplay muted playsinline loop></video></div></div>';
					break;
			
				case "gif":
		
					// sanitize URL
					$gif = esc_url( $gif );
		
					// Display GIF
					echo '<div class="images"><img src="'.$gif.'" /></div>';
					break;
			
				case "image":
        
        			// No video defined so get thumbnail
        			wc_get_template( 'single-product/product-image.php' );
    				break;
			}
		}

   }
}

$SEXHACK_SECTION = array('class' => 'SexhackWoocommerceProductVideos', 'description' => 'Video in Products for woocommerce', 'name' => 'sexhackme_video');

?>

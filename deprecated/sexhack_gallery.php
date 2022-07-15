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

$SEXHACK_GALLERY_DEFAULTSLUG = 'v';

if(!class_exists('SexHackVideoGallery')) {

	// Creating the widget
	class sexhack_gallery_widget extends \WP_Widget {
 
		function __construct() 
		{
			parent::__construct(
			// Base ID of your widget
			'sexhack_gallery_widget', 
 
			// Widget name will appear in UI
			__('SexHack Gallery', 'sexhack_widget_domain'), 
 
			// Widget description
			array( 'description' => __( 'Add SexHack Gallery links', 'sexhack_widget_domain' ), )
			);
		}
 
		// Creating widget front-end
		public function widget( $args, $instance ) 
		{
			global $post;

			$pattern = get_shortcode_regex();
 
    		if (   preg_match_all( '/'. $pattern .'/s', $post->post_content, $matches )
        		&& array_key_exists( 2, $matches )
        		&& in_array( 'sexgallery', $matches[2] )
    			) 
			{
				$current_url = get_permalink(get_the_ID());

				$title = apply_filters( 'widget_title', $instance['title'] );
 
				// before and after widget arguments are defined by themes
				echo $args['before_widget'];
				if ( ! empty( $title ) )
					echo $args['before_title'] . $title . $args['after_title'];
 				?>
					<ul>
						<li><a href="">All videos</a></li>
						<li><a href="?sexhack_vselect=public">Public videos</a></li>
						<li><a href="?sexhack_vselect=members">Members videos</a></li>
						<li><a href="?sexhack_vselect=premium">Premium videos</a></li>
						<li><a href="?sexhack_vselect=preview">Previews videos</a></li>
					</ul>
				<?php
				echo $args['after_widget'];
			}
		}
 		
		// Widget Backend
		public function form( $instance )
	 	{	
			if ( isset( $instance[ 'title' ] ) ) 
			{
				$title = $instance[ 'title' ];
			}
			else {
				$title = __( 'Filter gallery', 'sexhack_widget_domain' );
			}
			// Widget admin form
			?>
			<p>
				<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title:' ); ?></label>
				<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>" />
			</p>
			<?php
		}
 
		// Updating widget replacing old instances with new
		public function update( $new_instance, $old_instance ) 
		{
				$instance = array();
				$instance['title'] = ( ! empty( $new_instance['title'] ) ) ? strip_tags( $new_instance['title'] ) : '';
				return $instance;
		} 
 
			// Class wpb_widget ends here
	} 
 
   // Register and load the widget
	function gallery_load_widget() {
   		register_widget( 'wp_SexHackMe\sexhack_gallery_widget' );
	}
	add_action( 'widgets_init', 'wp_SexHackMe\gallery_load_widget' );



   class SexHackVideoGallery
   {


      public function __construct()
      {

         $this->productlist = false;

         // Register Query Vars
         add_filter("query_vars", array($this, "query_vars"));
         add_shortcode("sexgallery", array($this, "sexgallery_shortcode"));
         add_action('init', array($this, "register_sexhack_video_post_type"));
         //add_action('add_meta_boxes', array($this, "sexhack_video_metaboxes"));
         add_action('admin_init', array($this, "register_settings"));
			//add_filter('page_template', array($this, 'sexhack_video_template'));
			add_filter('archive_template', array($this, 'sexhack_video_template'));
			add_action('save_post', array($this, 'save_sexhack_video_meta_box_data' ));

			add_action('pre_get_posts', array($this, 'fix_video_query'), 1, 1);
         sexhack_log('SexHackVideoGallery() Instanced');

      }


      public function register_settings() 
      {
         add_settings_section('sexhackme-gallery-settings', ' ', array($this, 'settings_section'), 'sexhackme-gallery-settings');
         register_setting('sexhackme-gallery-settings', 'sexhack_gallery_slug');
         //add_settings_field('sexhack_gallery_slug', 'sexhack_gallery_slug', 'sexhack_gallery_slug',
         //                  array($this, 'settings_field'), 'sexhackme-gallery-settings', 'sexhackme-gallery-settings' );

      }

      public function settings_section() 
      { 
         echo "<h2>SexHackMe Gallery Settings</h2>";
      }

      /*
      public function settings_field($name) 
      {              
         echo $name;    
      } 
      */      

      public function check_rewrite($rules)
      {
         // TODO Check if our rules are present and call flush if not
         //      (double check if already done in the $this->register_sexhack_video_post_type and it's enough)
         sexhack_log($rules);
         return $rules;
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



      // sets custom post type
      // TODO: the idea is to have custom post type for models profiles and for videos.
      //       Ideally /$DEFAULTSLUG/nomevideo/ finisce sul corrispettivo prodotto woocommerce, 
      //       /$DEFAULTSLUG/modelname/nomevideo/ finisce sul corrispettivo page sexhackme_video quando show_in_menu e' attivo.
      //
      //       Devo pero' verificare le varie taxonomy e attributi della pagina, vedere come creare un prodotto in wordpress
      //       per ogni pagina sexhack_video che credo, sincronizzare prodotti e video pagine, gestire prodotti con lo stesso nome
      //       ( credo si possa fare dandogli differenti slugs ) 
		public function register_sexhack_video_post_type() 
		{
    		global $wp_rewrite;
         global $SEXHACK_GALLERY_DEFAULTSLUG;

         $DEFAULTSLUG = get_option('sexhack_gallery_slug', $SEXHACK_GALLERY_DEFAULTSLUG);


         sexhack_log("REGISTER SEXHACK_VIDEO ");

    		register_post_type('sexhack_video', array(
             'labels'        => array(
                'name'                  => 'Videos',
                'singular_name'         => 'Video',
                'add_new'               => 'Add New',
                'add_new_item'          => 'Add New Video',
                'edit_item'             => 'Edit Video',
                'not_found'             => 'There are no videos yet',
                'not_found_in_trash'    => 'Nothing found in Trash',
                'search_items'          => 'Search videos',
               ),
            'description' => 'Videos for SexHack.me gallery',
       		'public' => true,
				'register_meta_box_cb' => array($this, 'sexhack_video_metaboxes'),
       		'show_ui' => true,
       		'show_in_menu' => true,
				'show_in_rest' => true,
				'menu_position' => 32,
       		'capability_type' => 'post', // XXX We should create our own cap type?
				// 'capabilities' => array(), // Or just select specific capabilities here
       		'hierarchical' => true,
       		'publicly_queryable' => true,
       		'rewrite' => false,
       		'query_var' => true,
       		'has_archive' => true,
       		'supports' => array('title'), // 'thumbnail', 'editor','excerpt','trackbacks','custom-fields','comments','revisions','author','page-attributes'),
       		'taxonomies' => array('category','post_tag'), // XXX Shouldn't we have a "video_type" taxonomy for VR or flat?
    		));

         $projects_structure = '/'.$DEFAULTSLUG.'/%wooprod%/';
         $rules = $wp_rewrite->wp_rewrite_rules();
         if(array_key_exists($DEFAULTSLUG.'/([^/]+)/?$', $rules)) {
            sexhack_log("REWRITE: rules OK: ".$DEFAULTSLUG.'/([^/]+)/?$ => '.$rules[$DEFAULTSLUG.'/([^/]+)/?$']);
         } else {
            sexhack_log("REWRITE: Need to add and flush our rules!");
            $wp_rewrite->add_rewrite_tag("%wooprod%", '([^/]+)', "post_type=sexhack_video&wooprod=");
            $wp_rewrite->add_rewrite_tag("%videoaccess%", '([^/]+)', "videoaccess=");
            $wp_rewrite->add_permastruct($DEFAULTSLUG, $projects_structure, false);
            $wp_rewrite->add_permastruct($DEFAULTSLUG, $projects_structure."%videoaccess%/", false);
            update_option('need_rewrite_flush', 1);

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
   
         if(!$this->productlist)
            $this->productlist = $this->_getProducts($vcat);

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
         global $SEXHACK_GALLERY_DEFAULTSLUG;

         $DEFAULTSLUG = get_option('sexhack_gallery_slug', $SEXHACK_GALLERY_DEFAULTSLUG);

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
}


$SEXHACK_SECTION = array(
   'class' => 'SexHackVideoGallery', 
   'description' => 'Create Video galleries for Sexhack Video products', 
   //'require-page' => true,
   'name' => 'sexhackme_videogallery'
);

?>

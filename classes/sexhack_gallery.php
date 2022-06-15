<?php
namespace wp_SexHackMe;

if(!class_exists('SexHackVideoGallery')) {
   class SexHackVideoGallery
   {


      public function __construct()
      {

         $this->productlist = false;

         // Register Query Vars
         add_filter("query_vars", array($this, "query_vars"));
         add_action('wp_enqueue_scripts', array( $this, 'add_css' ), 200);
         add_shortcode("sexgallery", array($this, "sexgallery_shortcode"));
         add_action('init', array($this, "register_sexhack_video_post_type"));
			//add_filter('page_template', array($this, 'sexhack_video_template'));
			add_filter('archive_template', array($this, 'sexhack_video_template'));
			add_action('pre_get_posts', array($this, 'fix_video_query'), 1, 1);
         sexhack_log('SexHackVideoGallery() Instanced');

      }


      public function check_rewrite($rules)
      {
         // TODO Check if our rules are present and call flush if not
         sexhack_log($rules);
         return $rules;
      }

      public function add_css() 
      {
         wp_enqueue_style ('sexhackme_gallery', plugin_dir_url(__DIR__).'css/sexhackme_gallery.css');
      }

      public function query_vars($vars)
      {
			$vars[] = 'wooprod';
         return $vars;
      }

		public function sexhack_video_template($template) 
		{
   		$is_sexhack_video = get_query_var('wooprod', false);
   		if($is_sexhack_video ) {
      		set_query_var( 'post_type', 'sexhack_video' );
      		if ( file_exists( plugin_dir_path(__DIR__) . '/template/video.php')) {
         		sexhack_log("NEW TEMPLATE!: ".plugin_dir_path(__DIR__) . '/template/video.php');
         		return plugin_dir_path(__DIR__) . '/template/video.php';
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
         		$query->set('name', $wooprod);
         		$query->set('post_type', 'any');
         		//$query->set('post_type', '');
         		sexhack_log("AFTER ".print_r($query, true));
      		}
   		}
		}



      // sets custom post type
      // TODO: the idea is to have custom post type for models profiles and for videos.
      //       Ideally /v/nomevideo/ finisce sul corrispettivo prodotto woocommerce, 
      //       /v/modelname/nomevideo/ finisce sul corrispettivo page sexhackme_video quando show_in_menu e' attivo.
      //
      //       Devo pero' verificare le varie taxonomy e attributi della pagina, vedere come creare un prodotto in wordpress
      //       per ogni pagina sexhack_video che credo, sincronizzare prodotti e video pagine, gestire prodotti con lo stesso nome
      //       ( credo si possa fare dandogli differenti slugs ) 
		public function register_sexhack_video_post_type() 
		{
    		global $wp_rewrite;
         
         sexhack_log("REGISTER SEXHACK_VIDEO ");

    		register_post_type('sexhack_video', array(
       		'label' => 'Sexhack.me Video','description' => '',
       		'public' => true,
       		'show_ui' => true,
       		'show_in_menu' => false, // Visibility in admin menu.
       		'capability_type' => 'post',
       		'hierarchical' => false,
       		'publicly_queryable' => true,
       		'rewrite' => false,
       		'query_var' => true,
       		'has_archive' => true,
       		'supports' => array('title','editor','excerpt','trackbacks','custom-fields','comments','revisions','thumbnail','author','page-attributes'),
       		'taxonomies' => array('category','post_tag'),
       		// there are a lot more available arguments, but the above is plenty for now
    		));

    		$projects_structure = '/v/%wooprod%/';
         $rules = $wp_rewrite->wp_rewrite_rules();
         // XXX This is HORRIBLE. Using a Try/Catch to test an array key is stupid. But apparently php is also 
         //     stupid and array_key_exists() doesn't work.
         try {
            sexhack_log("REWRITE: rules OK: ".'v/([^/]+)/?$ => '.$rules['v/([^/]+)/?$']);
         } catch(Exception $e) {
            sexhack_log("REWRITE: Need to add and flush our rules!");
            $wp_rewrite->add_rewrite_tag("%wooprod%", '([^/]+)', "post_type=sexhack_video&wooprod=");
            $wp_rewrite->add_permastruct('v', $projects_structure, false);
            $wp_rewrite->flush_rules();

         }
		}


      public function getProducts() {
   
         if(!$this->productlist)
            $this->productlist = $this->_getProducts();

         return $this->productlist;

      }
      

      // TODO: add pagination support
	   public function _getProducts() 
		{
   		$products = new \WP_Query( array(
 
      		/*
       		* We're limiting the results to 100 products, change this as you
       		* see fit. -1 is for unlimted but could introduce performance issues.
       		*/
      		'posts_per_page' => 100,
      		'post_type'      => 'product',
            'post_status'    => 'publish',
            'product_cat'    => 'Videos',
      		'order'          => 'ASC',
      		'orderby'        => 'title',
            'tax_query'    => array( array(
               'taxonomy'  => 'product_visibility',
               'terms'     => array( 'exclude-from-catalog' ),
               'field'     => 'name',
               'operator'  => 'NOT IN',
            ) ),
            //'meta_query'   => array( array(
            //   'value'     => 'hls_public',
            //   'compare'   => 'like'
            //) ),
   		) );
			
			//sexhack_log(var_dump($products));
			return $products;
   	}

      
      public function sexgallery_shortcode($attr, $cont)
      {
         global $post;
         extract( shortcode_atts(array(
            "category" => "free",
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
      	$id = get_the_ID();
      	$prod = wc_get_product($id);
      	$image = get_the_post_thumbnail($id, "medium", array("class" => "sexhack_thumbnail")); //array("class" => "alignleft sexhack_thumbnail"));

      	$gif = $prod->get_attribute("gif_preview");
			if($gif) $image .= "<img src='$gif' class='alignleft sexhack_thumb_hover' />";

      	$html = '<li class="product type-product sexhack_thumbli">';

      	$vurl = str_replace("/product/", "/v/", esc_url( get_the_permalink() ));
      	$vtitle = esc_html( get_the_title() );

      	$html .= "<a href=\"$vurl\" class=\"woocommerce-LoopProduct-link woocommerce-loop-product__link\">";
      	$html .= "<div class='sexhack_thumb_cont'>".$image."</div>";
			// XXX Add the "hover" text preview, in "alt"?
      	$html .= "<h2 class=\"woocommerce-loop-product__title\" alt='".$vtitle."'>".trim_text_preview($vtitle, 120)."</h2>";
      	$html .= "</a></li>";

			return $html;
		}
	}
}




$SEXHACK_SECTION = array(
   'class' => 'SexHackVideoGallery', 
   'description' => 'Create Video galleries for Sexhack Video products', 
   'require-page' => true,
   'name' => 'sexhackme_videogallery'
);

?>

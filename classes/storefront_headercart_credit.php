<?php
namespace wp_SexHackMe;

if(!class_exists('StorefrontMoveHeaderCart')) {
   class StorefrontMoveHeaderCart
   {
      public function __construct()
      {
         sexhack_log('StorefrontMoveHeaderCart() Instanced');
         add_action( 'init', array($this, 'remove_header_cart' ));
         add_filter('storefront_credit_link', false);
         add_action('wp_enqueue_scripts', array( $this, 'add_css' ), 200);

			add_action( 'storefront_header', array($this, 'add_header_cart'), 40);
      }

      public function add_css()
      {
         wp_enqueue_style ('sexhackme_header', plugin_dir_url(__DIR__).'css/sexhackme_header.css');

      }

		public function remove_header_cart()
		{
			remove_action( 'storefront_header', 'storefront_header_cart', 60 );
			remove_action( 'storefront_header', 'storefront_product_search', 40); 
		}

		public function add_header_cart()
		{
			storefront_header_cart();
		}
   }
}




$SEXHACK_SECTION = array(
   'class' => 'StorefrontMoveHeaderCart', 
   'description' => 'Move storefront header cart and remove find products and credits', 
   'name' => 'sexhackme_sf_headercart'
);

?>

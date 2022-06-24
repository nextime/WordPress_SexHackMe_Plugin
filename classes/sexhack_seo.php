<?php
namespace wp_SexHackMe;

if(!class_exists('SexHackSEO')) {
   class SexHackSEO
   {
      public function __construct()
      {
         do_action( 'wp_head', array($this, 'add_meta_tags' ));
         sexhack_log('SexHackSEO() Instanced');
      }

      public function add_meta_tags() 
      {
         global $post;
         // XXX Use post tags for meta keywords, if not available like in pages?
         // What to use for description?
      }
   }
}




$SEXHACK_SECTION = array(
   'class' => 'SexHackSEO', 
   'description' => 'SEO functionality for SexHack', 
   'name' => 'sexhackme_seo'
);

?>

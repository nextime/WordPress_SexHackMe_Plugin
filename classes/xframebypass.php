<?php
namespace wp_SexHackMe;

if(!class_exists('XFrameByPass')) {
   class XFrameByPass
   {
      public function __construct()
      {
         sexhack_log('XFrameByPass() Instanced');
         add_shortcode( 'xfbp', array( $this, 'xfbp_shortcode_fn' ));
         add_action('wp_enqueue_scripts', array( $this, 'xfbp_js' ));
      }

      public function xfbp_js()
      {
         wp_enqueue_script('xfbp_poly', plugin_dir_url(__DIR__).'js/custom-elements-builtin.js');
         wp_enqueue_script('xfbp_js', plugin_dir_url(__DIR__).'js/x-frame-bypass.js');
      }

      public function xfbp_shortcode_fn($attributes, $content)
      {
         extract( shortcode_atts(array(
            'url' => 'https://www.sexhack.me',
         ), $attributes));
         return '<iframe is="x-frame-bypass" src="'.$url.'"></iframe>';
      }
   }
}




$SEXHACK_SECTION = array(
   'class' => 'XFrameByPass', 
   'description' => 'Bypass iframe limitation with x-frame-bypass', 
   'name' => 'sexhackme_xframebypass'
);

?>

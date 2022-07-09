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

if(!class_exists('SexhackAdvert')) {

   class sexhackadv_widget extends \WP_Widget {
      function __construct() 
      {
         parent::__construct(
         // Base ID of your widget
         'sexhackadv_widget', 
 
         // Widget name will appear in UI
         __('SexHack ADV', 'sexhack_widget_domain'), 
 
         // Widget description
         array( 'description' => __( 'Adverstising widget for SexHackMe', 'sexhack_widget_domain' ), )
         );
      }

     public function widget( $args, $instance )
     {
         global $post;

         echo $args['before_widget'];
         $id=$instance['advid'];
         if ( ! empty($id)) 
            echo /*$args['before_advid'] . */ do_shortcode('[sexadv adv='.$id.']'); //. $args['after_advid'];
			echo $args['after_widget'];
	  }

      public function form( $instance )
      {
         if ( isset( $instance[ 'advid' ] ) )
         {
            $advid = $instance[ 'advid' ];
         }
         else {
            $advid = __( 'ADVert ID', 'sexhack_widget_domain' );
         }
         // Widget admin form
         ?>
         <p>
            <label for="<?php echo $this->get_field_id( 'advid' ); ?>"><?php _e( 'ID Advert:' ); ?></label>
            <input class="widefat" id="<?php echo $this->get_field_id( 'advid' ); ?>" name="<?php echo $this->get_field_name( 'advid' ); ?>" type="text" value="<?php echo esc_attr( $advid ); ?>" />
         </p>
         <?php
      }

     // Updating widget replacing old instances with new
      public function update( $new_instance, $old_instance )
      {
            $instance = array();
            $instance['advid'] = ( ! empty( $new_instance['advid'] ) ) ? strip_tags( $new_instance['advid'] ) : '';
            return $instance;
      }

	}

  // Register and load the widget
   function adv_load_widget() {
     register_widget( 'wp_SexHackMe\sexhackadv_widget' );
   }
   add_action( 'widgets_init', 'wp_SexHackMe\adv_load_widget' );


   class SexhackAdvert
   {
      public function __construct()
      {
			add_shortcode("sexadv", array($this, "adv_shortcode"));
		   add_action('init', array($this, "register_sexhackadv_post_type"));	



         sexhack_log('SexhackAdvert() Instanced');
      }

      public function revealid_add_id_column($columns) 
      {
         $columns['revealid_id'] = 'ID';
         $columns['revealid_short'] = 'shortcode';
         return $columns;
      }

      public function revealid_id_column_content( $column, $id ) {
  			if( 'revealid_id' == $column ) {
    			echo $id;
  			}
			if( 'revealid_short' == $column ) {
				echo "[sexadv adv=$id]";
			}
		}

	   public function register_sexhackadv_post_type()
	   {
			register_post_type('sexhackadv', array(
				'label' => 'Advertising','description' => '',
            'public' => true,
            'show_ui' => true,
            'show_in_menu' => true, // Visibility in admin menu.
            'menu_position' => 151,
            'capability_type' => 'post',
            'hierarchical' => false,
            'publicly_queryable' => true,
            'rewrite' => false,
            'query_var' => true,
            'has_archive' => true,
            'supports' => array('title','editor','page-attributes', 'shortcode'),
            'taxonomies' => array(),
            // there are a lot more available arguments, but the above is plenty for now

			));

			add_filter( 'manage_sexhackadv_posts_columns', array($this, 'revealid_add_id_column'), 5 );
         add_action( 'manage_sexhackadv_posts_custom_column', array($this, 'revealid_id_column_content'), 5, 2 );

		}


      public function adv_shortcode($attr, $cont)
      {
         global $post;
         global $sexhack_pms;
         extract( shortcode_atts(array(
            "adv" => false,
         ), $attr));
         if(isset($sexhack_pms) && !$sexhack_pms->is_premium())
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

   }
}




$SEXHACK_SECTION = array(
   'class' => 'SexhackAdvert', 
   'description' => 'Advertising support for SexHackMe', 
   'name' => 'sexhackme_advertising',
   //'require-page' => 'sexhackadv'
   'require-page' => array(array('post_type' => 'sexhackadv', 'title' => 'Top Banner Video Page', 'option' => 'sexadv_video_top'),
                           array('post_type' => 'sexhackadv', 'title' => 'Bottom Banner Video Page', 'option' => 'sexadv_video_bot')
                        )
);

?>

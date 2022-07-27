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


if(!class_exists('SH_ADVWidget')) {
   class SH_ADVWidget extends \WP_Widget 
   {
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

      public static function register()
      {
         register_widget('wp_SexHackMe\SH_ADVWidget');
      }

   }

   add_action( 'widgets_init', array('wp_SexHackMe\SH_ADVWidget', 'register' ));

}


if(!class_exists('SH_GalleryWidget')) {

   // Creating the widget
   class SH_GalleryWidget extends \WP_Widget {

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

         if(!is_object($post)) return;

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


      public static function register()
      {  
         register_widget('wp_SexHackMe\SH_GalleryWidget');
      }


   }  // Class widget ends here

    add_action( 'widgets_init', array('wp_SexHackMe\SH_GalleryWidget', 'register' ));
}

?>

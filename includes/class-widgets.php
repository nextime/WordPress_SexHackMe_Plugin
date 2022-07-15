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


?>

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

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;
?>
   <div class="wrap">
         <?php do_settings_sections( 'sexhackme-advert-settings' ); ?>
         <form method="post" action="/wp-admin/options.php">
         <?php settings_fields( 'sexhackme-advert-settings' ); ?>
         <table class="form-table">
            <tr align="top">
               <td>
                    <select id="sexadv_video_top" name="sexadv_video_top" class="widefat">
                        <option value="-1">Choose...</option>
                        <?php
                        $opt=get_option("sexadv_video_top");
                        foreach( get_posts(array('post_type'  => 'sexhackadv', 'parent' => 0, 'numberposts' => -1)) as $page ) {
                           echo '<option value="' . esc_attr( $page->ID ) . '"';
                           if ($opt == $page->ID) { echo "selected";}
                           echo '>' . esc_html( $page->post_title ) . ' ( ID: ' . esc_attr( $page->ID ) . ')' . '</option>';
                        }  ?>
                     </select>
							<p class="description">Select ADV for top Videogallery</p>
                     <select id="sexadv_video_bot" name="sexadv_video_bot" class="widefat">
                         <option value="-1">Choose...</option>
                         <?php
                         $opt=get_option("sexadv_video_bot");
                         foreach( get_posts(array('post_type'  => 'sexhackadv', 'parent' => 0)) as $page ) {
                            echo '<option value="' . esc_attr( $page->ID ) . '"';
                            if ($opt == $page->ID) { echo "selected";}
                            echo '>' . esc_html( $page->post_title ) . ' ( ID: ' . esc_attr( $page->ID ) . ')' . '</option>';
                         }  ?>
                      </select>
                     <p class="description">Select ADV for bottom Videogallery</p>
               </td>
            </tr>
          </table>
         <?php submit_button(); ?>
         </form>
   </div>


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
         <?php do_settings_sections( 'sexhackme-gallery-settings' ); ?>
         <form method="post" action="/wp-admin/options.php">
         <?php settings_fields( 'sexhackme-gallery-settings' ); ?>
         <table class="form-table">
            <tr align="top">
               <td>
                     <select id="sexhack_video_page" name="sexhack_video_page" class="widefat">
                         <option value="-1">Choose...</option>
                         <?php
                         $opt=get_option("sexhack_video_page");
                         foreach( get_pages() as $page ) {
                            echo '<option value="' . esc_attr( $page->ID ) . '"';
                            if ($opt == $page->ID) { echo "selected";}
                            echo '>' . esc_html( $page->post_title ) . ' ( ID: ' . esc_attr( $page->ID ) . ')' . '</option>';
                         }  ?>
                      </select>
                     <p class="description">Select Video page</p>
               </td>
            </tr>
            <tr align="top">
               <td>
                     <select id="sexhack_gallery_page" name="sexhack_gallery_page" class="widefat">
                         <option value="-1">Choose...</option>
                         <?php
                         $opt=get_option("sexhack_gallery_page");
                         foreach( get_pages() as $page ) {
                            echo '<option value="' . esc_attr( $page->ID ) . '"';
                            if ($opt == $page->ID) { echo "selected";}
                            echo '>' . esc_html( $page->post_title ) . ' ( ID: ' . esc_attr( $page->ID ) . ')' . '</option>';
                         }  ?>
                      </select>
                     <p class="description">Select Gallery page</p>
               </td>
            </tr>
            <tr align="top">
               <td>
                     <select id="sexhack_video404_page" name="sexhack_video404_page" class="widefat">
                         <option value="-1">Choose...</option>
                         <?php
                         $opt=get_option("sexhack_video404_page");
                         foreach( get_pages() as $page ) {
                            echo '<option value="' . esc_attr( $page->ID ) . '"';
                            if ($opt == $page->ID) { echo "selected";}
                            echo '>' . esc_html( $page->post_title ) . ' ( ID: ' . esc_attr( $page->ID ) . ')' . '</option>';
                         }  ?>
                      </select>
                     <p class="description">Select Video not found page</p>
               </td>
            </tr>
               <tr>
                  <td>
                     <label> Use filter script for HLS?</label>
                     <input type="checkbox" name="sexhack_shmdown" value='1' <?php if(get_option('sexhack_shmdown', false)) echo "checked"; ?>>
                  </td>

                  <td>
                     <label>HLS Filter script URI</label>
                     <input type='text' name='sexhack_shmdown_uri' value='<?php echo get_option('sexhack_shmdown_uri', ''); ?>'>
                  </td>
               </tr>
               <tr>
                  <td>
                     <label>Video Upload TMP path</label>
                     <input type='text' name='sexhack_video_tmp_path' value='<?php echo get_option('sexhack_video_tmp_path', '/tmp'); ?>'>
                  </td>
               </tr>
            <tr>
                  <td>
                     <label>Video Upload FLAT path</label>
                     <input type='text' name='sexhack_video_flat_path' value='<?php echo get_option('sexhack_video_flat_path', '/tmp'); ?>'>
                  </td>
               </tr>
            <tr>
                  <td>
                     <label>Video Upload VR path</label>
                     <input type='text' name='sexhack_video_vr_path' value='<?php echo get_option('sexhack_video_vr_path', '/tmp'); ?>'>
                  </td>
               </tr>
            <tr>
                  <td>
                     <label>Video Storage HLS</label>
                     <input type='text' name='sexhack_video_hls_storage' value='<?php echo get_option('sexhack_video_hls_storage', ABSPATH.'HLS'); ?>'>
                  </td>
                  <td>
                     <label>Video URI HLS</label>
                     <input type='text' name='sexhack_video_hls_uri' value='<?php echo get_option('sexhack_video_hls_uri', '/HLS/'); ?>'>
                  </td>

               </tr>
            <tr>
                  <td>
                     <label>Video Storage Video</label>
                     <input type='text' name='sexhack_video_video_storage' value='<?php echo get_option('sexhack_video_video_storage', ABSPATH.'Videos'); ?>'>
                  </td>
                  <td>
                     <label>Video URI Video</label>
                     <input type='text' name='sexhack_video_video_uri' value='<?php echo get_option('sexhack_video_video_uri', '/Videos/'); ?>'>
                  </td>

               </tr>
            <tr>
                  <td>
                     <label>Video Storage Photo</label>
                     <input type='text' name='sexhack_video_photo_storage' value='<?php echo get_option('sexhack_video_photo_storage', ABSPATH.'Photos'); ?>'>
                  </td>
                  <td>
                     <label>Video URI Photo</label>
                     <input type='text' name='sexhack_video_photo_uri' value='<?php echo get_option('sexhack_video_photo_uri', '/Photos/'); ?>'>
                  </td>

               </tr>
            <tr>
                  <td>
                     <label>Video Storage GIF</label>
                     <input type='text' name='sexhack_video_gif_storage' value='<?php echo get_option('sexhack_video_gif_storage', ABSPATH.'GIF'); ?>'>
                  </td>
                  <td>
                     <label>Video URI GIF</label>
                     <input type='text' name='sexhack_video_gif_uri' value='<?php echo get_option('sexhack_video_gif_uri', '/GIF/'); ?>'>
                  </td>

               </tr>
            <tr>
                  <td>
                     <label>Video Storage VR</label>
                     <input type='text' name='sexhack_video_vr_storage' value='<?php echo get_option('sexhack_video_vr_storage', ABSPATH.'VR'); ?>'>
                  </td>
                  <td>
                     <label>Video URI VR</label>
                     <input type='text' name='sexhack_video_vr_uri' value='<?php echo get_option('sexhack_video_vr_uri', '/VR/'); ?>'>
                  </td>

               </tr>
            <tr>
                  <td>
                     <label>Thumbnail Storage</label>
                     <input type='text' name='sexhack_thumbnail_storage' value='<?php echo get_option('sexhack_thumbnail_storage', ABSPATH.'Thumbs'); ?>'>
                  </td>
                  <td>
                     <label>Thumbnail URI</label>
                     <input type='text' name='sexhack_thumbnail_uri' value='<?php echo get_option('sexhack_thumbnail_uri', '/Thumbs/'); ?>'>
                  </td>

               </tr>
            <tr>
                  <td>
                     <label>Social Post Storage</label>
                     <input type='text' name='sexhack_socialpost_storage' value='<?php echo get_option('sexhack_socialpost_storage', ABSPATH.'SOCIALPOSTS'); ?>'>
                  </td>
               </tr>

            
         </table>
         <?php submit_button(); ?>
         </form>
   </div>


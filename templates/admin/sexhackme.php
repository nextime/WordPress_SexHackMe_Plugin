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
?>
<div class="wrap">
    <h2>SexHackMe Plugin Settings</h2>
    <form method="post" action="/wp-admin/options.php">
        <?php settings_fields( 'sexhackme-settings' ); ?>
        <?php do_settings_sections( 'sexhackme-settings' ); ?>
        <table class="form-table">
           <?php foreach($this->SECTIONS as $section) { ?>
                <tr align="top">
                   <th scope="row"><?php echo $section['description'];?></th>
                     <td>
                        <input type="checkbox" name="<?php echo $section['name'];?>" value="1" <?php echo wp_SexHackMe\checkbox(get_option( $section['name'] )); ?>/>
                        <br>
                      <?php
                         if(array_key_exists('require-page', $section) && ($section['require-page']))
                         {
                            $reqps = array();
                            if(is_string($section['require-page']))
                            {
                               $reqtitle="Select the base plugin module  page";
                               $reqpages=get_posts(array('post_type'    => $section['require-page'], 'parent' => 0));
                               $reqps[] = array('title' => $reqtitle, 'pages' => $reqpages, 'option' => $section['name']."-page");
                            } elseif(is_array($section['require-page'])) {
                               $i=0;
                               foreach($section['require-page'] as $rpage) {
                                  if(array_key_exists('post_type', $rpage)) {
                                     $reqpsa = array('title' => 'Select Page', 'option' => $section['name']."-page$i",
                                        'pages' => get_posts(array('post_type'  => $rpage['post_type'], 'parent' => 0)));
                                     if(array_key_exists('option', $rpage)) $reqpsa['option'] = $rpage['option'];
                                     if(array_key_exists('title', $rpage)) $reqpsa['title'] = $rpage['title'];
                                     $reqps[] = $reqpsa;
                                  }
                                  $i++;

                               }

                            } else {
                               $reqtitle="Select the base plugin module  page";
                               $reqpages=get_pages();
                               $reqps[] = array('title' => $reqtitle, 'pages' => $reqpages, 'option' => $section['name']."-page");
                            }
                           foreach($reqps as $reqarr) {
                        ?>
                        <select id="<?php echo $reqarr['option'];?>" name="<?php echo $reqarr['option']; ?>" class="widefat">
                           <option value="-1"><?php esc_html_e( 'Choose...', 'paid-member-subscriptions' ) ?></option>
                           <?php
                           $opt=get_option($reqarr['option']);
                           foreach( $reqarr['pages'] as $page ) {
                              echo '<option value="' . esc_attr( $page->ID ) . '"';
                              if ($opt == $page->ID) { echo "selected";}
                              echo '>' . esc_html( $page->post_title ) . ' ( ID: ' . esc_attr( $page->ID ) . ')' . '</option>';
                           }  ?>
                        </select>
                        <p class="description"><?php echo $reqarr['title']; ?></p>
                        <?php } ?>
                     <?php } ?>
                     </td>
                  </tr>
               <?php } ?>
            </table>
           <?php submit_button(); ?>
        </form>
     </div>

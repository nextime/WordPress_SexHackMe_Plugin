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

$plans = wp_SexHackMe\sh_get_subscription_plans();

?>
   <div class="wrap">
         <?php do_settings_sections( 'sexhackme-wcpms-settings' ); ?>
         <form method="post" action="/wp-admin/options.php">
         <?php settings_fields( 'sexhackme-wcpms-settings' ); ?>
         <table class="form-table">
            <?php
            foreach($plans as $plan)
            {  
               if($plan->price > 0)
               {
            ?>
            <tr align="top">
               <td>
                  <label><b><?php echo $plan->name ?> woocommerce product</b></label><br>
                  <select id="sexhack-wcpms-<?php echo $plan->id;?>" name="sexhack-wcpms-<?php echo $plan->id; ?>" class="widefat">
                  <?php
                  $opt = get_option('sexhack-wcpms-'.strval($plan->id));
                  foreach(wp_SexHackMe\get_wc_subscription_products_priced($plan->price, $plan->id) as $prod)
                  {
                  ?>
                     <option value='<?php echo $prod->get_id(); ?>' <?php if($opt == $prod->get_id()) echo "selected"; ?> >
                        <?php echo $prod->get_title() ?> (<?php echo $prod->get_id(); ?>)
                     </option>

                  <?php
                  }
                  ?>
                  </select>
               </td>
            </tr>
            <?php 
               } 
            }  
            ?>
         </table>
         <?php do_settings_sections( 'sexhackme-wcpms-settings-email' ); ?>
         <table class="form-table">
            <tr align="top">
               <td>
                    <select id="sexhack_registration_mail_endpoint" name="sexhack_registration_mail_endpoint" class="widefat">
                        <option value="-1">Choose...</option>
                        <?php
                        $opt=get_option("sexhack_registration_mail_endpoint");
                        foreach( get_posts(array('post_type'  => 'page', 'parent' => 0)) as $page ) {
                           echo '<option value="' . esc_attr( $page->ID ) . '"';
                           if ($opt == $page->ID) { echo "selected";}
                           echo '>' . esc_html( $page->post_title ) . ' ( ID: ' . esc_attr( $page->ID ) . ')' . '</option>';
                        }  ?>
                     </select>
                     <p class="description">Select email checkout registration redirect page</p>
               </td>
            </tr>
          </table>
         <?php submit_button(); ?>
         </form>
   </div>


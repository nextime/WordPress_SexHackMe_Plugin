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
$wp_roles = wp_roles();
$roles = $wp_roles->get_names();

if ( ! defined( 'ABSPATH' ) ) exit;
?>
   <div class="wrap">
         <?php do_settings_sections( 'sexhackme-settings' ); ?>
         <h3>SexHackMe general settings</h3>
         <form method="post" action="/wp-admin/options.php">
         <?php settings_fields( 'sexhackme-settings' ); ?>
         <table class="form-table">
            <tr align="top">
               <td>
                  <label>Choose the wp role for Models:</label>
                  <select name="sexhack-model-role">
                     <option value="0">Choose...</option>
                     <?php 
                     $model = get_option('sexhack-model-role', false);
                     foreach($roles as $role)
                     {
                        $add='';
                        if(($model) && ($model==$role)) $add='selected';
                        echo "<option value='".$role."' ".$add." >".$role."</option>";
                     }
                  ?>
                  </select>
               </td>
            </tr>
          </table>
         <?php submit_button(); ?>
         </form>
   </div>


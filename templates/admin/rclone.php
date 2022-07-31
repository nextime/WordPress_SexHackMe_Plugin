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

global $sh_rclone; 

?>
   <div class="wrap">
         <?php do_settings_sections( 'sexhackme-rclone-settings' ); ?>
         <?php if(!wp_SexHackMe\rclone_active()) { ?>
            <p>Rclone isn't installed on your server and/or shell_exec() isn't enabled.</p>
            <p>Please install rclone and enable shell_exec() if you would like to use this feature</p>
         <?php } ?>
            <form method="post" action="/wp-admin/options.php">
            <?php settings_fields( 'sexhackme-rclone-settings' ); ?>
            <table class="form-table">
               <tr>
                  <td>
                     <label>rclone executable path</label>
                     <input type='text' name='sexhack_rclone_path' value='<?php echo $sh_rclone->get_path(); ?>'>
                  </td>
               </tr>
					<tr><td><h4>Google Drive Support:</h4></td></tr>
               <tr>
                  <td>
                     <label>Select Google Drive remote if any:</label>
							<select name="sexhack_rclone_gdrive_name">
								<option value="0">Choose...</option>
								<?php 
									foreach($sh_rclone->get_remotes() as $remote)
									{
										$selected="";
										if($remote == get_option('sexhack_rclone_gdrive_name', false)) $selected="selected";
										if($remote) echo "<option value='".$remote."' $selected>".$remote."</option>";
									}
								?>
							</select>
							<label> Use shared with me?</label>
							<input type="checkbox" name="sexhack_rclone_gdrive_shared" value='1' <?php if(get_option('sexhack_rclone_gdrive_shared', false)) echo "checked"; ?>>
                  </td>
               </tr>

            </table>
            <?php submit_button(); ?>
            </form>
   </div>

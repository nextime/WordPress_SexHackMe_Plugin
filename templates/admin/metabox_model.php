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
   <script type="text/javascript">
   window.guestChange = function(trig)
   {
      if(trig.value > 0)
      {
         var newsel = $('.guest_selection').clone();
         newsel.insertAfter($('.guest_list p').last());
         newsel.show()
         newsel.removeClass('guest_selection');
      } else {
			$('.guest_list p').last().remove();
	   }
   }

   </script>
   <div class="wrap">
         <table class="form-table">
            <tr align="top">
               <td>
                  <p><label>Select Model user</label></p>
                  <?php // XXX When this will be with thousands of model will definely not scale! ?>
                  <select name='video_model'>
                  <?php
                     $models = get_users( array( 'role__in' => array( 'model' ) ) );
                     foreach($models as $user)
                     {
                        echo "<option value='".$user->ID."' ";
                        if($video->user_id==$user->ID) echo "selected";
                        echo '>'.$user->user_login." (id:".$user->ID.")</option>";
                     } ?>
                  </select>
               </td>
            </tr>
            <tr align="top">
               <td class='guest_list'>
						<p style="display:none" class="guest_selection">
							<select name='vguests[]' onchange='javascript:guestChange(this);'>
								<option value="0">NO GUEST</option>
								<?php
								foreach($models as $user)
								{
									echo "<option value='".$user->ID."' ";
									echo '>'.$user->user_login." (id:".$user->ID.")</option>";
								}
								?>
							</select>
						</p>
						<p>
                  	<p><label>Add guest model</label></p>
                  	<?php // XXX When this will be with thousands of model will definely not scale! ?>
                  	<select name='vguests[]' onchange='javascript:guestChange(this);'>
								<option value="0">NO GUEST</option>
                  		<?php
                     	foreach($models as $user)
                     	{
                        	echo "<option value='".$user->ID."' ";
                        	echo '>'.$user->user_login." (id:".$user->ID.")</option>";
                     	} ?>
                  	</select>
						</p>
						<?php 
							foreach($video->get_guests(true) as $uid => $guest)
							{
								?>
						<p>
							<select name='vguests[]' onchange='javascript:guestChange(this);'>
								<option value="0">NO GUEST</option>
								<?php
								foreach($models as $user)
								{
									echo "<option value='".$user->ID."' ";
									if($uid==$user->ID) echo "selected";
									echo '>'.$user->user_login." (id:".$user->ID.")</option>";
								} ?>
							</select>
						</p>
								<?php
							}
						?>
               </td>
            </tr>

         </table>
   </div>


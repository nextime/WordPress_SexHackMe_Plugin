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

if(!class_exists('SH_WooCommerce_Registration_Integration')) {


   class SH_WooCommerce_Registration_Integration2
   {
		public function gen_random_pwd() 
      {
         sh_genpass_register_form();
		}
   }
}


function wcpms_adminpage()
{
	$plans = sh_get_subscription_plans();
	sexhack_log($plans);

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
						foreach(get_wc_subscription_products_priced($plan->price, $plan->id) as $prod)
						{
							
						?>
							<option value='<?php echo $prod->id; ?>' <?php if($opt == $prod->id) echo "selected"; ?> >
								<?php echo $prod->get_title() ?> (<?php echo $prod->id; ?>)
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
         <?php submit_button(); ?>
         </form>
   </div>
<?php

}

function settings_wcpms_section()
{
	echo "<h2>SexHackMe PMS - WooCommerce integration Settings</h2>";
}

function wcpms_initialize_options() 
{
	$plans = pms_get_subscription_plans();
	add_settings_section('sexhackme-wcpms-settings', ' ', 'wp_SexHackMe\settings_wcpms_section', 'sexhackme-wcpms-settings');
   register_setting('sexhackme-wcpms-settings', 'sexhack-wcpms-checkout');
	foreach($plans as $plan)
	{
		if($plan->price > 0)
		{
			register_setting('sexhackme-wcpms-settings', 'sexhack-wcpms-'.strval($plan->id));
		}
	}
}

add_action('admin_init', 'wp_SexHackMe\wcpms_initialize_options');

$SEXHACK_SECTION = array(
   'class' => 'SH_WooCommerce_Registration_Integration2', 
   'description' => 'Integrate woocommerce account page and sexhack modified registration form on pms to send password change link by email', 
   'name' => 'sexhackme_pmswooregistration',
   'require-page' => array(
                        array('post_type' => 'page', 'title' => 'Set password mail page', 'option' => 'sexhack_registration_mail_endpoint')
                     ),
   'adminmenu' => array(
                     array('title' => 'WC-PMS Integration',
                           'slug' => 'wcpms-integration',
                           'callback' => 'wp_SexHackMe\wcpms_adminpage')
                     ),

   'slugs' => array('account', 'register', 'login', 'password-reset')
);

?>

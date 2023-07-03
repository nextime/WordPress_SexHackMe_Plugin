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

function pms_register_form_after_create_user($user_data)
{
   do_action('sh_register_form_after_create_user', $user_data);
}
add_action('pms_register_form_after_create_user', 'wp_SexHackMe\pms_register_form_after_create_user');


// XXX In the docs of PMS they indicate to use add_action but they use a filter... uhmmm
//add_action('pms_get_redirect_url', 'pms_get_redirect_url');
add_filter('pms_get_redirect_url', 'wp_SexHackMe\pms_get_redirect_url', 100, 2);
function pms_get_redirect_url($url, $location=false)
{
   if( !isset( $_POST['pay_gate'] ) || $_POST['pay_gate'] != 'manual' )
      return $url;
   // XXX BUG apply_filter ont found??
   return apply_filter('sh_get_redirect_url', $url, $location);
}

?>

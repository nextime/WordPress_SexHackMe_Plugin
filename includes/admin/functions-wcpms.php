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


function wcpms_adminpage()
{  
   sh_get_template('admin/wcpms.php');
}

function settings_wcpms_section()
{
   echo "<h2>SexHackMe PMS - WooCommerce integration Settings</h2>";
}

function settings_wcpms_section_email()
{
   echo "<h2>WooCommerce Checkout Email endpoint</h2>";
}

function settings_wcpms_section_prodcat()
{
   echo "<h2>WooCommerce auto-roducts for Videos</h2>";
}

?>

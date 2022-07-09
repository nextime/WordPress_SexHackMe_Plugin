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


class SexHackPMSHelper
{

	public function __construct()
	{
		$this->plans = $this->get_pms_plantype();
	}

	public function get_pms_plantype()
	{
    	$plans = array(
       	'member' => array(),
       	'premium'=> array()
    	);
    	$splans=pms_get_subscription_plans(true);
    	foreach($splans as $splan)
    	{
       	if(intval($splan->price)==0) $plans['member'][] = $splan->id;
       	else $plans['premium'][] = $splan->id;
    	}
	 	return $plans;
	}

	public function is_member($uid='')
	{
		return pms_is_member( $uid, $this->plans['member'] );
	}

	public function is_premium($uid='')
	{
		return pms_is_member( $uid, $this->plans['premium'] );
	}
}


function instancePMSHelper() {
	$GLOBALS['sexhack_pms'] = new SexHackPMSHelper();
}


add_action('wp', 'wp_SexHackMe\instancePMSHelper');

?>

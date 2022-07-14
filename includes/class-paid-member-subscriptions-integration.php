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

class SexHackPMSHelper
{

	public function __construct()
	{
      $this->plans = false;
	}

	private function set_pms_plans()
	{
    	$plans = array(
       	'member' => array(),
         'premium'=> array(),
         'byid' => array()
      );
      
    	$splans=pms_get_subscription_plans(true);
    	foreach($splans as $splan)
    	{
       	if(intval($splan->price)==0) $plans['member'][] = $splan->id;
         else $plans['premium'][] = $splan->id;

         $plans['byid'][$splan->id] = $splan;
      }
      $this->plans = $plans;
	 	return $plans;
	}


   public function refresh_plans()
   {
      $this->plans = set_pms_plans();
      return $this->plans;
   }


   // XXX Here we just return the first "member" (free) plan
   //     if any in our array.
   //
   //     I should probably make it configurable with an option?
   //     And should not be limited to the free ones?
   public function get_default_plan()
   {
      if(!$this->plans) $this->set_pms_plans();
      if(count($this->plans['member']) > 0)
      {
         return $this->plans['byid'][$this->plans['member'][0]];
      }
      return false;
   }

   public function get_member_plans()
   {
      if(!$this->plans) $this->set_pms_plans(); 
      return $this->plans['member'];
   }

   public function get_premium_plans()
   {
      if(!$this->plans) $this->set_pms_plans();
      return $this->plans['premium'];
   }

   public function get_plans($pid=false)
   {
      if(!$this->plans) $this->set_pms_plans();
      if($pid)
      { 
         if(array_key_exists($pid, $this->plans['byid'])) return $this->plans['byid'][$pid];
         return false;
      }
      return $this->plans['byid'];
   }


	public function is_member($uid='')
	{
		return pms_is_member( $uid, $this->get_member_plans() );
	}

	public function is_premium($uid='')
	{
		return pms_is_member( $uid, $this->get_premium_plans() );
	}
}


function instancePMSHelper() {
	$GLOBALS['sexhack_pms'] = new SexHackPMSHelper();
}


add_action('wp', 'wp_SexHackMe\instancePMSHelper');

?>

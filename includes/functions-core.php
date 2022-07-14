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

function sh_get_template($tmpl, $args=array())
{  
   foreach($args as $var => $data) $$var = $data; 
   if(file_exists(SH_PLUGIN_DIR_PATH . 'templates/' . $tmpl)) 
      return include_once SH_PLUGIN_DIR_PATH . 'templates/' . $tmpl;
   return false;
}


function sh_disclaimer()
{
    echo sh_get_template("blocks/disclaimer.php");
}

function sh_hls_player($video_url, $posters='')
{
    echo SH_VideoPlayer::addPlayer('hls', $video_url, $posters);
}

function sh_xr_player($video_url, $posters='', $projection='180_LR')
{
    echo SH_VideoPlayer::addPlayer('xr', $video_url, $posters, $projection);
}

function sh_fix_user_with_no_plan($userid)
{

    global $sexhack_pms;

    if(!($sexhack_pms->is_member($user->ID)) && !($sexhack_pms->is_premium($user->ID)))
    {
       $subscription_plan = $sexhack_pms->get_default_plan();
       if($subscription_plan)
       {
           $data = array(
               'user_id'              => $userid,
               'subscription_plan_id' => $subscription_plan->id,
               'start_date'           => date( 'Y-m-d H:i:s' ),
               'expiration_date'      => $subscription_plan->get_expiration_date(),
               'status'               => 'active',


            );
            $member_subscription = new \PMS_Member_Subscription();
            $inserted            = $member_subscription->insert( $data );
        }
    }
}


function user_has_premium_access($uid='')
{
   global $sexhack_pms;

   return $sexhack_pms->is_premium($uid) AND is_user_logged_in();
}

function user_is_premium($uid='')
{
   global $sexhack_pms;

   return $sexhack_pms->is_premium($uid);
}

function user_has_member_access($uid='')
{
   global $sexhack_pms;

   if($uid) return $sexhack_pms->is_member($uid) OR $sexhack_pms->is_premium($uid);
   return is_user_logged_in();

}

function user_is_member($uid='')
{
   global $sexhack_pms;

   return $sexhack_pms->is_member($uid);
}



?>

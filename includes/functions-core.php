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


function sh_save_video($video)
{
   return SH_Query::save_Video($video);
}

function sh_get_videos($vcat=false)
{
   return SH_Query::get_Videos($vcat);
}

function sh_get_video($id)
{
   return SH_Query::get_Video($id);
}

function sh_get_video_from_post($p)
{
   if(is_int($p) && $p > 0) return SH_Query::get_Video($p, 'post');
   else if(is_object($p)) return SH_Query::get_Video($p->ID, 'post');
   return false;
}

function sh_get_video_from_product($p)
{
   if(is_int($p) and $p > 0) return SH_Query::get_Video($p, 'product');
   else if(is_object($p)) return SH_Query::get_Video($p->get_id(), 'product');
   return false;
}

function sh_get_subscription_plans()
{
   return pms_get_subscription_plans();
}

function sh_disclaimer()
{
    echo sh_get_template("blocks/disclaimer.php");
}

function sh_account_subscription_content()
{
    echo '<h3>Subscriptions</h3>';
    echo do_shortcode( '[pms-account show_tabs="no"]' );
    echo "<h3>Payment History:</h3>";
    echo do_shortcode( '[pms-payment-history]');
}

function sh_genpass_register_form()
{
     // Check nonce
     if ( !isset( $_POST['pmstkn'] ) || !wp_verify_nonce( sanitize_text_field( $_POST['pmstkn'] ), 'pms_register_form_nonce') )
          return;

     $pwd = wp_generate_password();
     $_POST['pass1'] = $pwd;
     $_POST['pass2'] = $pwd;

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

function sh_get_subscription_plan($plans)
{
   return pms_get_subscription_plan($plans);
}


function user_has_premium_access($uid='')
{
   global $sexhack_pms;

   if(!isset($sexhack_pms)) return false;
   return $sexhack_pms->is_premium($uid) AND is_user_logged_in();
}

function user_is_premium($uid='')
{
   global $sexhack_pms;

   if(!isset($sexhack_pms)) return false; 
   return $sexhack_pms->is_premium($uid);
}

function user_has_member_access($uid='')
{
   global $sexhack_pms;

   if(!isset($sexhack_pms)) return false; 
   if($uid) return $sexhack_pms->is_member($uid) OR $sexhack_pms->is_premium($uid);
   return is_user_logged_in();

}

function user_is_member($uid='')
{
   global $sexhack_pms;

   if(!isset($sexhack_pms)) return false; 
   return $sexhack_pms->is_member($uid);
}



?>

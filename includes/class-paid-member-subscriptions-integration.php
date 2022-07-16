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

if(!class_exists('SH_PMS_Support')) {
   class SH_PMS_Support
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

   function instance_SH_PMS_Support() {
      // add $sh_pms global object
      $GLOBALS['sh_pms'] = new SH_PMS_Support();

      // backward compatibility
      $GLOBALS['sexhack_pms'] = $GLOBALS['sh_pms'];

      // Do action after instancing the global var to notify is reay
      do_action('sh_pms_ready');
   }

   // Create the sh_pms object
   add_action('wp', 'wp_SexHackMe\instance_SH_PMS_Support');
}

if(!class_exists('SexhackPmsPasswordDataLeak')) {
   class SexhackPmsPasswordDataLeak
   {
      public function __construct()
      {
         add_filter( 'pms_recover_password_message', array($this, "change_recover_form_message") );
         add_action( 'init', array($this, 'reset_password_form'), 9);
         add_action( 'login_form_rp', array( $this, 'redirect_password_reset' ) );
         add_action( 'login_form_resetpass', array( $this, 'redirect_password_reset' ) );
      }

      public function change_recover_form_message($string)
      {
         // XXX This should be in a template file as a full substitute
         return str_replace("<br/>", "<br/>If valid, ", $string);
      }

      public function redirect_password_reset() 
      {
         // XXX This should be configurable.
         wp_redirect( home_url( 'password-reset' ) );
      }

      public function reset_password_form() 
      {

         /*
         * Username or Email
         */
         $error=false;
         if( isset( $_POST['pms_username_email'] ) ) {

            //Check recover password form nonce;
            if( !isset( $_POST['pmstkn'] ) || ( !wp_verify_nonce( sanitize_text_field( $_POST['pmstkn'] ), 'pms_recover_password_form_nonce') ) )
                return;

            if( is_email( $_POST['pms_username_email'] ) )
                $username_email = sanitize_email( $_POST['pms_username_email'] );
            else
                $username_email = sanitize_text_field( $_POST['pms_username_email'] );



            if( empty( $username_email ) )
                pms_errors()->add( 'pms_username_email', __( 'Please enter a username or email address.', 'paid-member-subscriptions' ) );
            else {

                $user = '';
                // verify if it's a username and a valid one
                if ( !is_email($username_email) ) {
                    if ( username_exists($username_email) ) {
                        $user = get_user_by('login',$username_email);
                    }
                        else $error=true; 
                }

                //verify if it's a valid email
                if ( is_email( $username_email ) ){
                    if ( email_exists($username_email) ) {
                        $user = get_user_by('email', $username_email);
                    }
                    else $error=true;  
                }
            }

            // Extra validation
            do_action( 'pms_recover_password_form_validation' );

            //If entered username or email is valid (no errors), email the password reset confirmation link
            if ( count( pms_errors()->get_error_codes() ) == 0 && !$error) {

                // XXX this option?
                $mailpage = get_option('sexhack_registration_mail_endpoint', false);
                if($mailpage) {
                   $page = get_page($mailpage);
                   $mailpage = $page->post_name;
                }
                send_changepwd_mail($user, $mailpage);



             }
            } // isset($_POST[pms_username_email])
           unset($_POST['pms_username_email']);
      }
   }


   // Let's create the Fixes
   new SexhackPmsPasswordDataLeak;
}

?>

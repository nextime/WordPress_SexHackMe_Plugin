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

if(!class_exists('SexhackPmsPasswordDataLeak')) {
   class SexhackPmsPasswordDataLeak
   {
      public function __construct()
      {
         sexhack_log('SexhackPmsPasswordDataLeak() Instanced');
         add_filter( 'pms_recover_password_message', array($this, "change_recover_form_message") );
         add_action( 'init', array($this, 'reset_password_form'), 9);
			add_action( 'login_form_rp', array( $this, 'redirect_password_reset' ) );
			add_action( 'login_form_resetpass', array( $this, 'redirect_password_reset' ) );
      }

      public function change_recover_form_message($string)
      {
         return str_replace("<br/>", "<br/>If valid, ", $string);
      }

		public function redirect_password_reset() 
		{
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
                        else $error=true; //pms_errors()->add('pms_username_email',__( 'The entered username doesn\'t exist. Please try again.', 'paid-member-subscriptions'));
                }

                //verify if it's a valid email
                if ( is_email( $username_email ) ){
                    if ( email_exists($username_email) ) {
                        $user = get_user_by('email', $username_email);
                    }
                    else $error=true;    //pms_errors()->add('pms_username_email',__( 'The entered email wasn\'t found in our database. Please try again.', 'paid-member-subscriptions'));
                }
			   }

            // Extra validation
            do_action( 'pms_recover_password_form_validation' );

            //If entered username or email is valid (no errors), email the password reset confirmation link
            if ( count( pms_errors()->get_error_codes() ) == 0 && !$error) {

                $mailpage = get_option('sexhack_registration_mail_endpoint', false);
                if($mailpage) {
                   $page = get_page($mailpage);
                   $mailpage = $page->post_name;
                }
                send_changepwd_mail($user, $mailpage);
                /* 
                if (is_object($user)) {  //user data is set
                    $requestedUserID = $user->ID;
                    $requestedUserLogin = $user->user_login;
                    $requestedUserEmail = $user->user_email;

                    //search if there is already an activation key present, if not create one
                    $key = pms_retrieve_activation_key( $requestedUserLogin );

                    //Confirmation link email content
                    $recoveruserMailMessage1 = sprintf(__('Someone has just requested a password reset for the following account: <b>%1$s</b><br/><br/>If this was a mistake, just ignore this email and nothing will happen.<br/>To reset your password, visit the following link: %2$s', 'paid-member-subscriptions'), $username_email, '<a href="' . esc_url(add_query_arg(array('loginName' => urlencode( $requestedUserLogin ), 'key' => $key), pms_get_current_page_url())) . '">' . esc_url(add_query_arg(array('loginName' => urlencode( $requestedUserLogin ), 'key' => $key), pms_get_current_page_url())) . '</a>');
                    $recoveruserMailMessage1 = apply_filters('pms_recover_password_message_content_sent_to_user1', $recoveruserMailMessage1, $requestedUserID, $requestedUserLogin, $requestedUserEmail);

                    //Confirmation link email title
                    $recoveruserMailMessageTitle1 = sprintf(__('Password Reset from "%s"', 'paid-member-subscriptions'), $blogname = wp_specialchars_decode(get_option('blogname'), ENT_QUOTES));
                    $recoveruserMailMessageTitle1 = apply_filters('pms_recover_password_message_title_sent_to_user1', $recoveruserMailMessageTitle1, $requestedUserLogin);

                    //we add this filter to enable html encoding
                    add_filter('wp_mail_content_type', function () { return 'text/html'; } );

                    // Temporary change the from name and from email
                    add_filter( 'wp_mail_from_name', array( 'PMS_Emails', 'pms_email_website_name' ), 20, 1 );
                    add_filter( 'wp_mail_from', array( 'PMS_Emails', 'pms_email_website_email' ), 20, 1 );

                    //send mail to the user notifying him of the reset request
                    if (trim($recoveruserMailMessageTitle1) != '') {
                        $sent = wp_mail($requestedUserEmail, $recoveruserMailMessageTitle1, $recoveruserMailMessage1);
                        if ($sent === false)
                            pms_errors()->add('pms_username_email',__( 'There was an error while trying to send the activation link.', 'paid-member-subscriptions'));
                    }

                    // Reset the from name and email
                    remove_filter( 'wp_mail_from_name', array( 'PMS_Emails', 'pms_email_website_name' ), 20 );
                    remove_filter( 'wp_mail_from', array( 'PMS_Emails', 'pms_email_website_email' ), 20 );

                    // add option to store all user $id => $key and timestamp values that reset their passwords every 24 hours
                    if ( false === ( $activation_keys = get_option( 'pms_recover_password_activation_keys' ) ) ) {
                        $activation_keys = array();
                    }
                    $activation_keys[$user->ID]['key'] = $key;
                    $activation_keys[$user->ID]['time'] = time();

                    update_option( 'pms_recover_password_activation_keys', $activation_keys );

                    if( $sent === true )
                        do_action( 'pms_password_reset_email_sent', $user, $key );
                } */



             }
     		 } // isset($_POST[pms_username_email])
	 		 unset($_POST['pms_username_email']);
		}
   }
}

$SEXHACK_SECTION = array('class' => 'SexhackPmsPasswordDataLeak', 
	'description' => 'Fix Pay Member Subscription password-reset data leak', 
	'name' => 'sexhackme_pms_resetfix',
   'require-page' => array(
                           array('post_type' => 'page', 'title' => 'Reset password page', 'option' => 'sexhack_reset_pwd_fix')
                        )
);

?>

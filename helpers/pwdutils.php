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

function send_changepwd_mail($user_login, $baseurl=false){
	
    global $wpdb, $wp_hasher;
    if(!is_object($user_login)) {
      $user_login = sanitize_text_field($user_login);
      if ( empty( $user_login) ) {
        return false;
      } else if ( strpos( $user_login, '@' ) ) {
        $user_data = get_user_by( 'email', trim( $user_login ) );
        if ( empty( $user_data ) )
           return false;
      } else {
        $login = trim($user_login);
        $user_data = get_user_by('login', $login);
      }
    }
    
    do_action('lostpassword_post');
	
    if ( !$user_data ) return false;
    if ( !is_object($user_data) ) return false;

    // redefining user_login ensures we return the right case in the email
    $user_login = $user_data->user_login;
    $user_email = $user_data->user_email;
    do_action('retreive_password', $user_login);  // Misspelled and deprecated
    do_action('retrieve_password', $user_login);
    $allow = apply_filters('allow_password_reset', true, $user_data->ID);
    if ( ! $allow )
        return false;
    else if ( is_wp_error($allow) )
        return false;

	 $key = pms_retrieve_activation_key( $user_login );
	 //$key = get_password_reset_key( $user_data );
    do_action( 'retrieve_password_key', $user_login, $key );

    if ( empty( $wp_hasher ) ) {
        require_once ABSPATH . 'wp-includes/class-phpass.php';
        $wp_hasher = new PasswordHash( 8, true );
    }
    //$hashed = $wp_hasher->HashPassword( $key );    
    //$wpdb->update( $wpdb->users, array( 'user_activation_key' => time().":".$hashed ), array( 'user_login' => $user_login ) );
    $message = __('Someone requested that the password be reset for the following account:') . "\r\n\r\n";
    $message .= network_home_url( '/' ) . "\r\n\r\n";
    $message .= sprintf(__('Username: %s'), $user_login) . "\r\n\r\n";
    $message .= __('If this was a mistake, just ignore this email and nothing will happen.') . "\r\n\r\n";
    $message .= __('To reset your password, visit the following address:') . "\r\n\r\n";

    // XXX This is an hardcoded default. Do I really like it that way?
    if(!$baseurl) $baseurl='password-reset';
    $message .= '<' . network_site_url("/$baseurl/?key=$key&loginName=" . rawurlencode($user_login), 'login') . ">\r\n";
    //$message .= '<' . network_site_url("wp-login.php?action=rp&key=$key&login=" . rawurlencode($user_login), 'login') . ">\r\n";

    
    // XXX Should we send it with html for the link or can we assume links are ok with mail clients? verify please!
    //add_filter('wp_mail_content_type', function () { return 'text/html'; } );

    // Temporary change the from name and from email
    // XXX Require PMS! do we want it? Should we change with our own for sexhack?
    add_filter( 'wp_mail_from_name', array( 'PMS_Emails', 'pms_email_website_name' ), 20, 1 );
    add_filter( 'wp_mail_from', array( 'PMS_Emails', 'pms_email_website_email' ), 20, 1 );

    if ( is_multisite() )
        $blogname = $GLOBALS['current_site']->site_name;
    else
        $blogname = wp_specialchars_decode(get_option('blogname'), ENT_QUOTES);

    $title = sprintf( __('[%s] Password Reset'), $blogname );

    $title = apply_filters('retrieve_password_title', $title);
    $message = apply_filters('retrieve_password_message', $message, $key);


    // add option to store all user $id => $key and timestamp values that reset their passwords every 24 hours
    // XXX Require PMS, shouldn't we use normal wordpress activations keys? See commented parts on user_activation_key here
    if ( false === ( $activation_keys = get_option( 'pms_recover_password_activation_keys' ) ) ) {
         $activation_keys = array();
    }
    $activation_keys[$user_data->ID]['key'] = $key;
    $activation_keys[$user_data->ID]['time'] = time();
    update_option( 'pms_recover_password_activation_keys', $activation_keys );

    if ( $message && !wp_mail($user_email, $title, $message) )
        wp_die( __('The e-mail could not be sent.') . "<br />\n" . __('Possible reason: your host may have disabled the mail() function...') );

	
}

?>

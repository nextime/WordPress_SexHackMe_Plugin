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


if(!function_exists('sexhack_log')){
  function sexhack_log( $message, $dumps=false) {
    if( WP_DEBUG === true ){
      if( is_array( $message ) || is_object( $message ) ){
        error_log( "SexHackMe: ".print_r( $message, true ) );
      } else {
        if($dumps) error_log( "SexHackMe: ".$message." ".str_replace("\n", "", print_r($dumps, TRUE)) );
        else error_log( "SexHackMe: ".$message );
      }
    }
  }
}


function sanitize_idtype($idt=false)
{
   if((!$idt) || ($idt=='')) $idt='id';

   switch($idt)
   {
      case 'post':
      case 'product':
      case 'cat':
      case 'video':
      case 'user':
      case 'tag':
         return $idt."_id";
         break;
      case 'id':
      case 'slug':
         return $idt;
         break;
      default:
         return false;
   }
} 

function debug_rewrite_rules($matchonly=false) 
{
   $matchonly=true;
   global $wp_rewrite, $wp, $template;
   $i=1;
   if (!empty($wp_rewrite->rules)) {
      foreach($wp_rewrite->rules as $name => $value) {
         if($name==$wp->matched_rule) {
            sexhack_log("MATCHED REWRITE RULE $i!!! NAME: ".$name." , VALUE: ".$value." , REQUEST: ".$wp->request." , MATCHED: ".$wp->matched_query." , TEMPLATE:".$template);
         } else {
            if(!$matchonly) 
               sexhack_log("REWRITE $i: $name -> $value ");
         }
         $i++;
      }
   }
}


function starts_with ($startString, $string)
{
    $len = strlen($startString);
    return (substr($string, 0, $len) === $startString);
}

function dump_rewrite( &$wp ) {
    global $wp_rewrite;

    ini_set( 'error_reporting', -1 );
    ini_set( 'display_errors', 'On' );
    echo '<h2>rewrite rules</h2>';
    echo var_export( $wp_rewrite->wp_rewrite_rules(), true );

    echo '<h2>permalink structure</h2>';
    echo var_export( $wp_rewrite->permalink_structure, true );

    echo '<h2>page permastruct</h2>';
    echo var_export( $wp_rewrite->get_page_permastruct(), true );

    echo '<h2>matched rule and query</h2>';
    echo var_export( $wp->matched_rule, true );

    echo '<h2>matched query</h2>';
    echo var_export( $wp->matched_query, true );

    echo '<h2>request</h2>';
    echo var_export( $wp->request, true );

    global $wp_the_query;
    echo '<h2>the query</h2>';
    echo var_export( $wp_the_query, true );
}

function do_dump_rewrite() {
   add_action( 'parse_request', 'wp_SexHackMe\sarca' );
}


function get_proto(){
    if(is_ssl()) {
        return 'https://';
    } else {
        return 'http://';
    }
}


function send_changepwd_mail($user_login, $baseurl=false){
   
    global $wpdb; //, $wp_hasher;
    if(!is_object($user_login)) {
      $user_login = sanitize_text_field($user_login);
      if ( empty( $user_login) ) {
         sexhack_log("EMPTY LOGIN");
        return false;
      } else if ( strpos( $user_login, '@' ) ) {
        $user_data = get_user_by( 'email', trim( $user_login ) );
        if ( empty( $user_data ) )
        {
           sexhack_log("EMPTY USER DATA");
           return false;
        }
      } else {
        $login = trim($user_login);
        $user_data = get_user_by('login', $login);
      }
    }


    do_action('lostpassword_post');
   
    if ( !isset($user_data) ) return false;
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



    $genkey = pms_retrieve_activation_key( $user_login );
    do_action( 'retrieve_password_key', $user_login, $genkey );
    $key = get_password_reset_key( $user_data );

    //if ( empty( $wp_hasher ) ) {
    //    require_once ABSPATH . 'wp-includes/class-phpass.php';
    //    $wp_hasher = new \PasswordHash( 8, true );
    //}
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
    sexhack_log("SENT EMAIL TO ".$user_email);

   
}

function sexhack_getURL($url)
{
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch,CURLOPT_USERAGENT,'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/83.0.4103.61 Safari/537.36');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    $out = curl_exec($ch);
    curl_close($ch);
    return $out;
}


function trim_text_preview($text, $len=340, $fill=false)
{
   $min="10";
   if($len < $min) $len=$min;
   if (strlen($text) > $len)
   {
       $offset = ($len - 3) - strlen($text);
       $text = substr($text, 0, strrpos($text, ' ', $offset)) . '...';
   }  
   if($fill)
   {
      $start=strlen($text);
      while($start < $len+1) {
         $start++;
         $text .= "&nbsp";
      }
   }
   return $text;
}

function check_url_or_path($url)
{
   if (strncmp($url, "/", 1) === 0)
      return 'path';
   else if(strncmp($url, 'gdrive://', 9) === 0)
      return 'gdrive';
   else if(filter_var($url, FILTER_VALIDATE_URL))
      return 'uri';

   return false;
}

function user_has_role($user_id, $role_name)
{
    $user_meta = get_userdata($user_id);
    $user_roles = $user_meta->roles;
    return in_array($role_name, $user_roles);
}

function uniqidReal($lenght = 13) {
    // uniqid gives 13 chars, but you could adjust it to your needs.
    if (function_exists("random_bytes")) {
        $bytes = random_bytes(ceil($lenght / 2));
    } elseif (function_exists("openssl_random_pseudo_bytes")) {
        $bytes = openssl_random_pseudo_bytes(ceil($lenght / 2));
    } else {
        throw new Exception("no cryptographically secure random function available");
    }
    return substr(bin2hex($bytes), 0, $lenght);
}

function html2text($html)
{
    // remove comments and any content found in the the comment area (strip_tags only removes the actual tags).
    $plaintext = preg_replace('#<!--.*?-->#s', '', $html);

    // put a space between list items (strip_tags just removes the tags).
    $plaintext = preg_replace('#</li>#', ' </li>', $plaintext);

    // remove all script and style tags
    $plaintext = preg_replace('#<(script|style)\b[^>]*>(.*?)</(script|style)>#is', "", $plaintext);

    // remove br tags (missed by strip_tags)
    $plaintext = preg_replace('#<br[^>]*?>#', " ", $plaintext);

    // remove all remaining html
    $plaintext = strip_tags($plaintext);

    return $plaintext;
}

function checkbox($res)
{
     if($res=="1") return "checked";
}


?>

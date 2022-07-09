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

?>

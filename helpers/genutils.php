<?php

namespace wp_SexHackMe;

function debug_rewrite_rules($matchonly=false) 
{
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

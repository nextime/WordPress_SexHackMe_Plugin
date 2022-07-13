<?php
/* add new tab called "mytab" */

namespace wp_SexHackMe;


//add_filter('um_account_page_default_tabs_hook', 'wp_SexHackMe\my_custom_tab_in_um', 100 );
function my_custom_tab_in_um( $tabs ) {
	$tabs[800]['mytab']['icon'] = 'um-faicon-pencil';
	$tabs[800]['mytab']['title'] = 'My Custom Tab';
	$tabs[800]['mytab']['custom'] = true;
	return $tabs;
}
	
/* make our new tab hookable */

add_action('um_account_tab__mytab', 'wp_SexHackMe\um_account_tab__mytab');
function um_account_tab__mytab( $info ) {
	global $ultimatemember;
	extract( $info );

	$output = $ultimatemember->account->get_tab_output('mytab');
	if ( $output ) { echo $output; }
}

/* Finally we add some content in the tab */

add_filter('um_account_content_hook_mytab', 'wp_SexHackMe\um_account_content_hook_mytab');
function um_account_content_hook_mytab( $output ){
	ob_start();
	?>
		
	<div class="um-field">
		
		<!-- Here goes your custom content -->
		
	</div>		
		
	<?php
		
	$output .= ob_get_contents();
	ob_end_clean();
	return $output;
}




// You could set the default privacy for custom tab and disable to change the tab privacy settings in admin menu.
/*
* There are values for 'default_privacy' atribute
* 0 - Anyone,
* 1 - Guests only,
* 2 - Members only,
* 3 - Only the owner
*/
// Filter
function um_mycustomtab_add_tab( $tabs ) {
	$tabs['mycustomtab'] = array(
		'name' 				=> 'My Custom Antani',
		'icon' 				=> 'um-faicon-pencil',
		//'default_privacy'   => 0,
	);
	return $tabs;
}
//add_filter( 'um_profile_tabs', 'wp_SexHackMe\um_mycustomtab_add_tab', 10000 );

/**
 * Check an ability to view tab
 *
 * @param $tabs
 *
 * @return mixed
 */
function um_mycustomtab_add_tab_visibility( $tabs ) {
	if ( empty( $tabs['mycustomtab'] ) ) {
		return $tabs;
	}

	$user_id = um_profile_id();

	//if ( ! user_can( $user_id, '{here some capability which you need to check}' ) ) {
	//	unset( $tabs['mycustomtab'] );
	//}

	return $tabs;
}
add_filter( 'um_user_profile_tabs', 'wp_SexHackMe\um_mycustomtab_add_tab_visibility', 2000, 1 );

// Action
function um_profile_content_mycustomtab_default( $args ) {
	echo 'Hello world!';
}
add_action( 'um_profile_content_mycustomtab_default', 'wp_SexHackMe\um_profile_content_mycustomtab_default' );



?>

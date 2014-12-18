<?php
/**
 * Template Name: Discourse SSO
 * Author: PrimeTime Code (etcio, nphaskins)
 * Version: 0.1
 * Author URI: https://primetimecode.com
 *
 */

$sso = WP_Discourse_SSO::get_instance();
if ( ! $sso->configured ) {

	// Error message
	echo( 'This plugin has not been configured yet.' );

	// Terminate
	exit;

}

if ( ! isset( $_GET['sso'] ) ) {

	// Error message
	echo( 'Invalid request. Missing SSO token.' );

	// Terminate
	exit;
}

// Not logged in to WordPress, redirect to WordPress login page with redirect back to here
if ( ! is_user_logged_in() ) {

	// Preserve sso and sig parameters
	$redirect = add_query_arg( NULL, NULL );
	
	// Change %0A to %0B so it's not stripped out in wp_sanitize_redirect
	$redirect = str_replace( '%0A', '%0B', $redirect );

	// Build login URL
	$login = wp_login_url( $redirect );

	// Redirect to login
	wp_redirect( $login );
	exit;

}

// Logged in to WordPress, now try to log in to Discourse with WordPress user information
else {

	// Payload and signature
	if ( isset($_GET['sso'] ) ) {
		$payload = $_GET['sso'];
	}

	if ( isset($_GET['sig'] ) ) {
		$sig = $_GET['sig'];
	}

	// Change %0B back to %0A
	$payload = urldecode( str_replace( '%0B', '%0A', urlencode( $payload ) ) );

	if ( ! ( $sso->validate( $payload, $sig ) ) ) {
		
		// Error message
		echo( 'Invalid request.' );
		
		// Terminate
		exit;

	}

	// Nonce    
	$nonce = $sso->getNonce( $payload );

	// Current user info
	get_currentuserinfo();

	// Map information
	$params = array(
		'nonce' => $nonce,
		'name' => $current_user->display_name,
		'username' => $current_user->user_login,
		'email' => $current_user->user_email,
		'about_me' => $current_user->description,
		'external_id' => $current_user->ID
	);

	// Build login string
	$q = $sso->buildLoginString( $params );

	// Redirect back to Discourse
	wp_redirect( $sso->discourse_url . '/session/sso_login?' . $q );
	exit;

}
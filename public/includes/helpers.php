<?php

/**
*
*	Grab an optoin from our settings
* 	example $secret_key = pt_wp_sso_get_option('secret_key','pt_wp_sso_settings');
*
*	@param $option string name of the option
*	@param $section string name of the section
*	@param $default string/int default option value
*	@return the option value
*	@since 1.0
*/
function pt_wp_sso_get_option( $option, $section, $default = '' ) {

	if ( empty( $option ) )
		return;

    $options = get_option( $section );


    if ( isset( $options[$option] ) ) {
        return $options[$option];
    }

    return $default;
}
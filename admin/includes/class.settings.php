<?php
/**
* creates setting tabs
*
* @since version 1.0
* @param null
* @return global settings
*/

require_once dirname( __FILE__ ) . '/class.settings-api.php';

if ( !class_exists('pt_wp_discourse_sso_settings_api_wrap' ) ):
class pt_wp_discourse_sso_settings_api_wrap {

    private $settings_api;

    const version = '1.0';

    function __construct() {

        $this->dir  		= plugin_dir_path( __FILE__ );
        $this->url  		= plugins_url( '', __FILE__ );
        $this->settings_api = new WeDevs_Settings_API;

        add_action( 'admin_init', 					array($this, 'admin_init') );
        add_action( 'admin_menu', 					array($this,'sub_menu_page'));

    }

    function admin_init() {

        //set the settings
        $this->settings_api->set_sections( $this->get_settings_sections() );
        $this->settings_api->set_fields( $this->get_settings_fields() );

        //initialize settings
        $this->settings_api->admin_init();
    }

	function sub_menu_page() {
		add_submenu_page( 'options-general.php', 'PrimeTime WP + Discourse SSO Settings', __('WP Discourse SSO','pt-wp-discourse-sso'), 'manage_options', 'wp-sso-settings', array($this,'submenu_page_callback') );
	}


	function submenu_page_callback() {

		echo '<div class="wrap">';
			?><h2><?php _e('PrimeTime WP + Discourse SSO Settings','pt-wp-discourse-sso');?></h2><?php

			$this->settings_api->show_navigation();
        	$this->settings_api->show_forms();

		echo '</div>';

	}

    function get_settings_sections() {
        $sections = array(
            array(
                'id' 	=> 'pt_wp_sso_settings',
                'title' => 'Discourse Settings'
            )
        );

        if (is_plugin_active('membermouse/index.php')) {
            $sections[] = array (
                'id' 	=> 'pt_wp_sso_mm_settings',
                'title' => 'Membermouse Settings'
            );
        }
        return $sections;
    }

    function get_settings_fields() {

        $settings_fields = array(
            'pt_wp_sso_settings' => array(
            	array(
                    'name' 				=> 'secret_key',
                    'label' 			=> 'Secret Key',
                    'desc' 				=> 'Random string that will be the same on both your WP and Discourse installation.',
                    'type' 				=> 'text',
                    'default' 			=> '',
                    'sanitize_callback' => 'sanitize_text_field'
                ),
                array(
                    'name' 				=> 'discourse_url',
                    'label' 			=> 'Discourse URL',
                    'desc' 				=> 'The base URL to your Discourse installation (include protocol)',
                    'type' 				=> 'text',
                    'default' 			=> '',
                    'sanitize_callback' => 'sanitize_text_field'
                ),
                array(
                    'name' 				=> 'sync_logout',
                    'label' 			=> 'Synchronize Logout',
                    'desc' 				=> 'If user logs out of WP, also log them out of your Discourse installation',
                    'type' 				=> 'checkbox',
                    'default' 			=> 'off',
                    'sanitize_callback' => 'sanitize_checkbox'
                ),
                array(
                    'name' 				=> 'api_key',
                    'label' 			=> 'Admin API Key',
                    'desc' 				=> 'Required to synchronize logout',
                    'type' 				=> 'text',
                    'default' 			=> '',
                    'sanitize_callback' => 'sanitize_text_field'
                ),
                array(
                    'name' 				=> 'api_username',
                    'label' 			=> 'Admin Username',
                    'desc' 				=> 'Required to synchronize logout',
                    'type' 				=> 'text',
                    'default' 			=> '',
                    'sanitize_callback' => 'sanitize_text_field'
                )
            )
        );

        //If Membermouse is present, add option to disallow free members.
        if (is_plugin_active('membermouse/index.php')) {
            $settings_fields['pt_wp_sso_mm_settings'] = array(array(
                    'name' 				=> 'block_membermouse_free',
                    'label' 			=> 'Disallow Free Members',
                    'desc' 				=> 'Block free MemberMouse members from accessing your Discourse installation.',
                    'type' 				=> 'checkbox',
                    'default' 			=> 'off',
                    'sanitize_callback' => 'sanitize_checkbox'
            ));
        };

        return $settings_fields;
    }

    /**
    *
    *	Sanitize checkbox input
    *
    */
    function sanitize_checkbox( $input ) {

		if ( $input ) {

			$output = '1';

		} else {

			$output = false;

		}

		return $output;
	}

	/**
	*
	*	Sanitize integers
	*
	*/
	function sanitize_int( $input ) {

		if ( $input ) {

			$output = absint( $input );

		} else {

			$output = false;

		}

		return $output;
	}
}
endif;

$settings = new pt_wp_discourse_sso_settings_api_wrap();







<?php
/**
 * PrimeTime WP Discourse SSO
 *
 * @package   WP_Discourse_SSO
 * @author    ET Cook <e@etc.io>
 * @license   GPL-2.0+
 * @link      http://primetimecode.com
 * @copyright 2014 PrimeTime Code
 */

/**
 * Plugin class. This class should ideally be used to work with the
 * public-facing side of the WordPress site.
 *
 *
 * @package WP_Discourse_SSO
 * @author  ET Cook <e@etc.io>
 */
class WP_Discourse_SSO {

	public $configured = array();
	private $sso_secret;
	public $discourse_url;
	public $admin_url;

	/**
	 * Plugin version, used for cache-busting of style and script file references.
	 *
	 * @since   0.1
	 *
	 * @var     string
	 */
	const VERSION = PT_WP_DISCOURSE_SSO_VERSION;

	/**
	 * Unique identifier
	 *
	 *
	 * The variable name is used as the text domain when internationalizing strings
	 * of text. Its value should match the Text Domain file header in the main
	 * plugin file.
	 *
	 * @since    0.1
	 *
	 * @var      string
	 */
	protected $plugin_slug = 'pt-wp-discourse-sso';

	/**
	 * Instance of this class.
	 *
	 * @since    0.1
	 *
	 * @var      object
	 */
	protected static $instance = null;

	/**
	 * Initialize the plugin by setting localization and loading public scripts
	 * and styles.
	 *
	 * @since     0.1
	 */
	private function __construct() {

		// Activate plugin when new blog is added
		add_action( 'wpmu_new_blog', array( $this, 'activate_new_site' ) );

		require_once(PT_WP_DISCOURSE_SSO_DIR.'public/includes/helpers.php');

		$this->check_configuration();

		$this->admin_url = admin_url('options-general.php?page=wp-sso-settings');

		add_action( 'init', array( $this, 'interceptSSORequest' ) );

	}

	private function check_configuration() {
		$this->configured['all'] = FALSE;
		$this->configured['secret'] = FALSE;
		$this->configured['discourse_url'] = FALSE;
		$this->configured['activated'] = FALSE;

		if ( get_site_option( 'pt_wp_discourse_sso_activated' ) ) {
			$this->configured['activated'] = TRUE;
		}
		
		if ( NULL != pt_wp_sso_get_option('secret_key','pt_wp_sso_settings') ) {
			$this->configured['secret'] = TRUE;
			$this->sso_secret = pt_wp_sso_get_option('secret_key','pt_wp_sso_settings');
		}

		if ( NULL != pt_wp_sso_get_option('discourse_url','pt_wp_sso_settings') ) {
			$this->configured['discourse_url'] = TRUE;
			$this->discourse_url = rtrim(pt_wp_sso_get_option('discourse_url','pt_wp_sso_settings'),'/');
		}

		// Make sure the plugin is configured
		if ( $this->configured['secret'] && $this->configured['discourse_url'] && $this->configured['activated'] ) {
			$this->configured['all'] = TRUE;
		}

		if ( ! $this->configured['all'] ) {
			add_action( 'admin_notices', array( $this, 'render_admin_notice' ) );
		}

	}

	public function render_admin_notice() {
		?>
    <div class="error">
			<h4>Setting up your WP + Discourse SSO connection is easy!</h4>
			<p>
			<ol>
				<li><?php echo ($this->configured['secret'] ? '<s>' : ''); ?>Generate or come up with a random string of characters to use as a "key." Enter it in the <a href="<?php echo $this->admin_url; ?>">settings page</a>.<?php echo ($this->configured['secret'] ? '</s>' : ''); ?></li>
				<li><?php echo ($this->configured['discourse_url'] ? '<s>' : ''); ?>Paste your Discourse URL (http://example.discourse.org) into <a href="<?php echo $this->admin_url; ?>">settings page</a>.<?php echo ($this->configured['discourse_url'] ? '</s>' : ''); ?></li>
				<li><?php echo ($this->configured['activated'] ? '<s>' : ''); ?>Handle your first authentication. <i>(This message will go away once you'd validated your first SSO request)</i><?php echo ($this->configured['activated'] ? '</s>' : ''); ?></li>
			</ol>
			</p>
    </div>
    <?php
	}

	/**
	 * Return the plugin slug.
	 *
	 * @since    0.1
	 *
	 * @return    Plugin slug variable.
	 */
	public function get_plugin_slug() {
		return $this->plugin_slug;
	}

	/**
	 * Return an instance of this class.
	 *
	 * @since     0.1
	 *
	 * @return    object    A single instance of this class.
	 */
	public static function get_instance() {

		// If the single instance hasn't been set, set it now.
		if ( null == self::$instance ) {
			self::$instance = new self;
		}

		return self::$instance;
	}

	/**
	 * Fired when the plugin is activated.
	 *
	 * @since    0.1
	 *
	 * @param    boolean    $network_wide    True if WPMU superadmin uses
	 *                                       "Network Activate" action, false if
	 *                                       WPMU is disabled or plugin is
	 *                                       activated on an individual blog.
	 */
	public static function activate( $network_wide ) {

		if ( function_exists( 'is_multisite' ) && is_multisite() ) {

			if ( $network_wide  ) {

				// Get all blog ids
				$blog_ids = self::get_blog_ids();

				foreach ( $blog_ids as $blog_id ) {

					switch_to_blog( $blog_id );
					self::single_activate();
				}

				restore_current_blog();

			} else {
				self::single_activate();
			}

		} else {
			self::single_activate();
		}

	}

	/**
	 * Fired when the plugin is deactivated.
	 *
	 * @since    0.1
	 *
	 * @param    boolean    $network_wide    True if WPMU superadmin uses
	 *                                       "Network Deactivate" action, false if
	 *                                       WPMU is disabled or plugin is
	 *                                       deactivated on an individual blog.
	 */
	public static function deactivate( $network_wide ) {

		if ( function_exists( 'is_multisite' ) && is_multisite() ) {

			if ( $network_wide ) {

				// Get all blog ids
				$blog_ids = self::get_blog_ids();

				foreach ( $blog_ids as $blog_id ) {

					switch_to_blog( $blog_id );
					self::single_deactivate();

				}

				restore_current_blog();

			} else {
				self::single_deactivate();
			}

		} else {
			self::single_deactivate();
		}

	}

	/**
	 * Fired when a new site is activated with a WPMU environment.
	 *
	 * @since    0.1
	 *
	 * @param    int    $blog_id    ID of the new blog.
	 */
	public function activate_new_site( $blog_id ) {

		if ( 1 !== did_action( 'wpmu_new_blog' ) ) {
			return;
		}

		switch_to_blog( $blog_id );
		self::single_activate();
		restore_current_blog();

	}

	/**
	 * Get all blog ids of blogs in the current network that are:
	 * - not archived
	 * - not spam
	 * - not deleted
	 *
	 * @since    0.1
	 *
	 * @return   array|false    The blog ids, false if no matches.
	 */
	private static function get_blog_ids() {

		global $wpdb;

		// get an array of blog ids
		$sql = "SELECT blog_id FROM $wpdb->blogs
			WHERE archived = '0' AND spam = '0'
			AND deleted = '0'";

		return $wpdb->get_col( $sql );

	}

	/**
	 * Fired for each blog when the plugin is activated.
	 *
	 * @since    0.1
	 */
	private static function single_activate() {
		// @TODO: Define activation functionality here
	}

	/**
	 * Fired for each blog when the plugin is deactivated.
	 *
	 * @since    0.1
	 */
	private static function single_deactivate() {
		// @TODO: Define deactivation functionality here
	}

	/**
	 * Validate the SSO payload
	 * @param  [type] $payload [description]
	 * @param  [type] $sig     [description]
	 * @return [type]          [description]
	 */
	public function validate($payload, $sig) {

		$payload = urldecode($payload);
		if(hash_hmac("sha256", $payload, $this->sso_secret) === $sig) {
			if( !$this->configured['activated'] ){
				add_site_option( 'pt_wp_discourse_sso_activated', TRUE );
			}
			return true;
		} else {
			return false;
		}
	}
	
	/**
	 * Get nonce out of original payload
	 * @param  [type] $payload [description]
	 * @return [type]          [description]
	 */
	public function getNonce($payload) {
		$payload = urldecode($payload);
		$query = array();
		parse_str(base64_decode($payload), $query);
		if(isset($query["nonce"])) {
			return $query["nonce"];
		} else {
			throw new Exception("Nonce not found in payload!");
		}
	}
	
	/**
	 * Create a login string to authenticate with Discourse
	 * @param  [type] $params [description]
	 * @return [type]         [description]
	 */
	public function buildLoginString($params) {
		if(!isset($params["external_id"])) {
			throw new Exception("Missing required parameter 'external_id'");
		}
		if(!isset($params["nonce"])) {
			throw new Exception("Missing required parameter 'nonce'");
		}
		if(!isset($params["email"])) {
			throw new Exception("Missing required parameter 'email'");
		}
		$payload = base64_encode(http_build_query($params));
		$sig = hash_hmac("sha256", $payload, $this->sso_secret);
		
		return http_build_query(array("sso" => $payload, "sig" => $sig));
	}

	private function cleansePayload($p){
		return str_replace( '%0A', '%0B', $p );
	}

	private function restorePayload($p){
		return urldecode( str_replace( '%0B', '%0A', urlencode( $p ) ) );
	}

	/**
	 * Callback function to intercept and validate SSO requests
	 * @return [type] [description]
	 */
	public function interceptSSORequest() {

		if( isset( $_GET['sso'] ) && isset( $_GET['sig'] ) ) {

			$varsso = $_GET['sso'];
			$varsig = $_GET['sig'];

			if( ! $this->validate( $varsso, $varsig ) ) {
				//return;
			}
			
			// Check to see whether the user is logged in or not
			if ( ! is_user_logged_in() ) {

				// Preserve sso and sig parameters
				$redirect = add_query_arg( array( 'sso' => urlencode( $varsso ), 'sig' => urlencode( $varsig ) ) );
				
				// Change %0A to %0B so it's not stripped out in wp_sanitize_redirect
				$redirect = $this->cleansePayload( $redirect );

				// Build login URL
				$login = wp_login_url( $redirect );

				// Redirect to login
				wp_redirect( $login );
				exit;

			}

			// Logged in to WordPress, now try to log in to Discourse with WordPress user information
			else {

				$ssopayload = $this->restorePayload( $_GET['sso'] );
				$sigpayload = $_GET['sig'];

				if ( ! ( $this->validate( $ssopayload, $sigpayload ) ) ) {
					
					// Error message
					echo( 'Invalid request.' );
					
					// Terminate
					exit;

				}

				// Nonce    
				$nonce = $this->getNonce( $ssopayload );

				$current_user = wp_get_current_user();

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
				$q = $this->buildLoginString( $params );

				// Redirect back to Discourse
				wp_redirect( $this->discourse_url . '/session/sso_login?' . $q );

				exit;

			}
		}
	}

}






<?php
/**
 * PrimeTime WP Discourse SSO
 *
 * @package   WP_Discourse_SSO
 * @author    ET Cook <e@etc.io>
 * @license   GPL-2.0+
 * @link      http://example.com
 * @copyright 2014 Your Name or Company Name
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

	public $configured = FALSE;
	private $sso_secret;
	public $discourse_url;
	public $admin_url;

	/**
	 * Plugin version, used for cache-busting of style and script file references.
	 *
	 * @since   0.0.1
	 *
	 * @var     string
	 */
	const VERSION = '0.0.1';

	/**
	 * Unique identifier
	 *
	 *
	 * The variable name is used as the text domain when internationalizing strings
	 * of text. Its value should match the Text Domain file header in the main
	 * plugin file.
	 *
	 * @since    0.0.1
	 *
	 * @var      string
	 */
	protected $plugin_slug = 'pt-wp-discourse-sso';

	/**
	 * Instance of this class.
	 *
	 * @since    0.0.1
	 *
	 * @var      object
	 */
	protected static $instance = null;

	/**
	 * Initialize the plugin by setting localization and loading public scripts
	 * and styles.
	 *
	 * @since     0.0.1
	 */
	private function __construct() {

		// Activate plugin when new blog is added
		add_action( 'wpmu_new_blog', array( $this, 'activate_new_site' ) );

		require_once(PT_WP_DISCOURSE_SSO_DIR.'/public/includes/helpers.php');
		require_once(PT_WP_DISCOURSE_SSO_DIR.'/public/includes/template-loader.php');

		$this->check_configuration();

		$this->admin_url = admin_url('options-general.php?page=wp-sso-settings');

	}

	private function check_configuration() {

		// Make sure the plugin is configured
		if ( NULL != pt_wp_sso_get_option('secret_key','pt_wp_sso_settings') && NULL != pt_wp_sso_get_option('discourse_url','pt_wp_sso_settings') ) {
			$this->configured = TRUE;
			$this->sso_secret = pt_wp_sso_get_option('secret_key','pt_wp_sso_settings');
			$this->discourse_url = pt_wp_sso_get_option('discourse_url','pt_wp_sso_settings');
		}

		$assigned_template = PT_Template_Loader::get_page_by_template( 'template/pt-wp-discourse-sso.php' );

		if ( ! $this->configured ) {
			add_action( 'admin_notices', function() {
    		?>
		    <div class="error">
		        <p>Click <a href="<?php echo $this->admin_url; ?>">here</a> to configure the WP + Discourse SSO plugins.</p>
		    </div>
		    <?php
			});
		}

		if ( ! $assigned_template ) {
			add_action( 'admin_notices', function() {
    		?>
		    <div class="error">
		        <p>You need to create a new page and assign the WP Discourse SSO template to fully configure this plugin!</p>
		    </div>
		    <?php
			});
		}

	}

	/**
	 * Return the plugin slug.
	 *
	 * @since    0.0.1
	 *
	 * @return    Plugin slug variable.
	 */
	public function get_plugin_slug() {
		return $this->plugin_slug;
	}

	/**
	 * Return an instance of this class.
	 *
	 * @since     0.0.1
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
	 * @since    0.0.1
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
	 * @since    0.0.1
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
	 * @since    0.0.1
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
	 * @since    0.0.1
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
	 * @since    0.0.1
	 */
	private static function single_activate() {
		// @TODO: Define activation functionality here
	}

	/**
	 * Fired for each blog when the plugin is deactivated.
	 *
	 * @since    0.0.1
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

}






<?php
/*
Plugin Name: WP Job Manager -  Company Listings
Plugin URI: https://shop.opentuteplus.com/downloads/wp-job-manager-company-listings/
Description: Outputs a list of all companies that have submitted jobs with links to their listings and profile.
Version: 1.0.0
Author: OpenTute+
Author URI: http://opentuteplus.com/
Requires at least: 4.1
Tested up to: 4.7

Copyright: 2016 OpenTute+
License: GNU General Public License v3.0
License URI: http://www.gnu.org/licenses/gpl-3.0.html
*/

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * WP_Job_Manager_Company_Listings class.
 */
class WP_Job_Manager_Company_Listings {

	/**
	 * __construct function.
	 */
	public function __construct() {
		// Define constants
		define( 'RESUME_MANAGER_VERSION', '1.15.2' );
		define( 'RESUME_MANAGER_PLUGIN_DIR', untrailingslashit( plugin_dir_path( __FILE__ ) ) );
		define( 'RESUME_MANAGER_PLUGIN_URL', untrailingslashit( plugins_url( basename( plugin_dir_path( __FILE__ ) ), basename( __FILE__ ) ) ) );

		// Includes
		include( 'includes/wp-job-manager-company-listings-functions.php' );
		include( 'includes/wp-job-manager-company-listings-template.php' );
		include( 'includes/class-wp-job-manager-company-listings-post-types.php' );
		include( 'includes/class-wp-job-manager-company-listings-forms.php' );
		include( 'includes/class-wp-job-manager-company-listings-ajax.php' );
		include( 'includes/class-wp-job-manager-company-listings-shortcodes.php' );
		include( 'includes/class-wp-job-manager-company-listings-geocode.php' );
		include( 'includes/class-wp-job-manager-company-listings-email-notification.php' );
		include( 'includes/class-wp-job-manager-company-listings-apply.php' );

		// Init classes
		$this->apply      = new WP_Job_Manager_Company_Listings_Apply();
		$this->forms      = new WP_Job_Manager_Company_Listings_Forms();
		$this->post_types = new WP_Job_Manager_Company_Listings_Post_Types();

		// Activation - works with symlinks
		register_activation_hook( basename( dirname( __FILE__ ) ) . '/' . basename( __FILE__ ), array( $this->post_types, 'register_post_types' ), 10 );
		register_activation_hook( basename( dirname( __FILE__ ) ) . '/' . basename( __FILE__ ), create_function( "", "include_once( 'includes/class-wp-job-manager-company-listings-install.php' );" ), 10 );
		register_activation_hook( basename( dirname( __FILE__ ) ) . '/' . basename( __FILE__ ), 'flush_rewrite_rules', 15 );

		// Actions
		add_action( 'admin_notices', array( $this, 'version_check' ) );
		add_action( 'plugins_loaded', array( $this, 'admin' ) );
		add_action( 'widgets_init', array( $this, 'widgets_init' ), 12 );
		add_action( 'plugins_loaded', array( $this, 'load_plugin_textdomain' ) );
		add_action( 'switch_theme', array( $this->post_types, 'register_post_types' ), 10 );
		add_action( 'switch_theme', 'flush_rewrite_rules', 15 );
		add_action( 'wp_enqueue_scripts', array( $this, 'frontend_scripts' ) );
		add_action( 'admin_init', array( $this, 'updater' ) );
	}

	/**
	 * Check JM version
	 */
	public function version_check() {
		$required_jm_version      = '1.22.0';
		if ( ! defined( 'JOB_MANAGER_VERSION' ) ) {
			?><div class="error"><p><?php _e( 'Resume Manager requires WP Job Manager to be installed!', 'wp-job-manager-applications' ); ?></p></div><?php
		} elseif ( version_compare( JOB_MANAGER_VERSION, $required_jm_version, '<' ) ) {
			?><div class="error"><p><?php printf( __( 'Resume Manager requires WP Job Manager %s (you are using %s)', 'wp-job-manager-applications' ), $required_jm_version, JOB_MANAGER_VERSION ); ?></p></div><?php
		}
	}

	/**
	 * Handle Updates
	 */
	public function updater() {
		if ( version_compare( RESUME_MANAGER_VERSION, get_option( 'wp_resume_manager_version' ), '>' ) ) {
			include_once( 'includes/class-wp-job-manager-company-listings-install.php' );
		}
	}

	/**
	 * Include admin
	 */
	public function admin() {
		if ( is_admin() && class_exists( 'WP_Job_Manager' ) ) {
			include( 'includes/admin/class-wp-job-manager-company-listings-admin.php' );
		}
	}

	/**
	 * Includes once plugins are loaded
	 */
	public function widgets_init() {
		include_once( 'includes/class-wp-job-manager-company-listings-widgets.php' );
	}

	/**
	 * Localisation
	 *
	 * @access private
	 * @return void
	 */
	public function load_plugin_textdomain() {
		$locale = apply_filters( 'plugin_locale', get_locale(), 'wp-job-manager-company-listings' );

		load_textdomain( 'wp-job-manager-company-listings', WP_LANG_DIR . "/wp-job-manager-company-listings/wp-job-manager-company-listings-$locale.mo" );
		load_plugin_textdomain( 'wp-job-manager-company-listings', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
	}

	/**
	 * frontend_scripts function.
	 *
	 * @access public
	 * @return void
	 */
	public function frontend_scripts() {
		$ajax_url         = admin_url( 'admin-ajax.php', 'relative' );
		$ajax_filter_deps = array( 'jquery' );

		// WPML workaround until this is standardized
		if ( defined( 'ICL_LANGUAGE_CODE' ) ) {
			$ajax_url = add_query_arg( 'lang', ICL_LANGUAGE_CODE, $ajax_url );
		}

		if ( apply_filters( 'job_manager_chosen_enabled', true ) ) {
			$ajax_filter_deps[] = 'chosen';
		}

		wp_register_script( 'wp-job-manager-company-listings-ajax-filters', RESUME_MANAGER_PLUGIN_URL . '/assets/js/ajax-filters.min.js', $ajax_filter_deps, RESUME_MANAGER_VERSION, true );
		wp_register_script( 'wp-job-manager-company-listings-candidate-dashboard', RESUME_MANAGER_PLUGIN_URL . '/assets/js/candidate-dashboard.min.js', array( 'jquery' ), RESUME_MANAGER_VERSION, true );
		wp_register_script( 'wp-job-manager-company-listings-company-submission', RESUME_MANAGER_PLUGIN_URL . '/assets/js/resume-submission.min.js', array( 'jquery', 'jquery-ui-sortable' ), RESUME_MANAGER_VERSION, true );
		wp_register_script( 'wp-job-manager-company-listings-company-contact-details', RESUME_MANAGER_PLUGIN_URL . '/assets/js/contact-details.min.js', array( 'jquery' ), RESUME_MANAGER_VERSION, true );

		wp_localize_script( 'wp-job-manager-company-listings-company-submission', 'resume_manager_resume_submission', array(
			'i18n_navigate'       => __( 'If you wish to edit the posted details use the "edit resume" button instead, otherwise changes may be lost.', 'wp-job-manager-company-listings' ),
			'i18n_confirm_remove' => __( 'Are you sure you want to remove this item?', 'wp-job-manager-company-listings' ),
			'i18n_remove'         => __( 'remove', 'wp-job-manager-company-listings' )
		) );
		wp_localize_script( 'wp-job-manager-company-listings-ajax-filters', 'resume_manager_ajax_filters', array(
			'ajax_url' => $ajax_url
		) );
		wp_localize_script( 'wp-job-manager-company-listings-candidate-dashboard', 'resume_manager_candidate_dashboard', array(
			'i18n_confirm_delete' => __( 'Are you sure you want to delete this resume?', 'wp-job-manager-company-listings' )
		) );

		wp_enqueue_style( 'wp-job-manager-company-frontend', RESUME_MANAGER_PLUGIN_URL . '/assets/css/frontend.css' );
	}
}

$GLOBALS['resume_manager'] = new WP_Job_Manager_Company_Listings();

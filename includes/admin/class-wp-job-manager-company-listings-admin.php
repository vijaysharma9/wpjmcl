<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * WP_Job_Manager_Company_Listings_Admin class.
 */
class WP_Job_Manager_Company_Listings_Admin {

	/**
	 * __construct function.
	 *
	 * @access public
	 * @return void
	 */
	public function __construct() {
		include_once( 'class-wp-job-manager-company-listings-cpt.php' );
		include_once( 'class-wp-job-manager-company-listings-writepanels.php' );
		include_once( 'class-wp-job-manager-company-listings-settings.php' );
		include_once( 'class-wp-job-manager-company-listings-setup.php' );

		add_action( 'job_manager_admin_screen_ids', array( $this, 'add_screen_ids' ) );
		add_action( 'admin_menu', array( $this, 'admin_menu' ), 12 );
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ), 20 );

		$this->settings_page = new WP_Job_Manager_Company_Listings_Settings();
	}

	/**
	 * Add screen ids
	 * @param array $screen_ids
	 * @return  array
	 */
	public function add_screen_ids( $screen_ids ) {
		$screen_ids[] = 'edit-company';
		$screen_ids[] = 'company';
		return $screen_ids;
	}

	/**
	 * admin_enqueue_scripts function.
	 *
	 * @access public
	 * @return void
	 */
	public function admin_enqueue_scripts() {
		global $wp_scripts;

		$jquery_version = isset( $wp_scripts->registered['jquery-ui-core']->ver ) ? $wp_scripts->registered['jquery-ui-core']->ver : '1.9.2';


		/** STYLE ******************************************************************************/
		wp_enqueue_style('select2-style', COMPANY_LISTINGS_PLUGIN_URL . '/assets/css/select2.css', array(), '3.5.4');
		wp_enqueue_style( 'jquery-ui-style', '//ajax.googleapis.com/ajax/libs/jqueryui/' . $jquery_version . '/themes/smoothness/jquery-ui.css' );
		wp_enqueue_style( 'company_listings_admin_css', COMPANY_LISTINGS_PLUGIN_URL . '/assets/css/admin.css' );
		wp_enqueue_style( 'job-edit-style', COMPANY_LISTINGS_PLUGIN_URL . '/assets/css/job-edit.css' );

		/** SCRIPTS ******************************************************************************/
		wp_enqueue_script('select2-script', COMPANY_LISTINGS_PLUGIN_URL . '/assets/js/select2/select2.min.js', array(), '3.5.4');
		wp_register_script( 'jquery-tiptip', COMPANY_LISTINGS_PLUGIN_URL. '/assets/js/jquery-tiptip/jquery.tipTip.min.js', array( 'jquery' ), JOB_MANAGER_VERSION, true );
		wp_enqueue_script( 'company_listings_admin_js', COMPANY_LISTINGS_PLUGIN_URL. '/assets/js/admin.min.js', array( 'jquery', 'jquery-tiptip', 'jquery-ui-datepicker', 'jquery-ui-sortable' ), COMPANY_LISTINGS_VERSION, true );
		wp_enqueue_script( 'job-edit-script', COMPANY_LISTINGS_PLUGIN_URL. '/assets/js/job-edit.js', array( 'jquery'  ), COMPANY_LISTINGS_VERSION, true );
	}

	/**
	 * admin_menu function.
	 *
	 * @access public
	 * @return void
	 */
	public function admin_menu() {
		add_submenu_page( 'edit.php?post_type=company', __( 'Settings', 'wp-job-manager-company-listings' ), __( 'Settings', 'wp-job-manager-company-listings' ), 'manage_options', 'company-listings-settings', array( $this->settings_page, 'output' ) );
	}
}

new WP_Job_Manager_Company_Listings_Admin();
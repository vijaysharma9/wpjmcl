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

		if ( version_compare( COMPANY_LISTINGS_VERSION, get_option( 'wp_company_listings_version' ), '>' ) ) {
			// Run setup/install
			include_once( COMPANY_LISTINGS_PLUGIN_DIR.'/includes/class-wp-job-manager-company-listings-install.php' );
		}

		include_once( 'class-wp-job-manager-company-listings-cpt.php' );
		include_once( 'class-wp-job-manager-company-listings-writepanels.php' );
		include_once( 'class-wp-job-manager-company-listings-settings.php' );
		include_once( 'class-wp-job-manager-company-listings-setup.php' );
		include_once( 'class-wp-job-manager-company-listings-license.php' );
		include_once( 'class-wp-job-manager-company-listings-updater.php' );

		add_action( 'job_manager_admin_screen_ids', array( $this, 'add_screen_ids' ) );
		add_action( 'admin_menu', 					array( $this, 'admin_menu' ), 12 );
		add_action( 'admin_enqueue_scripts', 		array( $this, 'admin_enqueue_scripts' ), 20 );

		add_filter( 'job_manager_job_listing_data_fields', array( $this, 'update_job_listing_data_fields' ) );

		$this->settings_page = new WP_Job_Manager_Company_Listings_Settings();
	}

	/**
	 * Add screen ids
	 * @param array $screen_ids
	 * @return  array
	 */
	public function add_screen_ids( $screen_ids ) {
		$screen_ids[] = 'edit-company_listings';
		$screen_ids[] = 'company_listings';
		return $screen_ids;
	}

	/**
	 * admin_enqueue_scripts function.
	 *
	 * @access public
	 * @return void
	 */
	public function admin_enqueue_scripts( $hook ) {
		$screen = get_current_screen();

		if ( $screen->id !== 'company_listings'
			&& $screen->id !== 'edit-company_listings'
			&& $hook !== 'company_listings_page_company-listings-settings' ) {
			return;
		}

		$suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';

		/** STYLE ******************************************************************************/
		wp_enqueue_style( 'company_listings_admin_css', COMPANY_LISTINGS_PLUGIN_URL . '/assets/css/admin' . $suffix . '.css' );

		/** SCRIPTS ******************************************************************************/
		wp_enqueue_script( 'company_listings_admin_js', COMPANY_LISTINGS_PLUGIN_URL. '/assets/js/admin' . $suffix . '.js', array( 'jquery', 'jquery-ui-sortable' ), COMPANY_LISTINGS_VERSION, true );
	}

	/**
	 * admin_menu function.
	 *
	 * @access public
	 * @return void
	 */
	public function admin_menu() {
		add_submenu_page( 'edit.php?post_type=company_listings', __( 'Settings', 'wp-job-manager-company-listings' ), __( 'Settings', 'wp-job-manager-company-listings' ), 'manage_options', 'company-listings-settings', array( $this->settings_page, 'output' ) );
	}

	public function update_job_listing_data_fields( $fields ) {
		if ( isset( $fields['_company_name'] ) ) {
			unset( $fields['_company_name'] );
		}

		if ( isset( $fields['_company_website'] ) ) {
			unset( $fields['_company_website'] );
		}

		if ( isset( $fields['_company_tagline'] ) ) {
			unset( $fields['_company_tagline'] );
		}

		if ( isset( $fields['_company_twitter'] ) ) {
			unset( $fields['_company_twitter'] );
		}

		if ( isset( $fields['_company_video'] ) ) {
			unset( $fields['_company_video'] );
		}

		$company_field_required = apply_filters( 'submit_job_form_fields_select_company_field_required', true );
		$company_field_position = apply_filters( 'submit_job_form_fields_select_company_field_position', 0 );
		$options = jmcl_get_companies_for_dropdown_field();

		$fields['_company_id'] = array(
		    'label'       => __( 'Company', 'wp-job-manager-company-listings' ),
		    'type'        => 'select',
		    'required'    => $company_field_required,
		    'placeholder' => __( 'Select company', 'wp-job-manager-company-listings' ),
		    'priority'    => $company_field_position,
		    'options'     => $options,
		);

		return $fields;
	}
}

new WP_Job_Manager_Company_Listings_Admin();

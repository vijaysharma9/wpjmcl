<?php
/**
 *  Allow WP Job Manager Company Listings to be updated directly from the dashboard.
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

if ( ! class_exists( 'WP_Job_Mananger_Company_Listings_Updater' ) ) :

/**
 * Main WP_Job_Mananger_Company_Listings_Updater Class
 *
 * @class WP_Job_Mananger_Company_Listings_Updater
 * @version	1.0
 */
class WP_Job_Mananger_Company_Listings_Updater {

	/**
	 * Constructor
	 */
	public function __construct() {
		// Define constants
		$this->define_constants();

		// Include required files
		$this->includes();
		
		// Check for updates
		add_action( 'admin_init', array( $this, 'check_for_updates' ), 0 );

		// Activate and Deactivate license
		add_action( 'admin_init', array( $this, 'activate_license' ), 5 );
		add_action( 'admin_init', array( $this, 'deactivate_license' ), 2 );
	}

	/**
	 * Define constants
	*/
	private function define_constants() {
		if ( !defined( 'JMCL_UPDATER_VERSION' ) )
			define( 'JMCL_UPDATER_VERSION', '1.0' );

		if ( !defined( 'JMCL_UPDATER_URL' ) )
			define( 'JMCL_UPDATER_URL', plugin_dir_url( __FILE__ ) );

		if ( !defined( 'JMCL_UPDATER_DIR' ) )
			define( 'JMCL_UPDATER_DIR', plugin_dir_path( __FILE__ ) );

		if ( !defined( 'JMCL_UPDATER_STORE_URL' ) )
			define( 'JMCL_UPDATER_STORE_URL', 'http://wpdrift.com' );

		if ( !defined( 'JMCL_UPDATER_ITEM_NAME' ) )
			define( 'JMCL_UPDATER_ITEM_NAME', 'WP Job Manager -  Company Listings' );
	}

	/**
	 * Include required files
	*/
	private function includes() {
		if ( !class_exists( 'WPDrift_Plugin_Updater' ) ) {
			// load our custom updater
			include( COMPANY_LISTINGS_PLUGIN_DIR. '/includes/class-wpdrift-updater.php' );
		}
	}

	/**
	 * Check for updates
	 */
	public function check_for_updates() {

		// retrieve our license key from the DB
		$license = trim( get_site_option( 'jmcl_license_key' ) );

		// setup the updater
		$edd_updater = new WPDrift_Plugin_Updater( JMCL_UPDATER_STORE_URL, COMPANY_LISTINGS_PLUGIN_FILE, array(
				'version' 	=> COMPANY_LISTINGS_VERSION, 		// current version number
				'license' 	=> $license, 						// license key (used get_site_option above to retrieve from DB)
				'item_name' => JMCL_UPDATER_ITEM_NAME, 			// name of this plugin
				'author' 	=> 'WPDrift' 						// author of this plugin
			)
		);

		//var_dump( $edd_updater ); die;
	}

	/**
	 * Activate license
	 */
	public function activate_license() {
		$license_status = get_option('jmcl_license_status') ;
		// listen for our activate button to be clicked
		if ( isset( $_POST['jmcl_license_key'] )  && ! empty( $_POST['jmcl_license_key'] ) && in_array( $license_status, array( 'invalid', '' ) ) ) {

			// retrieve the license from the database
			$license = trim( $_POST['jmcl_license_key'] );

			// data to send in our API request
			$api_params = array(
				'edd_action'=> 'activate_license',
				'license' 	=> $license,
				'item_name' => urlencode( JMCL_UPDATER_ITEM_NAME ), // the name of our product in EDD
				'url'       => home_url()
			);

			// Call the custom API.
			$response = wp_remote_post( JMCL_UPDATER_STORE_URL, array( 'timeout' => 15, 'sslverify' => false, 'body' => $api_params ) );

			// Make sure the response came back okay
			if ( is_wp_error( $response ) ) {
				WP_Job_Manager_Company_Listings_Settings::add_error( __( 'Sorry, there has been an error.', 'wp-job-manager-company-listings' ) );
				return false;
			}

			// Decode the license data
			$license_data = json_decode( wp_remote_retrieve_body( $response ) );

			// Update license status
			update_site_option( 'jmcl_license_status', $license_data->license );

			// Update License or display error
			if ( 'valid' == $license_data->license ) {
				WP_Job_Manager_Company_Listings_Settings::add_override( __( 'License activated.', 'wp-job-manager-company-listings' ) );
			} else {
				WP_Job_Manager_Company_Listings_Settings::add_error( __( 'License invalid.', 'wp-job-manager-company-listings' ) );
			}
		}
	}

	/**
	 * Deactivate license
	 */
	public function deactivate_license() {

		$license_status = get_option('jmcl_license_status');
		$license 		= get_option('jmcl_license_key'); // retrieve the license from the database
		// listen for our activate button to be clicked

		if ( isset( $_POST['jmcl_license_key'] ) && empty( $_POST['jmcl_license_key'] ) && 'valid' === $license_status ) {

			// data to send in our API request
			$api_params = array(
				'edd_action'=> 'deactivate_license',
				'license' 	=> $license,
				'item_name' => urlencode( JMCL_UPDATER_ITEM_NAME ), // the name of our product in EDD
				'url'       => home_url()
			);

			// Call the custom API.
			$response = wp_remote_post( JMCL_UPDATER_STORE_URL, array( 'timeout' => 15, 'sslverify' => false, 'body' => $api_params ) );

			// make sure the response came back okay
			if ( is_wp_error( $response ) ) {
				WP_Job_Manager_Company_Listings_Settings::add_error( __( 'Sorry, there has been an error.', 'wp-job-manager-company-listings' ) );
				update_option( 'jmcl_license_key', $license ); //restore license key
				return false;
			}

			// decode the license data
			$license_data = json_decode( wp_remote_retrieve_body( $response ) );

			// $license_data->license will be either "deactivated" or "failed"
			if ( $license_data->license == 'deactivated' ) {
				delete_site_option( 'jmcl_license_status' );
				WP_Job_Manager_Company_Listings_Settings::add_override( __( 'License deactivated.', 'wp-job-manager-company-listings' ) );
			} else {
				WP_Job_Manager_Company_Listings_Settings::add_error( __( 'Sorry, there has been an error.', 'wp-job-manager-company-listings' ) );
				update_option( 'jmcl_license_key', $license ); //restore license key
			}
		}
	}
}

endif;

new WP_Job_Mananger_Company_Listings_Updater();

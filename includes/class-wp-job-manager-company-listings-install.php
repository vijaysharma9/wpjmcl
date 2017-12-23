<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * WP_Job_Manager_Company_Listings_Install
 */
class WP_Job_Manager_Company_Listings_Install {

	/**
	 * __construct function.
	 *
	 * @access public
	 * @return void
	 */
	public function __construct() {
		global $wpdb;

		$this->init_user_roles();
		$this->create_files();
		$this->cron();

		// Redirect to setup screen for new install
		if ( ! get_option( 'wp_company_listings_version' ) ) {
			set_transient( '_company_listings_activation_redirect', 1, HOUR_IN_SECONDS );
		}

		// Meta update
		if ( version_compare( get_option( 'wp_company_listings_version' ), '1.6.1', '<' ) ) {
			$wpdb->query( "INSERT INTO {$wpdb->postmeta}( post_id, meta_key, meta_value ) SELECT DISTINCT ID AS post_id, '_featured' AS meta_key, 0 AS meta_value FROM {$wpdb->posts} WHERE post_type = 'company_listings' AND post_status = 'publish';" );
		}

		// Update featured posts ordering
		if ( version_compare( get_option( 'wp_company_listings_version', COMPANY_LISTINGS_VERSION ), '1.12.0', '<' ) ) {
			$wpdb->query( "UPDATE {$wpdb->posts} p SET p.menu_order = 0 WHERE p.post_type='company_listings';" );
			$wpdb->query( "UPDATE {$wpdb->posts} p LEFT JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id SET p.menu_order = -1 WHERE pm.meta_key = '_featured' AND pm.meta_value='1' AND p.post_type='company_listings';" );
		}

		// Update legacy options
		if ( false === get_option( 'company_listings_submit_company_form_page_id', false ) && get_option( 'company_listings_submit_page_id' ) ) {
			$page_id = get_option( 'company_listings_submit_page_id' );
			update_option( 'company_listings_submit_company_form_page_id', $page_id );
		}

		update_option( 'wp_company_listings_version', COMPANY_LISTINGS_VERSION );
	}

	/**
	 * Init user roles
	 *
	 * @access public
	 * @return void
	 */
	public function init_user_roles() {
		global $wp_roles;

		if ( class_exists( 'WP_Roles' ) && ! isset( $wp_roles ) )
			$wp_roles = new WP_Roles();

		if ( is_object( $wp_roles ) ) {
			$wp_roles->add_cap( 'administrator', 'manage_companies' );

			// Customer role
			add_role( 'company', __( 'Company', 'wp-job-manager-company-listings' ), array(
			    'read' 						=> true,
			    'edit_posts' 				=> false,
			    'delete_posts' 				=> false
			) );
		}
	}

	/**
	 * Create files/directories
	 */
	private function create_files() {
		// Install files and folders for uploading files and prevent hotlinking
		$upload_dir =  wp_upload_dir();

		// Remove old htaccess
		@unlink( $upload_dir['basedir'] . '/companies/.htaccess' );

		$files = array(
			array(
				'base' 		=> $upload_dir['basedir'] . '/companies/company_files',
				'file' 		=> '.htaccess',
				'content' 	=> 'deny from all'
			),
			array(
				'base' 		=> $upload_dir['basedir'] . '/companies/company_files',
				'file' 		=> 'index.html',
				'content' 	=> ''
			)
		);

		foreach ( $files as $file ) {
			if ( wp_mkdir_p( $file['base'] ) && ! file_exists( trailingslashit( $file['base'] ) . $file['file'] ) ) {
				if ( $file_handle = @fopen( trailingslashit( $file['base'] ) . $file['file'], 'w' ) ) {
					fwrite( $file_handle, $file['content'] );
					fclose( $file_handle );
				}
			}
		}
	}

	/**
	 * Setup cron jobs
	 */
	public function cron() {
		wp_clear_scheduled_hook( 'company_listings_check_for_expired_companies' );
		wp_schedule_event( time(), 'hourly', 'company_listings_check_for_expired_companies' );
	}
}

new WP_Job_Manager_Company_Listings_Install();
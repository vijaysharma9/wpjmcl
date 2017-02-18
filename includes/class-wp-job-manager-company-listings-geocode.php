<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * WP_Job_Manager_Company_Listings_Geocode
 *
 * Obtains Geolocation data for posted companies from Google.
 */
class WP_Job_Manager_Company_Listings_Geocode {

	/**
	 * Constructor
	 */
	public function __construct() {
		add_action( 'company_manager_update_company_data', array( $this, 'update_location_data' ), 20, 2 );
		add_action( 'company_manager_company_location_edited', array( $this, 'change_location_data' ), 20, 2 );
	}

	/**
	 * Update location data - when submitting a company
	 */
	public function update_location_data( $company_id, $values ) {
		if ( apply_filters( 'company_manager_geolocation_enabled', true ) ) {
			$address_data = WP_Job_Manager_Geocode::get_location_data( $values['company_fields']['company_location'] );
			WP_Job_Manager_Geocode::save_location_data( $company_id, $address_data );
		}
	}

	/**
	 * Change a companies location data upon editing
	 * @param  int $company_id
	 * @param  string $new_location
	 */
	public function change_location_data( $company_id, $new_location ) {
		if ( apply_filters( 'company_manager_geolocation_enabled', true ) ) {
			$address_data = WP_Job_Manager_Geocode::get_location_data( $new_location );
			WP_Job_Manager_Geocode::clear_location_data( $company_id );
			WP_Job_Manager_Geocode::save_location_data( $company_id, $address_data );
		}
	}
}

new WP_Job_Manager_Company_Listings_Geocode();
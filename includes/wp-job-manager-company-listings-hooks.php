<?php
/**
 * Hooks
 *
 * Action/filter hooks used for company listing.
 *
 * @version     1.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}


/**
 * Move Company Name field on first position
 * @param $fields
 * @return mixed
 */
function jmcl_reorder_job_listing_data_fields( $fields ) {
    $fields['_company_name']['priority'] = 0;
    return $fields;
}

add_filter( 'job_manager_job_listing_data_fields', 'jmcl_reorder_job_listing_data_fields' );
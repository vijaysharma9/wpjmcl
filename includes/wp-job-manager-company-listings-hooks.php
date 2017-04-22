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

/**
 *
 */
function jmcl_filter_jobs( $query_args, $args ) {
    global $wpdb;

    //Bail if it is not job listing filter
    if ( ! ( strstr( $_SERVER['REQUEST_URI'], '/jm-ajax/' ) ) ) {
        return $query_args;
    }

    $form_data_parts = parse_url( $_REQUEST['form_data'] );
    parse_str($form_data_parts['path'], $form_data );

    if( empty( $form_data['job_company_id'] ) )
        return $query_args;

    $company_id   = $form_data['job_company_id'];
    $company_jobs = $wpdb->get_col( "SELECT post_id FROM {$wpdb->postmeta} WHERE meta_key = '_company_id' AND meta_value = '{$company_id}'");

    //Set matched job ids in WP_Query
    if ( ! empty( $company_jobs ) )
        $query_args['post__in'] = $company_jobs;
    else
        $query_args['post__in'] = array(0);
    
    return apply_filters( 'jmcl_filter_jobs', $query_args ) ;
}

add_filter( 'get_job_listings_query_args', 'jmcl_filter_jobs', 10, 2 );

/**
 *
 */
function jmcl_search_form_group_field( $atts ) {
    global $wpdb, $post;


    if ( ! is_singular( 'company' ) ) {
        return false;
    } ?>
    <input type="hidden" name="job_company_id" value="<?php echo $post->ID; ?>" />
    <?php
}

add_action( 'job_manager_job_filters_search_jobs_start', 'jmcl_search_form_group_field', 10, 1 );

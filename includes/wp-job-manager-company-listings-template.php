<?php
/**
 * Template Functions
 *
 * Template functions specifically created for bp job manager
 *
 * @category 	Core
 * @package 	BP Job Manager/Template
 * @version     1.1
 */

/**
 * Get and include template files.
 *
 * @param mixed $template_name
 * @param array $args (default: array())
 * @param string $template_path (default: '')
 * @param string $default_path (default: '')
 * @return void
 */
function get_company_listings_template( $template_name, $args = array(), $template_path = 'company_listings', $default_path = '' ) {
    if ( $args && is_array( $args ) ) {
        extract( $args );
    }
    include( locate_company_listings_template( $template_name, $template_path, $default_path ) );
}

/**
 * Locate a template and return the path for inclusion.
 *
 * This is the load order:
 *
 *		yourtheme		/	$template_path	/	$template_name
 *		yourtheme		/	$template_name
 *		$default_path	/	$template_name
 *
 * @param string $template_name
 * @param string $template_path (default: 'job_manager')
 * @param string|bool $default_path (default: '') False to not load a default
 * @return string
 */
function locate_company_listings_template( $template_name, $template_path = 'company_listings', $default_path = '' ) {
    // Look within passed path within the theme - this is priority
    $template = locate_template(
        array(
            trailingslashit( $template_path ) . $template_name,
            $template_name
        )
    );

    // Get default template
    if ( ! $template && $default_path !== false ) {
        $default_path = $default_path ? $default_path : COMPANY_LISTINGS_PLUGIN_DIR . '/templates/';
        if ( file_exists( trailingslashit( $default_path ) . $template_name ) ) {
            $template = trailingslashit( $default_path ) . $template_name;
        }
    }

    // Return what we found
    return apply_filters( 'locate_company_listings_template', $template, $template_name, $template_path );
}

/**
 * Get template part (for templates in loops).
 *
 * @param string $slug
 * @param string $name (default: '')
 * @param string $template_path (default: 'company_listings')
 * @param string|bool $default_path (default: '') False to not load a default
 */
function get_company_listings_template_part( $slug, $name = '', $template_path = 'company_listings', $default_path = '' ) {
    $template = '';

    if ( $name ) {
        $template = locate_company_listings_template( "{$slug}-{$name}.php", $template_path, $default_path );
    }

    // If template file doesn't exist, look in yourtheme/slug.php and yourtheme/company_listings/slug.php
    if ( ! $template ) {
        $template = locate_company_listings_template( "{$slug}.php", $template_path, $default_path );
    }

    if ( $template ) {
        load_template( $template, false );
    }
}

/**
 * Get the default filename for a template.
 *
 */
function jmcl_get_template_loader_default_file() {
    if ( is_singular( 'company_listings' ) ) {
        $default_file = 'single-company.php';
    } else {
        $default_file = '';
    }
    return $default_file;
}


if ( ! function_exists( 'jmcl_default_company_tabs' ) ) {

    /**
     * Add default company tabs to compnay pages.
     *
     * @param array $tabs
     * @return array
     */
    function jmcl_default_company_tabs( $tabs = array() ) {
        global $post;

        // Description tab - shows product content
        $tabs['about'] = array(
            'title'    => __( 'About', 'wp-job-manager-company-listings' ),
            'priority' => 10,
            'callback' => 'company_listings_company_about_tab',
        );

        // Reviews tab - shows comments
        $tabs['jobs'] = array(
            'title'    => sprintf( __( 'Jobs (%d)', 'wp-job-manager-company-listings' ), jmcl_get_company_jobs_counts( $post->ID ) ),
            'priority' => 20,
            'callback' => 'company_listings_company_jobs_tab',
        );

        return apply_filters( 'company_listings_tabs', $tabs );
    }
}

if ( ! function_exists( 'jmcl_sort_company_tabs' ) ) {

    /**
     * Sort tabs by priority.
     *
     * @param array $tabs
     * @return array
     */
    function jmcl_sort_company_tabs( $tabs = array() ) {

        // Make sure the $tabs parameter is an array
        if ( ! is_array( $tabs ) ) {
            trigger_error( "Function jmcl_sort_company_tabs() expects an array as the first parameter. Defaulting to empty array." );
            $tabs = array();
        }

        // Re-order tabs by priority
        if ( ! function_exists( '_sort_priority_callback' ) ) {
            function _sort_priority_callback( $a, $b ) {
                if ( $a['priority'] === $b['priority'] ) {
                    return 0;
                }
                return ( $a['priority'] < $b['priority'] ) ? -1 : 1;
            }
        }

        uasort( $tabs, '_sort_priority_callback' );

        return $tabs;
    }
}


/**
 * Output the search url
 *
 * @uses jmcl_get_search_url() To get the search url
 */
function jmcl_search_url() {
    echo esc_url( jmcl_get_search_url() );
}
/**
 * Return the search url
 *
 * @return string Search url
 */
function jmcl_get_search_url() {

    // Pretty permalinkss
    $page_id = (int) get_option( 'company_listings_company_directory_page_id' );
    $url = trailingslashit( get_permalink( $page_id ) . 'search' );

    return apply_filters( 'jmcl_get_search_url', $url );
}

/**
 * Output the search results url
 *
 */
function jmcl_search_results_url() {
    echo esc_url( jmcl_get_search_results_url() );
}
/**
 * Return the search url
 *
 * @return string Search url
 */
function jmcl_get_search_results_url() {

    // Get the search terms
    $search_terms = get_query_var('search');

    $page_id = (int) get_option( 'company_listings_company_directory_page_id' );
    $url =    trailingslashit( get_permalink( $page_id ) . 'search' );

    // Append search terms
    if ( ! empty( $search_terms ) ) {
        $url = trailingslashit( $url ) . urlencode( $search_terms );
    }

    // Run through home_url()
    $url = user_trailingslashit( $url );

    return apply_filters( 'jmcl_get_search_results_url', $url );
}

/**
 * Output the search terms
 */
function jmcl_search_terms( $search_terms = '' ) {
    echo jmcl_get_search_terms( $search_terms );
}

/**
 * Get the search terms
 *
 * @return bool|string Search terms on success, false on failure
 */
function jmcl_get_search_terms( $passed_terms = '' ) {

    // Sanitize terms if they were passed in
    if ( ! empty( $passed_terms ) ) {
        $search_terms = sanitize_title( $passed_terms );

        // Use query variable if not
    } else {
        $search_terms = get_query_var( 'search' );
    }

    // Trim whitespace and decode, or set explicitly to false if empty
    $search_terms = ! empty( $search_terms ) ? urldecode( trim( $search_terms ) ) : false;

    return apply_filters( 'jmcl_get_search_terms', $search_terms, $passed_terms );
}


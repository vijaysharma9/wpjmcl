<?php
if ( ! function_exists( 'get_companies' ) ) :
/**
 * Queries job listings with certain criteria and returns them
 *
 * @access public
 * @return void
 */
function get_companies( $args = array() ) {
	global $wpdb, $company_listings_keyword;

	$args = wp_parse_args( $args, array(
		'search_location'   => '',
		'search_keywords'   => '',
		'search_categories' => array(),
		'offset'            => '',
		'posts_per_page'    => '-1',
		'orderby'           => 'date',
		'order'             => 'DESC',
		'featured'          => null,
		'fields'            => 'all'
	) );

	$query_args = array(
		'post_type'              => 'company',
		'post_status'            => 'publish',
		'ignore_sticky_posts'    => 1,
		'offset'                 => absint( $args['offset'] ),
		'posts_per_page'         => intval( $args['posts_per_page'] ),
		'orderby'                => $args['orderby'],
		'order'                  => $args['order'],
		'tax_query'              => array(),
		'meta_query'             => array(),
		'update_post_term_cache' => false,
		'update_post_meta_cache' => false,
		'cache_results'          => false,
		'fields'                 => $args['fields']
	);

	if ( $args['posts_per_page'] < 0 ) {
		$query_args['no_found_rows'] = true;
	}

	if ( ! empty( $args['search_location'] ) ) {
		$location_meta_keys = array( 'geolocation_formatted_address', '_company_location', 'geolocation_state_long' );
		$location_search    = array( 'relation' => 'OR' );
		foreach ( $location_meta_keys as $meta_key ) {
			$location_search[] = array(
				'key'     => $meta_key,
				'value'   => $args['search_location'],
				'compare' => 'like'
			);
		}
		$query_args['meta_query'][] = $location_search;
	}

	if ( ! is_null( $args['featured'] ) ) {
		$query_args['meta_query'][] = array(
			'key'     => '_featured',
			'value'   => '1',
			'compare' => $args['featured'] ? '=' : '!='
		);
	}

	if ( ! empty( $args['search_categories'] ) ) {
		$field    = is_numeric( $args['search_categories'][0] ) ? 'term_id' : 'slug';
		$operator = 'all' === get_option( 'company_listings_category_filter_type', 'all' ) && sizeof( $args['search_categories'] ) > 1 ? 'AND' : 'IN';
		$query_args['tax_query'][] = array(
			'taxonomy'         => 'company_category',
			'field'            => $field,
			'terms'            => array_values( $args['search_categories'] ),
			'include_children' => $operator !== 'AND' ,
			'operator'         => $operator
		);
	}

	if ( 'featured' === $args['orderby'] ) {
		$query_args['orderby'] = array(
			'menu_order' => 'ASC',
			'title'      => 'DESC'
		);
	}

	if ( $company_listings_keyword = sanitize_text_field( $args['search_keywords'] ) ) {
		$query_args['_keyword'] = $company_listings_keyword; // Does nothing but needed for unique hash
		add_filter( 'posts_clauses', 'get_companies_keyword_search' );
	}

	$query_args = apply_filters( 'company_listings_get_companies', $query_args, $args );

	if ( empty( $query_args['meta_query'] ) ) {
		unset( $query_args['meta_query'] );
	}

	if ( empty( $query_args['tax_query'] ) ) {
		unset( $query_args['tax_query'] );
	}

	// Filter args
	$query_args = apply_filters( 'get_companies_query_args', $query_args, $args );

	// Generate hash
	$to_hash         = defined( 'ICL_LANGUAGE_CODE' ) ? json_encode( $query_args ) . ICL_LANGUAGE_CODE : json_encode( $query_args );
	$query_args_hash = 'jm_' . md5( $to_hash ) . WP_Job_Manager_Cache_Helper::get_transient_version( 'get_company_listings' );

	do_action( 'before_get_job_listings', $query_args, $args );

	if ( false === ( $result = get_transient( $query_args_hash ) ) ) {
		$result = new WP_Query( $query_args );
		set_transient( $query_args_hash, $result, DAY_IN_SECONDS * 30 );
	}

	do_action( 'after_get_companies', $query_args, $args );

	remove_filter( 'posts_clauses', 'get_companies_keyword_search' );

	return $result;
}
endif;

if ( ! function_exists( 'get_companies_keyword_search' ) ) :
	/**
	 * Join and where query for keywords
	 *
	 * @param array $args
	 * @return array
	 */
	function get_companies_keyword_search( $args ) {
		global $wpdb, $company_listings_keyword;

		// Meta searching - Query matching ids to avoid more joins
		$post_ids = $wpdb->get_col( "SELECT post_id FROM {$wpdb->postmeta} WHERE meta_value LIKE '" . esc_sql( $company_listings_keyword ) . "%'" );

		// Term searching
		$post_ids = array_merge( $post_ids, $wpdb->get_col( "SELECT object_id FROM {$wpdb->term_relationships} AS tr LEFT JOIN {$wpdb->terms} AS t ON tr.term_taxonomy_id = t.term_id WHERE t.name LIKE '" . esc_sql( $company_listings_keyword ) . "%'" ) );

		// Title and content searching
		$conditions = array();
		$conditions[] = "{$wpdb->posts}.post_title LIKE '%" . esc_sql( $company_listings_keyword ) . "%'";
		$conditions[] = "{$wpdb->posts}.post_content RLIKE '[[:<:]]" . esc_sql( $company_listings_keyword ) . "[[:>:]]'";

		if ( $post_ids ) {
			$conditions[] = "{$wpdb->posts}.ID IN (" . esc_sql( implode( ',', array_unique( $post_ids ) ) ) . ")";
		}

		$args['where'] .= " AND ( " . implode( ' OR ', $conditions ) . " ) ";

		return $args;
	}
endif;

if ( ! function_exists( 'order_featured_company' ) ) :
	/**
	 * WP Core doens't let us change the sort direction for invidual orderby params - http://core.trac.wordpress.org/ticket/17065
	 *
	 * @access public
	 * @param array $args
	 * @return array
	 */
	function order_featured_company( $args ) {
		global $wpdb;

		$args['orderby'] = "$wpdb->postmeta.meta_value+0 DESC, $wpdb->posts.post_title ASC";

		return $args;
	}
endif;

if ( ! function_exists( 'get_company_share_link' ) ) :
/**
 * Generates a sharing link which allows someone to view the company directly (even if permissions do not usually allow it)
 *
 * @access public
 * @return array
 */
function get_company_share_link( $company_id ) {
	if ( ! $key = get_post_meta( $company_id, 'share_link_key', true ) ) {
		$key = wp_generate_password( 32, false );
		update_post_meta( $company_id, 'share_link_key', $key );
	}

	return add_query_arg( 'key', $key, get_permalink( $company_id ) );
}
endif;

if ( ! function_exists( 'get_company_categories' ) ) :
/**
 * Outputs a form to submit a new job to the site from the frontend.
 *
 * @access public
 * @return array
 */
function get_company_categories() {
	if ( ! get_option( 'company_listings_enable_categories' ) ) {
		return array();
	}

	return get_terms( "company_category", array(
		'orderby'       => 'name',
	    'order'         => 'ASC',
	    'hide_empty'    => false,
	) );
}
endif;

if ( ! function_exists( 'company_listings_get_filtered_links' ) ) :
/**
 * Shows links after filtering companies
 */
function company_listings_get_filtered_links( $args = array() ) {

	$links = apply_filters( 'company_listings_company_filters_showing_companies_links', array(
		'reset' => array(
			'name' => __( 'Reset', 'wp-job-manager-company-listings' ),
			'url'  => '#'
		)
	), $args );

	$return = '';

	foreach ( $links as $key => $link ) {
		$return .= '<a href="' . esc_url( $link['url'] ) . '" class="' . esc_attr( $key ) . '">' . $link['name'] . '</a>';
	}

	return $return;
}
endif;

/**
 * True if an the user can edit a company.
 *
 * @return bool
 */
function company_listings_user_can_edit_company( $company_id ) {
	$can_edit = true;
	$company   = get_post( $company_id );

	if ( ! is_user_logged_in() ) {
		$can_edit = false;
	} elseif ( $company->post_author != get_current_user_id() ) {
		$can_edit = false;
	}

	return apply_filters( 'company_listings_user_can_edit_company', $can_edit, $company_id );
}

/**
 * True if an the user can browse companies.
 *
 * @return bool
 */
function company_listings_user_can_browse_companies() {
	$can_browse = true;
	$caps       = array_filter( array_map( 'trim', array_map( 'strtolower', explode( ',', get_option( 'company_listings_browse_company_capability' ) ) ) ) );

	if ( $caps ) {
		$can_browse = false;
		foreach ( $caps as $cap ) {
			if ( current_user_can( $cap ) ) {
				$can_browse = true;
				break;
			}
		}
	}

	return apply_filters( 'company_listings_user_can_browse_companies', $can_browse );
}

/**
 * True if an the user can view the full company name.
 *
 * @return bool
 */
function company_listings_user_can_view_company_name( $company_id ) {
	$can_view = true;
	$company   = get_post( $company_id );
	$caps     = array_filter( array_map( 'trim', array_map( 'strtolower', explode( ',', get_option( 'company_listings_view_name_capability' ) ) ) ) );

	// Allow previews
	if ( $company->post_status === 'preview' ) {
		return true;
	}

	if ( $caps ) {
		$can_view = false;
		foreach ( $caps as $cap ) {
			if ( current_user_can( $cap ) ) {
				$can_view = true;
				break;
			}
		}
	}

	if ( $company->post_author > 0 && $company->post_author == get_current_user_id() ) {
		$can_view = true;
	}

	if ( ( $key = get_post_meta( $company_id, 'share_link_key', true ) ) && ! empty( $_GET['key'] ) && $key == $_GET['key'] ) {
		$can_view = true;
	}

	return apply_filters( 'company_listings_user_can_view_company_name', $can_view );
}


/**
 * True if an the user can view a company.
 *
 * @return bool
 */
function company_listings_user_can_view_company( $company_id ) {
	$can_view = true;
	$company   = get_post( $company_id );

	// Allow previews
	if ( $company->post_status === 'preview' ) {
		return true;
	}

	$caps = array_filter( array_map( 'trim', array_map( 'strtolower', explode( ',', get_option( 'company_listings_view_company_capability' ) ) ) ) );

	if ( $caps ) {
		$can_view = false;
		foreach ( $caps as $cap ) {
			if ( current_user_can( $cap ) ) {
				$can_view = true;
				break;
			}
		}
	}

	if ( $company->post_status === 'expired' ) {
		$can_view = false;
	}

	if ( $company->post_author > 0 && $company->post_author == get_current_user_id() ) {
		$can_view = true;
	}

	if ( ( $key = get_post_meta( $company_id, 'share_link_key', true ) ) && ! empty( $_GET['key'] ) && $key == $_GET['key'] ) {
		$can_view = true;
	}

	return apply_filters( 'company_listings_user_can_view_company', $can_view, $company_id );
}

/**
 * True if an the user can view a company.
 *
 * @return bool
 */
function company_listings_user_can_view_contact_details( $company_id ) {
	$can_view = true;
	$company   = get_post( $company_id );
	$caps     = array_filter( array_map( 'trim', array_map( 'strtolower', explode( ',', get_option( 'company_listings_contact_company_capability' ) ) ) ) );

	if ( $caps ) {
		$can_view = false;
		foreach ( $caps as $cap ) {
			if ( current_user_can( $cap ) ) {
				$can_view = true;
				break;
			}
		}
	}

	if ( $company->post_author > 0 && $company->post_author == get_current_user_id() ) {
		$can_view = true;
	}

	if ( ( $key = get_post_meta( $company_id, 'share_link_key', true ) ) && ! empty( $_GET['key'] ) && $key == $_GET['key'] ) {
		$can_view = true;
	}

	return apply_filters( 'company_listings_user_can_view_contact_details', $can_view, $company_id );
}

if ( ! function_exists( 'get_company_post_statuses' ) ) :
/**
 * Get post statuses used for companies
 *
 * @access public
 * @return array
 */
function get_company_post_statuses() {
	return apply_filters( 'company_post_statuses', array(
		'draft'           => _x( 'Draft', 'post status', 'wp-job-manager-company-listings' ),
		'expired'         => _x( 'Expired', 'post status', 'wp-job-manager-company-listings' ),
		'hidden'          => _x( 'Hidden', 'post status', 'wp-job-manager-company-listings' ),
		'preview'         => _x( 'Preview', 'post status', 'wp-job-manager-company-listings' ),
		'pending'         => _x( 'Pending approval', 'post status', 'wp-job-manager-company-listings' ),
		'pending_payment' => _x( 'Pending payment', 'post status', 'wp-job-manager-company-listings' ),
		'publish'         => _x( 'Published', 'post status', 'wp-job-manager-company-listings' ),
	) );
}
endif;

/**
 * Upload dir
 */
function company_listings_upload_dir( $dir, $field ) {
	if ( 'company_file' === $field ) {
		$dir = 'companies/company_files';
	}
	return $dir;
}
add_filter( 'job_manager_upload_dir', 'company_listings_upload_dir', 10, 2 );

/**
 * Count user companies
 * @param  integer $user_id
 * @return int
 */
function company_listings_count_user_companies( $user_id = 0 ) {
	global $wpdb;

	if ( ! $user_id ) {
		$user_id = get_current_user_id();
	}

	return $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(ID) FROM {$wpdb->posts} WHERE post_author = %d AND post_type = 'company' AND post_status IN ( 'publish', 'pending', 'expired', 'hidden' );", $user_id ) );
}

/**
 * Get the permalink of a page if set
 * @param  string $page e.g. company_dashboard, submit_company_form, companies
 * @return string|bool
 */
function company_listings_get_permalink( $page ) {
	$page_id = get_option( 'company_listings_' . $page . '_page_id', false );
	if ( $page_id ) {
		return get_permalink( $page_id );
	} else {
		return false;
	}
}

/**
 * Calculate and return the company expiry date
 * @param  int $company_id
 * @return string
 */
function calculate_company_expiry( $company_id ) {
	// Get duration from the product if set...
	$duration = get_post_meta( $company_id, '_company_duration', true );

	// ...otherwise use the global option
	if ( ! $duration ) {
		$duration = absint( get_option( 'company_listings_submission_duration' ) );
	}

	if ( $duration ) {
		return date( 'Y-m-d', strtotime( "+{$duration} days", current_time( 'timestamp' ) ) );
	}

	return '';
}

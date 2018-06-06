<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * WP_Job_Manager_Company_Listings_Ajax class.
 */
class WP_Job_Manager_Company_Listings_Ajax {

	/**
	 * Constructor
	 */
	public function __construct() {
		add_action( 'wp_ajax_nopriv_company_listings_get_companies', array( $this, 'get_companies' ) );
		add_action( 'wp_ajax_company_listings_get_companies', array( $this, 'get_companies' ) );
		add_action( 'wp_ajax_nopriv_company_listings_json_search_company', array( $this, 'json_search_company' ) );
		add_action( 'wp_ajax_company_listings_json_search_company', array( $this, 'json_search_company' ) );
		add_action( 'wp_ajax_nopriv_company_listings_json_company_data', array( $this, 'json_company_data' ) );
		add_action( 'wp_ajax_company_listings_json_company_data', array( $this, 'json_company_data' ) );
	}

	/**
	 * Get companies via ajax
	 */
	public function get_companies() {
		global $wpdb;

		ob_start();

		$search_location   = sanitize_text_field( stripslashes( $_POST['search_location'] ) );
		$search_keywords   = sanitize_text_field( stripslashes( $_POST['search_keywords'] ) );
		$search_categories = isset( $_POST['search_categories'] ) ? $_POST['search_categories'] : '';

		if ( is_array( $search_categories ) ) {
			$search_categories = array_map( 'sanitize_text_field', array_map( 'stripslashes', $search_categories ) );
		} else {
			$search_categories = array( sanitize_text_field( stripslashes( $search_categories ) ), 0 );
		}

		$search_categories = array_filter( $search_categories );

		$args = array(
			'search_location'   => $search_location,
			'search_keywords'   => $search_keywords,
			'search_categories' => $search_categories,
			'orderby'           => sanitize_text_field( $_POST['orderby'] ),
			'order'             => sanitize_text_field( $_POST['order'] ),
			'offset'            => ( absint( $_POST['page'] ) - 1 ) * absint( $_POST['per_page'] ),
			'posts_per_page'    => absint( $_POST['per_page'] )
		);

		if ( isset( $_POST['featured'] ) && ( $_POST['featured'] === 'true' || $_POST['featured'] === 'false' ) ) {
			$args['featured'] = $_POST['featured'] === 'true' ? true : false;
		}

		$companies = get_companies( apply_filters( 'company_listings_get_companies_args', $args ) );

		$result = array();
		$result['found_companies'] = false;

		if ( $companies->have_posts() ) : $result['found_companies'] = true; ?>

			<?php while ( $companies->have_posts() ) : $companies->the_post(); ?>

				<?php get_company_listings_template_part( 'content', 'company_listings', 'wp-job-manager-company-listings', COMPANY_LISTINGS_PLUGIN_DIR . '/templates/' ); ?>

			<?php endwhile; ?>

		<?php else : ?>

			<li class="no_companies_found"><?php _e( 'No companies found matching your selection.', 'wp-job-manager-company-listings' ); ?></li>

		<?php endif;

		$result['html']    = ob_get_clean();

		// Generate 'showing' text
		if ( $search_keywords || $search_location || $search_categories || apply_filters( 'company_listings_get_companies_custom_filter', false ) ) {

			$showing_categories = array();

			if ( $search_categories ) {
				foreach ( $search_categories as $category ) {
					if ( ! is_numeric( $category ) ) {
						$category_object = get_term_by( 'slug', $category, 'company_category' );
					}
					if ( is_numeric( $category ) || is_wp_error( $category_object ) || ! $category_object ) {
						$category_object = get_term_by( 'id', $category, 'company_category' );
					}
					if ( ! is_wp_error( $category_object ) ) {
						$showing_categories[] = $category_object->name;
					}
				}
			}

			if ( $search_keywords ) {
				$showing_companies  = sprintf( __( 'Showing &ldquo;%s&rdquo; %scompanies', 'wp-job-manager-company-listings' ), $search_keywords, implode( ', ', $showing_categories ) );
			} else {
				$showing_companies  = sprintf( __( 'Showing all %scompanies', 'wp-job-manager-company-listings' ), implode( ', ', $showing_categories ) . ' ' );
			}

			$showing_location  = $search_location ? sprintf( ' ' . __( 'located in &ldquo;%s&rdquo;', 'wp-job-manager-company-listings' ), $search_location ) : '';

			$result['showing'] = apply_filters( 'company_listings_get_companies_custom_filter_text', $showing_companies . $showing_location );

		} else {
			$result['showing'] = '';
		}

		// Generate RSS link
		$result['showing_links'] = company_listings_get_filtered_links( array(
			'search_location'   => $search_location,
			'search_categories' => $search_categories,
			'search_keywords'   => $search_keywords
		) );

		// Generate pagination
		if ( isset( $_POST['show_pagination'] ) && $_POST['show_pagination'] === 'true' ) {
			$result['pagination'] = get_job_listing_pagination( $companies->max_num_pages, absint( $_POST['page'] ) );
		}

		$result['max_num_pages'] = $companies->max_num_pages;

		echo '<!--WPJM-->';
		echo json_encode( $result );
		echo '<!--WPJM_END-->';

		die();
	}


	/**
	 * Search for company and return json.
	 */
	public function json_search_company() {
		$term = isset( $_POST['term'] ) ? $_POST['term'] : '';
		$companies = array();

		if ( $term ) {
			$args = apply_filters( 'search_company_listings_args', array(
				'post_type'      => 'company_listings',
				'post_status'    => 'publish' ,
				'posts_per_page' => -1,
				's'              => $term,
			) );

			$posts = get_posts( $args );

			if ( $posts ) {
				foreach ($posts as $post) {
					$companies[] = array(
						'id'   => $post->ID,
						'text' => $post->post_title,
					);
				}
			}
		}

		echo json_encode( $companies );
		exit;
	}

	/**
	 * Get the selected company data
	 */
	public function json_company_data() {

		ob_start();

		$company_id = stripslashes( $_GET['company_id'] );

		$data = array(
			'location'  	=> get_post_meta( $company_id, '_company_location', true ),
			'application'  	=> get_post_meta( $company_id, '_company_email', true ),
			'website'   	=> get_post_meta( $company_id, '_company_website', true ),
			'tagline'   	=> get_post_meta( $company_id, '_company_title', true ),
			'twitter'   	=> get_post_meta( $company_id, '_company_twitter', true ),
			'video'     	=> get_post_meta( $company_id, '_company_video', true ),
			'group_id'  	=> get_post_meta( $company_id, '_group_id', true )
		);

		//Company logo
		$thumbnail_id  = get_post_thumbnail_id( $company_id );

		if ( ! empty( $thumbnail_id ) ) {

			if ( isset( $_GET['post_ID'] ) ) {
				$data['logo_backend']  = _wp_post_thumbnail_html( $thumbnail_id, $_GET['post_ID'] );
			} else {

				ob_start();
				get_job_manager_template( 'form-fields/uploaded-file-html.php', array( 'name' => 'current_company_logo', 'value' => $thumbnail_id ) );
				$js_field_html_img = ob_get_clean();
				$data['logo_frontend'] =  $js_field_html_img;
			}
		}


		wp_send_json( apply_filters( 'json_company_data', $data ) );
	}
}

new WP_Job_Manager_Company_Listings_Ajax();

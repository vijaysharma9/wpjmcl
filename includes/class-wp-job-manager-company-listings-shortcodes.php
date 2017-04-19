<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * WP_Job_Manager_Company_Listings_Shortcodes class.
 */
class WP_Job_Manager_Company_Listings_Shortcodes {

	private $company_dashboard_message = '';

	/**
	 * Constructor
	 */
	public function __construct() {
		add_action( 'wp', array( $this, 'handle_redirects' ) );
		add_action( 'wp', array( $this, 'shortcode_action_handler' ) );
		add_shortcode( 'submit_company_form', array( $this, 'submit_company_form' ) );
		add_shortcode( 'company_dashboard', array( $this, 'company_dashboard' ) );
		add_shortcode( 'companies', array( $this, 'output_companies' ) );
		add_shortcode( 'company_directory', array( $this, 'output_company_directory' ) );
		add_action( 'company_listings_output_companies_no_results', array( $this, 'output_no_results' ) );
	}

	/**
	 * Handle redirects
	 */
	public function handle_redirects() {
		if ( ! get_current_user_id() || ! empty( $_REQUEST['company_id'] ) ) {
			return;
		}

		$submit_company_form_page_id              = get_option( 'company_listings_submit_company_form_page_id' );
		$company_dashboard_page_id = get_option( 'company_listings_company_dashboard_page_id' );
		$submission_limit            = get_option( 'company_listings_submission_limit' );
		$company_count                = company_listings_count_user_companies();

		if ( $submit_company_form_page_id && $company_dashboard_page_id && $submission_limit && $company_count >= $submission_limit && is_page( $submit_company_form_page_id ) ) {
			wp_redirect( get_permalink( $company_dashboard_page_id ) );
			exit;
		}
	}

	/**
	 * Handle actions which need to be run before the shortcode e.g. post actions
	 */
	public function shortcode_action_handler() {
		global $post;

		if ( is_page() && strstr( $post->post_content, '[company_dashboard' ) ) {
			$this->company_dashboard_handler();
		}
	}

	/**
	 * Show the company submission form
	 */
	public function submit_company_form( $atts = array() ) {
		return $GLOBALS['company_listings']->forms->get_form( 'submit-company', $atts );
	}

	/**
	 * Handles actions on company dashboard
	 */
	public function company_dashboard_handler() {
		if ( ! empty( $_REQUEST['action'] ) && ! empty( $_REQUEST['_wpnonce'] ) && wp_verify_nonce( $_REQUEST['_wpnonce'], 'company_listings_my_company_actions' ) ) {

			$action    = sanitize_title( $_REQUEST['action'] );
			$company_id = absint( $_REQUEST['company_id'] );

			try {
				// Get company
				$company = get_post( $company_id );

				// Check ownership
				if ( ! $company || $company->post_author != get_current_user_id() )
					throw new Exception( __( 'Invalid Company ID', 'wp-job-manager-company-listings' ) );

				switch ( $action ) {
					case 'delete' :
						// Trash it
						wp_trash_post( $company_id );

						// Message
						$this->company_dashboard_message = '<div class="job-manager-message">' . sprintf( __( '%s has been deleted', 'wp-job-manager-company-listings' ), $company->post_title ) . '</div>';

					break;
					case 'hide' :
						if ( $company->post_status === 'publish' ) {
							$update_company = array( 'ID' => $company_id, 'post_status' => 'hidden' );
							wp_update_post( $update_company );
							$this->company_dashboard_message = '<div class="job-manager-message">' . sprintf( __( '%s has been hidden', 'wp-job-manager-company-listings' ), $company->post_title ) . '</div>';
						}
					break;
					case 'publish' :
						if ( $company->post_status === 'hidden' ) {
							$update_company = array( 'ID' => $company_id, 'post_status' => 'publish' );
							wp_update_post( $update_company );
							$this->company_dashboard_message = '<div class="job-manager-message">' . sprintf( __( '%s has been published', 'wp-job-manager-company-listings' ), $company->post_title ) . '</div>';
						}
					break;
					case 'relist' :
						// redirect to post page
						wp_redirect( add_query_arg( array( 'company_id' => absint( $company_id ) ), get_permalink( get_option( 'company_listings_submit_company_form_page_id' ) ) ) );

						break;
				}

				do_action( 'company_listings_my_company_do_action', $action, $company_id );

			} catch ( Exception $e ) {
				$this->company_dashboard_message = '<div class="job-manager-error">' . $e->getMessage() . '</div>';
			}
		}
	}

	/**
	 * Shortcode which lists the logged in user's companies
	 */
	public function company_dashboard( $atts ) {
		global $company_listings;

		if ( ! is_user_logged_in() ) {
			ob_start();
			get_job_manager_template( 'company-dashboard-login.php', array(), 'wp-job-manager-company-listings', COMPANY_LISTINGS_PLUGIN_DIR . '/templates/' );
			return ob_get_clean();
		}

		extract( shortcode_atts( array(
			'posts_per_page' => '25',
		), $atts ) );

		wp_enqueue_script( 'wp-job-manager-company-listings-company-dashboard' );

		// If doing an action, show conditional content if needed....
		if ( ! empty( $_REQUEST['action'] ) ) {

			$action    = sanitize_title( $_REQUEST['action'] );
			$company_id = absint( $_REQUEST['company_id'] );

			switch ( $action ) {
				case 'edit' :
					return $company_listings->forms->get_form( 'edit-company' );
			}
		}

		// ....If not show the company dashboard
		$args = apply_filters( 'company_listings_get_dashboard_companies_args', array(
			'post_type'           => 'company',
			'post_status'         => array( 'publish', 'expired', 'pending', 'hidden' ),
			'ignore_sticky_posts' => 1,
			'posts_per_page'      => $posts_per_page,
			'offset'              => ( max( 1, get_query_var('paged') ) - 1 ) * $posts_per_page,
			'orderby'             => 'date',
			'order'               => 'desc',
			'author'              => get_current_user_id()
		) );

		$companies = new WP_Query;

		ob_start();

		echo $this->company_dashboard_message;

		$company_dashboard_columns = apply_filters( 'company_listings_company_dashboard_columns', array(
			'company-title'       => __( 'Name', 'wp-job-manager-company-listings' ),
			'company-title'    => __( 'Title', 'wp-job-manager-company-listings' ),
			'company-location' => __( 'Location', 'wp-job-manager-company-listings' ),
			'company-category'    => __( 'Category', 'wp-job-manager-company-listings' ),
			'date'               => __( 'Date Posted', 'wp-job-manager-company-listings' )
		) );

		if ( ! get_option( 'company_listings_enable_categories' ) ) {
			unset( $company_dashboard_columns['company-category'] );
		}

		get_job_manager_template( 'company-dashboard.php', array( 'companies' => $companies->query( $args ), 'max_num_pages' => $companies->max_num_pages, 'company_dashboard_columns' => $company_dashboard_columns ), 'wp-job-manager-company-listings', COMPANY_LISTINGS_PLUGIN_DIR . '/templates/' );

		return ob_get_clean();
	}

	/**
	 * output_companies function.
	 *
	 * @access public
	 * @param mixed $args
	 * @return void
	 */
	public function output_companies( $atts ) {
		global $company_listings;

		ob_start();

		if ( ! company_listings_user_can_browse_companies() ) {
			get_job_manager_template_part( 'access-denied', 'browse-companies', 'wp-job-manager-company-listings', COMPANY_LISTINGS_PLUGIN_DIR . '/templates/' );
			return ob_get_clean();
		}

		extract( $atts = shortcode_atts( apply_filters( 'company_listings_output_companies_defaults', array(
			'per_page'                  => get_option( 'company_listings_per_page' ),
			'order'                     => 'DESC',
			'orderby'                   => 'featured',
			'show_filters'              => true,
			'show_categories'           => get_option( 'company_listings_enable_categories' ),
			'categories'                => '',
			'featured'                  => null, // True to show only featured, false to hide featured, leave null to show both.
			'show_category_multiselect' => get_option( 'company_listings_enable_default_category_multiselect', false ),
			'selected_category'         => '',
			'show_pagination'           => false,
			'show_more'                 => true,
		) ), $atts ) );

		$categories = array_filter( array_map( 'trim', explode( ',', $categories ) ) );
		$keywords   = '';
		$location   = '';

		// String and bool handling
		$show_filters              = $this->string_to_bool( $show_filters );
		$show_categories           = $this->string_to_bool( $show_categories );
		$show_category_multiselect = $this->string_to_bool( $show_category_multiselect );
		$show_more                 = $this->string_to_bool( $show_more );
		$show_pagination           = $this->string_to_bool( $show_pagination );

		if ( ! is_null( $featured ) ) {
			$featured = ( is_bool( $featured ) && $featured ) || in_array( $featured, array( '1', 'true', 'yes' ) ) ? true : false;
		}

		if ( ! empty( $_GET['search_keywords'] ) ) {
			$keywords = sanitize_text_field( $_GET['search_keywords'] );
		}

		if ( ! empty( $_GET['search_location'] ) ) {
			$location = sanitize_text_field( $_GET['search_location'] );
		}

		if ( ! empty( $_GET['search_category'] ) ) {
			$selected_category = sanitize_text_field( $_GET['search_category'] );
		}

		if ( $show_filters ) {

			get_job_manager_template( 'company-filters.php', array( 'per_page' => $per_page, 'orderby' => $orderby, 'order' => $order, 'show_categories' => $show_categories, 'categories' => $categories, 'selected_category' => $selected_category, 'atts' => $atts, 'location' => $location, 'keywords' => $keywords, 'show_category_multiselect' => $show_category_multiselect ), 'wp-job-manager-company-listings', COMPANY_LISTINGS_PLUGIN_DIR . '/templates/' );

			get_job_manager_template( 'companies-start.php', array(), 'wp-job-manager-company-listings', COMPANY_LISTINGS_PLUGIN_DIR . '/templates/' );
			get_job_manager_template( 'companies-end.php', array(), 'wp-job-manager-company-listings', COMPANY_LISTINGS_PLUGIN_DIR . '/templates/' );

			if ( ! $show_pagination && $show_more ) {
				echo '<a class="load_more_companies" href="#" style="display:none;"><strong>' . __( 'Load more companies', 'wp-job-manager-company-listings' ) . '</strong></a>';
			}

		} else {

			$companies = get_companies( apply_filters( 'company_listings_output_companies_args', array(
				'search_categories' => $categories,
				'orderby'           => $orderby,
				'order'             => $order,
				'posts_per_page'    => $per_page,
				'featured'          => $featured
			) ) );

			if ( $companies->have_posts() ) : ?>

				<?php get_job_manager_template( 'companies-start.php', array(), 'wp-job-manager-company-listings', COMPANY_LISTINGS_PLUGIN_DIR . '/templates/' ); ?>

				<?php while ( $companies->have_posts() ) : $companies->the_post(); ?>
					<?php get_job_manager_template_part( 'content', 'company', 'wp-job-manager-company-listings', COMPANY_LISTINGS_PLUGIN_DIR . '/templates/' ); ?>
				<?php endwhile; ?>

				<?php get_job_manager_template( 'companies-end.php', array(), 'wp-job-manager-company-listings', COMPANY_LISTINGS_PLUGIN_DIR . '/templates/' ); ?>

				<?php if ( $companies->found_posts > $per_page && $show_more ) : ?>

					<?php wp_enqueue_script( 'wp-job-manager-company-listings-ajax-filters' ); ?>

					<?php if ( $show_pagination ) : ?>
						<?php echo get_job_listing_pagination( $companies->max_num_pages ); ?>
					<?php else : ?>
						<a class="load_more_companies" href="#"><strong><?php _e( 'Load more companies', 'wp-job-manager-company-listings' ); ?></strong></a>
					<?php endif; ?>

				<?php endif; ?>

			<?php else :
				do_action( 'company_listings_output_companies_no_results' );
			endif;

			wp_reset_postdata();
		}

		$data_attributes_string = '';
		$data_attributes        = array(
			'location'        => $location,
			'keywords'        => $keywords,
			'show_filters'    => $show_filters ? 'true' : 'false',
			'show_pagination' => $show_pagination ? 'true' : 'false',
			'per_page'        => $per_page,
			'orderby'         => $orderby,
			'order'           => $order,
			'categories'      => implode( ',', $categories )
		);
		if ( ! is_null( $featured ) ) {
			$data_attributes[ 'featured' ] = $featured ? 'true' : 'false';
		}
		foreach ( $data_attributes as $key => $value ) {
			$data_attributes_string .= 'data-' . esc_attr( $key ) . '="' . esc_attr( $value ) . '" ';
		}

		return '<div class="companies" ' . $data_attributes_string . '>' . ob_get_clean() . '</div>';
	}


	/**
	 * output_company_directory function.
	 *
	 * @access public
	 * @param mixed $args
	 * @return void
	 */
	public function output_company_directory( $atts ) {
		global $company_listings;

		ob_start();

		if ( ! company_listings_user_can_browse_companies() ) {
			get_job_manager_template_part( 'access-denied', 'browse-companies', 'wp-job-manager-company-listings', COMPANY_LISTINGS_PLUGIN_DIR . '/templates/' );
			return ob_get_clean();
		}

		extract( $atts = shortcode_atts( apply_filters( 'company_listings_output_companies_defaults', array(
			'per_page'                  => get_option( 'company_listings_per_page' ),
			'order'                     => 'DESC',
			'orderby'                   => 'featured',
			'show_filters'              => true,
			'show_categories'           => get_option( 'company_listings_enable_categories' ),
			'categories'                => '',
			'featured'                  => null, // True to show only featured, false to hide featured, leave null to show both.
			'show_category_multiselect' => get_option( 'company_listings_enable_default_category_multiselect', false ),
			'selected_category'         => '',
			'show_pagination'           => false,
			'show_more'                 => true,
		) ), $atts ) );

		$categories = array_filter( array_map( 'trim', explode( ',', $categories ) ) );
		$keywords   = '';
		$location   = '';

		// String and bool handling
		$show_filters              = $this->string_to_bool( $show_filters );
		$show_categories           = $this->string_to_bool( $show_categories );
		$show_category_multiselect = $this->string_to_bool( $show_category_multiselect );
		$show_more                 = $this->string_to_bool( $show_more );
		$show_pagination           = $this->string_to_bool( $show_pagination );

		if ( ! is_null( $featured ) ) {
			$featured = ( is_bool( $featured ) && $featured ) || in_array( $featured, array( '1', 'true', 'yes' ) ) ? true : false;
		}

		if ( ! empty( $_GET['search_keywords'] ) ) {
			$keywords = sanitize_text_field( $_GET['search_keywords'] );
		}

		if ( ! empty( $_GET['search_category'] ) ) {
			$selected_category = sanitize_text_field( $_GET['search_category'] );
		}

		$companies = get_companies( apply_filters( 'company_listings_output_companies_args', array(
				'search_categories' => $categories,
				'orderby'           => $orderby,
				'order'             => $order,
				'posts_per_page'    => $per_page,
				'featured'          => $featured
		) ) );

		if ( $companies->have_posts() ) : ?>

			<?php get_job_manager_template( 'company-directory-start.php', array(), 'wp-job-manager-company-listings', COMPANY_LISTINGS_PLUGIN_DIR . '/templates/' ); ?>

			<?php while ( $companies->have_posts() ) : $companies->the_post(); ?>
				<?php get_job_manager_template_part( 'company-directory', 'content', 'wp-job-manager-company-listings', COMPANY_LISTINGS_PLUGIN_DIR . '/templates/' ); ?>
			<?php endwhile; ?>

			<?php get_job_manager_template( 'company-directory-end.php', array(), 'wp-job-manager-company-listings', COMPANY_LISTINGS_PLUGIN_DIR . '/templates/' ); ?>

			<?php if ( $companies->found_posts > $per_page && $show_more ) : ?>

				<?php wp_enqueue_script( 'wp-job-manager-company-listings-ajax-filters' ); ?>

				<?php if ( $show_pagination ) : ?>
					<?php echo get_job_listing_pagination( $companies->max_num_pages ); ?>
				<?php else : ?>
					<a class="load_more_companies" href="#"><strong><?php _e( 'Load more companies', 'wp-job-manager-company-listings' ); ?></strong></a>
				<?php endif; ?>

			<?php endif; ?>

		<?php else :
			do_action( 'company_listings_output_companies_no_results' );
		endif;

		wp_reset_postdata();

		$data_attributes_string = '';
		$data_attributes        = array(
			'location'        => $location,
			'keywords'        => $keywords,
			'show_filters'    => $show_filters ? 'true' : 'false',
			'show_pagination' => $show_pagination ? 'true' : 'false',
			'per_page'        => $per_page,
			'orderby'         => $orderby,
			'order'           => $order,
			'categories'      => implode( ',', $categories )
		);
		if ( ! is_null( $featured ) ) {
			$data_attributes[ 'featured' ] = $featured ? 'true' : 'false';
		}
		foreach ( $data_attributes as $key => $value ) {
			$data_attributes_string .= 'data-' . esc_attr( $key ) . '="' . esc_attr( $value ) . '" ';
		}

		return '<div class="companies" ' . $data_attributes_string . '>' . ob_get_clean() . '</div>';
	}

	/**
	 * Output some content when no results were found
	 */
	public function output_no_results() {
		get_job_manager_template( 'content-no-companies-found.php', array(), 'wp-job-manager-company-listings', COMPANY_LISTINGS_PLUGIN_DIR . '/templates/' );
	}

	/**
	 * Get string as a bool
	 * @param  string $value
	 * @return bool
	 */
	public function string_to_bool( $value ) {
		return ( is_bool( $value ) && $value ) || in_array( $value, array( '1', 'true', 'yes' ) ) ? true : false;
	}
}

new WP_Job_Manager_Company_Listings_Shortcodes();

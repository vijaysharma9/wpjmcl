<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * WP_Job_Manager_Company_Listings_Apply class.
 *
 * Handles application forms, and also integration with applications plugin if installed.
 */
class WP_Job_Manager_Company_Listings_Apply {

	private $error   = "";
	private $message = "";

	/**
	 * Constructor
	 */
	public function __construct() {
		add_action( 'wp', array( $this, 'init' ), 20 );
		add_action( 'wp', array( $this, 'apply_with_company_handler' ) );
		add_action( 'submit_company_form_start', array( $this, 'company_form_intro' ) );
	}

	/**
	 * Ensure application areas show the correct content.
	 */
	public function init() {
		global $job_manager;

		$user_companies = $this->get_user_companies();

		/**
		 * What content is shown is based on settings and whether or not the user has companies.
		 */
		if ( empty( $user_companies ) && get_option( 'company_listings_force_company' ) ) {
			remove_all_actions( 'job_manager_application_details_email' );
			remove_all_actions( 'job_manager_application_details_url' );
			add_action( 'job_manager_application_details_email', array( $this, 'force_apply_with_company' ), 20 );
			add_action( 'job_manager_application_details_url', array( $this, 'force_apply_with_company' ), 20 );
		} else {
			if ( get_option( 'company_listings_enable_application', 1 ) ) {
				// If we're forcing application through company manager, we should disable other forms and content.
				if ( get_option( 'company_listings_force_application' ) ) {
					remove_all_actions( 'job_manager_application_details_email' );
				}
				add_action( 'job_manager_application_details_email', array( $this, 'apply_with_company' ), 20 );
			}
			if ( class_exists( 'WP_Job_Manager_Applications' ) && get_option( 'company_listings_enable_application_for_url_method', 1 ) ) {
				// If we're forcing application through company manager, we should disable other forms and content.
				if ( get_option( 'company_listings_force_application' ) ) {
					remove_all_actions( 'job_manager_application_details_url' );
				}
				add_action( 'job_manager_application_details_url', array( $this, 'apply_with_company' ), 20 );
			}
		}
	}

	/**
	 * Company form intro
	 */
	public function company_form_intro() {
		if ( ! empty( $_REQUEST['job_id'] ) ) {
			$job_id = absint( $_REQUEST['job_id'] );

			if ( get_post_type( $job_id ) !== 'job_listing' ) {
				return;
			}

			echo '<p class="applying_for">' . sprintf( __( 'Submit your company below to apply for the job "%s".', 'wp-job-manager-company-listings' ), '<a href="' . get_permalink( $job_id ) . '">' . get_the_title( $job_id ) . '</a>' ) .'</p>';
		}
	}

	/**
	 * Get a user's companies which they can apply with
	 * @return array
	 */
	private function get_user_companies() {
		if ( is_user_logged_in() ) {
			$args = apply_filters( 'company_listings_get_application_form_companies_args', array(
				'post_type'           => 'company_listing',
				'post_status'         => array( 'publish', 'pending', 'hidden' ),
				'ignore_sticky_posts' => 1,
				'posts_per_page'      => -1,
				'orderby'             => 'date',
				'order'               => 'desc',
				'author'              => get_current_user_id()
			) );

			$companies = get_posts( $args );
		} else {
			$companies = array();
		}

		return $companies;
	}

	/**
	 * Allow users to apply to a job with a company
	 */
	public function apply_with_company() {
		get_job_manager_template( 'apply-with-company.php', array( 'companies' => $this->get_user_companies() ), 'wp-job-manager-company-listings', COMPANY_LISTINGS_PLUGIN_DIR . '/templates/' );
	}

	/**
	 * Allow users to apply to a job with a company
	 */
	public function force_apply_with_company() {
		get_job_manager_template( 'force-apply-with-company.php', array(), 'wp-job-manager-company-listings', COMPANY_LISTINGS_PLUGIN_DIR . '/templates/' );
	}

	/**
	 * Send the application email if posted
	 */
	public function apply_with_company_handler() {
		if ( ! empty( $_POST['wp_job_manager_companies_apply_with_company'] ) ) {
			$company_id           = absint( $_POST['company_id'] );
			$job_id              = absint( $_POST['job_id'] );
			$application_message = str_replace( '[nl]', "\n", sanitize_text_field( str_replace( "\n", '[nl]', strip_tags( stripslashes( $_POST['application_message'] ) ) ) ) );

			add_action( 'job_content_start', array( $this, 'apply_with_company_result' ) );
			add_action( 'job_manager_before_job_apply_' . $job_id, array( $this, 'apply_with_company_result' ) );

			try {
				if ( empty( $company_id ) ) {
					throw new Exception( __( 'Please choose a company to apply with', 'wp-job-manager-company-listings' ) );
				}

				if ( empty( $job_id ) ) {
					throw new Exception( __( 'This job cannot be applied for using a company', 'wp-job-manager-company-listings' ) );
				}

				if ( empty( $application_message ) ) {
					throw new Exception( __( 'Please enter a message to include with your application', 'wp-job-manager-company-listings' ) );
				}

				$method = get_the_job_application_method( $job_id );

				if ( "email" !== $method->type && ! ( class_exists( 'WP_Job_Manager_Applications' ) && get_option( 'company_listings_enable_application_for_url_method', 1 ) ) ) {
					throw new Exception( __( 'This job cannot be applied for using a company', 'wp-job-manager-company-listings' ) );
				}

				if ( $this->send_application( $job_id, $company_id, $application_message ) ) {
					$this->message = __( 'Your application has been sent successfully', 'wp-job-manager-company-listings' );
					add_filter( 'job_manager_show_job_apply_' . $job_id, '__return_false' );
				} else {
					throw new Exception( __( 'Error sending application', 'wp-job-manager-company-listings' ) );
				}
			} catch ( Exception $e ) {
				  $this->error = $e->getMessage();
			}
		}
	}

	/**
	 * Sent the application email
	 */
	public static function send_application( $job_id, $company_id, $application_message ) {
		$user            = wp_get_current_user();
		$company_link     = get_company_share_link( $company_id );
		$company_name  = get_the_title( $company_id );
		$company_email = get_post_meta( $company_id, '_company_email', true );
		$method          = get_the_job_application_method( $job_id );
		$sent            = false;
		$attachments     = array();
		$company          = get_post( $company_id );
		$file_paths      = get_company_files( $company );

		foreach ( $file_paths as $file_path ) {
			$attachments[] = str_replace( array( site_url( '/', 'http' ), site_url( '/', 'https' ) ), ABSPATH, get_post_meta( $company_id, '_company_file', true ) );
		}

		if ( empty( $company_email ) ) {
			$company_email = $user->user_email;
		}

		$message     = apply_filters( 'apply_with_company_email_message', array(
			'greeting'      => __( 'Hello', 'wp-job-manager-company-listings' ),
			'position'      => sprintf( "\n\n" . __( 'A company has applied online for the position "%s".', 'wp-job-manager-company-listings' ), get_the_title( $job_id ) ),
			'start_message' => "\n\n-----------\n\n",
			'message'       => $application_message,
			'end_message'   => "\n\n-----------\n\n",
			'view_company'   => sprintf( __( 'You can view their online company here: %s.', 'wp-job-manager-company-listings' ), $company_link ),
			'contact'       => "\n" . sprintf( __( 'Or you can contact them directly at: %s.', 'wp-job-manager-company-listings' ), $company_email ),
		), get_current_user_id(), $job_id, $company_id, $application_message );

		if ( ! empty( $method->raw_email ) ) {
			$headers   = array();
			$headers[] = 'From: ' . $company_name . ' <' . $company_email . '>';
			$headers[] = 'Reply-To: ' . $company_email;

			$sent = wp_mail(
				apply_filters( 'apply_with_company_email_recipient', $method->raw_email, $job_id, $company_id ),
				apply_filters( 'apply_with_company_email_subject', $method->subject, $job_id, $company_id ),
				implode( '', $message ),
				apply_filters( 'apply_with_company_email_headers', $headers, $job_id, $company_id ),
				apply_filters( 'apply_with_company_email_attachments', array_filter( $attachments ), $job_id, $company_id )
			);
		}

		do_action( 'applied_with_company', get_current_user_id(), $job_id, $company_id, $application_message, $sent );

		if ( "email" !== $method->type && class_exists( 'WP_Job_Manager_Applications' ) && get_option( 'company_listings_enable_application_for_url_method', 1 ) ) {
			$sent = true;
		}

		return $sent;
	}

	/**
	 * Show results - errors and messages
	 */
	public function apply_with_company_result() {
		if ( $this->message ) {
			echo '<p class="job-manager-message">' . esc_html( $this->message ) . '</p>';
		} elseif ( $this->error ) {
			echo '<p class="job-manager-error">' . esc_html( $this->error ) . '</p>';
		}
	}
}

<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * WP_Job_Manager_Company_Listings_Email_Notification class.
 */
class WP_Job_Manager_Company_Listings_Email_Notification {

	/**
	 * Constructor
	 */
	public function __construct() {
		if ( get_option( 'company_listings_submission_notification' ) ) {
			add_action( 'company_listings_company_submitted', array( $this, 'new_company_submitted' ) );
		}
	}

	/**
	 * New company notification
	 */
	public function new_company_submitted( $company_id ) {
		include_once( 'admin/class-wp-job-manager-company-listings-writepanels.php' );

		$custom_fields = array_diff_key( WP_Job_Manager_Company_Listings_Writepanels::company_fields(), array( '_company_file' => '' ) );
		$company        = get_post( $company_id );
		$recipient     = get_option( 'company_listings_email_notifications' );
		$recipient     = ! empty( $recipient ) ? $recipient : get_option( 'admin_email' );
		$subject       = sprintf( __( 'New Company Submission From %s', 'wp-job-manager-company-listings' ), $company->post_title );
		$attachments   = array();
		$file_paths    = get_company_files( $company );

		foreach ( $file_paths as $file_path ) {
			$attachments[] = str_replace( array( WP_CONTENT_URL, site_url() ), array( WP_CONTENT_DIR, ABSPATH ), $file_path );
		}

		ob_start();
		get_job_manager_template( 'company_listings-submitted-notification.php', array(
			'company'       => $company,
			'company_id'    => $company_id,
			'custom_fields' => $custom_fields
		), 'wp-job-manager-company-listings', COMPANY_LISTINGS_PLUGIN_DIR . '/templates/' );
		$message = ob_get_clean();

		add_filter( 'wp_mail_from', array( __CLASS__, 'get_from_address' ) );
		add_filter( 'wp_mail_from_name', array( __CLASS__, 'get_from_name' ) );

		wp_mail(
			apply_filters( 'company_listings_new_company_notification_recipient', $recipient, $company_id ),
			apply_filters( 'company_listings_new_company_notification_subject', $subject, $company_id ),
			$message,
			apply_filters( 'company_listings_new_company_notification_headers', '', $company_id ),
			apply_filters( 'company_listings_new_company_notification_attachments', array_filter( $attachments ), $company_id )
		);

		remove_filter( 'wp_mail_from', array( __CLASS__, 'get_from_address' ) );
		remove_filter( 'wp_mail_from_name', array( __CLASS__, 'get_from_name' ) );
	}

	/**
	 * Get from name for email.
	 *
	 * @access public
	 * @return string
	 */
	public static function get_from_name() {
		return wp_specialchars_decode( esc_html( get_bloginfo( 'name' ) ), ENT_QUOTES );
	}

	/**
	 * Get from email address.
	 *
	 * @access public
	 * @return string
	 */
	public static function get_from_address() {
		$site_url  = parse_url( site_url() );
		$nice_host = str_replace( 'www.', '', $site_url['host'] );

		// Basic check that the request URL ends with the domain (leading dot).
		if ( false === stripos( $site_url['host'], '.' ) )
			$nice_host .= '.local';

		return sanitize_email( 'noreply@' . $nice_host );
	}
}

new WP_Job_Manager_Company_Listings_Email_Notification();

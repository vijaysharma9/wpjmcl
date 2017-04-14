<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * WP_Job_Manager_Company_Listings_Settings class.
 */
class WP_Job_Manager_Company_Listings_Settings extends WP_Job_Manager_Settings {

	/**
	 * __construct function.
	 *
	 * @access public
	 * @return void
	 */
	public function __construct() {
		$this->settings_group = 'wp-job-manager-company-listings';
		add_action( 'admin_init', array( $this, 'register_settings' ) );
	}

	/**
	 * init_settings function.
	 *
	 * @access protected
	 * @return void
	 */
	protected function init_settings() {
		// Prepare roles option
		$roles         = get_editable_roles();
		$account_roles = array();

		foreach ( $roles as $key => $role ) {
			if ( $key == 'administrator' ) {
				continue;
			}
			$account_roles[ $key ] = $role['name'];
		}

		$this->settings = apply_filters( 'company_listings_settings',
			array(
				'company_listings' => array(
					__( 'Company Listings', 'wp-job-manager-company-listings' ),
					array(
						array(
							'name'        => 'company_listings_per_page',
							'std'         => '10',
							'placeholder' => '',
							'label'       => __( 'Companies Per Page', 'wp-job-manager-company-listings' ),
							'desc'        => __( 'How many companies should be shown per page by default?', 'wp-job-manager-company-listings' ),
							'attributes'  => array()
						),
						array(
							'name'       => 'company_listings_enable_categories',
							'std'        => '0',
							'label'      => __( 'Categories', 'wp-job-manager-company-listings' ),
							'cb_label'   => __( 'Enable company categories', 'wp-job-manager-company-listings' ),
							'desc'       => __( 'Choose whether to enable company categories. Categories must be setup by an admin for users to choose during job submission.', 'wp-job-manager-company-listings' ),
							'type'       => 'checkbox',
							'attributes' => array()
						),
						array(
							'name'       => 'company_listings_enable_default_category_multiselect',
							'std'        => '0',
							'label'      => __( 'Multi-select Categories', 'wp-job-manager-company-listings' ),
							'cb_label'   => __( 'Enable category multiselect by default', 'wp-job-manager-company-listings' ),
							'desc'       => __( 'If enabled, the category select box will default to a multiselect on the [companies] shortcode.', 'wp-job-manager-company-listings' ),
							'type'       => 'checkbox',
							'attributes' => array()
						),
						array(
							'name'       => 'company_listings_category_filter_type',
							'std'        => 'any',
							'label'      => __( 'Category Filter Type', 'wp-job-manager-company-listings' ),
							'desc'       => __( 'If enabled, the category select box will default to a multiselect on the [companies] shortcode.', 'wp-job-manager-company-listings' ),
							'type'       => 'select',
							'options' => array(
								'any'  => __( 'Companies will be shown if within ANY selected category', 'wp-job-manager-company-listings' ),
								'all' => __( 'Companies will be shown if within ALL selected categories', 'wp-job-manager-company-listings' ),
							)
						),
						array(
							'name'       => 'company_listings_enable_skills',
							'std'        => '0',
							'label'      => __( 'Skills', 'wp-job-manager-company-listings' ),
							'cb_label'   => __( 'Enable company skills', 'wp-job-manager-company-listings' ),
							'desc'       => __( 'Choose whether to enable the company skills field. Skills work like tags and can be added by users during company submission.', 'wp-job-manager-company-listings' ),
							'type'       => 'checkbox',
							'attributes' => array()
						),
						array(
							'name'        => 'company_listings_max_skills',
							'std'         => '',
							'label'       => __( 'Maximum Skills', 'wp-job-manager-company-listings' ),
							'placeholder' => __( 'Unlimited', 'wp-job-manager-company-listings' ),
							'desc'        => __( 'Enter the number of skills per company submission you wish to allow, or leave blank for unlimited skills.', 'wp-job-manager-company-listings' ),
							'type'        => 'input'
						),
						array(
							'name'       => 'company_listings_enable_company_upload',
							'std'        => '0',
							'label'      => __( 'Company Upload', 'wp-job-manager-company-listings' ),
							'cb_label'   => __( 'Enable company upload', 'wp-job-manager-company-listings' ),
							'desc'       => __( 'Choose whether to allow companys to upload a company file.', 'wp-job-manager-company-listings' ),
							'type'       => 'checkbox',
							'attributes' => array()
						)
					),
				),
				'company_submission' => array(
					__( 'Company Submission', 'wp-job-manager-company-listings' ),
					array(
						array(
							'name'       => 'company_listings_user_requires_account',
							'std'        => '1',
							'label'      => __( 'Account Required', 'wp-job-manager' ),
							'cb_label'   => __( 'Submitting listings requires an account', 'wp-job-manager' ),
							'desc'       => __( 'If disabled, non-logged in users will be able to submit listings without creating an account. Please note that this will prevent non-registered users from being able to edit their listings at a later date.', 'wp-job-manager-company-listings' ),
							'type'       => 'checkbox',
							'attributes' => array()
						),
						array(
							'name'       => 'company_listings_enable_registration',
							'std'        => '1',
							'label'      => __( 'Account Creation', 'wp-job-manager-company-listings' ),
							'cb_label'   => __( 'Allow account creation', 'wp-job-manager-company-listings' ),
							'desc'       => __( 'If enabled, non-logged in users will be able to create an account by entering their email address on the company submission form.', 'wp-job-manager-company-listings' ),
							'type'       => 'checkbox',
							'attributes' => array()
						),
						array(
							'name'       => 'company_listings_generate_username_from_email',
							'std'        => '1',
							'label'      => __( 'Account Username', 'wp-job-manager-company-listings' ),
							'cb_label'   => __( 'Automatically Generate Username from Email Address', 'wp-job-manager-company-listings' ),
							'desc'       => __( 'If enabled, a username will be generated from the first part of the user email address. Otherwise, a username field will be shown.', 'wp-job-manager-company-listings' ),
							'type'       => 'checkbox',
							'attributes' => array()
						),
						array(
							'name'       => 'company_listings_registration_role',
							'std'        => 'company',
							'label'      => __( 'Account Role', 'wp-job-manager-company-listings' ),
							'desc'       => __( 'If you enable registration on your submission form, choose a role for the new user.', 'wp-job-manager-company-listings' ),
							'type'       => 'select',
							'options'    => $account_roles
						),
						array(
							'name'       => 'company_listings_submission_requires_approval',
							'std'        => '1',
							'label'      => __( 'Approval Required', 'wp-job-manager-company-listings' ),
							'cb_label'   => __( 'New submissions require admin approval', 'wp-job-manager-company-listings' ),
							'desc'       => __( 'If enabled, new submissions will be inactive, pending admin approval.', 'wp-job-manager-company-listings' ),
							'type'       => 'checkbox',
							'attributes' => array()
						),
						array(
							'name'       => 'company_listings_submission_notification',
							'std'        => '1',
							'label'      => __( 'Email New Submissions', 'wp-job-manager-company-listings' ),
							'cb_label'   => __( 'Email company details to the admin/notification recipient after submission.', 'wp-job-manager-company-listings' ),
							'desc'       => sprintf( __( 'If enabled, all company details for new submissions will be emailed to %s.', 'wp-job-manager-company-listings' ), get_option( 'company_listings_email_notifications' ) ? get_option( 'company_listings_email_notifications' ) : get_option( 'admin_email' ) ),
							'type'       => 'checkbox',
							'attributes' => array()
						),
						array(
							'name' 		  => 'company_listings_email_notifications',
						    'std' 		  => '',
							'placeholder' => get_option( 'admin_email' ),
						    'label' 	  => __( 'Notify Email Address(es)', 'wp-job-manager-company-listings' ),
						    'desc'		  => __( 'Instead of the admin, email notifications to these these folks instead. Comma separate addresses.', 'wp-job-manager-company-listings' ),
						    'type'        => 'input'
						),
						array(
							'name'        => 'company_listings_submission_duration',
							'std'         => '',
							'label'       => __( 'Listing Duration', 'wp-job-manager-company-listings' ),
							'desc'        => __( 'How many <strong>days</strong> listings are live before expiring. Can be left blank to never expire. Expired listings must be relisted to become visible.', 'wp-job-manager-company-listings' ),
							'attributes'  => array(),
							'placeholder' => __( 'Never expire', 'wp-job-manager-company-listings' )
						),
						array(
							'name'       => 'company_listings_autohide',
							'std'        => '',
							'label'      => __( 'Auto-hide Companies', 'wp-job-manager-company-listings' ),
							'desc'       => __( 'How many <strong>days</strong> un-modified companies should be published before being hidden. Can be left blank to never hide companies automaticaly. Companies can re-publish hidden companies form their dashboard.', 'wp-job-manager-company-listings' ),
							'attributes' => array(),
							'placeholder' => __( 'Never auto-hide', 'wp-job-manager-company-listings' )
						),
						array(
							'name'        => 'company_listings_submission_limit',
							'std'         => '',
							'label'       => __( 'Listing Limit', 'wp-job-manager-company-listings' ),
							'desc'        => __( 'How many listings are users allowed to post. Can be left blank to allow unlimited listings per account.', 'wp-job-manager-company-listings' ),
							'attributes'  => array(),
							'placeholder' => __( 'No limit', 'wp-job-manager-company-listings' )
						),
						array(
							'name' 		=> 'company_listings_linkedin_import',
							'std'        => '0',
							'label'      => __( 'Linkedin Import', 'wp-job-manager-company-listings' ),
							'cb_label'   => __( 'Allow import of company data from LinkedIn', 'wp-job-manager-company-listings' ),
							'desc'       => __( 'If enabled, users will be able to login to LinkedIn and have the company submission form automatically populated.', 'wp-job-manager-company-listings' ),
							'type'       => 'checkbox',
							'attributes' => array()
						),
						'api_key' => array(
							'name' 		=> 'job_manager_linkedin_api_key',
							'std' 		=> '',
							'label' 	=> __( 'Linkedin Api Key', 'wp-job-manager-company-listings' ),
							'desc'		=> __( 'Get your API key by creating a new application on https://www.linkedin.com/secure/developer', 'wp-job-manager-company-listings' ),
							'type'      => 'input'
						),
					)
				),
				'company_pages' => array(
					__( 'Pages', 'wp-job-manager' ),
					array(
						array(
							'name' 		=> 'company_listings_submit_company_form_page_id',
							'std' 		=> '',
							'label' 	=> __( 'Submit Company Page', 'wp-job-manager-company-listings' ),
							'desc'		=> __( 'Select the page where you have placed the [submit_company_form] shortcode. This lets the plugin know where the form is located.', 'wp-job-manager-company-listings' ),
							'type'      => 'page'
						),
						array(
							'name' 		=> 'company_listings_company_dashboard_page_id',
							'std' 		=> '',
							'label' 	=> __( 'Company Dashboard Page', 'wp-job-manager-company-listings' ),
							'desc'		=> __( 'Select the page where you have placed the [company_dashboard] shortcode. This lets the plugin know where the dashboard is located.', 'wp-job-manager-company-listings' ),
							'type'      => 'page'
						),
						array(
							'name' 		=> 'company_listings_companies_page_id',
							'std' 		=> '',
							'label' 	=> __( 'Company Listings Page', 'wp-job-manager-company-listings' ),
							'desc'		=> __( 'Select the page where you have placed the [companies] shortcode. This lets the plugin know where the company listings page is located.', 'wp-job-manager-company-listings' ),
							'type'      => 'page'
						),
						array(
							'name' 		=> 'company_listings_company_directory_page_id',
							'std' 		=> '',
							'label' 	=> __( 'Company Directory Page', 'wp-job-manager-company-listings' ),
							'desc'		=> __( 'Select the page where you have placed the [company_directory] shortcode. This lets the plugin know where the company Directory page is located.', 'wp-job-manager-company-listings' ),
							'type'      => 'page'
						),
					)
				),
				'company_visibility' => array(
					__( 'Company Visibility', 'wp-job-manager-company-listings' ),
					array(
						array(
							'name'       => 'company_listings_view_name_capability',
							'std'        => '',
							'label'      => __( 'View Company name Capability', 'wp-job-manager-company-listings' ),
							'type'      => 'input',
							'desc'       => sprintf( __( 'Enter the <a href="%s">capability</a> required in order to view companies names. Supports a comma separated list of roles/capabilities.', 'wp-job-manager-company-listings' ), 'http://codex.wordpress.org/Roles_and_Capabilities' )
						),
						array(
							'name'       => 'company_listings_browse_company_capability',
							'std'        => '',
							'label'      => __( 'Browse Company Capability', 'wp-job-manager-company-listings' ),
							'type'      => 'input',
							'desc'       => sprintf( __( 'Enter the <a href="%s">capability</a> required in order to browse companies. Supports a comma separated list of roles/capabilities.', 'wp-job-manager-company-listings' ), 'http://codex.wordpress.org/Roles_and_Capabilities' )
						),
						array(
							'name'       => 'company_listings_view_company_capability',
							'std'        => '',
							'label'      => __( 'View Company Capability', 'wp-job-manager-company-listings' ),
							'type'      => 'input',
							'desc'       => sprintf( __( 'Enter the <a href="%s">capability</a> required in order to view a single company. Supports a comma separated list of roles/capabilities.', 'wp-job-manager-company-listings' ), 'http://codex.wordpress.org/Roles_and_Capabilities' )
						),
						array(
							'name'       => 'company_listings_contact_company_capability',
							'std'        => '',
							'label'      => __( 'Contact Details Capability', 'wp-job-manager-company-listings' ),
							'type'      => 'input',
							'desc'       => sprintf( __( 'Enter the <a href="%s">capability</a> required in order to view contact details on a company. Supports a comma separated list of roles/capabilities.', 'wp-job-manager-company-listings' ), 'http://codex.wordpress.org/Roles_and_Capabilities' )
						),
					),
				),
			)
		);

		if ( ! class_exists( 'WP_Job_Manager_Applications' ) ) {
			unset( $this->settings['company_application'][1][1] );
		}
	}
}

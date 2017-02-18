<?php
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit();
}

wp_clear_scheduled_hook( 'company_manager_check_for_expired_companies' );

wp_trash_post( get_option( 'company_manager_submit_company_form_page_id' ) );

$options = array(
	'wp_company_manager_version',
	'company_manager_submit_company_form_page_id',
	'company_manager_per_page',
	'company_manager_enable_categories',
	'company_manager_enable_skills',
	'company_manager_enable_company_upload',
	'company_manager_enable_application',
	'company_manager_force_company',
	'company_manager_force_application',
	'company_manager_autohide',
	'company_manager_user_requires_account',
	'company_manager_enable_registration',
	'company_manager_registration_role',
	'company_manager_submission_requires_approval',
	'company_manager_submission_duration',
	'company_manager_linkedin_import',
	'job_manager_linkedin_api_key',
	'company_manager_browse_company_capability',
	'company_manager_view_company_capability',
	'company_manager_contact_company_capability',
	'company_manager_submit_page_slug',
	'company_manager_generate_username_from_email'
);

foreach ( $options as $option ) {
	delete_option( $option );
}

<?php
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit();
}

wp_trash_post( get_option( 'company_listings_submit_company_form_page_id' ) );

$options = array(
	'wp_company_listings_version',
	'company_listings_submit_company_form_page_id',
	'company_listings_per_page',
	'company_listings_enable_categories',
	'company_listings_enable_skills',
	'company_listings_enable_company_upload',
	'company_listings_enable_application',
	'company_listings_force_company',
	'company_listings_force_application',
	'company_listings_user_requires_account',
	'company_listings_enable_registration',
	'company_listings_registration_role',
	'company_listings_submission_requires_approval',
	'company_listings_submission_duration',
	'company_listings_browse_company_capability',
	'company_listings_view_company_capability',
	'company_listings_contact_company_capability',
	'company_listings_submit_page_slug',
	'company_listings_generate_username_from_email'
);

foreach ( $options as $option ) {
	delete_option( $option );
}

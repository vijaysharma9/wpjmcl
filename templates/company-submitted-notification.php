<?php
/**
 * This is the email notification sent to admin when a company is submitted.
 *
 * Note: This is in plain text format
 */
$message             = array();
$message['greeting'] = __( 'Hello', 'wp-job-manager-company-listings' ) . "\n" . "\n";
$message['intro']    = sprintf( __( 'A new company has just been submitted by *%s*. The details of their company are as follows:', 'wp-job-manager-company-listings' ), $company->post_title ) . "\n" . "\n";

// Get admin custom fields and loop through
foreach ( $custom_fields as $meta_key => $field ) {
	if ( ( $meta_value = get_post_meta( $company_id, $meta_key, true ) ) && ! empty( $meta_value ) && is_string( $meta_value ) ) {
		$message_line = ' - ' . sprintf( '%s: %s', $field['label'], esc_html( $meta_value ) ) . "\n";
	} else {
		$message_line = '';
	}
	$message[] = apply_filters( 'company_manager_new_company_notification_meta_row', $message_line, $meta_key, $field );
}

// Show Company Content
$message['content_intro'] = "\n" . __( 'The content of their company is as follows:', 'wp-job-manager-company-listings' ) . "\n" . "\n";
$message['content']       = strip_tags( $company->post_content ) . "\n" . "\n" . '-----------' . "\n" . "\n";

// Output Links
if ( $items = get_post_meta( $company_id, '_links', true ) ) {
	$message['link_start'] = __( 'Links:', 'wp-job-manager-company-listings' ) . "\n" . "\n";
	foreach ( $items as $key => $item ) {
		$message[ 'link_' . $key ] = $item['name'] . ': ' . $item['url'] . "\n";
	}
	$message['link_end'] = "\n" . '-----------' . "\n" . "\n";
}

// Education
if ( $items = get_post_meta( $company_id, '_company_education', true ) ) {
	$message['education_start'] = __( 'Education:', 'wp-job-manager-company-listings' ) . "\n" . "\n";
	foreach ( $items as $key => $item ) {
		$message[ 'education_location_' . $key ]      = sprintf( __( 'Location: %s', 'wp-job-manager-company-listings' ), $item['location'] ) . "\n";
		$message[ 'education_date_' . $key ]          = sprintf( __( 'Date: %s', 'wp-job-manager-company-listings' ), $item['date'] ) . "\n";
		$message[ 'education_qualification_' . $key ] = sprintf( __( 'Qualification: %s', 'wp-job-manager-company-listings' ), $item['qualification'] ) . "\n";
		$message[ 'education_notes_' . $key ]         = $item['notes'] . "\n" . "\n";
	}
	$message['education_end'] = '-----------' . "\n" . "\n";
}

// Experience
if ( $items = get_post_meta( $company_id, '_company_experience', true ) ) {
	$message['experience_start'] = __( 'Experience:', 'wp-job-manager-company-listings' ) . "\n" . "\n";
	foreach ( $items as $key => $item ) {
		$message[ 'experience_employer_' . $key ] = sprintf( __( 'Employer: %s', 'wp-job-manager-company-listings' ), $item['employer'] ) . "\n";
		$message[ 'experience_location_' . $key ] = sprintf( __( 'Date: %s', 'wp-job-manager-company-listings' ), $item['date'] ) . "\n";
		$message[ 'experience_title_' . $key ]    = sprintf( __( 'Job Title: %s', 'wp-job-manager-company-listings' ), $item['job_title'] ) . "\n";
		$message[ 'experience_notes_' . $key ]    = $item['notes'] . "\n" . "\n";
	}
	$message['experience_end'] = '-----------' . "\n" . "\n";
}

$message['view_company_link']       = sprintf( __( 'You can view this company here: %s' ), get_permalink( $company_id ) ) . "\n";
$message['admin_view_company_link'] = sprintf( __( 'You can view/edit this company in the backend by clicking here: %s' ), admin_url( 'post.php?post=' . $company_id . '&action=edit' ) ) . "\n" . "\n";

echo implode( "", apply_filters( 'company_manager_new_company_notification_meta', $message, $company_id, $company ) );

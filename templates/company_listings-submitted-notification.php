<?php
/**
 * This is the email notification sent to admin when a company is submitted.
 *
 * Note: This is in plain text format
 */
$message             = array();
$message['greeting'] = __( 'Hello,', 'wp-job-manager-company-listings' ) . "\n" . "\n";
$message['intro']    = sprintf( __( 'A new company has just been submitted by *%s*. The details of their company are as follows:', 'wp-job-manager-company-listings' ), $company->post_title ) . "\n" . "\n";

// Get admin custom fields and loop through
foreach ( $custom_fields as $meta_key => $field ) {
	if ( ( $meta_value = get_post_meta( $company_id, $meta_key, true ) ) && ! empty( $meta_value ) && is_string( $meta_value ) ) {
		$message_line = ' - ' . sprintf( '%s: %s', $field['label'], esc_html( $meta_value ) ) . "\n";
	} else {
		$message_line = '';
	}
	$message[] = apply_filters( 'company_listings_new_company_notification_meta_row', $message_line, $meta_key, $field );
}

// Show Company Content
$message['content_intro'] = "\n" . __( 'The content of their company is as follows:', 'wp-job-manager-company-listings' ) . "\n" . "\n";
$message['content']       = strip_tags( $company->post_content ) . "\n" . "\n" . '-----------' . "\n" . "\n";

// Output URL(s)
if ( $items = get_post_meta( $company_id, '_links', true ) ) {
	$message['link_start'] = __( 'URL(s):', 'wp-job-manager-company-listings' ) . "\n" . "\n";
	foreach ( $items as $key => $item ) {
		$message[ 'link_' . $key ] = $item['name'] . ': ' . $item['url'] . "\n";
	}
	$message['link_end'] = "\n" . '-----------' . "\n" . "\n";
}

// Output Info(s)
if ( $items = get_post_meta( $company_id, '_info', true ) ) {
	$message['info_start'] = __( 'Info(s):', 'wp-job-manager-company-listings' ) . "\n" . "\n";
	foreach ( $items as $key => $item ) {
		$message[ 'info_' . $key ] = $item['name'] . ': ' . $item['info'] . "\n";
	}
	$message['info_end'] = "\n" . '-----------' . "\n" . "\n";
}

// Perks
if ( $items = get_post_meta( $company_id, '_company_perk', true ) ) {
	$message['perk_start'] = __( 'Perks:', 'wp-job-manager-company-listings' ) . "\n" . "\n";
	foreach ( $items as $key => $item ) {
		$message[ 'perk_notes_' . $key ] = $item['notes'] . "\n" . "\n";
	}
	$message['perk_end'] = '-----------' . "\n" . "\n";
}

// Press
if ( $items = get_post_meta( $company_id, '_company_press', true ) ) {
	$message['press_start'] = __( 'Press:', 'wp-job-manager-company-listings' ) . "\n" . "\n";
	foreach ( $items as $key => $item ) {
		$message[ 'press_' . $key ] = $item['job_title'] . ': ' . $item['notes'] . "\n";
	}
	$message['press_end'] = "\n" . '-----------' . "\n" . "\n";
}

$message['view_company_link']       = sprintf( __( 'You can view this company here: %s' ), get_permalink( $company_id ) ) . "\n";
$message['admin_view_company_link'] = sprintf( __( 'You can view/edit this company in the backend by clicking here: %s' ), admin_url( 'post.php?post=' . $company_id . '&action=edit' ) ) . "\n" . "\n";

echo implode( "", apply_filters( 'company_listings_new_company_notification_meta', $message, $company_id, $company ) );

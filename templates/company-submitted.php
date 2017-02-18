<?php
switch ( $company->post_status ) :
	case 'publish' :
		if ( company_manager_user_can_view_company( $company->ID ) ) {
			printf( '<p class="company-submitted">' . __( 'Your company has been submitted successfully. To view your company <a href="%s">click here</a>.', 'wp-job-manager-company-listings' ) . '</p>', get_permalink( $company->ID ) );
		} else {
			print( '<p class="company-submitted">' . __( 'Your company has been submitted successfully.', 'wp-job-manager-company-listings' ) . '</p>' );
		}
	break;
	case 'pending' :
		print( '<p class="company-submitted">' . __( 'Your company has been submitted successfully and is pending approval.', 'wp-job-manager-company-listings' ) . '</p>' );
	break;
	default :
		do_action( 'company_manager_company_submitted_content_' . str_replace( '-', '_', sanitize_title( $company->post_status ) ), $company );
	break;
endswitch;

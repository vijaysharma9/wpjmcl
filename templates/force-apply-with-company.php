<?php global $post; ?>

<form class="apply_with_company" method="post" action="<?php echo get_permalink( get_option( 'company_manager_submit_company_form_page_id' ) ); ?>">
    <p><?php _e( 'Before applying for this position you need to submit your <strong>online company</strong>. Click the button below to continue.', 'wp-job-manager-company-listings' ); ?></p>
    <p>
        <input type="submit" name="wp_job_manager_companies_apply_with_company_create" value="<?php esc_attr_e( 'Submit Resume', 'wp-job-manager-company-listings' ); ?>" />
        <input type="hidden" name="job_id" value="<?php echo absint( $post->ID ); ?>" />
    </p>
</form>

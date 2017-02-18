<?php global $post;

if ( ! get_option( 'company_manager_force_application' ) ) {
	echo '<hr />';
}

if ( is_user_logged_in() && sizeof( $companies ) ) : ?>
	<form class="apply_with_company" method="post">
		<p><?php _e( 'Apply using your online company; just enter a short message to send your application.', 'wp-job-manager-company-listings' ); ?></p>
		<p>
			<label for="company_id"><?php _e( 'Online company', 'wp-job-manager-company-listings' ); ?>:</label>
			<select name="company_id" id="company_id" required>
				<?php
					foreach ( $companies as $company ) {
						echo '<option value="' . absint( $company->ID ) . '">' . esc_html( $company->post_title ) . '</option>';
					}
				?>
			</select>
		</p>
		<p>
			<label><?php _e( 'Message', 'wp-job-manager-company-listings' ); ?>:</label>
			<textarea name="application_message" cols="20" rows="4" required><?php
				if ( isset( $_POST['application_message'] ) ) {
					echo esc_textarea( stripslashes( $_POST['application_message'] ) );
				} else {
					echo _x( 'To whom it may concern,', 'default cover letter', 'wp-job-manager-company-listings' ) . "\n\n";

					printf( _x( 'I am very interested in the %s position at %s. I believe my skills and work experience make me an ideal company for this role. I look forward to speaking with you soon about this position.', 'default cover letter', 'wp-job-manager-company-listings' ), $post->post_title, get_post_meta( $post->ID, '_company_name', true ) );

					echo "\n\n" . _x( 'Thank you for your consideration.', 'default cover letter', 'wp-job-manager-company-listings' );
				}
			?></textarea>
		</p>
		<p>
			<input type="submit" name="wp_job_manager_companies_apply_with_company" value="<?php esc_attr_e( 'Send Application', 'wp-job-manager-company-listings' ); ?>" />
			<input type="hidden" name="job_id" value="<?php echo absint( $post->ID ); ?>" />
		</p>
	</form>
<?php else : ?>
	<form class="apply_with_company" method="post" action="<?php echo get_permalink( get_option( 'company_manager_submit_company_form_page_id' ) ); ?>">
		<p><?php _e( 'You can apply to this job and others using your online company. Click the link below to submit your online company and email your application to this employer.', 'wp-job-manager-company-listings' ); ?></p>

		<p>
			<input type="submit" name="wp_job_manager_companies_apply_with_company_create" value="<?php esc_attr_e( 'Submit Company &amp; Apply', 'wp-job-manager-company-listings' ); ?>" />
			<input type="hidden" name="job_id" value="<?php echo absint( $post->ID ); ?>" />
		</p>
	</form>
<?php endif; ?>

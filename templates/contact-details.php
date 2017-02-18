<?php
global $resume_preview;

if ( $resume_preview ) {
	return;
}

if ( resume_manager_user_can_view_contact_details( $post->ID ) ) :
	wp_enqueue_script( 'wp-job-manager-company-listings-company-contact-details' );
	?>
	<div class="resume_contact">
		<input class="resume_contact_button" type="button" value="<?php _e( 'Contact', 'wp-job-manager-company-listings' ); ?>" />

		<div class="resume_contact_details">
			<?php do_action( 'resume_manager_contact_details' ); ?>
		</div>
	</div>
<?php else : ?>

	<?php get_job_manager_template_part( 'access-denied', 'contact-details', 'wp-job-manager-company-listings', RESUME_MANAGER_PLUGIN_DIR . '/templates/' ); ?>

<?php endif; ?>
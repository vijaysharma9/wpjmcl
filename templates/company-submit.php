<?php
/**
 * Company Submission Form
 */
if ( ! defined( 'ABSPATH' ) ) exit;

wp_enqueue_script( 'wp-job-manager-company-listings-company-submission' );
?>
<form action="<?php echo $action; ?>" method="post" id="submit-company-form" class="job-manager-form" enctype="multipart/form-data">

	<?php do_action( 'submit_company_form_start' ); ?>

	<?php if ( apply_filters( 'submit_company_form_show_signin', true ) ) : ?>

		<?php get_job_manager_template( 'account-signin.php', array( 'class' => $class ), 'wp-job-manager-company-listings', COMPANY_LISTINGS_PLUGIN_DIR . '/templates/' ); ?>

	<?php endif; ?>

	<?php if ( company_listings_user_can_post_company() ) : ?>

		<?php if ( get_option( 'company_listings_linkedin_import' ) ) : ?>

			<?php get_job_manager_template( 'linkedin-import.php', '', 'wp-job-manager-company-listings', COMPANY_LISTINGS_PLUGIN_DIR . '/templates/' ); ?>

		<?php endif; ?>

		<!-- Company Fields -->
		<?php do_action( 'submit_company_form_company_fields_start' ); ?>

		<?php foreach ( $company_fields as $key => $field ) : ?>
			<fieldset class="fieldset-<?php esc_attr_e( $key ); ?>">
				<label for="<?php esc_attr_e( $key ); ?>"><?php echo $field['label'] . apply_filters( 'submit_company_form_required_label', $field['required'] ? '' : ' <small>' . __( '(optional)', 'wp-job-manager-company-listings' ) . '</small>', $field ); ?></label>
				<div class="field">
					<?php $class->get_field_template( $key, $field ); ?>
				</div>
			</fieldset>
		<?php endforeach; ?>

		<?php do_action( 'submit_company_form_company_fields_end' ); ?>

		<p>
			<?php wp_nonce_field( 'submit_form_posted' ); ?>
			<input type="hidden" name="company_listings_form" value="<?php echo $form; ?>" />
			<input type="hidden" name="company_id" value="<?php echo esc_attr( $company_id ); ?>" />
			<input type="hidden" name="job_id" value="<?php echo esc_attr( $job_id ); ?>" />
			<input type="hidden" name="step" value="<?php echo esc_attr( $step ); ?>" />
			<input type="submit" name="submit_company" class="button" value="<?php esc_attr_e( $submit_button_text ); ?>" />
		</p>

	<?php else : ?>

		<?php do_action( 'submit_company_form_disabled' ); ?>

	<?php endif; ?>

	<?php do_action( 'submit_company_form_end' ); ?>
</form>

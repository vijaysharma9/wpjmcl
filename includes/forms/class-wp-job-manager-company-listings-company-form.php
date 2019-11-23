<?php

/**
 * WP_Job_Manager_Company_Form
 *
 * @since      1.0.4
 */
class WP_Job_Manager_Company_Form extends WP_Job_Manager_Form {
	/**
	 * Processes the form result and can also change view if step is complete.
	 */
	public function process() {

		// reset cookie.
		if (
			isset( $_GET['new'] ) &&
			isset( $_COOKIE['wp-job-manager-submitting-company-id'] ) &&
			isset( $_COOKIE['wp-job-manager-submitting-company-key'] ) &&
			get_post_meta( $_COOKIE['wp-job-manager-submitting-company-id'], '_submitting_key', true ) === $_COOKIE['wp-job-manager-submitting-company-key']
		) {
			delete_post_meta( $_COOKIE['wp-job-manager-submitting-company-id'], '_submitting_key' );
			setcookie( 'wp-job-manager-submitting-company-id', '', time() - 3600, COOKIEPATH, COOKIE_DOMAIN, false );
			setcookie( 'wp-job-manager-submitting-company-key', '', time() - 3600, COOKIEPATH, COOKIE_DOMAIN, false );
			wp_redirect( remove_query_arg( array( 'new', 'key' ), $_SERVER['REQUEST_URI'] ) );
		}

		$step_key = $this->get_step_key( $this->step );

		if ( $step_key && is_callable( $this->steps[ $step_key ]['handler'] ) ) {
			call_user_func( $this->steps[ $step_key ]['handler'] );
		}

		$next_step_key = $this->get_step_key( $this->step );

		// if the step changed, but the next step has no 'view', call the next handler in sequence.
		if ( $next_step_key && $step_key !== $next_step_key && ! is_callable( $this->steps[ $next_step_key ]['view'] ) ) {
			$this->process();
		}
	}
}

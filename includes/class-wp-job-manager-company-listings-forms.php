<?php
/**
 * WP_Job_Manager_Company_Listings_Forms class.
 */
class WP_Job_Manager_Company_Listings_Forms {

	/**
	 * Constructor
	 */
	public function __construct() {
		add_action( 'init', array( $this, 'load_posted_form' ) );
	}

	/**
	 * If a form was posted, load its class so that it can be processed before display.
	 */
	public function load_posted_form() {
		if ( ! empty( $_POST['company_listings_form'] ) ) {
			$this->load_form_class( sanitize_title( $_POST['company_listings_form'] ) );
		}
	}

	/**
	 * Load a form's class
	 *
	 * @param  string $form_name
	 * @return string class name on success, false on failure
	 */
	private function load_form_class( $form_name ) {
		if ( ! class_exists( 'WP_Job_Manager_Form' ) ) {
			include( JOB_MANAGER_PLUGIN_DIR . '/includes/abstracts/abstract-wp-job-manager-form.php' );
		}

		// Now try to load the form_name
		$form_class  = 'WP_Job_Manager_Company_Listings_Form_' . str_replace( '-', '_', $form_name );
		$form_file   = COMPANY_LISTINGS_PLUGIN_DIR . '/includes/forms/class-wp-job-manager-company-listings-form-' . $form_name . '.php';

		if ( class_exists( $form_class ) ) {
			return call_user_func( array( $form_class, 'instance' ) );
		}

		if ( ! file_exists( $form_file ) ) {
			return false;
		}

		if ( ! class_exists( $form_class ) ) {
			include $form_file;
		}

		// Init the form
		return call_user_func( array( $form_class, 'instance' ) );
	}

	/**
	 * get_form function.
	 *
	 * @param string $form_name
	 * @param  array $atts Optional passed attributes
	 * @return string
	 */
	public function get_form( $form_name, $atts = array() ) {
		if ( $form = $this->load_form_class( $form_name ) ) {
			ob_start();
			$form->output( $atts );
			return ob_get_clean();
		}
	}
}
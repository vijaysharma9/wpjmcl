<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

if ( ! class_exists( 'WP_Job_Mananger_Company_Listings_License' ) ) {

	/**
	 * Class for wp job mananger company listings license.
	 */
	class WP_Job_Mananger_Company_Listings_License {

		/**
		 * Constructor.
		 */
		public function __construct() {
			// Define constants
			$this->define_constants();

			add_action( 'admin_menu', array( $this, 'license_menu' ), 13 );
			add_action( 'admin_init', array( $this, 'register_option' ) );
			add_action( 'admin_init', array( $this, 'updater' ) );
			add_action( 'admin_init', array( $this, 'activate_license' ) );
			add_action( 'admin_init', array( $this, 'deactivate_license' ) );
		}

		/**
		 * Define constants.
		 */
		public function define_constants() {
			if ( ! defined( 'WPDRIFT_STORE_URL' ) ) {
				define( 'WPDRIFT_STORE_URL', 'https://wpdrift.com' );
			}

			if ( ! defined( 'WPDRIFT_ITEM_ID' ) ) {
				define( 'WPDRIFT_ITEM_ID', 134 );
			}

			if ( ! defined( 'WPDRIFT_ITEM_NAME' ) ) {
				define( 'WPDRIFT_ITEM_NAME', 'WP Job Manager - Company Listings' );
			}

			if ( ! defined( 'JMCL_LICENSE_PAGE' ) ) {
				define( 'JMCL_LICENSE_PAGE', 'company-listings-license' );
			}
		}

		/**
		 * Register menu.
		 */
		public function license_menu() {
			add_submenu_page( 'edit.php?post_type=company_listings', __( 'License', 'wp-job-manager-company-listings' ), __( 'License', 'wp-job-manager-company-listings' ), 'manage_options', JMCL_LICENSE_PAGE, array( $this, 'license_page' ) );
		}

		/**
		 * Output license page.
		 */
		public function license_page() {
			$license = get_option( 'jmcl_license_key' );
			$status  = get_option( 'jmcl_license_status' );
			?>
			<div class="wrap">
				<h2><?php esc_html_e( 'WP Job Manager - Company Listings License', 'wp-job-manager-company-listings' ); ?></h2>
				<form method="post" action="options.php">

					<?php settings_fields( 'wp_job_manager_company_listings' ); ?>

					<table class="form-table">
						<tbody>
							<tr valign="top">
								<th scope="row" valign="top">
									<?php esc_html_e( 'License Key', 'wp-job-manager-company-listings' ); ?>
								</th>
								<td>
									<input id="jmcl_license_key" name="jmcl_license_key" type="text" class="regular-text" value="<?php esc_attr_e( $license ); ?>" />
									<label class="description" for="jmcl_license_key"><?php esc_html_e( 'Enter your license key', 'wp-job-manager-company-listings' ); ?></label>
								</td>
							</tr>
							<?php if ( '' !== $license ) { ?>
								<tr valign="top">
									<th scope="row" valign="top">
										<?php esc_html_e( 'Activate License', 'wp-job-manager-company-listings' ); ?>
									</th>
									<td>
										<?php if ( $status !== false && $status == 'valid' ) { ?>
											<span style="color:green;"><?php esc_html_e( 'active', 'wp-job-manager-company-listings' ); ?></span>
											<?php wp_nonce_field( 'jmcl_nonce', 'jmcl_nonce' ); ?>
											<input type="submit" class="button-secondary" name="jmcl_license_deactivate" value="<?php esc_attr_e( 'Deactivate License', 'wp-job-manager-company-listings' ); ?>"/>
										<?php } else {
											wp_nonce_field( 'jmcl_nonce', 'jmcl_nonce' ); ?>
											<input type="submit" class="button-secondary" name="jmcl_license_activate" value="<?php esc_html_e( 'Activate License', 'wp-job-manager-company-listings' ); ?>"/>
										<?php } ?>
									</td>
								</tr>
							<?php } ?>
						</tbody>
					</table>
					<?php submit_button(); ?>

				</form>
			<?php
		}

		/**
		 * Initiate updater.
		 */
		public function updater() {
			// retrieve our license key from the DB
			$license_key = trim( get_option( 'jmcl_license_key' ) );

			// setup the updater
			$edd_updater = new WP_Job_Manager_Company_Listings_Updater( WPDRIFT_STORE_URL, COMPANY_LISTINGS_PLUGIN_FILE,
				array(
					'version'   => COMPANY_LISTINGS_VERSION,	// current version number
					'license'   => $license_key,             	// license key (used get_option above to retrieve from DB)
					'item_id'   => WPDRIFT_ITEM_ID,       		// ID of the product
					'item_name' => WPDRIFT_ITEM_NAME,      		// name of the product
					'author'    => 'WPdrift', 					// author of this plugin
					'beta'      => false,
				)
			);
		}

		/**
		 * Creates our settings in the options table.
		 */
		public function register_option() {
			register_setting( 'wp_job_manager_company_listings', 'jmcl_license_key', 'sanitize_license' );
		}

		/**
		 * Sanitize license keys.
		 *
		 * @param      string  $new    The new
		 *
		 * @return     string
		 */
		public function sanitize_license( $new ) {
			$old = get_option( 'jmcl_license_key' );

			if ( $old && $old != $new ) {
				delete_option( 'jmcl_license_status' ); // new license has been entered, so must reactivate
			}

			return $new;
		}

		/**
		 * Handle request to activate license.
		 */
		public function activate_license() {
			// listen for our activate button to be clicked
			if ( isset( $_POST['jmcl_license_activate'] ) ) {

				// run a quick security check
			 	if( ! check_admin_referer( 'jmcl_nonce', 'jmcl_nonce' ) )
					return; // get out if we didn't click the Activate button

				// retrieve the license from the database
				$license = trim( get_option( 'jmcl_license_key' ) );

				// data to send in our API request
				$api_params = array(
					'edd_action' => 'activate_license',
					'license'    => $license,
					'item_id'    => WPDRIFT_ITEM_ID,
					'item_name'  => urlencode( WPDRIFT_ITEM_NAME ), // the name of our product in EDD
					'url'        => home_url(),
				);

				// Call the custom API.
				$response = wp_remote_post( WPDRIFT_STORE_URL, array( 'timeout' => 15, 'sslverify' => false, 'body' => $api_params ) );

				// make sure the response came back okay
				if ( is_wp_error( $response ) || 200 !== wp_remote_retrieve_response_code( $response ) ) {

					if ( is_wp_error( $response ) ) {
						$message = $response->get_error_message();
					} else {
						$message = __( 'An error occurred, please try again.', 'wp-job-manager-company-listings' );
					}

				} else {

					$license_data = json_decode( wp_remote_retrieve_body( $response ) );

					if ( false === $license_data->success ) {

						switch( $license_data->error ) {

							case 'expired' :

								$message = sprintf(
									__( 'Your license key expired on %s.', 'wp-job-manager-company-listings' ),
									date_i18n( get_option( 'date_format' ), strtotime( $license_data->expires, current_time( 'timestamp' ) ) )
								);
								break;

							case 'disabled' :
							case 'revoked' :

								$message = __( 'Your license key has been disabled.', 'wp-job-manager-company-listings' );
								break;

							case 'missing' :

								$message = __( 'Invalid license.', 'wp-job-manager-company-listings' );
								break;

							case 'invalid' :
							case 'site_inactive' :

								$message = __( 'Your license is not active for this URL.', 'wp-job-manager-company-listings' );
								break;

							case 'item_name_mismatch' :

								$message = sprintf( __( 'This appears to be an invalid license key for %s.', 'wp-job-manager-company-listings' ), WPDRIFT_ITEM_NAME );
								break;

							case 'no_activations_left':

								$message = __( 'Your license key has reached its activation limit.', 'wp-job-manager-company-listings' );
								break;

							default :

								$message = __( 'An error occurred, please try again.', 'wp-job-manager-company-listings' );
								break;
						}

					}

				}

				// Check if anything passed on a message constituting a failure
				if ( ! empty( $message ) ) {
					$base_url = admin_url( 'edit.php?post_type=company_listings&page=' . JMCL_LICENSE_PAGE );
					$redirect = add_query_arg( array( 'sl_activation' => 'false', 'message' => urlencode( $message ) ), $base_url );

					wp_redirect( $redirect );
					exit();
				}

				// $license_data->license will be either "valid" or "invalid"

				update_option( 'jmcl_license_status', $license_data->license );
				wp_redirect( admin_url( 'edit.php?post_type=company_listings&page=' . JMCL_LICENSE_PAGE ) );
				exit();
			}
		}

		/**
		 * Handle request to deactivate license.
		 */
		public function deactivate_license() {
			// listen for our activate button to be clicked
			if ( isset( $_POST['jmcl_license_deactivate'] ) ) {

				// run a quick security check
			 	if ( ! check_admin_referer( 'jmcl_nonce', 'jmcl_nonce' ) )
					return; // get out if we didn't click the Activate button

				// retrieve the license from the database
				$license = trim( get_option( 'jmcl_license_key' ) );

				// data to send in our API request
				$api_params = array(
					'edd_action' => 'deactivate_license',
					'license'    => $license,
					'item_id'    => WPDRIFT_ITEM_ID,
					'item_name'  => urlencode( WPDRIFT_ITEM_NAME ), // the name of our product in EDD
					'url'        => home_url(),
				);

				// Call the custom API.
				$response = wp_remote_post( WPDRIFT_STORE_URL, array( 'timeout' => 15, 'sslverify' => false, 'body' => $api_params ) );

				// make sure the response came back okay
				if ( is_wp_error( $response ) || 200 !== wp_remote_retrieve_response_code( $response ) ) {

					if ( is_wp_error( $response ) ) {
						$message = $response->get_error_message();
					} else {
						$message = __( 'An error occurred, please try again.', 'wp-job-manager-company-listings' );
					}

					$base_url = admin_url( 'edit.php?post_type=company_listings&page=' . JMCL_LICENSE_PAGE );
					$redirect = add_query_arg( array( 'sl_activation' => 'false', 'message' => urlencode( $message ) ), $base_url );

					wp_redirect( $redirect );
					exit();
				}

				// decode the license data
				$license_data = json_decode( wp_remote_retrieve_body( $response ) );

				// $license_data->license will be either "deactivated" or "failed"
				if ( $license_data->license == 'deactivated' ) {
					delete_option( 'jmcl_license_status' );
				}

				wp_redirect( admin_url( 'edit.php?post_type=company_listings&page=' . JMCL_LICENSE_PAGE ) );
				exit();

			}
		}

	}

}

new WP_Job_Mananger_Company_Listings_License();

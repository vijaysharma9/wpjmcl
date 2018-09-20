<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * WP_Job_Manager_Company_Listings_Settings class.
 */
class WP_Job_Manager_Company_Listings_Settings {

	/**
	 * Our Settings.
	 *
	 * @var        array          Settings.
	 */
	private $settings = array();

	/**
	 * __construct function.
	 *
	 * @access     public
	 */
	public function __construct() {
		$this->settings_group = 'wp-job-manager-company-listings';
		add_action( 'admin_init', array( $this, 'register_settings' ) );
	}

	/**
	 * init_settings function.
	 *
	 * @access     protected
	 */
	protected function init_settings() {
		// Prepare roles option
		$roles         = get_editable_roles();
		$account_roles = array();

		foreach ( $roles as $key => $role ) {
			if ( $key == 'administrator' ) {
				continue;
			}
			$account_roles[ $key ] = $role['name'];
		}

		$this->settings = apply_filters( 'company_listings_settings',
			array(
				'company_listings' => array(
					__( 'Company Listings', 'wp-job-manager-company-listings' ),
					array(
						array(
							'name'        => 'company_listings_per_page',
							'std'         => '10',
							'placeholder' => '',
							'label'       => __( 'Companies Per Page', 'wp-job-manager-company-listings' ),
							'desc'        => __( 'How many companies should be shown per page by default?', 'wp-job-manager-company-listings' ),
							'attributes'  => array()
						),
						array(
							'name'       => 'company_listings_enable_categories',
							'std'        => '0',
							'label'      => __( 'Categories', 'wp-job-manager-company-listings' ),
							'cb_label'   => __( 'Enable company categories', 'wp-job-manager-company-listings' ),
							'desc'       => __( 'Choose whether to enable company categories. Categories must be setup by an admin for users to choose during job submission.', 'wp-job-manager-company-listings' ),
							'type'       => 'checkbox',
							'attributes' => array()
						),
						array(
							'name'       => 'company_listings_enable_default_category_multiselect',
							'std'        => '0',
							'label'      => __( 'Multi-select Categories', 'wp-job-manager-company-listings' ),
							'cb_label'   => __( 'Enable category multiselect by default', 'wp-job-manager-company-listings' ),
							'desc'       => __( 'If enabled, the category select box will default to a multiselect on the [companies] shortcode.', 'wp-job-manager-company-listings' ),
							'type'       => 'checkbox',
							'attributes' => array()
						),
						array(
							'name'       => 'company_listings_category_filter_type',
							'std'        => 'any',
							'label'      => __( 'Category Filter Type', 'wp-job-manager-company-listings' ),
							'desc'       => __( 'If enabled, the category select box will default to a multiselect on the [companies] shortcode.', 'wp-job-manager-company-listings' ),
							'type'       => 'select',
							'options' => array(
								'any'  => __( 'Companies will be shown if within ANY selected category', 'wp-job-manager-company-listings' ),
								'all' => __( 'Companies will be shown if within ALL selected categories', 'wp-job-manager-company-listings' ),
							)
						),
						array(
							'name'       => 'company_listings_enable_skills',
							'std'        => '0',
							'label'      => __( 'Skills', 'wp-job-manager-company-listings' ),
							'cb_label'   => __( 'Enable company skills', 'wp-job-manager-company-listings' ),
							'desc'       => __( 'Choose whether to enable the company skills field. Skills work like tags and can be added by users during company submission.', 'wp-job-manager-company-listings' ),
							'type'       => 'checkbox',
							'attributes' => array()
						),
						array(
							'name'        => 'company_listings_max_skills',
							'std'         => '',
							'label'       => __( 'Maximum Skills', 'wp-job-manager-company-listings' ),
							'placeholder' => __( 'Unlimited', 'wp-job-manager-company-listings' ),
							'desc'        => __( 'Enter the number of skills per company submission you wish to allow, or leave blank for unlimited skills.', 'wp-job-manager-company-listings' ),
							'type'        => 'input'
						),
						array(
							'name'       => 'company_listings_enable_company_upload',
							'std'        => '0',
							'label'      => __( 'Company Upload', 'wp-job-manager-company-listings' ),
							'cb_label'   => __( 'Enable company upload', 'wp-job-manager-company-listings' ),
							'desc'       => __( 'Choose whether to allow companys to upload a company file.', 'wp-job-manager-company-listings' ),
							'type'       => 'checkbox',
							'attributes' => array()
						),
						array(
							'name'       => 'company_only_self',
							'std'        => '0',
							'label'      => __( 'Company Dropdown', 'wp-job-manager-company-listings' ),
							'cb_label'   => __( 'Show only self companies', 'wp-job-manager-company-listings' ),
							'desc'       => __( 'Choose whether to show only self companies in the company name dropdown.', 'wp-job-manager-company-listings' ),
							'type'       => 'checkbox',
							'attributes' => array()
						),
					),
				),
				'company_submission' => array(
					__( 'Company Submission', 'wp-job-manager-company-listings' ),
					array(
						array(
							'name'       => 'company_listings_user_requires_account',
							'std'        => '1',
							'label'      => __( 'Account Required', 'wp-job-manager' ),
							'cb_label'   => __( 'Submitting listings requires an account', 'wp-job-manager' ),
							'desc'       => __( 'If disabled, non-logged in users will be able to submit listings without creating an account. Please note that this will prevent non-registered users from being able to edit their listings at a later date.', 'wp-job-manager-company-listings' ),
							'type'       => 'checkbox',
							'attributes' => array()
						),
						array(
							'name'       => 'company_listings_enable_registration',
							'std'        => '1',
							'label'      => __( 'Account Creation', 'wp-job-manager-company-listings' ),
							'cb_label'   => __( 'Allow account creation', 'wp-job-manager-company-listings' ),
							'desc'       => __( 'If enabled, non-logged in users will be able to create an account by entering their email address on the company submission form.', 'wp-job-manager-company-listings' ),
							'type'       => 'checkbox',
							'attributes' => array()
						),
						array(
							'name'       => 'company_listings_generate_username_from_email',
							'std'        => '1',
							'label'      => __( 'Account Username', 'wp-job-manager-company-listings' ),
							'cb_label'   => __( 'Automatically Generate Username from Email Address', 'wp-job-manager-company-listings' ),
							'desc'       => __( 'If enabled, a username will be generated from the first part of the user email address. Otherwise, a username field will be shown.', 'wp-job-manager-company-listings' ),
							'type'       => 'checkbox',
							'attributes' => array()
						),
						array(
							'name'       => 'company_listings_registration_role',
							'std'        => 'company',
							'label'      => __( 'Account Role', 'wp-job-manager-company-listings' ),
							'desc'       => __( 'If you enable registration on your submission form, choose a role for the new user.', 'wp-job-manager-company-listings' ),
							'type'       => 'select',
							'options'    => $account_roles
						),
						array(
							'name'       => 'company_listings_submission_requires_approval',
							'std'        => '1',
							'label'      => __( 'Approval Required', 'wp-job-manager-company-listings' ),
							'cb_label'   => __( 'New submissions require admin approval', 'wp-job-manager-company-listings' ),
							'desc'       => __( 'If enabled, new submissions will be inactive, pending admin approval.', 'wp-job-manager-company-listings' ),
							'type'       => 'checkbox',
							'attributes' => array()
						),
						array(
							'name'       => 'company_listings_submission_notification',
							'std'        => '1',
							'label'      => __( 'Email New Submissions', 'wp-job-manager-company-listings' ),
							'cb_label'   => __( 'Email company details to the admin/notification recipient after submission.', 'wp-job-manager-company-listings' ),
							'desc'       => sprintf( __( 'If enabled, all company details for new submissions will be emailed to %s.', 'wp-job-manager-company-listings' ), get_option( 'company_listings_email_notifications' ) ? get_option( 'company_listings_email_notifications' ) : get_option( 'admin_email' ) ),
							'type'       => 'checkbox',
							'attributes' => array()
						),
						array(
							'name' 		  => 'company_listings_email_notifications',
						    'std' 		  => '',
							'placeholder' => get_option( 'admin_email' ),
						    'label' 	  => __( 'Notify Email Address(es)', 'wp-job-manager-company-listings' ),
						    'desc'		  => __( 'Instead of the admin, email notifications to these these folks instead. Comma separate addresses.', 'wp-job-manager-company-listings' ),
						    'type'        => 'input'
						),
						array(
							'name'        => 'company_listings_submission_limit',
							'std'         => '',
							'label'       => __( 'Listing Limit', 'wp-job-manager-company-listings' ),
							'desc'        => __( 'How many listings are users allowed to post. Can be left blank to allow unlimited listings per account.', 'wp-job-manager-company-listings' ),
							'attributes'  => array(),
							'placeholder' => __( 'No limit', 'wp-job-manager-company-listings' )
						),
					)
				),
				'company_pages' => array(
					__( 'Pages', 'wp-job-manager' ),
					array(
						array(
							'name' 		=> 'company_listings_submit_company_form_page_id',
							'std' 		=> '',
							'label' 	=> __( 'Submit Company Page', 'wp-job-manager-company-listings' ),
							'desc'		=> __( 'Select the page where you have placed the [submit_company_form] shortcode. This lets the plugin know where the form is located.', 'wp-job-manager-company-listings' ),
							'type'      => 'page'
						),
						array(
							'name' 		=> 'company_listings_company_dashboard_page_id',
							'std' 		=> '',
							'label' 	=> __( 'Company Dashboard Page', 'wp-job-manager-company-listings' ),
							'desc'		=> __( 'Select the page where you have placed the [company_dashboard] shortcode. This lets the plugin know where the dashboard is located.', 'wp-job-manager-company-listings' ),
							'type'      => 'page'
						),
						array(
							'name' 		=> 'company_listings_companies_page_id',
							'std' 		=> '',
							'label' 	=> __( 'Company Listings Page', 'wp-job-manager-company-listings' ),
							'desc'		=> __( 'Select the page where you have placed the [companies] shortcode. This lets the plugin know where the company listings page is located.', 'wp-job-manager-company-listings' ),
							'type'      => 'page'
						),
						array(
							'name' 		=> 'company_listings_company_directory_page_id',
							'std' 		=> '',
							'label' 	=> __( 'Company Directory Page', 'wp-job-manager-company-listings' ),
							'desc'		=> __( 'Select the page where you have placed the [company_directory] shortcode. This lets the plugin know where the company Directory page is located.', 'wp-job-manager-company-listings' ),
							'type'      => 'page'
						),
					)
				),
				'company_visibility' => array(
					__( 'Company Visibility', 'wp-job-manager-company-listings' ),
					array(
						array(
							'name'       => 'company_listings_view_name_capability',
							'std'        => '',
							'label'      => __( 'View Company name Capability', 'wp-job-manager-company-listings' ),
							'type'      => 'input',
							'desc'       => sprintf( __( 'Enter the <a href="%s">capability</a> required in order to view companies names. Supports a comma separated list of roles/capabilities.', 'wp-job-manager-company-listings' ), 'http://codex.wordpress.org/Roles_and_Capabilities' )
						),
						array(
							'name'       => 'company_listings_browse_company_capability',
							'std'        => '',
							'label'      => __( 'Browse Company Capability', 'wp-job-manager-company-listings' ),
							'type'      => 'input',
							'desc'       => sprintf( __( 'Enter the <a href="%s">capability</a> required in order to browse companies. Supports a comma separated list of roles/capabilities.', 'wp-job-manager-company-listings' ), 'http://codex.wordpress.org/Roles_and_Capabilities' )
						),
						array(
							'name'       => 'company_listings_view_company_capability',
							'std'        => '',
							'label'      => __( 'View Company Capability', 'wp-job-manager-company-listings' ),
							'type'      => 'input',
							'desc'       => sprintf( __( 'Enter the <a href="%s">capability</a> required in order to view a single company. Supports a comma separated list of roles/capabilities.', 'wp-job-manager-company-listings' ), 'http://codex.wordpress.org/Roles_and_Capabilities' )
						),
						array(
							'name'       => 'company_listings_contact_company_capability',
							'std'        => '',
							'label'      => __( 'Contact Details Capability', 'wp-job-manager-company-listings' ),
							'type'      => 'input',
							'desc'       => sprintf( __( 'Enter the <a href="%s">capability</a> required in order to view contact details on a company. Supports a comma separated list of roles/capabilities.', 'wp-job-manager-company-listings' ), 'http://codex.wordpress.org/Roles_and_Capabilities' )
						),
					),
				),
			)
		);
	}

	/**
	 * register_settings function.
	 *
	 * @access     public
	 */
	public function register_settings() {
		$this->init_settings();

		foreach ( $this->settings as $section ) {
			foreach ( $section[1] as $option ) {
				if ( isset( $option['std'] ) )
					add_option( $option['name'], $option['std'] );
				register_setting( $this->settings_group, $option['name'] );
			}
		}
	}

	/**
	 * output function.
	 *
	 * @access     public
	 */
	public function output() {
		$this->init_settings();
		?>
		<div class="wrap wp-job-manager-company-listings-settings-wrap">
			<form class="wp-job-manager-company-listings-options" method="post" action="options.php">

				<?php settings_fields( $this->settings_group ); ?>

				<h2 class="nav-tab-wrapper">
					<?php
					foreach ( $this->settings as $key => $section ) {
						echo '<a href="#settings-' . sanitize_title( $key ) . '" class="nav-tab">' . esc_html( $section[0] ) . '</a>';
					}
					?>
				</h2>

				<?php
				if ( ! empty( $_GET['settings-updated'] ) ) {
					flush_rewrite_rules();
					echo '<div class="updated fade wp-job-manager-company-listings-updated"><p>' . __( 'Settings successfully saved', 'wp-job-manager-company-listings' ) . '</p></div>';
				}

				foreach ( $this->settings as $key => $section ) {

					echo '<div id="settings-' . sanitize_title( $key ) . '" class="settings_panel">';

					echo '<table class="form-table">';

					foreach ( $section[1] as $option ) {

						$placeholder    = ( ! empty( $option['placeholder'] ) ) ? 'placeholder="' . $option['placeholder'] . '"' : '';
						$class          = ! empty( $option['class'] ) ? $option['class'] : '';
						$value          = get_option( $option['name'] );
						$option['type'] = ! empty( $option['type'] ) ? $option['type'] : '';
						$attributes     = array();

						if ( ! empty( $option['attributes'] ) && is_array( $option['attributes'] ) )
							foreach ( $option['attributes'] as $attribute_name => $attribute_value )
								$attributes[] = esc_attr( $attribute_name ) . '="' . esc_attr( $attribute_value ) . '"';

						echo '<tr valign="top" class="' . $class . '"><th scope="row"><label for="setting-' . $option['name'] . '">' . $option['label'] . '</a></th><td>';

						switch ( $option['type'] ) {

							case "checkbox" :

								?><label><input id="setting-<?php echo $option['name']; ?>" name="<?php echo $option['name']; ?>" type="checkbox" value="1" <?php echo implode( ' ', $attributes ); ?> <?php checked( '1', $value ); ?> /> <?php echo $option['cb_label']; ?></label><?php

								if ( $option['desc'] )
									echo ' <p class="description">' . $option['desc'] . '</p>';

								break;
							case "textarea" :

								?><textarea id="setting-<?php echo $option['name']; ?>" class="large-text" cols="50" rows="3" name="<?php echo $option['name']; ?>" <?php echo implode( ' ', $attributes ); ?> <?php echo $placeholder; ?>><?php echo esc_textarea( $value ); ?></textarea><?php

								if ( $option['desc'] )
									echo ' <p class="description">' . $option['desc'] . '</p>';

								break;
							case "select" :

								?><select id="setting-<?php echo $option['name']; ?>" class="regular-text" name="<?php echo $option['name']; ?>" <?php echo implode( ' ', $attributes ); ?>><?php
								foreach( $option['options'] as $key => $name )
									echo '<option value="' . esc_attr( $key ) . '" ' . selected( $value, $key, false ) . '>' . esc_html( $name ) . '</option>';
								?></select><?php

								if ( $option['desc'] ) {
									echo ' <p class="description">' . $option['desc'] . '</p>';
								}

								break;
							case "page" :

								$args = array(
									'name'             => $option['name'],
									'id'               => $option['name'],
									'sort_column'      => 'menu_order',
									'sort_order'       => 'ASC',
									'show_option_none' => __( '--no page--', 'wp-job-manager-company-listings' ),
									'echo'             => false,
									'selected'         => absint( $value )
								);

								echo str_replace(' id=', " data-placeholder='" . __( 'Select a page&hellip;', 'wp-job-manager-company-listings' ) .  "' id=", wp_dropdown_pages( $args ) );

								if ( $option['desc'] ) {
									echo ' <p class="description">' . $option['desc'] . '</p>';
								}

								break;
							case "password" :

								?><input id="setting-<?php echo $option['name']; ?>" class="regular-text" type="password" name="<?php echo $option['name']; ?>" value="<?php esc_attr_e( $value ); ?>" <?php echo implode( ' ', $attributes ); ?> <?php echo $placeholder; ?> /><?php

								if ( $option['desc'] ) {
									echo ' <p class="description">' . $option['desc'] . '</p>';
								}

								break;
							case "number" :
								?><input id="setting-<?php echo $option['name']; ?>" class="regular-text" type="number" name="<?php echo $option['name']; ?>" value="<?php esc_attr_e( $value ); ?>" <?php echo implode( ' ', $attributes ); ?> <?php echo $placeholder; ?> /><?php

								if ( $option['desc'] ) {
									echo ' <p class="description">' . $option['desc'] . '</p>';
								}
								break;
							case "" :
							case "input" :
							case "text" :
								?><input id="setting-<?php echo $option['name']; ?>" class="regular-text" type="text" name="<?php echo $option['name']; ?>" value="<?php esc_attr_e( $value ); ?>" <?php echo implode( ' ', $attributes ); ?> <?php echo $placeholder; ?> /><?php

								if ( $option['desc'] ) {
									echo ' <p class="description">' . $option['desc'] . '</p>';
								}
								break;
							default :
								do_action( 'wp_bp_events_calendar_admin_field_' . $option['type'], $option, $attributes, $value, $placeholder );
								break;

						}

						echo '</td></tr>';
					}

					echo '</table></div>';

				}
				?>
				<p class="submit">
					<input type="submit" class="button-primary" value="<?php _e( 'Save Changes', 'wp-job-manager-company-listings' ); ?>" />
				</p>
			</form>
		</div>
		<script type="text/javascript">
			jQuery('.nav-tab-wrapper a').click(function() {
				if ( '#' !== jQuery(this).attr( 'href' ).substr( 0, 1 ) ) {
					return false;
				}
				jQuery('.settings_panel').hide();
				jQuery('.nav-tab-active').removeClass('nav-tab-active');
				jQuery( jQuery(this).attr('href') ).show();
				jQuery(this).addClass('nav-tab-active');
				window.location.hash = jQuery(this).attr('href');
				jQuery( 'form.wp-job-manager-company-listings-options' ).attr( 'action', 'options.php' + jQuery(this).attr( 'href' ) );
				window.scrollTo( 0, 0 );
				return false;
			});
			var goto_hash = window.location.hash;
			if ( '#' === goto_hash.substr( 0, 1 ) ) {
				jQuery( 'form.wp-job-manager-company-listings-options' ).attr( 'action', 'options.php' + jQuery(this).attr( 'href' ) );
			}
			if ( goto_hash ) {
				var the_tab = jQuery( 'a[href="' + goto_hash + '"]' );
				if ( the_tab.length > 0 ) {
					the_tab.click();
				} else {
					jQuery( '.nav-tab-wrapper a:first' ).click();
				}
			} else {
				jQuery( '.nav-tab-wrapper a:first' ).click();
			}
		</script>
		<?php
	}

}

<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * WP_Job_Manager_Company_Listings_Form_Submit_Companies class.
 */
class WP_Job_Manager_Company_Listings_Form_Submit_Company extends WP_Job_Manager_Company_Form {

	/**
	 * Form name.
	 *
	 * @var        string
	 */
	public $form_name = 'submit-company';

	/**
	 * Company ID.
	 *
	 * @access     protected
	 * @var        int
	 */
	protected $company_id;

	/**
	 * Job listing ID.
	 *
	 * @access     protected
	 * @var        int
	 */
	protected $job_id;

	/**
	 * Preview company
	 *
	 * @access     protected
	 * @var        string
	 */
	protected $preview_company;

	/**
	 * Stores static instance of class.
	 *
	 * @access     protected
	 *
	 * @var        WP_Job_Manager_Company_Listings_Form_Submit_Company          The single instance of the class
	 */
	protected static $_instance = null;

	/**
	 * Returns static instance of class.
	 *
	 * @return     self
	 */
	public static function instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

	/**
	 * Constructor.
	 */
	public function __construct() {
		add_action( 'wp', array( $this, 'process' ) );

		$this->steps  = (array) apply_filters(
			'submit_company_steps',
			array(
				'submit' => array(
					'name'     => __( 'Submit Details', 'wp-job-manager-company-listings' ),
					'view'     => array( $this, 'submit' ),
					'handler'  => array( $this, 'submit_handler' ),
					'priority' => 10,
				),
				'preview' => array(
					'name'     => __( 'Preview', 'wp-job-manager-company-listings' ),
					'view'     => array( $this, 'preview' ),
					'handler'  => array( $this, 'preview_handler' ),
					'priority' => 20,
				),
				'done' => array(
					'name'     => __( 'Done', 'wp-job-manager-company-listings' ),
					'view'     => array( $this, 'done' ),
					'handler'  => '',
					'priority' => 30,
				),
			)
		);

		uasort( $this->steps, array( $this, 'sort_by_priority' ) );

		// Get step/company
		if ( ! empty( $_REQUEST['step'] ) ) {
			$this->step = is_numeric( $_REQUEST['step'] ) ? max( absint( $_REQUEST['step'] ), 0 ) : array_search( $_REQUEST['step'], array_keys( $this->steps ) );
		}

		$this->company_id = ! empty( $_REQUEST['company_id'] ) ? absint( $_REQUEST[ 'company_id' ] ) : 0;
		$this->job_id = ! empty( $_REQUEST['job_id'] ) ? absint( $_REQUEST[ 'job_id' ] ) : 0;

		// Allow resuming from cookie.
		$this->resume_edit = false;

		if ( ! isset( $_GET['new'] ) && ! empty( $_COOKIE['wp-job-manager-submitting-company-id'] ) && ! empty( $_COOKIE['wp-job-manager-submitting-company-key'] ) ) {
			$company_id = absint( $_COOKIE['wp-job-manager-submitting-company-id'] );
			$company_status = get_post_status( $company_id );

			if ( 'preview' === $company_status && get_post_meta( $company_id, '_submitting_key', true ) === $_COOKIE['wp-job-manager-submitting-company-key'] ) {
				$this->company_id = $company_id;
				$this->resume_edit = get_post_meta( $company_id, '_submitting_key', true );
			}
		}

		// Load company details
		if ( $this->company_id ) {
			$company_status = get_post_status( $this->company_id );

			if ( 0 === $this->step && ! in_array( $company_status, apply_filters( 'company_listings_valid_submit_company_statuses', array( 'preview' ) ) ) && empty( $_POST['company_application_submit_button'] ) ) {
				$this->company_id = 0;
				$this->job_id = 0;
				$this->step = 0;
			}
		}
	}

	/**
	 * Get the submitted company ID
	 * @return int
	 */
	public function get_company_id() {
		return absint( $this->company_id );
	}

	/**
	 * Get the job ID if applying
	 * @return int
	 */
	public function get_job_id() {
		return absint( $this->job_id );
	}

	/**
	 * Get a field from either company manager or job manager
	 */
	public function get_field_template( $key, $field ) {
		switch ( $field['type'] ) {
			case 'repeated' :
			case 'perk' :
			case 'press' :
			case 'links' :
				get_job_manager_template( 'form-fields/repeated-field.php', array( 'key' => $key, 'field' => $field, 'class' => $this ), 'wp-job-manager-company-listings', COMPANY_LISTINGS_PLUGIN_DIR . '/templates/' );
			break;
			default :
				get_job_manager_template( 'form-fields/' . $field['type'] . '-field.php', array( 'key' => $key, 'field' => $field, 'class' => $this ) );
			break;
		}
	}

	/**
	 * init_fields function.
	 */
	public function init_fields() {
		if ( $this->fields ) {
			return;
		}
		if ( $max = get_option( 'company_listings_max_skills' ) ) {
			$max = ' ' . sprintf( __( 'Maximum of %d.', 'wp-job-manager-company-listings' ), $max );
		}

		$this->fields = apply_filters( 'submit_company_form_fields', array(
			'company_fields' => array(
				'company_name' => array(
					'label'       => __( 'Name', 'wp-job-manager-company-listings' ),
					'type'        => 'text',
					'required'    => true,
					'placeholder' => __( 'Company name', 'wp-job-manager-company-listings' ),
					'priority'    => 1,
				),
				'company_email' => array(
					'label'       => __( 'Email', 'wp-job-manager-company-listings' ),
					'type'        => 'text',
					'sanitizer'   => 'email',
					'required'    => true,
					'placeholder' => __( 'contact@companydomain.com', 'wp-job-manager-company-listings' ),
					'priority'    => 2,
				),
				'company_tagline' => array(
					'label'       => __( 'Tagline', 'wp-job-manager-company-listings' ),
					'type'        => 'text',
					'required'    => true,
					'placeholder' => __( 'e.g. "Web Developer"', 'wp-job-manager-company-listings' ),
					'priority'    => 3,
				),
				'company_location' => array(
					'label'       => __( 'Location', 'wp-job-manager-company-listings' ),
					'type'        => 'text',
					'required'    => true,
					'placeholder' => __( 'e.g. "London, UK", "New York", "Houston, TX"', 'wp-job-manager-company-listings' ),
					'priority'    => 4,
				),
				'company_logo' => array(
					'label'       => __( 'Logo', 'wp-job-manager-company-listings' ),
					'type'        => 'file',
					'required'    => false,
					'placeholder' => '',
					'priority'    => 5,
					'ajax'        => true,
					'allowed_mime_types' => array(
						'jpg'  => 'image/jpeg',
						'jpeg' => 'image/jpeg',
						'gif'  => 'image/gif',
						'png'  => 'image/png'
					)
				),
				'company_video' => array(
					'label'       => __( 'Video', 'wp-job-manager-company-listings' ),
					'type'        => 'text',
					'sanitizer'   => 'url',
					'required'    => false,
					'priority'    => 6,
					'placeholder' => __( 'A link to a video about yourself', 'wp-job-manager-company-listings' ),
				),
				'company_website' => array(
					'label'       => __( 'Website', 'wp-job-manager-company-listings' ),
					'type'        => 'text',
					'sanitizer'   => 'url',
					'required'    => false,
					'priority'    => 7,
					'placeholder' => 'http://',
				),
				'company_twitter' => array(
					'label'       => __( 'Twitter username', 'wp-job-manager-company-listings' ),
					'type'        => 'text',
					'required'    => false,
					'priority'    => 8,
					'placeholder' => '@yourcompany',
				),
				'company_category' => array(
					'label'       => __( 'Industry Type', 'wp-job-manager-company-listings' ),
					'type'        => 'term-multiselect',
					'taxonomy'    => 'company_category',
					'required'    => true,
					'placeholder' => '',
					'priority'    => 9,
				),
				'company_content' => array(
					'label'       => __( 'Content', 'wp-job-manager-company-listings' ),
					'type'        => 'wp-editor',
					'required'    => true,
					'placeholder' => '',
					'priority'    => 10,
				),
				'company_skills' => array(
					'label'       => __( 'Skills', 'wp-job-manager-company-listings' ),
					'type'        => 'text',
					'required'    => false,
					'placeholder' => __( 'Comma separate a list of relevant skills', 'wp-job-manager-company-listings' ) . $max,
					'priority'    => 11,
				),
				'links' => array(
					'label'       => __( 'URL(s)', 'wp-job-manager-company-listings' ),
					'add_row'     => __( 'Add URL', 'wp-job-manager-company-listings' ),
					'type'        => 'links', // repeated
					'required'    => false,
					'placeholder' => '',
					'description' => __( 'Optionally provide links to any of your websites or social network profiles.', 'wp-job-manager-company-listings' ),
					'priority'    => 12,
					'fields'      => array(
						'name' => array(
							'label'       => __( 'Name', 'wp-job-manager-company-listings' ),
							'type'        => 'text',
							'required'    => true,
							'placeholder' => '',
							'priority'    => 1,
						),
						'url' => array(
							'label'       => __( 'URL', 'wp-job-manager-company-listings' ),
							'type'        => 'text',
							'sanitizer'   => 'url',
							'required'    => true,
							'placeholder' => '',
							'priority'    => 2,
						),
					),
				),
				'info' => array(
					'label'       => __( 'Info(s)', 'wp-job-manager-company-listings' ),
					'add_row'     => __( 'Add Info', 'wp-job-manager-company-listings' ),
					'type'        => 'links', // repeated
					'required'    => false,
					'placeholder' => '',
					'description' => __( 'Optionally provide information of your company foundation date, type, strength etc.', 'wp-job-manager-company-listings' ),
					'priority'    => 13,
					'fields'      => array(
						'name' => array(
							'label'       => __( 'Name', 'wp-job-manager-company-listings' ),
							'type'        => 'text',
							'required'    => true,
							'placeholder' => '',
							'priority'    => 1,
						),
						'info' => array(
							'label'       => __( 'Info', 'wp-job-manager-company-listings' ),
							'type'        => 'text',
							'required'    => true,
							'placeholder' => '',
							'priority'    => 2,
						),
					),
				),
				'company_perk' => array(
					'label'       => __( 'Perks', 'wp-job-manager-company-listings' ),
					'add_row'     => __( 'Add Perks', 'wp-job-manager-company-listings' ),
					'type'        => 'perk', // repeated
					'required'    => false,
					'placeholder' => '',
					'priority'    => 14,
					'fields'      => array(
						'notes' => array(
							'label'       => __( 'Notes', 'wp-job-manager-company-listings' ),
							'type'        => 'text',
							'required'    => true,
							'placeholder' => '',
							'priority'    => 1,
						),
					),
				),
				'company_press' => array(
					'label'       => __( 'Press', 'wp-job-manager-company-listings' ),
					'add_row'     => __( 'Add Press', 'wp-job-manager-company-listings' ),
					'type'        => 'press', // repeated
					'required'    => false,
					'placeholder' => '',
					'priority'    => 15,
					'fields'      => array(
						'job_title' => array(
							'label'       => __( 'Title', 'wp-job-manager-company-listings' ),
							'type'        => 'text',
							'required'    => true,
							'placeholder' => '',
							'priority'    => 1,
						),
						'notes' => array(
							'label'       => __( 'URL', 'wp-job-manager-company-listings' ),
							'placeholder' => 'http://',
							'description' => '',
							'required'    => true,
							'type'        => 'text',
							'sanitizer'   => 'url',
							'priority'    => 2,
						),
					),
				),
				'company_file' => array(
					'label'       => __( 'File', 'wp-job-manager-company-listings' ),
					'type'        => 'file',
					'required'    => false,
					'ajax'        => true,
					'description' => sprintf( __( 'Upload your company for employers to view. Max. file size: %s.', 'wp-job-manager-company-listings' ), size_format( wp_max_upload_size() ) ),
					'priority'    => 16,
					'placeholder' => '',
				),
			)
		) );

		if ( ! get_option( 'company_listings_enable_company_upload' ) ) {
			unset( $this->fields['company_fields']['company_file'] );
		}

		if ( ! get_option( 'company_listings_enable_categories' ) || wp_count_terms( 'company_category' ) == 0 ) {
			unset( $this->fields['company_fields']['company_category'] );
		}

		if ( ! get_option( 'company_listings_enable_skills' ) ) {
			unset( $this->fields['company_fields']['company_skills'] );
		}
	}

	/**
	 * Get the value of a repeated fields (e.g. perk, links)
	 * @param  array $fields
	 * @return array
	 */
	public function get_repeated_field( $field_prefix, $fields ) {
		$items       = array();
		$field_keys  = array_keys( $fields );

		if ( ! empty( $_POST[ 'repeated-row-' . $field_prefix ] ) && is_array( $_POST[ 'repeated-row-' . $field_prefix ] ) ) {
			$indexes = array_map( 'absint', $_POST[ 'repeated-row-' . $field_prefix ] );
			foreach ( $indexes as $index ) {
				$item = array();
				foreach ( $fields as $key => $field ) {
					$field_name = $field_prefix . '_' . $key . '_' . $index;

					switch ( $field['type'] ) {
						case 'textarea' :
							$item[ $key ] = wp_kses_post( stripslashes( $_POST[ $field_name ] ) );
						break;
						case 'file' :
							$file = $this->upload_file( $field_name, $field );

							if ( ! $file ) {
								$file = $this->get_posted_field( 'current_' . $field_name, $field );
							} elseif ( is_array( $file ) ) {
								$file = array_filter( array_merge( $file, (array) $this->get_posted_field( 'current_' . $field_name, $field ) ) );
							}

							$item[ $key ] = $file;
						break;
						default :
							if ( is_array( $_POST[ $field_name ] ) ) {
								$item[ $key ] = array_filter( array_map( 'sanitize_text_field', array_map( 'stripslashes', $_POST[ $field_name ] ) ) );
							} else {
								if ( isset( $field['sanitizer'] ) ) {
									// do the sanitization for url and email
									$sanitizer = $field['sanitizer'];

									if ( $sanitizer === 'url' ) {
										$item[ $key ] = esc_url_raw( $_POST[ $field_name ] );
									} elseif ( $sanitizer === 'email' ) {
										$item[ $key ] = sanitize_email( $_POST[ $field_name ] );
									}
								} else {
									$item[ $key ] = sanitize_text_field( stripslashes( $_POST[ $field_name ] ) );
								}
							}
						break;
					}
					if ( empty( $item[ $key ] ) && ! empty( $field['required'] ) ) {
						continue 2;
					}
				}
				$items[] = $item;
			}
		}
		return $items;
	}

	/**
	 * Get the value of field type 'repeated'
	 * @param  string $key
	 * @param  array $field
	 * @return string
	 */
	public function get_posted_repeated_field( $key, $field ) {
		return apply_filters( 'submit_company_form_fields_get_repeated_field_data', $this->get_repeated_field( $key, $field['fields'] ) );
	}

	/**
	 * Get the value of field type 'links'
	 * @param  string $key
	 * @param  array $field
	 * @return string
	 */
	public function get_posted_links_field( $key, $field ) {
		return apply_filters( 'submit_company_form_fields_get_links_data', $this->get_repeated_field( $key, $field['fields'] ) );
	}

	/**
	 * Get the value of field type 'perk'
	 * @param  string $key
	 * @param  array $field
	 * @return string
	 */
	public function get_posted_perk_field( $key, $field ) {
		return apply_filters( 'submit_company_form_fields_get_perk_data', $this->get_repeated_field( $key, $field['fields'] ) );
	}

	/**
	 * Get the value of field type 'press'
	 * @param  string $key
	 * @param  array $field
	 * @return string
	 */
	public function get_posted_press_field( $key, $field ) {
		return apply_filters( 'submit_company_form_fields_get_press_data', $this->get_repeated_field( $key, $field['fields'] ) );
	}

	/**
	 * Validate the posted fields
	 *
	 * @return bool on success, WP_ERROR on failure
	 */
	protected function validate_fields( $values ) {
		foreach ( $this->fields as $group_key => $fields ) {
			foreach ( $fields as $key => $field ) {
				if ( $field['required'] && empty( $values[ $group_key ][ $key ] ) ) {
					return new WP_Error( 'validation-error', sprintf( __( '%s is a required field', 'wp-job-manager-company-listings' ), $field['label'] ) );
				}
				if ( ! empty( $field['taxonomy'] ) && in_array( $field['type'], array( 'term-checklist', 'term-select', 'term-multiselect' ) ) ) {
					if ( is_array( $values[ $group_key ][ $key ] ) ) {
						foreach ( $values[ $group_key ][ $key ] as $term ) {
							if ( ! term_exists( $term, $field['taxonomy'] ) ) {
								return new WP_Error( 'validation-error', sprintf( __( '%s is invalid', 'wp-job-manager-company-listings' ), $field['label'] ) );
							}
						}
					} elseif ( ! empty( $values[ $group_key ][ $key ] ) ) {
						if ( ! term_exists( $values[ $group_key ][ $key ], $field['taxonomy'] ) ) {
							return new WP_Error( 'validation-error', sprintf( __( '%s is invalid', 'wp-job-manager-company-listings' ), $field['label'] ) );
						}
					}
				}

				if ( 'company_email' === $key ) {
					if ( ! empty( $values[ $group_key ][ $key ] ) && ! is_email( $values[ $group_key ][ $key ] ) ) {
						throw new Exception( __( 'Please enter a valid email address', 'wp-job-manager-company-listings' ) );
					}
				}

				if ( 'company_skills' === $key ) {
					if ( is_string( $values[ $group_key ][ $key ] ) ) {
						$raw_skills = explode( ',', $values[ $group_key ][ $key ] );
					} else {
						$raw_skills = $values[ $group_key ][ $key ];
					}
					$max = get_option( 'company_listings_max_skills' );

					if ( $max && sizeof( $raw_skills ) > $max ) {
						return new WP_Error( 'validation-error', sprintf( __( 'Please enter no more than %d skills.', 'wp-job-manager-company-listings' ), $max ) );
					}
				}
			}
		}

		return apply_filters( 'submit_company_form_validate_fields', true, $this->fields, $values );
	}

	/**
	 * get categories.
	 *
	 * @access private
	 * @return void
	 */
	private function company_categories() {
		$options = array();
		$terms   = get_company_categories();
		foreach ( $terms as $term )
			$options[ $term->slug ] = $term->name;
		return $options;
	}

	/**
	 * Submit Step
	 */
	public function submit() {
		global $job_manager, $post;

		$this->init_fields();

		if ( $this->company_id ) {
			$company = get_post( $this->company_id );

			foreach ( $this->fields as $group_key => $group_fields ) {
				foreach ( $group_fields as $key => $field ) {
					switch ( $key ) {
						case 'company_name':
							$this->fields[ $group_key ][ $key ]['value'] = $company->post_title;
							break;
						case 'company_content':
							$this->fields[ $group_key ][ $key ]['value'] = $company->post_content;
							break;
						case 'company_category':
							$this->fields[ $group_key ][ $key ]['value'] = wp_get_object_terms( $company->ID, 'company_category', array( 'fields' => 'ids' ) );
							break;
						case 'company_logo':
							$this->fields[ $group_key ][ $key ]['value'] = has_post_thumbnail( $company->ID ) ? get_post_thumbnail_id( $company->ID ) : get_post_meta( $company->ID, '_' . $key, true );
							break;
						default:
							$this->fields[ $group_key ][ $key ]['value'] = get_post_meta( $company->ID, '_' . $key, true );
							break;
					}
				}
			}

			$this->fields = apply_filters( 'submit_company_form_fields_get_company_data', $this->fields, $company );

		} elseif ( is_user_logged_in() && empty( $_POST['submit_company'] ) ) {
			$user = wp_get_current_user();

			foreach ( $this->fields as $group_key => $fields ) {
				foreach ( $fields as $key => $field ) {
					switch ( $key ) {
						case 'company_email' :
						$this->fields[ $group_key ][ $key ]['value'] = $user->user_email;
						break;
					}
				}
			}

			$this->fields = apply_filters( 'submit_company_form_fields_get_user_data', $this->fields, get_current_user_id() );
		}

		get_job_manager_template( 'company_listings-submit.php', array(
			'class'              => $this,
			'form'               => $this->form_name,
			'company_id'         => $this->get_company_id(),
			'job_id'             => $this->get_job_id(),
			'resume_edit'        => $this->resume_edit,
			'action'             => $this->get_action(),
			'company_fields'     => $this->get_fields( 'company_fields' ),
			'step'               => $this->get_step(),
			'submit_button_text' => apply_filters( 'submit_company_form_submit_button_text', __( 'Preview &rarr;', 'wp-job-manager-company-listings' ) )
		), 'wp-job-manager-company-listings', COMPANY_LISTINGS_PLUGIN_DIR . '/templates/' );
	}

	/**
	 * Submit Step is posted
	 */
	public function submit_handler() {
		try {

			// Init fields
			$this->init_fields();

			// Get posted values
			$values = $this->get_posted_fields();

			if ( empty( $_POST['submit_company'] ) || ! wp_verify_nonce( $_POST['_wpnonce'], 'submit_form_posted' ) )
				return;

			// Validate required
			if ( is_wp_error( ( $return = $this->validate_fields( $values ) ) ) ) {
				throw new Exception( $return->get_error_message() );
			}

			// Account creation
			if ( ! is_user_logged_in() ) {
				$create_account = false;

				if ( company_listings_enable_registration() ) {
					if ( company_listings_user_requires_account() ) {
						if ( ! company_listings_generate_username_from_email() && empty( $_POST['create_account_username'] ) ) {
							throw new Exception( __( 'Please enter a username.', 'wp-job-manager-company-listings' ) );
						}
						if ( empty( $_POST['company_email'] ) ) {
							throw new Exception( __( 'Please enter your email address.', 'wp-job-manager-company-listings' ) );
						}
					}
					if ( ! empty( $_POST['company_email'] ) ) {
						if ( version_compare( JOB_MANAGER_VERSION, '1.20.0', '<' ) ) {
							$create_account = wp_job_manager_create_account( $_POST['company_email'], get_option( 'company_listings_registration_role', 'company' ) );
						} else {
							$create_account = wp_job_manager_create_account( array(
								'username' => empty( $_POST['create_account_username'] ) ? '' : $_POST['create_account_username'],
								'email'    => $_POST['company_email'],
								'role'     => get_option( 'company_listings_registration_role', 'company' )
							) );
						}
					}
				}

				if ( is_wp_error( $create_account ) ) {
					throw new Exception( $create_account->get_error_message() );
				}
			}

			if ( company_listings_user_requires_account() && ! is_user_logged_in() ) {
				throw new Exception( __( 'You must be signed in to post your company.', 'wp-job-manager-company-listings' ) );
			}

			// Update the job
			$this->save_company( $values['company_fields']['company_name'], $values['company_fields']['company_content'], $this->company_id ? '' : 'preview', $values );
			$this->update_company_data( $values );

			// Successful, show next step
			$this->step ++;

		} catch ( Exception $e ) {
			$this->add_error( $e->getMessage() );
			return;
		}
	}

	/**
	 * Creates a file attachment.
	 *
	 * @param  string $attachment_url
	 * @return int attachment id.
	 */
	protected function create_attachment( $attachment_url ) {
		include_once ABSPATH . 'wp-admin/includes/image.php';
		include_once ABSPATH . 'wp-admin/includes/media.php';

		$upload_dir     = wp_upload_dir();
		$attachment_url = str_replace( array( $upload_dir['baseurl'], WP_CONTENT_URL, site_url( '/' ) ), array( $upload_dir['basedir'], WP_CONTENT_DIR, ABSPATH ), $attachment_url );

		if ( empty( $attachment_url ) || ! is_string( $attachment_url ) ) {
			return 0;
		}

		$attachment = array(
			'post_title'   => get_the_title( $this->company_id ),
			'post_content' => '',
			'post_status'  => 'inherit',
			'post_parent'  => $this->company_id,
			'guid'         => $attachment_url,
		);

		$info = wp_check_filetype( $attachment_url );
		if ( $info ) {
			$attachment['post_mime_type'] = $info['type'];
		}

		$attachment_id = wp_insert_attachment( $attachment, $attachment_url, $this->company_id );

		if ( ! is_wp_error( $attachment_id ) ) {
			wp_update_attachment_metadata( $attachment_id, wp_generate_attachment_metadata( $attachment_id, $attachment_url ) );
			return $attachment_id;
		}

		return 0;
	}

	/**
	 * Update or create a job listing from posted data
	 *
	 * @param  string $post_title
	 * @param  string $post_content
	 * @param  string $status
	 */
	protected function save_company( $post_title, $post_content, $status = 'preview', $values = array() ) {
		// Get random key
		if ( $this->company_id ) {
			$prefix = get_post_meta( $this->company_id, '_company_name_prefix', true );

			if ( ! $prefix ) {
				$prefix = wp_generate_password( 10 );
			}
		} else {
			$prefix = wp_generate_password( 10 );
		}

		$company_slug = array();
		$company_slug[] = current( explode( ' ', $post_title ) );
		$company_slug[] = $prefix;

		if ( ! empty( $values['company_fields']['company_location'] ) ) {
			$company_slug[] = $values['company_fields']['company_location'];
		}

		$post_name = sanitize_title( implode( '-', $company_slug ) );
		$post_name = apply_filters( 'company_listing_post_slug', $post_name, $values );

		$data = array(
			'post_title'     => $post_title,
			'post_content'   => $post_content,
			'post_type'      => 'company_listings',
			'comment_status' => 'closed',
			'post_password'  => '',
			'post_name'      => $post_name,
		);

		if ( $status ) {
			$data['post_status'] = $status;
		}

		$data = apply_filters( 'submit_company_form_save_company_data', $data, $post_title, $post_content, $status, $values, $this );

		if ( $this->company_id ) {
			$data['ID'] = $this->company_id;
			wp_update_post( $data );
		} else {
			$this->company_id = wp_insert_post( $data );
			update_post_meta( $this->company_id, '_company_name_prefix', $prefix );

			if ( ! headers_sent() ) {
				$submitting_key = uniqid();

				setcookie( 'wp-job-manager-submitting-company-id', $this->company_id, false, COOKIEPATH, COOKIE_DOMAIN, false );
				setcookie( 'wp-job-manager-submitting-company-key', $submitting_key, false, COOKIEPATH, COOKIE_DOMAIN, false );

				update_post_meta( $this->company_id, '_submitting_key', $submitting_key );
			}
		}
	}

	/**
	 * Set job meta + terms based on posted values
	 *
	 * @param  array $values
	 */
	protected function update_company_data( $values ) {
		// Set defaults
		add_post_meta( $this->company_id, '_featured', 0, true );
		add_post_meta( $this->company_id, '_applying_for_job_id', $this->job_id, true );

		$maybe_attach = array();

		// Loop fields and save meta and term data
		foreach ( $this->fields as $group_key => $group_fields ) {
			foreach ( $group_fields as $key => $field ) {
				// Save taxonomies
				if ( ! empty( $field['taxonomy'] ) ) {
					if ( is_array( $values[ $group_key ][ $key ] ) ) {
						wp_set_object_terms( $this->company_id, $values[ $group_key ][ $key ], $field['taxonomy'], false );
					} else {
						wp_set_object_terms( $this->company_id, array( $values[ $group_key ][ $key ] ), $field['taxonomy'], false );
					}

					// Save meta data
				} elseif ( 'company_logo' === $key ) {
					$attachment_id = is_numeric( $values[ $group_key ][ $key ] ) ? absint( $values[ $group_key ][ $key ] ) : $this->create_attachment( $values[ $group_key ][ $key ] );
					if ( empty( $attachment_id ) ) {
						delete_post_thumbnail( $this->company_id );
						delete_post_meta( $this->company_id, '_company_logo' );
					} else {
						set_post_thumbnail( $this->company_id, $attachment_id );
						$company_logo_path = wp_get_attachment_url( $attachment_id );
						update_post_meta( $this->company_id, '_company_logo', $company_logo_path );
					}

					// Save meta data.
				} else {
					update_post_meta( $this->company_id, '_' . $key, $values[ $group_key ][ $key ] );

					// Handle attachments.
					if ( 'file' === $field['type'] ) {
						if ( is_array( $values[ $group_key ][ $key ] ) ) {
							foreach ( $values[ $group_key ][ $key ] as $file_url ) {
								$maybe_attach[] = $file_url;
							}
						} else {
							$maybe_attach[] = $values[ $group_key ][ $key ];
						}
					}
				}
			}
		}

		$maybe_attach = array_filter( $maybe_attach );

		// Handle attachments.
		if ( count( $maybe_attach ) && apply_filters( 'company_listings_attach_uploaded_files', true ) ) {
			// Get attachments.
			$attachments     = get_posts( 'post_parent=' . $this->company_id . '&post_type=attachment&fields=ids&numberposts=-1' );
			$attachment_urls = array();

			// Loop attachments already attached to the job.
			foreach ( $attachments as $attachment_id ) {
				$attachment_urls[] = wp_get_attachment_url( $attachment_id );
			}

			foreach ( $maybe_attach as $attachment_url ) {
				if ( ! in_array( $attachment_url, $attachment_urls, true ) ) {
					$this->create_attachment( $attachment_url );
				}
			}
		}

		if ( get_option( 'company_listings_enable_skills' ) && isset( $values['company_fields']['company_skills'] ) ) {

			$tags     = array();
			$raw_tags = $values['company_fields']['company_skills'];

			if ( is_string( $raw_tags ) ) {
				// Explode and clean
				$raw_tags = array_filter( array_map( 'sanitize_text_field', explode( ',', $raw_tags ) ) );

				if ( ! empty( $raw_tags ) ) {
					foreach ( $raw_tags as $tag ) {
						if ( $term = get_term_by( 'name', $tag, 'company_skill' ) ) {
							$tags[] = $term->term_id;
						} else {
							$term = wp_insert_term( $tag, 'company_skill' );

							if ( ! is_wp_error( $term ) ) {
								$tags[] = $term['term_id'];
							}
						}
					}
				}
			} else {
				$tags = array_map( 'absint', $raw_tags );
			}

			wp_set_object_terms( $this->company_id, $tags, 'company_skill', false );
		}

		do_action( 'company_listings_update_company_data', $this->company_id, $values );
	}

	/**
	 * Preview Step
	 */
	public function preview() {
		global $post, $company_preview;

		wp_enqueue_script( 'wp-job-manager-company-listings-company-submission' );

		if ( $this->company_id ) {

			$company_preview = true;
			$post = get_post( $this->company_id );
			setup_postdata( $post );
			?>
			<form method="post" id="company_preview" action="<?php echo esc_url( $this->get_action() ); ?>">
				<div class="company_preview_title">
					<input type="submit" name="continue" id="company_preview_submit_button" class="button" value="<?php echo apply_filters( 'submit_company_step_preview_submit_text', __( 'Submit Company &rarr;', 'wp-job-manager-company-listings' ) ); ?>" />
					<input type="submit" name="edit_company" class="button" value="<?php _e( '&larr; Edit company', 'wp-job-manager-company-listings' ); ?>" />
					<input type="hidden" name="company_id" value="<?php echo esc_attr( $this->company_id ); ?>" />
					<input type="hidden" name="job_id" value="<?php echo esc_attr( $this->job_id ); ?>" />
					<input type="hidden" name="step" value="<?php echo esc_attr( $this->step ); ?>" />
					<input type="hidden" name="company_listings_form" value="<?php echo $this->form_name; ?>" />
					<h2>
						<?php _e( 'Preview', 'wp-job-manager-company-listings' ); ?>
					</h2>
				</div>
				<div class="company_preview single-company">
					<h1><?php the_title(); ?></h1>
					<?php get_company_listings_template_part( 'content-single', 'company_listings', 'wp-job-manager-company-listings', COMPANY_LISTINGS_PLUGIN_DIR . '/templates/' ); ?>
				</div>
			</form>
			<?php

			wp_reset_postdata();
		}
	}

	/**
	 * Preview Step Form handler
	 */
	public function preview_handler() {
		if ( ! $_POST ) {
			return;
		}

		// Edit = show submit form again
		if ( ! empty( $_POST['edit_company'] ) ) {
			$this->step --;
		}

		// Continue = change job status then show next screen
		if ( ! empty( $_POST['continue'] ) ) {
			$company = get_post( $this->company_id );

			if ( in_array( $company->post_status, array( 'preview' ) ) ) {
				// Update listing
				$update_company                  = array();
				$update_company['ID']            = $company->ID;
				$update_company['post_date']     = current_time( 'mysql' );
				$update_company['post_date_gmt'] = current_time( 'mysql', 1 );
				$update_company['post_author']   = get_current_user_id();
				$update_company['post_status']   = apply_filters( 'submit_company_post_status', get_option( 'company_listings_submission_requires_approval' ) ? 'pending' : 'publish', $company );

				wp_update_post( $update_company );
			}

			$this->step ++;
			wp_safe_redirect( esc_url_raw( add_query_arg( array( 'step' => $this->step, 'job_id' => $this->job_id, 'company_id' => $this->company_id ) ) ) );
			exit;
		}
	}

	/**
	 * Done Step
	 */
	public function done() {
		do_action( 'company_listings_company_submitted', $this->company_id );

		get_job_manager_template(
			'company_listings-submitted.php',
			array( 'company_listings' => get_post( $this->company_id ),
			'job_id' => $this->job_id ), 'wp-job-manager-company-listings', COMPANY_LISTINGS_PLUGIN_DIR . '/templates/'
		);

		// Allow application
		if ( $this->job_id ) {
			echo '<h3 class="applying_for">' . sprintf( __( 'Submit your application to the job "%s".', 'wp-job-manager-company-listings' ), '<a href="' . get_permalink( $this->job_id ) . '">' . get_the_title( $this->job_id ) . '</a>' ) .'</h3>';

			echo do_shortcode( '[job_apply id="' . absint( $this->job_id ) . '"]' );
		}
	}
}

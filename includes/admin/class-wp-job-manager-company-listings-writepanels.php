<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
if ( ! class_exists( 'WP_Job_Manager_Writepanels' ) ) {
	include( JOB_MANAGER_PLUGIN_DIR . '/includes/admin/class-wp-job-manager-writepanels.php' );
}

class WP_Job_Manager_Company_Listings_Writepanels extends WP_Job_Manager_Writepanels {

	/**
	 * __construct function.
	 *
	 * @access public
	 * @return void
	 */
	public function __construct() {
		add_action( 'add_meta_boxes', 				 array( $this, 'add_meta_boxes' ) );
		add_action( 'save_post', 		 	 array( $this, 'save_post' ), 1, 2 );
		add_action( 'company_listings_save_company', array( $this, 'save_company_data' ), 1, 2 );
	}

	/**
	 * Company fields
	 *
	 * @return array
	 */
	public static function company_fields() {
		$fields = apply_filters( 'company_listings_company_fields', array(
			'_company_title' => array(
				'label'       => __( 'Company Tagline', 'wp-job-manager-company-listings' ),
				'placeholder' => '',
				'description' => ''
			),
			'_company_email' => array(
				'label'       => __( 'Contact Email', 'wp-job-manager-company-listings' ),
				'placeholder' => __( 'you@yourdomain.com', 'wp-job-manager-company-listings' ),
				'description' => ''
			),
			'_company_location' => array(
				'label'       => __( 'Company Location', 'wp-job-manager-company-listings' ),
				'placeholder' => __( 'e.g. "London, UK", "New York", "Houston, TX"', 'wp-job-manager-company-listings' ),
				'description' => ''
			),
			'_company_website' => array(
				'label'       => __( 'WebSite', 'wp-job-manager-company-listings' ),
				'placeholder' => __( 'URL to the company website', 'wp-job-manager-company-listings' ),
				'type'        => 'text'
			),
			'_company_twitter' => array(
				'label'       => __( 'Twitter', 'wp-job-manager-company-listings' ),
				'placeholder' => __( '@yourcompany', 'wp-job-manager-company-listings' ),
				'type'        => 'text'
			),
			'_company_video' => array(
				'label'       => __( 'Video', 'wp-job-manager-company-listings' ),
				'placeholder' => __( 'URL to the company video', 'wp-job-manager-company-listings' ),
				'type'        => 'text'
			),
			'_company_file' => array(
				'label'       => __( 'Company File', 'wp-job-manager-company-listings' ),
				'placeholder' => __( 'URL to the company\'s company file', 'wp-job-manager-company-listings' ),
				'type'        => 'file'
			),
			'_company_author' => array(
				'label' => __( 'Posted by', 'wp-job-manager-company-listings' ),
				'type'  => 'author',
				'placeholder' => '',
			),
			'_featured' => array(
				'label' => __( 'Feature this Company?', 'wp-job-manager-company-listings' ),
				'type'  => 'checkbox',
				'description' => __( 'Featured companies will be sticky during searches, and can be styled differently.', 'wp-job-manager-company-listings' )
			)
		) );

		if ( ! get_option( 'company_listings_enable_company_upload' ) ) {
			unset( $fields['_company_file'] );
		}

		return $fields;
	}

	/**
	 * add_meta_boxes function.
	 */
	public function add_meta_boxes() {
		add_meta_box( 'company_data', __( 'Company Data', 'wp-job-manager-company-listings' ), array( $this, 'company_data' ), 'company_listings', 'normal', 'high' );
		add_meta_box( 'company_url_data', __( 'URL(s)', 'wp-job-manager-company-listings' ), array( $this, 'url_data' ), 'company_listings', 'side', 'low' );
		add_meta_box( 'company_info_data', __( 'Company Info', 'wp-job-manager-company-listings' ), array( $this, 'info_data' ), 'company_listings', 'side', 'low' );
		add_meta_box( 'company_perk_data', __( 'Perks', 'wp-job-manager-company-listings' ), array( $this, 'perk_data' ), 'company_listings', 'normal', 'high' );
		add_meta_box( 'company_press_data', __( 'Press', 'wp-job-manager-company-listings' ), array( $this, 'experience_data' ), 'company_listings', 'normal', 'high' );
	}

	/**
	 * Company data
	 *
	 * @param mixed $post
	 */
	public function company_data( $post ) {
		global $post, $thepostid;

		$thepostid = $post->ID;

		echo '<div class="wp_company_listings_meta_data wp_job_manager_meta_data">';

		wp_nonce_field( 'save_meta_data', 'company_listings_nonce' );

		do_action( 'company_listings_company_data_start', $thepostid );

		foreach ( $this->company_fields() as $key => $field ) {
			$type = ! empty( $field['type'] ) ? $field['type'] : 'text';

			if( has_action( 'company_listings_input_' . $type ) ) {
				do_action( 'company_listings_input_' . $type, $key, $field );
			} elseif( method_exists( $this, 'input_' . $type ) ) {
				call_user_func( array( $this, 'input_' . $type ), $key, $field );
			}
		}

		do_action( 'company_listings_company_data_end', $thepostid );

		echo '</div>';
	}

	/**
	 * Output repeated rows
	 */
	public static function repeated_rows_html( $group_name, $fields, $data ) {
		?>
		<table class="wc-job-manager-company-listings-repeated-rows">
			<thead>
				<tr>
					<th class="sort-column">&nbsp;</th>
					<?php foreach ( $fields as $field ) : ?>
						<th><label><?php echo esc_html( $field['label'] ); ?></label></th>
					<?php endforeach; ?>
				</tr>
			</thead>
			<tfoot>
				<tr>
					<td colspan="<?php echo sizeof( $fields ) + 1; ?>">
						<div class="submit">
							<input type="submit" class="button company_listings_add_row" value="<?php printf( __( 'Add %s', 'wp-job-manager-company-listings' ), $group_name ); ?>" data-row="<?php
								ob_start();
								echo '<tr>';
								echo '<td class="sort-column" width="1%">&nbsp;</td>';
								foreach ( $fields as $key => $field ) {
									echo '<td>';
									$type           = ! empty( $field['type'] ) ? $field['type'] : 'text';
									$field['value'] = '';

									if ( method_exists( __CLASS__, 'input_' . $type ) ) {
										call_user_func( array( __CLASS__, 'input_' . $type ), $key, $field );
									} else {
										do_action( 'company_listings_input_' . $type, $key, $field );
									}
									echo '</td>';
								}
								echo '</tr>';
								echo esc_attr( ob_get_clean() );
							?>" />
						</div>
					</td>
				</tr>
			</tfoot>
			<tbody>
				<?php
					if ( $data ) {
						foreach ( $data as $item ) {
							echo '<tr>';
							echo '<td class="sort-column" width="1%">&nbsp;</td>';
							foreach ( $fields as $key => $field ) {
								echo '<td>';
								$type           = ! empty( $field['type'] ) ? $field['type'] : 'text';
								$field['value'] = isset( $item[ $key ] ) ? $item[ $key ] : '';

								if ( method_exists( __CLASS__, 'input_' . $type ) ) {
									call_user_func( array( __CLASS__, 'input_' . $type ), $key, $field );
								} else {
									do_action( 'company_listings_input_' . $type, $key, $field );
								}
								echo '</td>';
							}
							echo '</tr>';
						}
					}
				?>
			</tbody>
		</table>
		<?php
	}

	/**
	 * Company fields
	 * @return array
	 */
	public static function company_links_fields() {
		return apply_filters( 'company_listings_company_links_fields', array(
			'name' => array(
				'label'       => __( 'Name', 'wp-job-manager-company-listings' ),
				'name'        => 'company_url_name[]',
				'placeholder' => __( 'Your site', 'wp-job-manager-company-listings' ),
				'description' => '',
				'required'    => true
			),
			'url' => array(
				'label'       => __( 'URL', 'wp-job-manager-company-listings' ),
				'name'        => 'company_url[]',
				'placeholder' => 'http://',
				'description' => '',
				'required'    => true
			)
		) );
	}

	/**
	 * Company fields
	 * @return array
	 */
	public static function company_info_fields() {
		return apply_filters( 'company_listings_company_info_fields', array(
			'name' => array(
				'label'       => __( 'Name', 'wp-job-manager-company-listings' ),
				'name'        => 'company_info_name[]',
				'placeholder' => __( 'Info', 'wp-job-manager-company-listings' ),
				'description' => '',
				'required'    => true
			),
			'info' => array(
				'label'       => __( 'Info', 'wp-job-manager-company-listings' ),
				'name'        => 'company_info[]',
				'placeholder' => 'Value',
				'description' => '',
				'required'    => true
			)
		) );
	}

	/**
	 * Company fields
	 * @return array
	 */
	public static function company_perk_fields() {
		return apply_filters( 'company_listings_company_perk_fields', array(
			'notes' => array(
				'label'       => __( 'Notes', 'wp-job-manager-company-listings' ),
				'name'        => 'company_perk_notes[]',
				'placeholder' => '',
				'description' => '',
				'type'        => 'text',
			)
		) );
	}

	/**
	 * Company fields
	 * @return array
	 */
	public static function company_press_fields() {
		return apply_filters( 'company_listings_company_press_fields', array(
			'job_title' => array(
				'label'       => __( 'Post Title', 'wp-job-manager-company-listings' ),
				'name'        => 'company_press_job_title[]',
				'placeholder' => '',
				'description' => '',
				'required'    => true
			),
			'notes' => array(
				'label'       => __( 'URL', 'wp-job-manager-company-listings' ),
				'name'        => 'company_press_notes[]',
				'placeholder' => 'http://',
				'description' => '',
				'required'    => true
			)
		) );
	}

	/**
	 * Company URL data
	 * @param mixed $post
	 */
	public function url_data( $post ) {
		echo '<p>' . __( 'Optionally provide links to any of your websites or social network profiles.', 'wp-job-manager-company-listings' ) . '</p>';
		$fields = $this->company_links_fields();
		$this->repeated_rows_html( __( 'URL', 'wp-job-manager-company-listings' ), $fields, get_post_meta( $post->ID, '_links', true ) );
	}

	/**
	 * Company Info data
	 * @param mixed $post
	 */
	public function info_data( $post ) {
		echo '<p>' . __( 'Optionally provide information of your company foundation date, type, strength etc.', 'wp-job-manager-company-listings' ) . '</p>';
		$fields = $this->company_info_fields();
		$this->repeated_rows_html( __( 'Info', 'wp-job-manager-company-listings' ), $fields, get_post_meta( $post->ID, '_info', true ) );
	}

	/**
	 * Company Education data
	 *
	 * @param mixed $post
	 */
	public function perk_data( $post ) {
		$fields = $this->company_perk_fields();
		$this->repeated_rows_html( __( 'Perks', 'wp-job-manager-company-listings' ), $fields, get_post_meta( $post->ID, '_company_perk', true ) );
	}

	/**
	 * Company Education data
	 *
	 * @param mixed $post
	 */
	public function experience_data( $post ) {
		$fields = $this->company_press_fields();
		$this->repeated_rows_html( __( 'Press', 'wp-job-manager-company-listings' ), $fields, get_post_meta( $post->ID, '_company_press', true ) );
	}

	/**
	 * Triggered on Save Post
	 *
	 * @param mixed $post_id
	 * @param mixed $post
	 */
	public function save_post( $post_id, $post ) {
		if ( empty( $post_id ) || empty( $post ) || empty( $_POST ) ) return;
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;
		if ( is_int( wp_is_post_revision( $post ) ) ) return;
		if ( is_int( wp_is_post_autosave( $post ) ) ) return;
		if ( empty( $_POST['company_listings_nonce'] ) || ! wp_verify_nonce( $_POST['company_listings_nonce'], 'save_meta_data' ) ) return;
		if ( ! current_user_can( 'edit_post', $post_id ) ) return;
		if ( $post->post_type != 'company_listings' ) return;

		do_action( 'company_listings_save_company', $post_id, $post );
	}

	/**
	 * Save Company Meta
	 *
	 * @param mixed $post_id
	 * @param mixed $post
	 */
	public function save_company_data( $post_id, $post ) {
		global $wpdb;

		// These need to exist
		add_post_meta( $post_id, '_featured', 0, true );

		foreach ( $this->company_fields() as $key => $field ) {

			// Expirey date
			if ( '_company_expires' === $key ) {
				if ( ! empty( $_POST[ $key ] ) ) {
					update_post_meta( $post_id, $key, date( 'Y-m-d', strtotime( sanitize_text_field( $_POST[ $key ] ) ) ) );
				} else {
					update_post_meta( $post_id, $key, '' );
				}
			}

			elseif ( '_company_location' === $key ) {
				if ( update_post_meta( $post_id, $key, sanitize_text_field( $_POST[ $key ] ) ) ) {
					do_action( 'company_listings_company_location_edited', $post_id, sanitize_text_field( $_POST[ $key ] ) );
				} elseif ( apply_filters( 'company_listings_geolocation_enabled', true ) && ! WP_Job_Manager_Geocode::has_location_data( $post_id ) ) {
					WP_Job_Manager_Geocode::generate_location_data( $post_id, sanitize_text_field( $_POST[ $key ] ) );
				}
				continue;
			}

			elseif( '_company_author' === $key ) {
				$wpdb->update( $wpdb->posts, array( 'post_author' => $_POST[ $key ] > 0 ? absint( $_POST[ $key ] ) : 0 ), array( 'ID' => $post_id ) );
			}

			// Everything else
			else {
				$type = ! empty( $field['type'] ) ? $field['type'] : '';

				switch ( $type ) {
					case 'textarea' :
						update_post_meta( $post_id, $key, wp_kses_post( stripslashes( $_POST[ $key ] ) ) );
					break;
					case 'checkbox' :
						if ( isset( $_POST[ $key ] ) ) {
							update_post_meta( $post_id, $key, 1 );
						} else {
							update_post_meta( $post_id, $key, 0 );
						}
					break;
					default :
						if ( is_array( $_POST[ $key ] ) ) {
							update_post_meta( $post_id, $key, array_filter( array_map( 'sanitize_text_field', $_POST[ $key ] ) ) );
						} else {
							update_post_meta( $post_id, $key, sanitize_text_field( $_POST[ $key ] ) );
						}
					break;
				}
			}
		}

		$save_repeated_fields = array(
			'_links'                => $this->company_links_fields(),
			'_info'                => $this->company_info_fields(),
			'_company_perk'  => $this->company_perk_fields(),
			'_company_press' => $this->company_press_fields()
		);

		foreach ( $save_repeated_fields as $meta_key => $fields ) {
			$this->save_repeated_row( $post_id, $meta_key, $fields );
		}
	}

	/**
	 * Save repeated rows
	 * @since 1.11.3
	 */
	public static function save_repeated_row( $post_id, $meta_key, $fields ) {
		$items            = array();
		$first_field      = current( $fields );
		$first_field_name = str_replace( '[]', '', $first_field['name'] );

		if ( ! empty( $_POST[ $first_field_name ] ) && is_array( $_POST[ $first_field_name ] ) ) {
			$keys = array_keys( $_POST[ $first_field_name ] );
			foreach ( $keys as $posted_key ) {
				$item = array();
				foreach ( $fields as $key => $field ) {
					$input_name = str_replace( '[]', '', $field['name'] );
					$type       = ! empty( $field['type'] ) ? $field['type'] : 'text';

					switch ( $type ) {
						case 'textarea' :
							$item[ $key ] = wp_kses_post( stripslashes( $_POST[ $input_name ][ $posted_key ] ) );
						break;
						default :
							if ( is_array( $_POST[ $input_name ][ $posted_key ] ) ) {
								$item[ $key ] = array_filter( array_map( 'sanitize_text_field', array_map( 'stripslashes', $_POST[ $input_name ][ $posted_key ] ) ) );
							} else {
								$item[ $key ] = sanitize_text_field( stripslashes( $_POST[ $input_name ][ $posted_key ] ) );
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
		update_post_meta( $post_id, $meta_key, $items );
	}
}

new WP_Job_Manager_Company_Listings_Writepanels();
<?php
/**
 * WP_Job_Manager_Company_Listings_Mapping class.
 */
class WP_Job_Manager_Company_Listings_Mapping {

    /**
     * Constructor
     */
    public function __construct() {
        //Job form
        add_action( 'submit_job_form_fields', array( $this, 'update_form_fields' ) );
        add_action( 'job_manager_job_listing_data_end', array( $this, 'job_listing_data' ) );
        add_filter( 'submit_job_form_fields_get_user_data', array( $this, 'hide_user_company_data' ) );

        //Job save
        add_action( 'job_manager_update_job_data', array( $this, 'save_company_listing' ), 10, 2 );
        add_action( 'job_manager_job_submitted', array( $this, 'update_company_status') );

        //include custom form-field-type
        add_action( 'job_manager_locate_template', array( $this, 'include_templates' ), 10, 3 );

        //set company name
        add_action( 'submit_job_form_fields_get_job_data', array( $this, 'set_company_name' ), 10, 2 );
    }

    /**
     * Change the 'company_name' field type to 'select' from 'text'
     */
    public function update_form_fields( $fields ) {
        if ( isset( $fields['company']['company_name'] ) ) {
            unset( $fields['company']['company_name'] );
        }

        $fields['company']['company_id'] = array(
            'label'       => __( 'Company name', 'wp-job-manager-company-listings' ),
            'type'        => 'select-company',
            'required'    => true,
            'placeholder' => __( 'Enter the name of the company', 'wp-job-manager-company-listings' ),
            'priority'    => 1,
            'options'     => array(),
        );

        return $fields;
    }

    /**
     * Company input fields in an add job form
     */
    public function job_listing_data( $post_id ) {
        $company_id = get_post_meta( $post_id, '_company_id', true );
        if ( ! $company_id )
            $company_id = 'new';
        ?>
            <input type="hidden" name="_company_id" id="_company_id" value="<?php echo $company_id ?>" />
        <?php
    }

    /**
     * Hides the user company data when creating a new job post.
     *
     * @param      array  $fields  The fields
     *
     * @return     array
     */
    public function hide_user_company_data( $fields ) {
        if ( $fields ) {
            foreach ( $fields as $section => $section_fields ) {
                if ( $section === 'company' ) {
                    foreach ($section_fields as $key => $array) {
                        if ( $key === 'company_name' ) {
                            unset( $fields[$section][$key]['value'] );
                        }
                    }
                }
            }
        }

        return $fields;
    }

    /**
     * Saves a company listing.
     *
     * @param      int    $job_id  The job identifier
     * @param      array  $data    The data
     */
    public function save_company_listing( $job_id, $data ) {
        if ( ! $job_id ) {
            return;
        }

        if ( isset( $_POST['company_id'] ) ) {
            $post_title = sanitize_text_field( $_POST['company_id'] );
            $new_company = true;
            $post_status = 'pending';

            if ( $company = get_post( intval( $_POST['company_id'] ) ) ) {
                if ( $company->post_type === 'company_listings' ) {
                    $post_title = $company->post_title;
                    $post_status = $company->post_status;
                    $new_company = false;
                }
            }

            $company_title = $_POST['company_tagline'];
            $company_location = $_POST['job_location'];

            $company_data = array(
                'post_type'   => 'company_listings',
                'post_status' => $post_status,
            );

            if ( $new_company ) {
                $prefix = wp_generate_password( 10 );

                $company_slug = array();
                $company_slug[] = current( explode( ' ', $post_title ) );
                $company_slug[] = $prefix;

                if ( ! empty( $company_title ) ) {
                    $company_slug[] = $company_title;
                }

                if ( ! empty( $company_location ) ) {
                    $company_slug[] = $company_location;
                }

                $company_data['post_title'] = $post_title;
                $company_data['post_name'] = sanitize_title( implode( '-', $company_slug ) );
            } else {
                $company_data['post_title'] = $company->post_title;
                $company_data['ID'] = $company->ID;
                $company_data['post_date'] = $company->post_date;
            }

            $company_id = wp_insert_post( $company_data );

            if ( $company_id ) {
                if ( $new_company ) {
                    update_post_meta( $company_id, '_company_website', $_POST['company_website'] );
                    update_post_meta( $company_id, '_company_title', $company_title );
                    update_post_meta( $company_id, '_company_location', $company_location );
                    update_post_meta( $company_id, '_company_twitter', $_POST['company_twitter'] );
                    update_post_meta( $company_id, '_company_video', $_POST['company_video'] );
                    update_post_meta( $company_id, '_company_email', $_POST['application'] );
                    update_post_meta( $company_id, '_company_name_prefix', $prefix );

                    /* ------ Company logo ------- */
                    $thumbnail_id = intval( get_post_meta( $job_id, '_thumbnail_id', true ) );

                    if ( $thumbnail_id ) {
                        set_post_thumbnail( $company_id, $thumbnail_id );
                        $company_logo_path = wp_get_attachment_url( $thumbnail_id );
                        update_post_meta( $company_id, '_company_logo', $company_logo_path );
                    }
                }

                update_post_meta( $job_id, '_company_name', $post_title );
                update_post_meta( $job_id, '_company_id', $company_id );

                $_POST['company_id'] = $company_id;
            }
        }
    }

    /**
     * Update company status.
     *
     * @param      int   $job_id  The job identifier
     */
    public function update_company_status( $job_id ) {
        $company_id = intval( get_post_meta( $job_id, '_company_id', true ) );

        if ( $company_id ) {
            $company = get_post( $company_id );

            if ( $company && $company->post_status === 'preview' ) {
                wp_update_post(array(
                    'ID'          => $company_id,
                    'post_status' => apply_filters( 'submit_company_post_status', get_option( 'company_listings_submission_requires_approval' ) ? 'pending' : 'publish', $company ),
                ));
            }
        }
    }

    /**
     * Include custom templates.
     *
     * @param      mixed   $template       The template
     * @param      string  $template_name  The template name
     * @param      string  $template_path  The template path
     *
     * @return     mixed
     */
    public function include_templates( $template, $template_name, $template_path ) {
        if ( $template_name === 'form-fields/select-company-field.php' ) {
            $template = COMPANY_LISTINGS_PLUGIN_DIR . '/templates/' . $template_name;
        }

        return $template;
    }

    /**
     * Sets the company name.
     *
     * @param      array   $fields  The fields
     * @param      object  $job     The job
     */
    public function set_company_name( $fields, $job ) {
        if ( $fields ) {
            foreach ( $fields as $group_key => $group_fields ) {
                foreach ( $group_fields as $key => $field ) {
                    if ( $field['type'] === 'select-company' ) {
                        if ( isset( $_POST[ $key ] ) ) {
                            $value = $_POST[ $key ];
                        } elseif ( $job ) {
                            $value = get_post_meta( $job->ID, '_company_id', true );
                        }

                        $company_id = $company_name = $value;

                        if ( $company = get_post( intval( $value ) ) ) {
                            if ( $company->post_type === 'company_listings' ) {
                                $company_id = $value;
                                $company_name = $company->post_title;
                            }
                        }

                        $fields[ $group_key ][ $key ]['company_id'] = $company_id;
                        $fields[ $group_key ][ $key ]['company_name'] = $company_name;
                    }
                }
            }
        }

        return $fields;
    }
}

new WP_Job_Manager_Company_Listings_Mapping();

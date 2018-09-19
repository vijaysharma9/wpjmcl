<?php
/**
 * WP_Job_Manager_Company_Listings_Mapping class.
 */
class WP_Job_Manager_Company_Listings_Mapping {

    /**
     * Constructor
     */
    public function __construct() {
        // Job form
        add_action( 'submit_job_form_fields', array( $this, 'update_form_fields' ) );
        add_action( 'job_manager_job_listing_data_end', array( $this, 'job_listing_data' ) );

        // include custom templates
        add_action( 'job_manager_locate_template', array( $this, 'include_templates' ), 10, 3 );

        // set company data
        add_action( 'submit_job_form_fields_get_job_data', array( $this, 'set_company_data' ), 10, 2 );
    }

    /**
     * Modify job form fields.
     *
     * @param      array  $fields  The job form fields
     *
     * @return     array  Updated job form fields
     */
    public function update_form_fields( $fields ) {
        if ( isset( $fields['company'] ) ) {
            unset( $fields['company'] );
        }

        $company_field_required = apply_filters( 'submit_job_form_fields_select_company_field_required', true );
        $company_field_position = apply_filters( 'submit_job_form_fields_select_company_field_position', 0 );

        $job_logo_field_required = apply_filters( 'submit_job_form_fields_job_logo_field_required', false );
        $job_logo_field_position = apply_filters( 'submit_job_form_fields_job_logo_field_position', 7 );

        $options = array();
        $type = 'select-company';

        if ( ! jmcl_company_field_enable_select2_search() ) {
            $options = jmcl_get_companies_for_dropdown_field();
            $type = 'select';
        }

        $fields['job']['company_id'] = array(
            'label'       => __( 'Company', 'wp-job-manager-company-listings' ),
            'type'        => $type,
            'required'    => $company_field_required,
            'placeholder' => __( 'Select company', 'wp-job-manager-company-listings' ),
            'priority'    => $company_field_position,
            'options'     => $options,
        );

        $fields['job']['company_logo'] = array(
            'label'              => __( 'Logo', 'wp-job-manager-company-listings' ),
            'type'               => 'file',
            'required'           => $job_logo_field_required,
            'placeholder'        => '',
            'priority'           => $job_logo_field_position,
            'ajax'               => true,
            'multiple'           => false,
            'allowed_mime_types' => array(
                'jpg'  => 'image/jpeg',
                'jpeg' => 'image/jpeg',
                'gif'  => 'image/gif',
                'png'  => 'image/png',
            ),
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
        } elseif ( $template_name === 'content-single-job_listing-company.php' ) {
            $template = COMPANY_LISTINGS_PLUGIN_DIR . '/templates/' . $template_name;
        }

        return $template;
    }

    /**
     * Sets the company.
     *
     * @param      array   $fields  The job form fields
     * @param      object  $job     The job post object
     *
     * @return     array
     */
    public function set_company_data( $fields, $job ) {
        if ( $fields ) {
            foreach ( $fields as $group_key => $group_fields ) {
                foreach ( $group_fields as $key => $field ) {
                    if ( $field['type'] === 'select-company' ) {
                        $company_id = $company_name = '';

                        if ( isset( $_POST[ $key ] ) ) {
                            $company_id = $_POST[ $key ];
                        } elseif ( $job ) {
                            $company_id = jmcl_get_the_company( $job );
                        }

                        $company_name = apply_filters( 'submit_job_form_fields_set_select2_company_name', get_the_title( $company_id ), $company_id );

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

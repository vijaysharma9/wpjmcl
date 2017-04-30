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
		add_action( 'job_manager_job_listing_data_end',     array( $this, 'job_listing_data' ) );
        add_action( 'submit_job_form_company_fields_end',   array( $this, 'job_listing_data' ) );

        //Job save
        add_action( 'save_post_job_listing',                array( $this, 'save_job_listing_data' ), 21, 1 );
        add_action( 'job_manager_update_job_data',          array( $this, 'set_company_logo'), 10, 2 );
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
	 * save_job_listing_data function.
	 *
	 * @access public
	 * @param mixed $post_id
	 * @param mixed $post
	 * @return void
	 */
	public function save_job_listing_data( $post_id ) {

        if ( empty( $post_id ) || empty( $_POST ) ) return;
        if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;
        if ( ! current_user_can( 'edit_post', $post_id ) ) return;

        if ( isset( $_POST['_company_id'] ) && 'new' == $_POST['_company_id'] ) {

            if ( isset( $_POST['_company_name'] ) ) {
                $underscore = '_';
            } else if ( $_POST['company_name'] ) {
                $underscore = '';
            }

            /**
             * Create the company.
             */
            $new_company_id = wp_insert_post( array(
                'post_status'    => 'pending',
                'post_title'     => $_POST[$underscore.'company_name'],
                'post_type'      => 'company',
            ));

            update_post_meta( $new_company_id, '_company_website',  $_POST[$underscore.'company_website'] );
            update_post_meta( $new_company_id, '_company_title',    $_POST[$underscore.'company_tagline'] );
            update_post_meta( $new_company_id, '_company_location', $_POST[$underscore.'job_location'] );
            update_post_meta( $new_company_id, '_company_twitter',  $_POST[$underscore.'company_twitter'] );
            update_post_meta( $new_company_id, '_company_video',    $_POST[$underscore.'company_video'] );
            update_post_meta( $new_company_id, '_company_email',    $_POST[$underscore.'application'] );

            /* ------ Company logo ------- */
            $thumbnail_id = get_post_meta( $post_id, '_thumbnail_id', true );
            if ( ! empty( $thumbnail_id ) ) set_post_thumbnail( $new_company_id, $thumbnail_id );

            //@todo: why modify $_POST value?
            $_POST['_company_id'] = $new_company_id;
        }

        if ( isset( $_POST['_company_id'] ) ) {
            update_post_meta( $post_id, '_company_id', $_POST['_company_id'] );
        }
    }

    /**
     * The company logos
     * @param $job_id
     * @param $values
     */
    public function set_company_logo( $job_id, $values ) {
        /* ------ Company logo ------- */
        $thumbnail_id   = get_post_meta( $job_id, '_thumbnail_id', true );
        $company_id     = get_post_meta( $job_id, '_company_id', true );
        if ( ! empty( $thumbnail_id ) ) set_post_thumbnail( $company_id, $thumbnail_id );
    }
}

new WP_Job_Manager_Company_Listings_Mapping();
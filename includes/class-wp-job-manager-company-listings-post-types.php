<?php
/**
 * WP_Job_Manager_Company_Listings_Post_Types class.
 */
class WP_Job_Manager_Company_Listings_Post_Types {

	/**
	 * Constructor
	 */
	public function __construct() {
		add_action( 'init', array( $this, 'register_post_types' ), 0 );
		add_action( 'wp', array( $this, 'download_company_handler' ) );
		add_filter( 'admin_head', array( $this, 'admin_head' ) );
		add_filter( 'the_title', array( $this, 'company_title' ), 10, 2 );
		add_filter( 'single_post_title', array( $this, 'company_title' ), 10, 2 );
		add_filter( 'the_content', array( $this, 'company_content' ) );

		add_filter( 'the_company_metadescription', 'wptexturize'        );
		add_filter( 'the_company_metadescription', 'convert_smilies'    );
		add_filter( 'the_company_metadescription', 'convert_chars'      );
		add_filter( 'the_company_metadescription', 'wpautop'            );
		add_filter( 'the_company_metadescription', 'shortcode_unautop'  );
		add_filter( 'the_company_metadescription', 'prepend_attachment' );

		add_action( 'company_listings_contact_details', array( $this, 'contact_details_email' ) );

		add_action( 'pending_to_publish', array( $this, 'setup_autohide_cron' ) );
		add_action( 'preview_to_publish', array( $this, 'setup_autohide_cron' ) );
		add_action( 'draft_to_publish', array( $this, 'setup_autohide_cron' ) );
		add_action( 'auto-draft_to_publish', array( $this, 'setup_autohide_cron' ) );
		add_action( 'hidden_to_publish', array( $this, 'setup_autohide_cron' ) );
		add_action( 'save_post', array( $this, 'setup_autohide_cron' ) );
		add_action( 'auto-hide-company', array( $this, 'hide_company' ) );

		add_action( 'update_post_meta', array( $this, 'maybe_update_menu_order' ), 10, 4 );
		add_filter( 'wp_insert_post_data', array( $this, 'fix_post_name' ), 10, 2 );

		add_action( 'save_post', array( $this, 'flush_get_company_listings_cache' ) );
		add_action( 'company_listings_my_company_do_action', array( $this, 'company_listings_my_company_do_action' ) );
	}

	/**
	 * Flush the cache
	 */
	public function flush_get_company_listings_cache( $post_id ) {
		if ( 'company_listings' === get_post_type( $post_id ) ) {
			WP_Job_Manager_Cache_Helper::get_transient_version( 'get_company_listings', true );
		}
	}

	/**
	 * Flush the cache
	 */
	public function company_listings_my_company_do_action( $action ) {
		WP_Job_Manager_Cache_Helper::get_transient_version( 'get_company_listings', true );
	}

	/**
	 * register_post_types function.
	 *
	 * @access public
	 * @return void
	 */
	public function register_post_types() {

		if ( post_type_exists( 'company_listings' ) )
			return;

		$admin_capability = 'manage_companies';

		/**
		 * Taxonomies
		 */
		if ( get_option( 'company_listings_enable_categories' ) ) {
			$singular  = __( 'Company Category', 'wp-job-manager-company-listings' );
			$plural    = __( 'Company Categories', 'wp-job-manager-company-listings' );

			if ( current_theme_supports( 'company-listings-templates' ) ) {
				$rewrite     = array(
					'slug'         => _x( 'company-category', 'Company category slug - resave permalinks after changing this', 'wp-job-manager-company-listings' ),
					'with_front'   => false,
					'hierarchical' => false
				);
			} else {
				$rewrite = false;
			}

			register_taxonomy( "company_category",
		        array( 'company_listings' ),
		        array(
		            'hierarchical' 			=> true,
		            'update_count_callback' => '_update_post_term_count',
		            'label' 				=> $plural,
		            'labels' => array(
	                    'name' 				=> $plural,
	                    'singular_name' 	=> $singular,
	                    'search_items' 		=> sprintf( __( 'Search %s', 'wp-job-manager-company-listings' ), $plural ),
	                    'all_items' 		=> sprintf( __( 'All %s', 'wp-job-manager-company-listings' ), $plural ),
	                    'parent_item' 		=> sprintf( __( 'Parent %s', 'wp-job-manager-company-listings' ), $singular ),
	                    'parent_item_colon' => sprintf( __( 'Parent %s:', 'wp-job-manager-company-listings' ), $singular ),
	                    'edit_item' 		=> sprintf( __( 'Edit %s', 'wp-job-manager-company-listings' ), $singular ),
	                    'update_item' 		=> sprintf( __( 'Update %s', 'wp-job-manager-company-listings' ), $singular ),
	                    'add_new_item' 		=> sprintf( __( 'Add New %s', 'wp-job-manager-company-listings' ), $singular ),
	                    'new_item_name' 	=> sprintf( __( 'New %s Name', 'wp-job-manager-company-listings' ),  $singular )
	            	),
		            'show_ui' 				=> true,
		            'query_var' 			=> true,
		            'capabilities'			=> array(
		            	'manage_terms' 		=> $admin_capability,
		            	'edit_terms' 		=> $admin_capability,
		            	'delete_terms' 		=> $admin_capability,
		            	'assign_terms' 		=> $admin_capability,
		            ),
		            'rewrite' 				=> $rewrite,
		        )
		    );
		}

		if ( get_option( 'company_listings_enable_skills' ) ) {
			$singular  = __( 'Company Skill', 'wp-job-manager-company-listings' );
			$plural    = __( 'Company Skills', 'wp-job-manager-company-listings' );

			if ( current_theme_supports( 'company-listings-templates' ) ) {
				$rewrite     = array(
					'slug'         => _x( 'company-skill', 'Company skill slug - resave permalinks after changing this', 'wp-job-manager-company-listings' ),
					'with_front'   => false,
					'hierarchical' => false
				);
			} else {
				$rewrite = false;
			}

			register_taxonomy( "company_skill",
		        array( 'company_listings' ),
		        array(
		            'hierarchical' 			=> false,
		            'update_count_callback' => '_update_post_term_count',
		            'label' 				=> $plural,
		            'labels' => array(
	                    'name' 				=> $plural,
	                    'singular_name' 	=> $singular,
	                    'search_items' 		=> sprintf( __( 'Search %s', 'wp-job-manager-company-listings' ), $plural ),
	                    'all_items' 		=> sprintf( __( 'All %s', 'wp-job-manager-company-listings' ), $plural ),
	                    'parent_item' 		=> sprintf( __( 'Parent %s', 'wp-job-manager-company-listings' ), $singular ),
	                    'parent_item_colon' => sprintf( __( 'Parent %s:', 'wp-job-manager-company-listings' ), $singular ),
	                    'edit_item' 		=> sprintf( __( 'Edit %s', 'wp-job-manager-company-listings' ), $singular ),
	                    'update_item' 		=> sprintf( __( 'Update %s', 'wp-job-manager-company-listings' ), $singular ),
	                    'add_new_item' 		=> sprintf( __( 'Add New %s', 'wp-job-manager-company-listings' ), $singular ),
	                    'new_item_name' 	=> sprintf( __( 'New %s Name', 'wp-job-manager-company-listings' ),  $singular )
	            	),
		            'show_ui' 				=> true,
		            'query_var' 			=> true,
		            'capabilities'			=> array(
		            	'manage_terms' 		=> $admin_capability,
		            	'edit_terms' 		=> $admin_capability,
		            	'delete_terms' 		=> $admin_capability,
		            	'assign_terms' 		=> $admin_capability,
		            ),
		            'rewrite' 				=> $rewrite,
		        )
		    );
		}

	    /**
		 * Post types
		 */
		$singular  = __( 'Company', 'wp-job-manager-company-listings' );
		$plural    = __( 'Companies', 'wp-job-manager-company-listings' );

		if ( current_theme_supports( 'company-listings-templates' ) ) {
			$has_archive = _x( 'companies', 'Post type archive slug - resave permalinks after changing this', 'wp-job-manager-company-listings' );
		} else {
			$has_archive = false;
		}

		$rewrite     = array(
			'slug'       => _x( 'companies', 'Company permalink - resave permalinks after changing this', 'wp-job-manager-company-listings' ),
			'with_front' => false,
			'feeds'      => false,
			'pages'      => false
		);

		register_post_type( "company_listings",
			apply_filters( "register_post_type_company_listings", array(
				'labels' => array(
					'name' 					=> $plural,
					'singular_name' 		=> $singular,
					'menu_name'             => $plural,
					'all_items'             => sprintf( __( 'All %s', 'wp-job-manager-company-listings' ), $plural ),
					'add_new' 				=> __( 'Add New', 'wp-job-manager-company-listings' ),
					'add_new_item' 			=> sprintf( __( 'Add %s', 'wp-job-manager-company-listings' ), $singular ),
					'edit' 					=> __( 'Edit', 'wp-job-manager-company-listings' ),
					'edit_item' 			=> sprintf( __( 'Edit %s', 'wp-job-manager-company-listings' ), $singular ),
					'new_item' 				=> sprintf( __( 'New %s', 'wp-job-manager-company-listings' ), $singular ),
					'view' 					=> sprintf( __( 'View %s', 'wp-job-manager-company-listings' ), $singular ),
					'view_item' 			=> sprintf( __( 'View %s', 'wp-job-manager-company-listings' ), $singular ),
					'search_items' 			=> sprintf( __( 'Search %s', 'wp-job-manager-company-listings' ), $plural ),
					'not_found' 			=> sprintf( __( 'No %s found', 'wp-job-manager-company-listings' ), $plural ),
					'not_found_in_trash' 	=> sprintf( __( 'No %s found in trash', 'wp-job-manager-company-listings' ), $plural ),
					'parent' 				=> sprintf( __( 'Parent %s', 'wp-job-manager-company-listings' ), $singular ),
					'featured_image'        => __( 'Company Logo', 'wp-job-manager-company-listings' ),
					'set_featured_image'    => __( 'Set company logo', 'wp-job-manager-company-listings' ),
					'remove_featured_image' => __( 'Remove company logo', 'wp-job-manager-company-listings' ),
					'use_featured_image'    => __( 'Use as company logo', 'wp-job-manager-company-listings' ),
				),
				'description' => __( 'This is where you can create and manage user companies.', 'wp-job-manager-company-listings' ),
				'public' 				=> true,
				'show_ui' 				=> true,
				'capability_type' 		=> 'post',
				'capabilities' => array(
					'publish_posts' 		=> $admin_capability,
					'edit_posts' 			=> $admin_capability,
					'edit_others_posts' 	=> $admin_capability,
					'delete_posts' 			=> $admin_capability,
					'delete_others_posts'	=> $admin_capability,
					'read_private_posts'	=> $admin_capability,
					'edit_post' 			=> $admin_capability,
					'delete_post' 			=> $admin_capability,
					'read_post' 			=> $admin_capability
				),
				'publicly_queryable' 	=> true,
				'exclude_from_search' 	=> true,
				'hierarchical' 			=> false,
				'rewrite' 				=> $rewrite,
				'query_var' 			=> true,
				'supports' 				=> array( 'title', 'editor', 'custom-fields', 'thumbnail' ),
				'has_archive' 			=> $has_archive,
				'show_in_nav_menus' 	=> false,
				'menu_icon'           => 'dashicons-building'
			) )
		);

		register_post_status( 'hidden', array(
			'label'                     => _x( 'Hidden', 'post status', 'wp-job-manager-company-listings' ),
			'public'                    => true,
			'exclude_from_search'       => true,
			'show_in_admin_all_list'    => true,
			'show_in_admin_status_list' => true,
			'label_count'               => _n_noop( 'Hidden <span class="count">(%s)</span>', 'Hidden <span class="count">(%s)</span>', 'wp-job-manager-company-listings' ),
		) );
	}

	public function download_company_handler() {
		global $post, $is_IE;

		if ( empty( $_GET['download-company'] ) ) {
			return;
		}

		$company_id = absint( $_GET['download-company'] );

		if ( $company_id && company_listings_user_can_view_company( $company_id ) && apply_filters( 'company_listings_user_can_download_company_file', true, $company_id ) ) {
			$file_paths = get_company_files( $company_id );
			$file_id    = ! empty( $_GET['file-id'] ) ? absint( $_GET['file-id'] ) : 0;
			$file_path  = $file_paths[ $file_id ];

			if ( ! is_multisite() ) {

				/*
				 * Download file may be either http or https.
				 * site_url() depends on whether the page containing the download (ie; My Account) is served via SSL because WC
				 * modifies site_url() via a filter to force_ssl.
				 * So blindly doing a str_replace is incorrect because it will fail when schemes are mismatched. This code
				 * handles the various permutations.
				 */
				$scheme = parse_url( $file_path, PHP_URL_SCHEME );

				if ( $scheme ) {
					$site_url = set_url_scheme( site_url( '' ), $scheme );
				} else {
					$site_url = is_ssl() ? str_replace( 'https:', 'http:', site_url() ) : site_url();
				}

				$file_path   = str_replace( trailingslashit( $site_url ), ABSPATH, $file_path );

			} else {

				$network_url = is_ssl() ? str_replace( 'https:', 'http:', network_admin_url() ) : network_admin_url();
				$upload_dir  = wp_upload_dir();

				// Try to replace network url
				$file_path   = str_replace( trailingslashit( $network_url ), ABSPATH, $file_path );

				// Now try to replace upload URL
				$file_path   = str_replace( $upload_dir['baseurl'], $upload_dir['basedir'], $file_path );
			}

			$file_path      = realpath( $file_path );
			$file_extension = strtolower( substr( strrchr( $file_path, "." ), 1 ) );
			$ctype          = "application/force-download";

			foreach ( get_allowed_mime_types() as $mime => $type ) {
				$mimes = explode( '|', $mime );
				if ( in_array( $file_extension, $mimes ) ) {
					$ctype = $type;
					break;
				}
			}

			// Start setting headers
			if ( ! ini_get('safe_mode') ) {
				@set_time_limit(0);
			}

			if ( function_exists( 'get_magic_quotes_runtime' ) && get_magic_quotes_runtime() ) {
				@set_magic_quotes_runtime(0);
			}

			if ( function_exists( 'apache_setenv' ) ) {
				@apache_setenv( 'no-gzip', 1 );
			}

			@session_write_close();
			@ini_set( 'zlib.output_compression', 'Off' );

			/**
			 * Prevents errors, for example: transfer closed with 3 bytes remaining to read
			 */
			@ob_end_clean(); // Clear the output buffer

			if ( ob_get_level() ) {

				$levels = ob_get_level();

				for ( $i = 0; $i < $levels; $i++ ) {
					@ob_end_clean(); // Zip corruption fix
				}

			}

			if ( $is_IE && is_ssl() ) {
				// IE bug prevents download via SSL when Cache Control and Pragma no-cache headers set.
				header( 'Expires: Wed, 11 Jan 1984 05:00:00 GMT' );
				header( 'Cache-Control: private' );
			} else {
				nocache_headers();
			}

			$filename = basename( $file_path );

			if ( strstr( $filename, '?' ) ) {
				$filename = current( explode( '?', $filename ) );
			}

			header( "X-Robots-Tag: noindex, nofollow", true );
			header( "Content-Type: " . $ctype );
			header( "Content-Description: File Transfer" );
			header( "Content-Disposition: attachment; filename=\"" . $filename . "\";" );
			header( "Content-Transfer-Encoding: binary" );

	        if ( $size = @filesize( $file_path ) ) {
	        	header( "Content-Length: " . $size );
	        }

			$this->readfile_chunked( $file_path ) or wp_die( __( 'File not found', 'wp-job-manager-company-listings' ) . ' <a href="' . esc_url( home_url() ) . '" class="wc-forward">' . __( 'Go to homepage', 'wp-job-manager-company-listings' ) . '</a>' );

        	exit;
		}
	}

	/**
	 * readfile_chunked
	 * Reads file in chunks so big downloads are possible without changing PHP.INI - http://codeigniter.com/wiki/Download_helper_for_large_files/
	 * @param    string $file
	 * @param    bool   $retbytes return bytes of file
	 * @return bool|int
	 * @todo Meaning of the return value? Last return is status of fclose?
	 */
	public static function readfile_chunked( $file, $retbytes = true ) {

		$chunksize = 1 * ( 1024 * 1024 );
		$buffer = '';
		$cnt = 0;

		$handle = @fopen( $file, 'r' );
		if ( $handle === FALSE ) {
			return FALSE;
		}

		while ( ! feof( $handle ) ) {
			$buffer = fread( $handle, $chunksize );
			echo $buffer;
			@ob_flush();
			@flush();

			if ( $retbytes ) {
				$cnt += strlen( $buffer );
			}
		}

		$status = fclose( $handle );

		if ( $retbytes && $status ) {
			return $cnt;
		}

		return $status;
	}

	/**
	 * Change label
	 */
	public function admin_head() {
		global $menu;

		$plural        = __( 'Companies', 'wp-job-manager-company-listings' );
		$count_companies = wp_count_posts( 'company_listings', 'readable' );

		foreach ( $menu as $key => $menu_item ) {
			if ( strpos( $menu_item[0], $plural ) === 0 ) {
				if ( $company_count = $count_companies->pending ) {
					$menu[ $key ][0] .= " <span class='awaiting-mod update-plugins count-$company_count'><span class='pending-count'>" . number_format_i18n( $count_companies->pending ) . "</span></span>" ;
				}
				break;
			}
		}
	}

	/**
	 * Hide company titles from users without access
	 * @param  string $title
	 * @param  int $post_or_id
	 * @return string
	 */
	public function company_title( $title, $post_or_id = null ) {
		if ( $post_or_id && 'company_listings' === get_post_type( $post_or_id ) && ! company_listings_user_can_view_company_name( $post_or_id ) ) {
			$title_parts    = explode( ' ', $title );
			$hidden_title[] = array_shift( $title_parts );
			foreach ( $title_parts as $title_part ) {
				$hidden_title[] = str_repeat( '*', strlen( $title_part ) );
			}
			return apply_filters( 'company_listings_hidden_company_title', implode( ' ', $hidden_title ), $title, $post_or_id );
		}
		return $title;
	}

	/**
	 * Add extra content when showing companies
	 */
	public function company_content( $content ) {
		global $post;

		if ( ! is_singular( 'company_listings' ) || ! in_the_loop() ) {
			return $content;
		}

		remove_filter( 'the_content', array( $this, 'company_content' ) );

		if ( $post->post_type == 'company_listings' ) {
			ob_start();

			get_company_listings_template_part( 'content-single', 'company_listings', 'wp-job-manager-company-listings', COMPANY_LISTINGS_PLUGIN_DIR . '/templates/' );

			$content = ob_get_clean();
		}

		add_filter( 'the_content', array( $this, 'company_content' ) );

		return $content;
	}

	/**
	 * The application content when the application method is an email
	 */
	public function contact_details_email() {
		global $post;

		$email   = get_post_meta( $post->ID, '_company_email', true );
		$subject = sprintf( __( 'Contact via the company for "%s" on %s', 'wp-job-manager-company-listings' ), single_post_title( '', false ), home_url() );

		get_job_manager_template( 'contact-details-email.php', array( 'email' => $email, 'subject' => $subject ), 'wp-job-manager-company-listings', COMPANY_LISTINGS_PLUGIN_DIR . '/templates/' );
	}

	/**
	 * Setup event to hide a company after X days
	 * @param  object $post
	 */
	public function setup_autohide_cron( $post ) {
		if ( ! is_object( $post ) ) {
			$post = get_post( $post );
		}
		if ( $post->post_type !== 'company_listings' ) {
			return;
		}

		add_post_meta( $post->ID, '_featured', 0, true );
		wp_clear_scheduled_hook( 'auto-hide-company', array( $post->ID ) );

		$company_listings_autohide = get_option( 'company_listings_autohide' );

		if ( $company_listings_autohide ) {
			wp_schedule_single_event( strtotime( "+{$company_listings_autohide} day" ), 'auto-hide-company', array( $post->ID ) );
		}
	}

	/**
	 * Hide a company
	 * @param  int
	 */
	public function hide_company( $company_id ) {
		$company = get_post( $company_id );
		if ( $company->post_status === 'publish' ) {
			$update_company = array( 'ID' => $company_id, 'post_status' => 'hidden' );
			wp_update_post( $update_company );
			wp_clear_scheduled_hook( 'auto-hide-company', array( $company_id ) );
		}
	}

	/**
	 * Maybe set menu_order if the featured status of a company is changed
	 */
	public function maybe_update_menu_order( $meta_id, $object_id, $meta_key, $_meta_value ) {
		if ( '_featured' !== $meta_key || 'company_listings' !== get_post_type( $object_id ) ) {
			return;
		}
		global $wpdb;

		if ( '1' == $_meta_value ) {
			$wpdb->update( $wpdb->posts, array( 'menu_order' => -1 ), array( 'ID' => $object_id ) );
		} else {
			$wpdb->update( $wpdb->posts, array( 'menu_order' => 0 ), array( 'ID' => $object_id, 'menu_order' => -1 ) );
		}

		clean_post_cache( $object_id );
	}

	/**
	 * Fix post name when wp_update_post changes it
	 * @param  array $data
	 * @return array
	 */
	public function fix_post_name( $data, $postarr ) {
		 if ( 'company_listings' === $data['post_type'] && 'pending' === $data['post_status'] && ! current_user_can( 'publish_posts' ) && isset( $postarr['post_name'] ) ) {
			$data['post_name'] = $postarr['post_name'];
		 }
		 return $data;
	}
}

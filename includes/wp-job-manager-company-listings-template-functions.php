<?php
/**
 * Template Functions
 *
 * Template functions specifically created for companies
 *
 * @author 		Mike Jolley
 * @category 	Core
 * @package 	Company Manager/Template
 * @version     1.0.0
 */

/**
 * Echo the location for a company/company
 * @param  boolean $map_link whether or not to link to the map on google maps
 * @param WP_Post|int $post (default: null)
 */
function the_company_metalocation( $map_link = true, $post = null ) {
	$location = get_the_company_metalocation( $post );

	if ( $location ) {
		if ( $map_link )
			echo apply_filters( 'the_company_metalocation_map_link', '<a class="google_map_link company-location" href="http://maps.google.com/maps?q=' . urlencode( $location ) . '&zoom=14&size=512x512&maptype=roadmap&sensor=false">' . $location . '</a>', $location, $post );
		else
			echo '<span class="company-location">' . $location . '</span>';
	}
}

/**
 * Get the location for a company/company
 *
 * @param WP_Post|int $post (default: null)
 * @return string
 */
function get_the_company_metalocation( $post = null ) {
	$post = get_post( $post );
	if ( $post->post_type !== 'company_listings' )
		return;

	return apply_filters( 'the_company_metalocation', $post->_company_location, $post );
}

/**
 * Display a companys given job title
 *
 * @param  string  $before
 * @param  string  $after
 * @param  boolean $echo
 * @param WP_Post|int $post (default: null)
 * @return string
 */
function the_company_metatitle( $before = '', $after = '', $echo = true, $post = null ) {
	$title = get_the_company_metatitle( $post );

	if ( strlen( $title ) == 0 )
		return;

	$title = esc_attr( strip_tags( $title ) );
	$title = $before . $title . $after;

	if ( $echo )
		echo '<span class="tagline">' . $title . '</span>';
	else
		return $title;
}

/**
 * Get a companys given job title
 *
 * @param WP_Post|int $post (default: null)
 * @return string
 */
function get_the_company_metatitle( $post = null ) {
	$post = get_post( $post );

	if ( $post->post_type !== 'company_listings' )
		return '';

	return apply_filters( 'the_company_metatitle', $post->_company_title, $post );
}

/**
 * Output the photo for the company/company
 *
 * @param string $size (default: 'full')
 * @param mixed $default (default: null)
 * @param WP_Post|int $post (default: null)
 */
function the_company_metaphoto( $size = 'thumbnail', $default = null, $post = null ) {
	$logo = get_the_company_metaphoto( $post );

	if ( $logo ) {

		if ( $size !== 'full' ) {
			$logo = job_manager_get_resized_image( $logo, $size );
		}

		echo '<img class="company_photo" src="' . $logo . '" alt="Photo" />';

	} elseif ( $default )
		echo '<img class="company_photo" src="' . $default . '" alt="Photo" />';
	else
		echo '<img class="company_photo" src="' . apply_filters( 'job_manager_default_company_logo', JOB_MANAGER_PLUGIN_URL . '/assets/images/company.png' ) . '" alt="Logo" />';
}

/**
 * Get the photo for the company/company
 *
 * @param WP_Post|int $post (default: null)
 * @return string
 */
function get_the_company_metaphoto( $post = null ) {
	$post = get_post( $post );
	if ( $post->post_type !== 'company_listings' )
		return;

	return apply_filters( 'the_company_metaphoto', get_the_post_thumbnail_url($post), $post );
}

/**
 * Output the category
 * @param WP_Post|int $post (default: null)
 */
function the_company_metacategory( $post = null ) {
	echo get_the_company_metacategory( $post );
}

/**
 * Get the category
 * @param WP_Post|int $post (default: null)
 * @return  string
 */
function get_the_company_metacategory( $post = null ) {
	$post = get_post( $post );
	if ( $post->post_type !== 'company_listings' )
		return '';

	if ( ! get_option( 'company_listings_enable_categories' ) )
		return '';

	$categories = wp_get_object_terms( $post->ID, 'company_category', array( 'fields' => 'names' ) );

	if ( is_wp_error( $categories ) ) {
		return '';
	}

	return implode( ', ', $categories );
}

/**
 * Outputs the jobs status
 *
 * @param WP_Post|int $post (default: null)
 */
function the_company_metastatus( $post = null ) {
	echo get_the_company_metastatus( $post );
}

/**
 * Gets the jobs status
 * @param WP_Post|int $post (default: null)
 * @return string
 */
function get_the_company_metastatus( $post = null ) {
	$post = get_post( $post );

	$status = $post->post_status;

	if ( $status == 'publish' )
		$status = __( 'Published', 'wp-job-manager-company-listings' );
	elseif ( $status == 'expired' )
		$status = __( 'Expired', 'wp-job-manager-company-listings' );
	elseif ( $status == 'pending' )
		$status = __( 'Pending Review', 'wp-job-manager-company-listings' );
	elseif ( $status == 'hidden' )
		$status = __( 'Hidden', 'wp-job-manager-company-listings' );
	else
		$status = __( 'Inactive', 'wp-job-manager-company-listings' );

	return apply_filters( 'the_company_metastatus', $status, $post );
}

/**
 * True if an the user can post a company. By default, you must be logged in.
 *
 * @return bool
 */
function company_listings_user_can_post_company() {
	$can_post = true;

	if ( ! is_user_logged_in() ) {
		if ( company_listings_user_requires_account() && ! company_listings_enable_registration() ) {
			$can_post = false;
		}
	}

	return apply_filters( 'company_listings_user_can_post_company', $can_post );
}

/**
 * True if registration is enabled.
 *
 * @return bool
 */
function company_listings_enable_registration() {
	return apply_filters( 'company_listings_enable_registration', get_option( 'company_listings_enable_registration' ) == 1 ? true : false );
}

/**
 * True if an account is required to post.
 *
 * @return bool
 */
function company_listings_user_requires_account() {
	return apply_filters( 'company_listings_user_requires_account', get_option( 'company_listings_user_requires_account' ) == 1 ? true : false );
}

/**
 * True if usernames are generated from email addresses.
 *
 * @return bool
 */
function company_listings_generate_username_from_email() {
	return apply_filters( 'company_listings_generate_username_from_email', get_option( 'company_listings_generate_username_from_email' ) == 1 ? true : false );
}

/**
 * Output the class
 *
 * @param string $class (default: '')
 * @param mixed $post_id (default: null)
 * @return void
 */
function company_class( $class = '', $post_id = null ) {
	echo 'class="' . join( ' ', get_company_class( $class, $post_id ) ) . '"';
}

/**
 * Get the class
 *
 * @access public
 * @return array
 */
function get_company_class( $class = '', $post_id = null ) {
	$post = get_post( $post_id );
	if ( $post->post_type !== 'company_listings' )
		return array();

	$classes = array();

	if ( empty( $post ) ) {
		return $classes;
	}

	$classes[] = 'company_listings';

	if ( is_company_featured( $post ) ) {
		$classes[] = 'company_featured';
	}

	return get_post_class( $classes, $post->ID );
}

/**
 * Output the company permalinks
 *
 * @param WP_Post|int $post (default: null)
 */
function the_company_metapermalink( $post = null ) {
	$post = get_post( $post );
	echo get_the_company_metapermalink( $post );
}

/**
 * Output the company links
 *
 * @param WP_Post|int $post (default: null)
 */
function the_company_metalinks( $post = null ) {
	$post = get_post( $post );
	get_job_manager_template( 'company_listings-links.php', array( 'post' => $post ), 'wp-job-manager-company-listings', COMPANY_LISTINGS_PLUGIN_DIR . '/templates/' );
}

/**
 * Output the company info
 *
 * @param WP_Post|int $post (default: null)
 */
function the_company_metainfo( $post = null ) {
	$post = get_post( $post );
	get_job_manager_template( 'company_listings-info.php', array( 'post' => $post ), 'wp-job-manager-company-listings', COMPANY_LISTINGS_PLUGIN_DIR . '/templates/' );
}

/**
 * Get the company permalinks
 *
 * @param WP_Post|int $post (default: null)
 * @return string
 */
function get_the_company_metapermalink( $post = null ) {
	$post = get_post( $post );
	$link = get_permalink( $post );

	return apply_filters( 'the_company_metapermalink', $link, $post );
}

/**
 * Returns true or false based on whether the company has any website links to display.
 * @param  object $post
 * @return bool
 */
function company_has_links( $post = null ) {
	return sizeof( get_company_links( $post ) ) ? true : false;
}

/**
 * Returns true or false based on whether the company has any info to display.
 * @param  object $post
 * @return bool
 */
function company_has_info( $post = null ) {
	return sizeof( get_company_info( $post ) ) ? true : false;
}

/**
 * Returns true or false based on whether the company has a file uploaded.
 * @param  object $post
 * @return bool
 */
function company_has_file( $post = null ) {
	return get_company_file() ? true : false;
}

/**
 * Returns an array of links defined for a company
 * @param  object $post
 * @return array
 */
function get_company_links( $post = null ) {
	$post = get_post( $post );

	return array_filter( (array) get_post_meta( $post->ID, '_links', true ) );
}

/**
 * Returns an array of info defined for a company
 * @param  object $post
 * @return array
 */
function get_company_info( $post = null ) {
	$post = get_post( $post );

	return array_filter( (array) get_post_meta( $post->ID, '_info', true ) );
}

/**
 * If multiple files have been attached to the company_file field, return the in array format.
 * @return array
 */
function get_company_files( $post = null ) {
	$post  = get_post( $post );
	$files = get_post_meta( $post->ID, '_company_file', true );
	$files = is_array( $files ) ? $files : array( $files );
	return $files;
}

/**
 * Returns the company file attached to a company.
 * @param  object $post
 * @return string
 */
function get_company_file( $post = null ) {
	$post = get_post( $post );
	$file = get_post_meta( $post->ID, '_company_file', true );
	return is_array( $file ) ? current( $file ) : $file;
}

/**
 * Returns a download link for a company file.
 * @param  object $post
 * @param  file key
 * @return string
 */
function get_company_file_download_url( $post = null, $key = 0 ) {
	$post = get_post( $post );
	return add_query_arg( array( 'download-company' => $post->ID, 'file-id' => $key ) );
}

/**
 * Return whether or not the company has been featured
 *
 * @param  object $post
 * @return boolean
 */
function is_company_featured( $post = null ) {
	$post = get_post( $post );

	return $post->_featured ? true : false;
}

/**
 * Output the company video
 */
function the_company_metavideo( $post = null ) {
	$video    = get_the_company_metavideo( $post );
	$video    = is_ssl() ? str_replace( 'http:', 'https:', $video ) : $video;
	$filetype = wp_check_filetype( $video );

	if ( ! empty( $filetype['ext'] ) ) {
		$video_embed = wp_video_shortcode( array( 'src' => $video ) );
	} else {
		$video_embed = wp_oembed_get( $video );
	}

	if ( $video_embed ) {
		echo '<div class="company-video">' . $video_embed . '</div>';
	}
}

/**
 * Get the company video URL
 *
 * @param mixed $post (default: null)
 * @return string
 */
function get_the_company_metavideo( $post = null ) {
	$post = get_post( $post );
	if ( $post->post_type !== 'company_listings' ) {
		return;
	}
	return apply_filters( 'the_company_metavideo', $post->_company_video, $post );
}

if ( ! function_exists( 'jmcl_output_content_wrapper' ) ) {

	/**
	 * Output the start of the page wrapper.
	 *
	 */
	function jmcl_output_content_wrapper() {
		get_company_listings_template( 'global/wrapper-start.php' );
	}
}
if ( ! function_exists( 'jmcl_output_content_wrapper_end' ) ) {

	/**
	 * Output the end of the page wrapper.
	 *
	 */
	function jmcl_output_content_wrapper_end() {
		get_company_listings_template( 'global/wrapper-end.php' );
	}
}

if ( ! function_exists( 'jmcl_get_sidebar' ) ) {

	/**
	 * Get the sidebar template.
	 *
	 */
	function jmcl_get_sidebar() {
		get_company_listings_template( 'global/sidebar.php' );
	}
}

/**
 * Output the Description tab content on the company page
 */
function company_listings_company_about_tab() {
	global $post;
	?>
	<div class="cmp-about">

		<h3 class="container-title"><?php printf( __( 'About %s', 'wp-job-manager-company-listings' ), get_the_title() )  ?></h3>

		<?php the_company_metavideo() ?>

		<div class="cmp-content">
			<?php echo $post->post_content  ?>
		</div>

		<div class="cmp-contact-info">
			<p></p>
			<h3 class="container-title"><?php _e( 'Contact Info', 'wp-job-manager-company-listings' ) ?></h3>
			<table>
				<tbody>
				<tr>
					<td class="label"><?php _e('Website', 'wp-job-manager-company-listings') ?></td>
					<td class="data"><?php echo make_clickable( get_post_meta( $post->ID, '_company_website', true ) ) ?></td>
				</tr>
				<tr>
					<td class="label"><?php _e('Twitter', 'wp-job-manager-company-listings') ?></td>
					<td class="data"><?php echo make_clickable(  'https://twitter.com/' .get_post_meta( $post->ID, '_company_twitter', true ) ) ?></td>
				</tr>
				<tr>
					<td class="label"><?php _e('video', 'wp-job-manager-company-listings') ?></td>
					<td class="data"><?php echo make_clickable( get_post_meta( $post->ID, '_company_video', true ) ) ?></td>
				</tr>
				</tbody>
			</table>
		</div>
	</div>
<?php
}

/**
 * Output the jobs tab content on the company page
 */
function company_listings_company_jobs_tab() { ?>
	<div class="cmp-posted-jobs">
		<p></p>
		<h3 class="container-title"><?php printf( __( 'Jobs at %s', 'wp-job-manager-company-listings' ), get_the_title() )  ?></h3>
		<?php echo do_shortcode('[jobs]'); ?>
	</div> <?php
}
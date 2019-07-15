<?php
/**
 * Single view Company information box
 *
 * Hooked into single_job_listing_start priority 30
 *
 * This template can be overridden by copying it to yourtheme/job_manager/content-single-job_listing-company.php.
 *
 * @see         https://wpjobmanager.com/document/template-overrides/
 * @author      Automattic
 * @package     WP Job Manager
 * @category    Template
 * @since       1.14.0
 * @version     1.31.1
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! jmcl_get_the_company() ) {
	return;
}

$company = get_post( jmcl_get_the_company() );
?>
<div class="company">
	<?php the_company_logo( 'thumbnail', '', $company ); ?>

	<p class="name">
		<?php if ( $website = get_the_company_website() ) : ?>
			<a class="website" href="<?php echo esc_url( $website ); ?>" target="_blank" rel="nofollow"><?php esc_html_e( 'Website', 'wp-job-manager-company-listings' ); ?></a>
		<?php endif; ?>
		<?php the_company_twitter(); ?>
		<?php the_company_name( '<strong><a href="' . esc_url( get_the_permalink( $company ) ) . '" class="company-name">', '</strong></a>' ); ?>
	</p>
	<?php the_company_tagline( '<p class="tagline">', '</p>' ); ?>
	<?php the_company_video(); ?>
</div>

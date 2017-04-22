<?php
/**
 * The template for displaying company content in the single-company.php template
 *
 * This template can be overridden by copying it to yourtheme/buddypress_job_manager/content-single-job_company.php.
 *
 * HOWEVER, on occasion BuddyPress Job Manager will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @author 		Kishore
 * @package 	BuddyPress Job Manager/Templates
 * @version     1.1
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

?>

<?php

do_action( 'jmcl_before_single_company' );

if ( post_password_required() ) {
	echo get_the_password_form();
	return;
}
?>

<div id="company-<?php the_ID(); ?>" <?php post_class(); ?>>

	<?php
	do_action( 'jmcl_before_single_company_summary' );
	?>

	<div class="summary entry-summary cmp-entry-summary">

		<div class="cmp-top-card-module__container container-with-shadow">
			<?php the_post_thumbnail( array(180,180), array( 'class'=> 'cmp-top-card-module__logo' ) ); ?>

			<div class="cmp-top-card-module__details">
				<div>
					<h1>
						<?php the_title() ?>
						<span><?php echo get_post_meta( $post->ID, '_company_tagline', true ) ?></span>
					</h1>
					<p class="company-main-info-company-descriptions%">
						<span class="company-industries cmp-top-card-module__dot-separated-list"><?php echo get_post_meta( $post->ID, '_company_industries', true ); ?></span>
                        <span class="company-size cmp-top-card-module__dot-separated-list">
                           <?php printf( __( '%d+ employees ', 'wp-job-manager-company-listings' ), get_post_meta( $post->ID, '_company_size', true ) ) ?>
                        </span>
						<span ><?php echo get_post_meta( $post->ID, '_company_location', true ) ?></span>
					</p>
				</div>
			</div>
		</div>

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

		<div class="cmp-posted-jobs">
			<p></p>
			<h3 class="container-title"><?php _e( 'Posted Jobs', 'wp-job-manager-company-listings' ) ?></h3>
			<?php echo do_shortcode('[jobs]'); ?>
		</div>

		<?php
		do_action( 'jmcl_single_company_summary' );
		?>

	</div><!-- .summary -->

	<?php
	do_action( 'jmcl_after_single_company_summary' );
	?>

</div><!-- #product-<?php the_ID(); ?> -->

<?php do_action( 'jmcl_after_single_company' ); ?>

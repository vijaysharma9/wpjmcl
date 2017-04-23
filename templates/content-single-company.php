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

	<div class="jmcl-item-header">
		<div class="jmcl-item-header-logo">
			<a href="<?php the_post_thumbnail_url('full') ?>">
				<?php the_post_thumbnail( array(180,180), array( 'class'=> 'cmp-top-card-module__logo' ) ); ?>
			</a>
		</div>
		<div class="jmcl-item-header-content">
			<a class="company-title" href=""><h3><?php the_title() ?></h3></a>
			<p class="company-tagline"><?php echo the_company_metatitle() ?></p>
			<div class="company-location"><?php the_company_metalocation() ?></div>
			<ul class="company-info">
				<?php the_company_metainfo() ?>
			</ul>
		</div>
	</div>

	<div class="summary entry-summary cmp-entry-summary">

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

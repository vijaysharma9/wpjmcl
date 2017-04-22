<?php
/**
 * The Template for displaying all single company
 *
 * This template can be overridden by copying it to yourtheme/company_listings/single-company.php.
 *
 * HOWEVER, on occasion Buddypress Job Manager will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @author 		Kishore
 * @package 	Company Listings/Templates
 * @version     1.1
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

get_header(); ?>

<?php
do_action( 'jmcl_before_main_content' );
?>

<?php while ( have_posts() ) : the_post(); ?>


    <?php get_company_listings_template_part( 'content-single', 'company' ); ?>

<?php endwhile; // end of the loop. ?>

<?php
do_action( 'jmcl_after_main_content' );
?>

<?php
do_action( 'jmcl_sidebar' );
?>

<?php get_footer();

/* Omit closing PHP tag at the end of PHP files to avoid "headers already sent" issues. */

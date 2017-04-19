<?php 
	$company_directory_page_id = get_option( 'company_listings_company_directory_page_id' );
?>
<?php if ( defined( 'DOING_AJAX' ) || get_the_ID() == $company_directory_page_id ) : ?>
	<li class="no_companies_found"><?php _e( 'There are no listings matching your search.', 'wp-job-manager' ); ?></li>
<?php else : ?>
	<p class="no_companies_found"><?php _e( 'There are currently no companies.', 'wp-job-manager' ); ?></p>
<?php endif; ?>
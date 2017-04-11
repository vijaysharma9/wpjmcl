<?php if ( company_has_info() ) : ?>
	<div class="company-info-wrap">
		<h2><?php _e( 'Company Info', 'wp-job-manager-company-listings' ); ?></h2>
		<?php foreach( get_company_info() as $info ) : ?>
			<?php get_job_manager_template( 'content-company-info.php', array( 'post' => $post, 'info' => $info ), 'wp-job-manager-company-listings', COMPANY_LISTINGS_PLUGIN_DIR . '/templates/' ); ?>
		<?php endforeach; ?>
	</div>
<?php endif; ?>
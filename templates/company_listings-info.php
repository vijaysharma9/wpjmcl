<?php if ( company_has_info() ) : ?>
	<ul class="company-info-wrap">
		<?php foreach( get_company_info() as $info ) : ?>
			<?php get_job_manager_template( 'content-company_listings-info.php', array( 'post' => $post, 'info' => $info ), 'wp-job-manager-company-listings', COMPANY_LISTINGS_PLUGIN_DIR . '/templates/' ); ?>
		<?php endforeach; ?>
	</ul>
<?php endif; ?>
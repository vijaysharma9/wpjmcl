<?php if ( company_has_links() || company_has_file() ) : ?>
	<ul class="company-links">
		<?php foreach( get_company_links() as $link ) : ?>
			<?php get_job_manager_template( 'content-company-link.php', array( 'post' => $post, 'link' => $link ), 'wp-job-manager-company-listings', COMPANY_LISTINGS_PLUGIN_DIR . '/templates/' ); ?>
		<?php endforeach; ?>
		<?php if ( company_has_file() ) : ?>
			<?php get_job_manager_template( 'content-company-file.php', array( 'post' => $post ), 'wp-job-manager-company-listings', COMPANY_LISTINGS_PLUGIN_DIR . '/templates/' ); ?>
		<?php endif; ?>
	</ul>
<?php endif; ?>
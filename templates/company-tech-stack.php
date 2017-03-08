<?php if ( company_has_tech_stack() ) : ?>
	<h2><?php _e( 'Tech Stack', 'wp-job-manager-company-listings' ); ?></h2>
		<ul class="company-listings-tech-stack">
		<?php foreach( get_company_tech_stack() as $stack ) : ?>
			<?php get_job_manager_template( 'content-company-tech-stack.php', array( 'post' => $post, 'stack' => $stack ), 'wp-job-manager-company-listings', COMPANY_LISTINGS_PLUGIN_DIR . '/templates/' ); ?>
		<?php endforeach; ?>
	</ul>
<?php endif; ?>
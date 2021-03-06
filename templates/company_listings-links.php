<?php if ( company_has_links() || company_has_file() ) : ?>
	<div class="cmp-links">
		<h3 class="container-title"><?php _e( 'URL(s)', 'wp-job-manager-company-listings' ) ?></h3>

		<table>
			<tbody>
				<?php foreach( get_company_links() as $link ) : ?>
					<?php get_job_manager_template( 'content-company_listings-link.php', array( 'post' => $post, 'link' => $link ), 'wp-job-manager-company-listings', COMPANY_LISTINGS_PLUGIN_DIR . '/templates/' ); ?>
				<?php endforeach; ?>
				<?php if ( company_has_file() ) : ?>
					<?php get_job_manager_template( 'content-company_listings-file.php', array( 'post' => $post ), 'wp-job-manager-company-listings', COMPANY_LISTINGS_PLUGIN_DIR . '/templates/' ); ?>
				<?php endif; ?>
			</tbody>
		</table>
	</div>
<?php endif; ?>

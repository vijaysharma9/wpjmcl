<?php if ( company_has_perks() ) : ?>
	<div class="cmp-perks">
		<h3 class="container-title"><?php _e( 'Perks', 'wp-job-manager-company-listings' ) ?></h3>

		<ul class="perks-list">
			<?php foreach( get_company_perks() as $perks ) : ?>
				<li><?php echo esc_html( $perks['notes'] ); ?></li>
			<?php endforeach; ?>
		</ul>
	</div>
<?php endif; ?>

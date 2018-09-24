<div class="cmp-about">
	<h3 class="container-title"><?php printf( __( 'About %s', 'wp-job-manager-company-listings' ), get_the_title() )  ?></h3>

	<?php the_company_metavideo(); ?>

	<div class="cmp-content">
		<?php the_content();  ?>
	</div>

	<?php if ( $categories = get_the_company_metacategory() ): ?>
		<p class="company-categories">
			<strong><?php esc_html_e( 'Industry Type', 'wp-job-manager-company-listings' ); ?></strong>:
			<?php echo $categories; ?>
		</p>
	<?php endif ?>

	<?php if ( $skills = get_the_company_metaskills() ): ?>
		<p class="company-skills">
			<strong><?php esc_html_e( 'Skills', 'wp-job-manager-company-listings' ); ?></strong>:
			<?php echo $skills; ?>
		</p>
	<?php endif ?>
</div>

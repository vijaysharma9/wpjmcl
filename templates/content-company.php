<?php $category = get_the_company_metacategory(); ?>
<li <?php company_class(); ?>>
	<a href="<?php the_company_metapermalink(); ?>">
		<?php the_company_metaphoto(); ?>
		<div class="company-column">
			<h3><?php the_title(); ?></h3>
			<div class="company-title">
				<?php the_company_metatitle( '<strong>', '</strong> ' ); ?>
			</div>
		</div>
		<div class="company-location-column">
			<?php the_company_metalocation( false ); ?>
		</div>
		<div class="company-posted-column <?php if ( $category ) : ?>company-meta<?php endif; ?>">
			<date><?php printf( __( '%s ago', 'wp-job-manager-company-listings' ), human_time_diff( get_post_time( 'U' ), current_time( 'timestamp' ) ) ); ?></date>

			<?php if ( $category ) : ?>
				<div class="company-category">
					<?php echo $category ?>
				</div>
			<?php endif; ?>
		</div>
	</a>
</li>
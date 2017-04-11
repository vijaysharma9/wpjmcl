<?php if ( company_listings_user_can_view_company( $post->ID ) ) : ?>
	<div class="single-company-content">

		<?php do_action( 'single_company_start' ); ?>

		<div class="company-aside">
			<?php the_company_metaphoto(); ?>
			<?php the_company_metalinks(); ?>
			<p class="job-title"><?php the_company_metatitle(); ?></p>
			<p class="location"><?php the_company_metalocation(); ?></p>
			<?php the_company_metainfo(); ?>
			<?php the_company_metavideo(); ?>
		</div>

		<div class="company_description">
			<?php echo apply_filters( 'the_company_metadescription', get_the_content() ); ?>
		</div>

		<?php if ( ( $skills = wp_get_object_terms( $post->ID, 'company_skill', array( 'fields' => 'names' ) ) ) && is_array( $skills ) ) : ?>
			<h2><?php _e( 'Skills', 'wp-job-manager-company-listings' ); ?></h2>
			<ul class="company-listings-skills">
				<?php echo '<li>' . implode( '</li><li>', $skills ) . '</li>'; ?>
			</ul>
		<?php endif; ?>

		<?php if ( $items = get_post_meta( $post->ID, '_company_perk', true ) ) : ?>
			<h2><?php _e( 'Perks', 'wp-job-manager-company-listings' ); ?></h2>
			<ul class="company-listings-perk">
			<?php
				foreach( $items as $item ) : ?>
					<li>
						<?php echo wpautop( wptexturize( $item['notes'] ) ); ?>
					</li>

				<?php endforeach;
			?>
			</ul>
		<?php endif; ?>

		<?php if ( $items = get_post_meta( $post->ID, '_company_press', true ) ) : ?>
			<h2><?php _e( 'Press', 'wp-job-manager-company-listings' ); ?></h2>
			<dl class="company-listings-experience">
			<?php
				foreach( $items as $item ) : ?>

					<dt>
						<h3>
							<a target="_blank" href="<?php echo esc_html( $item['notes'] ); ?>"> 
								<?php echo esc_html( $item['job_title'] ); ?>
							</a>
						</h3>
					</dt>

				<?php endforeach;
			?>
			</dl>
		<?php endif; ?>

		<ul class="meta">
			<?php do_action( 'single_company_meta_start' ); ?>

			<?php if ( get_the_company_metacategory() ) : ?>
				<li class="company-category"><?php the_company_metacategory(); ?></li>
			<?php endif; ?>

			<li class="date-posted" itemprop="datePosted"><date><?php printf( __( 'Updated %s ago', 'wp-job-manager-company-listings' ), human_time_diff( get_the_modified_time( 'U' ), current_time( 'timestamp' ) ) ); ?></date></li>

			<?php do_action( 'single_company_meta_end' ); ?>
		</ul>

		<?php do_action( 'single_company_end' ); ?>
	</div>
<?php else : ?>

	<?php get_job_manager_template_part( 'access-denied', 'single-company', 'wp-job-manager-company-listings', COMPANY_LISTINGS_PLUGIN_DIR . '/templates/' ); ?>

<?php endif; ?>
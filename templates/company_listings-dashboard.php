<?php
$submission_limit = get_option( 'company_listings_submission_limit' );
$submit_company_form_page_id = get_option( 'company_listings_submit_company_form_page_id' );
?>
<div id="company-listings-company-dashboard">
	<p><?php echo _n( 'Your company can be viewed, edited or removed below.', 'Your company(s) can be viewed, edited or removed below.', company_listings_count_user_companies(), 'wp-job-manager-company-listings' ); ?></p>
	<table class="company-listings-companies">
		<thead>
			<tr>
				<?php foreach ( $company_dashboard_columns as $key => $column ) : ?>
					<th class="<?php echo esc_attr( $key ); ?>"><?php echo esc_html( $column ); ?></th>
				<?php endforeach; ?>
			</tr>
		</thead>
		<tbody>
			<?php if ( ! $companies ) : ?>
				<tr>
					<td colspan="<?php echo sizeof( $company_dashboard_columns ); ?>"><?php _e( 'You do not have any active company listings.', 'wp-job-manager-company-listings' ); ?></td>
				</tr>
			<?php else : ?>
				<?php foreach ( $companies as $company ) : ?>
					<tr>
						<?php foreach ( $company_dashboard_columns as $key => $column ) : ?>
							<td class="<?php echo esc_attr( $key ); ?>">
								<?php if ( 'company-title' === $key ) : ?>
									<?php if ( $company->post_status == 'publish' ) : ?>
										<a href="<?php echo get_permalink( $company->ID ); ?>"><?php echo esc_html( $company->post_title ); ?></a>
									<?php else : ?>
										<?php echo esc_html( $company->post_title ); ?> <small>(<?php the_company_metastatus( $company ); ?>)</small>
									<?php endif; ?>
									<ul class="company-dashboard-actions">
										<?php
											$actions = array();

											switch ( $company->post_status ) {
												case 'publish' :
													$actions['edit'] = array( 'label' => __( 'Edit', 'wp-job-manager-company-listings' ), 'nonce' => false );
													$actions['hide'] = array( 'label' => __( 'Hide', 'wp-job-manager-company-listings' ), 'nonce' => true );
												break;
												case 'hidden' :
													$actions['edit'] = array( 'label' => __( 'Edit', 'wp-job-manager-company-listings' ), 'nonce' => false );
													$actions['publish'] = array( 'label' => __( 'Publish', 'wp-job-manager-company-listings' ), 'nonce' => true );
												break;
											}

											$actions['delete'] = array( 'label' => __( 'Delete', 'wp-job-manager-company-listings' ), 'nonce' => true );

											$actions = apply_filters( 'company_listings_my_company_actions', $actions, $company );

											foreach ( $actions as $action => $value ) {
												$action_url = add_query_arg( array( 'action' => $action, 'company_id' => $company->ID ) );
												if ( $value['nonce'] )
													$action_url = wp_nonce_url( $action_url, 'company_listings_my_company_actions' );
												echo '<li><a href="' . $action_url . '" class="company-dashboard-action-' . $action . '">' . $value['label'] . '</a></li>';
											}
										?>
									</ul>
								<?php elseif ( 'company-title' === $key ) : ?>
									<?php the_company_metatitle( '', '', true, $company ); ?>
								<?php elseif ( 'company-location' === $key ) : ?>
									<?php the_company_metalocation( false, $company ); ?></td>
								<?php elseif ( 'company-category' === $key ) : ?>
									<?php the_company_metacategory( $company ); ?>
								<?php elseif ( 'status' === $key ) : ?>
									<?php the_company_metastatus( $company ); ?>
								<?php elseif ( 'date' === $key ) : ?>
									<?php echo date_i18n( get_option( 'date_format' ), strtotime( $company->post_date ) ); ?>
								<?php else : ?>
									<?php do_action( 'company_listings_company_dashboard_column_' . $key, $company ); ?>
								<?php endif; ?>
							</td>
						<?php endforeach; ?>
					</tr>
				<?php endforeach; ?>
			<?php endif; ?>
		</tbody>
	</table>
	<?php get_job_manager_template( 'pagination.php', array( 'max_num_pages' => $max_num_pages ) ); ?>
</div>

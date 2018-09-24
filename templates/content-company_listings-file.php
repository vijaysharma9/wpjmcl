<?php
if ( ( $company_files = get_company_files() ) && apply_filters( 'company_listings_user_can_download_company_file', true, $post->ID ) ) : ?>
	<tr>
		<td class="label"><?php esc_html_e( 'File', 'wp-job-manager-company-listings' ); ?></td>
		<td class="data">
			<?php foreach ( $company_files as $key => $company_file ) : ?>
				<div class="company-file company-file-<?php echo substr( strrchr( $company_file, '.' ), 1 ); ?>">
					<a rel="nofollow" target="_blank" href="<?php echo esc_url( get_company_file_download_url( null, $key ) ); ?>"><?php echo basename( $company_file ); ?></a>
				</div>
			<?php endforeach; ?>
		</td>
	</tr>
<?php endif; ?>

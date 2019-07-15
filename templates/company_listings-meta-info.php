<?php if ( company_has_info() ) : ?>
	<div class="cmp-meta-info">
		<h3 class="container-title"><?php _e( 'Info(s)', 'wp-job-manager-company-listings' ) ?></h3>

		<table>
			<tbody>
				<?php foreach( get_company_info() as $info ) : ?>
					<tr>
						<td class="label"><?php echo esc_html( $info['name'] ); ?></td>
						<td class="data"><?php echo esc_html( $info['info'] ); ?></td>
					</tr>
				<?php endforeach; ?>
			</tbody>
		</table>
	</div>
<?php endif; ?>

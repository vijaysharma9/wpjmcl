<?php if ( company_has_press() ) : ?>
	<div class="cmp-press">
		<h3 class="container-title"><?php _e( 'Press', 'wp-job-manager-company-listings' ) ?></h3>

		<table>
			<tbody>
				<?php foreach ( get_company_press() as $press ): ?>
					<tr>
						<td class="label"><?php echo esc_html( $press['job_title'] ); ?></td>
						<td class="data"><?php echo esc_html( $press['notes'] ); ?></td>
					</tr>
				<?php endforeach ?>
			</tbody>
		</table>
	</div>
<?php endif; ?>

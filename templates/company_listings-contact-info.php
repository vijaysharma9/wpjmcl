<?php

global $post;

$company_website = get_post_meta( $post->ID, '_company_website', true );
$company_twitter = get_post_meta( $post->ID, '_company_twitter', true );
$company_video = get_post_meta( $post->ID, '_company_video', true );
?>

<?php if ( $company_website || $company_twitter || $company_video ): ?>

	<div class="cmp-contact-info">
		<h3 class="container-title"><?php _e( 'Contact Info', 'wp-job-manager-company-listings' ) ?></h3>

		<table>
			<tbody>
				<?php if ( $company_website ): ?>
					<tr>
						<td class="label"><?php _e('Website', 'wp-job-manager-company-listings') ?></td>
						<td class="data"><a href="<?php echo esc_url( $company_website ); ?>" target="_blank"><?php echo esc_url( $company_website ); ?></a></td>
					</tr>
				<?php endif ?>

				<?php if ( $company_twitter ): ?>
					<tr>
						<td class="label"><?php _e('Twitter', 'wp-job-manager-company-listings') ?></td>
						<td class="data"><a href="<?php echo 'https://twitter.com/' . $company_twitter; ?>" target="_blank"><?php echo 'https://twitter.com/' . $company_twitter; ?></a></td>
					</tr>
				<?php endif ?>

				<?php if ( $company_video ): ?>
					<tr>
						<td class="label"><?php _e('Video', 'wp-job-manager-company-listings') ?></td>
						<td class="data"><a href="<?php echo esc_url( $company_video ); ?>" target="_blank"><?php echo esc_url( $company_video ); ?></a></td>
					</tr>
				<?php endif ?>
			</tbody>
		</table>
	</div>

<?php endif ?>

<?php
$parsed_url = parse_url( $link['url'] );
$host       = isset( $parsed_url['host'] ) ? current( explode( '.', $parsed_url['host'] ) ) : '';
?>

<tr class="company-link company-link-<?php echo esc_attr( sanitize_title( $host ) ); ?>">
	<td class="label"><?php echo esc_html( $link['name'] ); ?></td>
	<td class="data"><a rel="nofollow" href="<?php echo esc_url( $link['url'] ); ?>" target="_blank"><?php echo esc_html( $link['url'] ); ?></a></td>
</tr>

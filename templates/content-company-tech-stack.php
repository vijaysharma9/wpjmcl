<?php
$parsed_info = parse_url( $stack['stack'] );
$host       = isset( $parsed_info['host'] ) ? current( explode( '.', $parsed_info['host'] ) ) : '';
?>
<li class="company-info company-info-<?php echo esc_attr( sanitize_title( $host ) ); ?>">
	<span><?php echo esc_html( $stack['stack'] ); ?></span>&nbsp; &nbsp;<span><?php echo esc_html( $stack['notes'] ); ?> </span>
</li>
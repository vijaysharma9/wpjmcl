<?php
$parsed_info = parse_url( $info['info'] );
$host       = isset( $parsed_info['host'] ) ? current( explode( '.', $parsed_info['host'] ) ) : '';
?>
<div class="company-info company-info-<?php echo esc_attr( sanitize_title( $host ) ); ?>">
	<label><?php echo esc_html( $info['name'] ); ?></label>:
	<span  class="value"><?php echo esc_html( $info['info'] ); ?> </span>
</div>
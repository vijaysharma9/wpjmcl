<div class="company-directory">
	<strong class="xs-mr1 xs-block sm-inline-block xs-mbh0 sm-mb0"><?php _e('Find by name:', 'wp-job-manager-company-listings' ) ?></strong>
	<ul class="bare-list inline-list">
		<li class="inline-list__item xs-mlh0">
		  	<a href="<?php echo str_replace('?','', esc_url( add_query_arg( 'company-numeric','', get_permalink() ) )); ?>">#</a>
		</li>
		<?php
		$alphas = range('A', 'Z');
		
		foreach ($alphas as $letter) {
		  ?>
		  <li class="inline-list__item xs-mlh0">
		  	<a href="<?php echo str_replace('?','',esc_url( add_query_arg( $letter,'', get_permalink() ) )); ?>"><?php echo $letter; ?></a>
		  </li>
		  <?php
		}
		?>
	</ul>
</div>

<div class="company-dir-title">
	<h2><?php printf( __( ' %1$s Companies', 'wp-job-manager-company-listings' ),  get_query_var('fpage') ); ?></h2>
</div>

<ul class="company-dir-list">
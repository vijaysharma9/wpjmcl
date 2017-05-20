<div class="company-directory">

	<div class="company-directory-search">
		<form role="search" method="get" id="company-search-form" action="<?php jmcl_search_url(); ?>">
			<div>
				<label class="screen-reader-text hidden" for="jmcl_search"><?php esc_html_e( 'Search for:', 'wp-job-manager-company-listings' ); ?></label>
				<input type="hidden" name="action" value="jmcl-search-request" />
				<input type="text" value="<?php echo esc_attr( jmcl_get_search_terms() ); ?>" name="search" id="jmcl_search" />
				<input class="button" type="submit" id="jmcl_search_submit" value="<?php esc_attr_e( 'Search', 'wp-job-manager-company-listings' ); ?>" />
			</div>
		</form>
	</div>

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
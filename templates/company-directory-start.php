<div class="company-directory">
	<strong class="xs-mr1 xs-block sm-inline-block xs-mbh0 sm-mb0">Find by name:</strong>
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
<ul class="company-directory">
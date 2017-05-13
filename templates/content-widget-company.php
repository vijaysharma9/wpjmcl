<li <?php company_class(); ?>>
	<a href="<?php the_company_metapermalink(); ?>">
		<div class='company_listings'>
			<h3><?php the_title(); ?></h3>
		</div>
		<ul class="meta">
			<li class="company-title"><?php the_company_metatitle(); ?></li>
			<li class="company-location"><?php the_company_metalocation( false ); ?></li>
		</ul>
	</a>
</li>
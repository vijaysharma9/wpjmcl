<?php $category = get_the_company_metacategory(); ?>
<li <?php company_class(); ?>>
	<a href="<?php the_company_metapermalink(); ?>">
		<h3><?php the_title(); ?></h3>
	</a>
</li>
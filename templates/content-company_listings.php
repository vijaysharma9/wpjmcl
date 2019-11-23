<?php $category = get_the_company_metacategory(); ?>
<li <?php company_class(); ?>>
	<a href="<?php the_company_metapermalink(); ?>">
		<?php the_company_metaphoto(); ?>
		<div class="company-column">
			<h3><strong><?php the_title(); ?></strong></h3>
			<?php the_company_metalocation( false ); ?>
			<div class="company-title">
				<?php the_company_metatitle( '<p>', '</p> ' ); ?>
			</div>
		</div>
	</a>
</li>
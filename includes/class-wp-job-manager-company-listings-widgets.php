<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'WP_Job_Manager_Widget' ) ) {
	return;
}

/**
 * Recent Companies Widget
 */
class WP_Job_Manager_Company_Listings_Widget_Recent_Company extends WP_Job_Manager_Widget {

	/**
	 * Constructor
	 */
	public function __construct() {
		global $wp_post_types;

		$this->widget_cssclass    = 'job_manager widget_recent_companies';
		$this->widget_description = __( 'Display a list of recent listings on your site, optionally matching a keyword and location.', 'wp-job-manager-company-listings' );
		$this->widget_id          = 'widget_recent_companies';
		$this->widget_name        = sprintf( __( 'Recent %s', 'wp-job-manager-company-listings' ), $wp_post_types['company']->labels->name );
		$this->settings           = array(
			'title' => array(
				'type'  => 'text',
				'std'   => sprintf( __( 'Recent %s', 'wp-job-manager-company-listings' ), $wp_post_types['company']->labels->name ),
				'label' => __( 'Title', 'wp-job-manager-company-listings' )
			),
			'keyword' => array(
				'type'  => 'text',
				'std'   => '',
				'label' => __( 'Keyword', 'wp-job-manager-company-listings' )
			),
			'location' => array(
				'type'  => 'text',
				'std'   => '',
				'label' => __( 'Location', 'wp-job-manager-company-listings' )
			),
			'number' => array(
				'type'  => 'number',
				'step'  => 1,
				'min'   => 1,
				'max'   => '',
				'std'   => 10,
				'label' => __( 'Number of listings to show', 'wp-job-manager-company-listings' )
			)
		);
		$this->register();
	}

	/**
	 * widget function.
	 *
	 * @see WP_Widget
	 * @access public
	 * @param array $args
	 * @param array $instance
	 */
	public function widget( $args, $instance ) {
		if ( ! company_listings_user_can_browse_companies() ) {
			return;
		}

		if ( $this->get_cached_widget( $args ) ) {
			return;
		}

		ob_start();

		extract( $args );

		$title   = apply_filters( 'widget_title', $instance['title'], $instance, $this->id_base );
		$number  = absint( $instance['number'] );
		$companies = get_companies( array(
			'search_location'   => isset( $instance['location'] ) ? $instance['location'] : '',
			'search_keywords'   => isset( $instance['keyword'] ) ? $instance['keyword'] : '',
			'posts_per_page'    => $number,
			'orderby'           => 'date',
			'order'             => 'DESC',
		) );

		if ( $companies->have_posts() ) : ?>

			<?php echo $before_widget; ?>

			<?php if ( $title ) echo $before_title . $title . $after_title; ?>

			<ul class="companies">

				<?php while ( $companies->have_posts() ) : $companies->the_post(); ?>

					<?php get_company_listings_template_part( 'content-widget', 'company', 'wp-job-manager-company-listings', COMPANY_LISTINGS_PLUGIN_DIR . '/templates/' ); ?>

				<?php endwhile; ?>

			</ul>

			<?php echo $after_widget; ?>

		<?php else : ?>

			<?php get_company_listings_template_part( 'content-widget', 'no-companies-found', 'wp-job-manager-company-listings', COMPANY_LISTINGS_PLUGIN_DIR . '/templates/' ); ?>

		<?php endif;

		wp_reset_postdata();

		$content = ob_get_clean();

		echo $content;

		$this->cache_widget( $args, $content );
	}
}

/**
 * Featured Companies Widget
 */
class WP_Job_Manager_Company_Listings_Widget_Featured_Company extends WP_Job_Manager_Widget {

	/**
	 * Constructor
	 */
	public function __construct() {
		global $wp_post_types;

		$this->widget_cssclass    = 'job_manager widget_featured_companies';
		$this->widget_description = __( 'Display a list of featured listings on your site.', 'wp-job-manager-company-listings' );
		$this->widget_id          = 'widget_featured_companies';
		$this->widget_name        = sprintf( __( 'Featured %s', 'wp-job-manager-company-listings' ), $wp_post_types['company']->labels->name );
		$this->settings           = array(
			'title' => array(
				'type'  => 'text',
				'std'   => sprintf( __( 'Featured %s', 'wp-job-manager-company-listings' ), $wp_post_types['company']->labels->name ),
				'label' => __( 'Title', 'wp-job-manager-company-listings' )
			),
			'number' => array(
				'type'  => 'number',
				'step'  => 1,
				'min'   => 1,
				'max'   => '',
				'std'   => 10,
				'label' => __( 'Number of listings to show', 'wp-job-manager-company-listings' )
			)
		);
		$this->register();
	}

	/**
	 * widget function.
	 *
	 * @see WP_Widget
	 * @access public
	 * @param array $args
	 * @param array $instance
	 */
	public function widget( $args, $instance ) {
		if ( ! company_listings_user_can_browse_companies() ) {
			return;
		}

		if ( $this->get_cached_widget( $args ) ) {
			return;
		}

		ob_start();

		extract( $args );

		$title   = apply_filters( 'widget_title', $instance['title'], $instance, $this->id_base );
		$number  = absint( $instance['number'] );
		$companies = get_companies( array(
			'posts_per_page' => $number,
			'orderby'        => 'date',
			'order'          => 'DESC',
			'featured'       => true
		) );

		if ( $companies->have_posts() ) : ?>

			<?php echo $before_widget; ?>

			<?php if ( $title ) echo $before_title . $title . $after_title; ?>

			<ul class="companies">

				<?php while ( $companies->have_posts() ) : $companies->the_post(); ?>

					<?php get_company_listings_template_part( 'content-widget', 'company', 'wp-job-manager-company-listings', COMPANY_LISTINGS_PLUGIN_DIR . '/templates/' ); ?>

				<?php endwhile; ?>

			</ul>

			<?php echo $after_widget; ?>

		<?php else : ?>

			<?php get_company_listings_template_part( 'content-widget', 'no-companies-found', 'wp-job-manager-company-listings', COMPANY_LISTINGS_PLUGIN_DIR . '/templates/' ); ?>

		<?php endif;

		wp_reset_postdata();

		$content = ob_get_clean();

		echo $content;

		$this->cache_widget( $args, $content );
	}
}

/**
 * Featured Companies Widget
 */
class WP_Job_Manager_Company_Listings_Widget_Press extends WP_Job_Manager_Widget {
	/**
	 * Constructor
	 */
	public function __construct() {

		$this->widget_cssclass    = 'job_manager widget_press';
		$this->widget_description = __( 'Display company press information on single company page.', 'wp-job-manager-company-listings' );
		$this->widget_id          = 'widget_company_press';
		$this->widget_name        = __( 'Company Press', 'wp-job-manager-company-listings' );
		$this->settings           = array(
			'title' => array(
				'type'  => 'text',
				'std'   => __( 'Press', 'wp-job-manager-company-listings' ),
				'label' => __( 'Title', 'wp-job-manager-company-listings' )
			)
		);
		$this->register();
	}

	/**
	 * widget function.
	 *
	 * @see WP_Widget
	 * @access public
	 * @param array $args
	 * @param array $instance
	 */
	public function widget( $args, $instance ) {
		global $post;

		if ( ! company_listings_user_can_browse_companies() ) {
			return;
		}

		if ( $this->get_cached_widget( $args ) ) {
			return;
		}

		ob_start();

		extract( $args );

		$title   = apply_filters( 'widget_title', $instance['title'], $instance, $this->id_base );

		if ( $items = get_post_meta( $post->ID, '_company_press', true ) ) : ?>

			<?php echo $before_widget; ?>

			<?php if ( $title ) echo $before_title . $title . $after_title; ?>

			<ul class="company-listings-press">
				<?php
				foreach( $items as $item ) : ?>

					<li>

						<a target="_blank" href="<?php echo esc_html( $item['notes'] ); ?>">
							<?php echo esc_html( $item['job_title'] ); ?>
						</a>

					</li>

				<?php endforeach;
				?>
			</ul>

			<?php echo $after_widget; ?>

		<?php endif;

		$content = ob_get_clean();

		echo $content;

		$this->cache_widget( $args, $content );
	}
}

/**
 * Featured Companies Widget
 */
class WP_Job_Manager_Company_Listings_Widget_Perks extends WP_Job_Manager_Widget {
	/**
	 * Constructor
	 */
	public function __construct() {

		$this->widget_cssclass    = 'job_manager widget_perks';
		$this->widget_description = __( 'Display company perks information on single company page.', 'wp-job-manager-company-listings' );
		$this->widget_id          = 'widget_company_perks';
		$this->widget_name        = __( 'Company Perks', 'wp-job-manager-company-listings' );
		$this->settings           = array(
			'title' => array(
				'type'  => 'text',
				'std'   => __( 'Perks', 'wp-job-manager-company-listings' ),
				'label' => __( 'Title', 'wp-job-manager-company-listings' )
			)
		);
		$this->register();
	}

	/**
	 * widget function.
	 *
	 * @see WP_Widget
	 * @access public
	 * @param array $args
	 * @param array $instance
	 */
	public function widget( $args, $instance ) {
		global $post;

		if ( ! company_listings_user_can_browse_companies() ) {
			return;
		}

		if ( $this->get_cached_widget( $args ) ) {
			return;
		}

		ob_start();

		extract( $args );

		$title   = apply_filters( 'widget_title', $instance['title'], $instance, $this->id_base );

		if ( $items = get_post_meta( $post->ID, '_company_perk', true ) ) : ?>

			<?php echo $before_widget; ?>

			<?php if ( $title ) echo $before_title . $title . $after_title; ?>

			<ul class="company-listings-perk">
				<?php
				foreach( $items as $item ) : ?>
					<li>
						<?php echo wpautop( wptexturize( $item['notes'] ) ); ?>
					</li>

				<?php endforeach;
				?>
			</ul>

			<?php echo $after_widget; ?>

		<?php endif;

		$content = ob_get_clean();

		echo $content;

		$this->cache_widget( $args, $content );
	}
}

/**
 * Featured Companies Widget
 */
class WP_Job_Manager_Company_Listings_Widget_Links extends WP_Job_Manager_Widget {
	/**
	 * Constructor
	 */
	public function __construct() {

		$this->widget_cssclass    = 'job_manager widget_links';
		$this->widget_description = __( 'Display company perks information on single company page.', 'wp-job-manager-company-listings' );
		$this->widget_id          = 'widget_company_links';
		$this->widget_name        = __( 'Company Links', 'wp-job-manager-company-listings' );
		$this->settings           = array(
			'title' => array(
				'type'  => 'text',
				'std'   => __( 'Links', 'wp-job-manager-company-listings' ),
				'label' => __( 'Title', 'wp-job-manager-company-listings' )
			)
		);
		$this->register();
	}

	/**
	 * widget function.
	 *
	 * @see WP_Widget
	 * @access public
	 * @param array $args
	 * @param array $instance
	 */
	public function widget( $args, $instance ) {
		global $post;

		if ( ! company_listings_user_can_browse_companies() ) {
			return;
		}

		if ( $this->get_cached_widget( $args ) ) {
			return;
		}

		ob_start();

		extract( $args );

		$title   = apply_filters( 'widget_title', $instance['title'], $instance, $this->id_base );

		echo $before_widget;

		if ( $title ) echo $before_title . $title . $after_title;

		the_company_metalinks();

		echo $after_widget;

		$content = ob_get_clean();

		echo $content;

		$this->cache_widget( $args, $content );
	}
}

/**
 * Companies Logo Widget
 */
class WP_Job_Manager_Company_Listings_Widget_Logo extends WP_Job_Manager_Widget {
	/**
	 * Constructor
	 */
	public function __construct() {

		$this->widget_cssclass    = 'job_manager widget_logo';
		$this->widget_description = __( 'Display company logo on single company page.', 'wp-job-manager-company-listings' );
		$this->widget_id          = 'widget_company_logo';
		$this->widget_name        = __( 'Company Logo', 'wp-job-manager-company-listings' );
		$this->settings           = array(
			'title' => array(
				'type'  => 'text',
				'std'   => __( 'Logo', 'wp-job-manager-company-listings' ),
				'label' => __( 'Title', 'wp-job-manager-company-listings' )
			)
		);
		$this->register();
	}

	/**
	 * widget function.
	 *
	 * @see WP_Widget
	 * @access public
	 * @param array $args
	 * @param array $instance
	 */
	public function widget( $args, $instance ) {
		global $post;

		if ( ! company_listings_user_can_browse_companies() ) {
			return;
		}

		if ( $this->get_cached_widget( $args ) ) {
			return;
		}

		ob_start();

		extract( $args );

		$title   = apply_filters( 'widget_title', $instance['title'], $instance, $this->id_base );

		echo $before_widget;

		if ( $title ) echo $before_title . $title . $after_title;

		the_company_logo();

		echo $after_widget;

		$content = ob_get_clean();

		echo $content;

		$this->cache_widget( $args, $content );
	}
}

register_widget( 'WP_Job_Manager_Company_Listings_Widget_Recent_Company' );
register_widget( 'WP_Job_Manager_Company_Listings_Widget_Featured_Company' );
register_widget( 'WP_Job_Manager_Company_Listings_Widget_Press' );
register_widget( 'WP_Job_Manager_Company_Listings_Widget_Perks' );
register_widget( 'WP_Job_Manager_Company_Listings_Widget_Links' );
register_widget( 'WP_Job_Manager_Company_Listings_Widget_Logo' );

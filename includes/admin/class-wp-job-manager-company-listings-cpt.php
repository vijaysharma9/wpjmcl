<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * WP_Job_Manager_Company_Listings_CPT class.
 */
class WP_Job_Manager_Company_Listings_CPT {

	/**
	 * __construct function.
	 *
	 * @access public
	 * @return void
	 */
	public function __construct() {
		add_filter( 'enter_title_here', array( $this, 'enter_title_here' ), 1, 2 );
		add_filter( 'manage_edit-company_listings_columns', array( $this, 'columns' ) );
		add_action( 'manage_company_listings_posts_custom_column', array( $this, 'custom_columns' ), 2 );
		add_filter( 'manage_edit-company_listings_sortable_columns', array( $this, 'sortable_columns' ) );
		add_action( 'parse_query', array( $this, 'search_meta' ) );
		add_action( 'parse_query', array( $this, 'filter_meta' ) );
		add_filter( 'get_search_query', array( $this, 'search_meta_label' ) );
		add_filter( 'request', array( $this, 'sort_columns' ) );
		add_filter( 'post_updated_messages', array( $this, 'post_updated_messages' ) );
		add_action( 'admin_footer-edit.php', array( $this, 'add_bulk_actions' ) );
		add_action( 'load-edit.php', array( $this, 'do_bulk_actions' ) );
		add_action( 'admin_init', array( $this, 'approve_company' ) );
		add_action( 'admin_notices', array( $this, 'approved_notice' ) );

		if ( get_option( 'company_listings_enable_categories' ) ) {
			add_action( "restrict_manage_posts", array( $this, "companies_by_category" ) );
		}

		add_action( 'restrict_manage_posts', array( $this, 'companies_meta_filters' ) );

		foreach ( array( 'post', 'post-new' ) as $hook ) {
			add_action( "admin_footer-{$hook}.php", array( $this,'extend_submitdiv_post_status' ) );
		}
	}

	/**
	 * Edit bulk actions
	 */
	public function add_bulk_actions() {
		global $post_type;

		if ( $post_type == 'company_listings' ) {
			?>
			<script type="text/javascript">
		      jQuery(document).ready(function() {
		        jQuery('<option>').val('approve_companies').text('<?php _e( 'Approve Companies', 'wp-job-manager-company-listings' )?>').appendTo("select[name='action']");
		        jQuery('<option>').val('approve_companies').text('<?php _e( 'Approve Companies', 'wp-job-manager-company-listings' )?>').appendTo("select[name='action2']");
		      });
		    </script>
		    <?php
		}
	}

	/**
	 * Do custom bulk actions
	 */
	public function do_bulk_actions() {
		$wp_list_table = _get_list_table( 'WP_Posts_List_Table' );
		$action        = $wp_list_table->current_action();

		switch( $action ) {
			case 'approve_companies' :
				check_admin_referer( 'bulk-posts' );

				$post_ids      = array_map( 'absint', array_filter( (array) $_GET['post'] ) );
				$approved_companies = array();

				if ( ! empty( $post_ids ) )
					foreach( $post_ids as $post_id ) {
						$company_data = array(
							'ID'          => $post_id,
							'post_status' => 'publish'
						);
						if ( get_post_status( $post_id ) == 'pending' && wp_update_post( $company_data ) )
							$approved_companies[] = $post_id;
					}

				wp_redirect( remove_query_arg( 'approve_companies', add_query_arg( 'approved_companies', $approved_companies, admin_url( 'edit.php?post_type=company_listings' ) ) ) );
				exit;
			break;
		}

		return;
	}

	/**
	 * Approve a single company
	 */
	public function approve_company() {
		if ( ! empty( $_GET['approve_company'] ) && wp_verify_nonce( $_REQUEST['_wpnonce'], 'approve_company' ) && current_user_can( 'edit_post', $_GET['approve_company'] ) ) {
			$post_id = absint( $_GET['approve_company'] );
			$company_data = array(
				'ID'          => $post_id,
				'post_status' => 'publish'
			);
			wp_update_post( $company_data );
			wp_redirect( remove_query_arg( 'approve_company', add_query_arg( 'approved_companies', $post_id, admin_url( 'edit.php?post_type=company_listings' ) ) ) );
			exit;
		}
	}

	/**
	 * Show a notice if we did a bulk action or approval
	 */
	public function approved_notice() {
		 global $post_type, $pagenow;

		if ( $pagenow == 'edit.php' && $post_type == 'company_listings' && ! empty( $_REQUEST['approved_companies'] ) ) {
			$approved_companies = $_REQUEST['approved_companies'];
			if ( is_array( $approved_companies ) ) {
				$approved_companies = array_map( 'absint', $approved_companies );
				$titles           = array();
				foreach ( $approved_companies as $company_id )
					$titles[] = get_the_title( $company_id );
				echo '<div class="updated"><p>' . sprintf( __( '%s approved', 'wp-job-manager-company-listings' ), '&quot;' . implode( '&quot;, &quot;', $titles ) . '&quot;' ) . '</p></div>';
			} else {
				echo '<div class="updated"><p>' . sprintf( __( '%s approved', 'wp-job-manager-company-listings' ), '&quot;' . get_the_title( $approved_companies ) . '&quot;' ) . '</p></div>';
			}
		}
	}

	/**
	 * companies_by_category function.
	 *
	 * @access public
	 * @param int $show_counts (default: 1)
	 * @param int $hierarchical (default: 1)
	 * @param int $show_uncategorized (default: 1)
	 * @param string $orderby (default: '')
	 * @return void
	 */
	public function companies_by_category( $show_counts = 1, $hierarchical = 1, $show_uncategorized = 1, $orderby = '' ) {
		global $typenow, $wp_query;

	    if ( $typenow != 'company_listings' || ! taxonomy_exists( 'company_category' ) ) {
	    	return;
	    }

	    if ( file_exists( JOB_MANAGER_PLUGIN_DIR . '/includes/admin/class-wp-job-manager-category-walker.php' ) ) {
			include_once( JOB_MANAGER_PLUGIN_DIR . '/includes/admin/class-wp-job-manager-category-walker.php' );
		} else {
			include_once( JOB_MANAGER_PLUGIN_DIR . '/includes/class-wp-job-manager-category-walker.php' );
		}

		$r = array();
		$r['pad_counts']   = 1;
		$r['hierarchical'] = $hierarchical;
		$r['hide_empty']   = 0;
		$r['show_count']   = $show_counts;
		$r['selected']     = isset( $wp_query->query['company_category'] ) ? $wp_query->query['company_category'] : '';
		$r['menu_order']   = false;

		if ( $orderby == 'order' ) {
			$r['menu_order'] = 'asc';
		} elseif ( $orderby ) {
			$r['orderby'] = $orderby;
		}

		$terms = get_terms( 'company_category', $r );

		if ( ! $terms )
			return;

		$output  = "<select name='company_category' id='dropdown_company_category'>";
		$output .= '<option value="" ' .  selected( isset( $_GET['company_category'] ) ? $_GET['company_category'] : '', '', false ) . '>'.__( 'Select a category', 'wp-job-manager-company-listings' ).'</option>';
		$output .= $this->walk_category_dropdown_tree( $terms, 0, $r );
		$output .="</select>";

		echo $output;
	}

	/**
	 * Walk the Product Categories.
	 *
	 * @access public
	 * @return void
	 */
	private function walk_category_dropdown_tree() {
		$args = func_get_args();

		// the user's options are the third parameter
		if ( empty($args[2]['walker']) || !is_a($args[2]['walker'], 'Walker') ) {
			$walker = new WP_Job_Manager_Category_Walker;
		} else {
			$walker = $args[2]['walker'];
		}

		return call_user_func_array( array( $walker, 'walk' ), $args );
	}

	/**
	 * Output dropdowns for filters based on post meta.
	 *
	 * @since 1.0.4
	 */
	public function companies_meta_filters() {
		global $typenow;

		// Only add the filters for job_listings.
		if ( 'company_listings' !== $typenow ) {
			return;
		}

		// Filter by Featured.
		$this->companies_filter_dropdown(
			'company_listing_featured',
			array(
				array(
					'value' => '',
					'text'  => __( 'Select Featured', 'wp-job-manager-company-listings' ),
				),
				array(
					'value' => '1',
					'text'  => __( 'Featured', 'wp-job-manager-company-listings' ),
				),
				array(
					'value' => '0',
					'text'  => __( 'Not Featured', 'wp-job-manager-company-listings' ),
				),
			)
		);
	}

	/**
	 * Shows dropdown to filter by the given URL parameter. The dropdown will
	 * have three options: "Select $name", "$name", and "Not $name".
	 *
	 * The $options element should be an array of arrays, each with the
	 * attributes needed to create an <option> HTML element. The attributes are
	 * as follows:
	 *
	 * $options[i]['value']  The value for the <option> HTML element.
	 * $options[i]['text']   The text for the <option> HTML element.
	 *
	 * @since 1.31.0
	 *
	 * @param string $param        The URL parameter.
	 * @param array  $options      The options for the dropdown. See the description above.
	 */
	private function companies_filter_dropdown( $param, $options ) {
		$selected = isset( $_GET[ $param ] ) ? $_GET[ $param ] : '';

		echo '<select name="' . esc_attr( $param ) . '" id="dropdown_' . esc_attr( $param ) . '">';

		foreach ( $options as $option ) {
			echo '<option value="' . esc_attr( $option['value'] ) . '"'
				. ( $selected === $option['value'] ? ' selected' : '' )
				. '>' . esc_html( $option['text'] ) . '</option>';
		}
		echo '</select>';

	}

	/**
	 * enter_title_here function.
	 * @return string
	 */
	public function enter_title_here( $text, $post ) {
		if ( $post->post_type == 'company_listings' ) {
			return __( 'Company name', 'wp-job-manager-company-listings' );
		}
		return $text;
	}

	/**
	 * post_updated_messages function.
	 * @param array $messages
	 * @return array
	 */
	public function post_updated_messages( $messages ) {
		global $post, $post_ID;

		$messages['company_listings'] = array(
			0 => '',
			1 => sprintf( __( 'Company updated. <a href="%s">View Company</a>', 'wp-job-manager-company-listings' ), esc_url( get_permalink( $post_ID ) ) ),
			2 => __( 'Custom field updated.', 'wp-job-manager-company-listings' ),
			3 => __( 'Custom field deleted.', 'wp-job-manager-company-listings' ),
			4 => __( 'Company updated.', 'wp-job-manager-company-listings' ),
			5 => isset( $_GET['revision'] ) ? sprintf( __( 'Company restored to revision from %s', 'wp-job-manager-company-listings' ), wp_post_revision_title( (int) $_GET['revision'], false ) ) : false,
			6 => sprintf( __( 'Company published. <a href="%s">View Company</a>', 'wp-job-manager-company-listings' ), esc_url( get_permalink( $post_ID ) ) ),
			7 => __('Company saved.', 'wp-job-manager-company-listings'),
			8 => sprintf( __( 'Company submitted. <a target="_blank" href="%s">Preview Company</a>', 'wp-job-manager-company-listings' ), esc_url( add_query_arg( 'preview', 'true', get_permalink($post_ID) ) ) ),
			9 => sprintf( __( 'Company scheduled for: <strong>%1$s</strong>. <a target="_blank" href="%2$s">Preview Company</a>', 'wp-job-manager-company-listings' ),
			  date_i18n( __( 'M j, Y @ G:i', 'wp-job-manager-company-listings' ), strtotime( $post->post_date ) ), esc_url( get_permalink( $post_ID ) ) ),
			10 => sprintf( __( 'Company draft updated. <a target="_blank" href="%s">Preview Company</a>', 'wp-job-manager-company-listings' ), esc_url( add_query_arg( 'preview', 'true', get_permalink( $post_ID ) ) ) ),
		);
		return $messages;
	}

	/**
	 * columns function.
	 *
	 * @access public
	 * @param mixed $columns
	 * @return void
	 */
	public function columns( $columns ) {
		if ( ! is_array( $columns ) ) {
			$columns = array();
		}

		unset( $columns['title'], $columns['date'], $columns['author'] );

		// remove coauthors column coming from 'co-authors-plus' plugin
		if ( array_key_exists( 'coauthors', $columns ) ) {
			unset( $columns['coauthors'] );
		}

		$columns = apply_filters( 'company_listings_list_table_columns_before_company_listings', $columns );

		$columns['company_listings'] = __( 'Company', 'wp-job-manager-company-listings' );
		$columns["company_location"] = __( "Location", 'wp-job-manager-company-listings' );
		$columns['company_status']   = '<span class="tips" data-tip="' . __( "Status", 'wp-job-manager-company-listings' ) . '">' . __( "Status", 'wp-job-manager-company-listings' ) . '</span>';
		$columns["company_posted"]   = __( "Posted", 'wp-job-manager-company-listings' );

		$columns = apply_filters( 'company_listings_list_table_columns_after_company_posted', $columns );

		if ( get_option( 'company_listings_enable_skills' ) ) {
			$columns["company_skills"] = __( "Skills", 'wp-job-manager-company-listings' );
		}

		if ( get_option( 'company_listings_enable_categories' ) ) {
			$columns["company_category"] = __( "Categories", 'wp-job-manager-company-listings' );
		}

		$columns = apply_filters( 'company_listings_list_table_columns_before_featured_company', $columns );

		$columns['featured_company'] = '<span class="tips" data-tip="' . __( "Featured?", 'wp-job-manager-company-listings' ) . '">' . __( "Featured?", 'wp-job-manager-company-listings' ) . '</span>';
		$columns['company_actions']  = __( "Actions", 'wp-job-manager-company-listings' );

		$columns = apply_filters( 'company_listings_list_table_columns_after_company_actions', $columns );

		return $columns;
	}

	/**
	 * sortable_columns function.
	 * @param array $columns
	 * @return array
	 */
	public function sortable_columns( $columns ) {
		$custom = array(
			'company_posted'   => 'date',
			'company_listings' => 'title',
			'company_location' => 'company_location',
		);
		return wp_parse_args( $custom, $columns );
	}

	/**
	 * Search custom fields as well as content.
	 * @param WP_Query $wp
	 */
	public function search_meta( $wp ) {
		global $pagenow, $wpdb;

		if ( 'edit.php' != $pagenow || empty( $wp->query_vars['s'] ) || $wp->query_vars['post_type'] != 'company_listings' ) {
			return;
		}

		$post_ids = array_unique( array_merge(
			$wpdb->get_col(
				$wpdb->prepare( "
					SELECT posts.ID
					FROM {$wpdb->posts} posts
					INNER JOIN {$wpdb->postmeta} p1 ON posts.ID = p1.post_id
					WHERE p1.meta_value LIKE '%%%s%%'
					OR posts.post_title LIKE '%%%s%%'
					OR posts.post_content LIKE '%%%s%%'
					AND posts.post_type = 'company_listings'
					",
					esc_attr( $wp->query_vars['s'] ),
					esc_attr( $wp->query_vars['s'] ),
					esc_attr( $wp->query_vars['s'] )
				)
			),
			array( 0 )
		) );

		// Adjust the query vars
		unset( $wp->query_vars['s'] );
		$wp->query_vars['company_search'] = true;
		$wp->query_vars['post__in'] = $post_ids;
	}

	/**
	 * Filters by meta fields.
	 *
	 * @since      1.0.4
	 *
	 * @param WP_Query $wp
	 */
	public function filter_meta( $wp ) {
		global $pagenow;

		if ( 'edit.php' !== $pagenow || empty( $wp->query_vars['post_type'] ) || 'company_listings' !== $wp->query_vars['post_type'] ) {
			return;
		}

		$meta_query = $wp->get( 'meta_query' );
		if ( ! is_array( $meta_query ) ) {
			$meta_query = array();
		}

		// Filter on _featured meta.
		if ( isset( $_GET['company_listing_featured'] ) && '' !== $_GET['company_listing_featured'] ) {
			$meta_query[] = array(
				'key'   => '_featured',
				'value' => $_GET['company_listing_featured'],
			);
		}

		// Set new meta query.
		if ( ! empty( $meta_query ) ) {
			$wp->set( 'meta_query', $meta_query );
		}
	}

	/**
	 * Change the label when searching meta.
	 * @param string $query
	 * @return string
	 */
	public function search_meta_label( $query ) {
		global $pagenow, $typenow;

		if ( 'edit.php' != $pagenow || $typenow != 'company_listings' || ! get_query_var( 'company_search' ) ) {
			return $query;
		}

		return wp_unslash( sanitize_text_field( $_GET['s'] ) );
	}

	/**
	 * sort_columns function.
	 * @param array $vars
	 * @return array
	 */
	public function sort_columns( $vars ) {
		if ( isset( $vars['orderby'] ) ) {
			if ( 'company_location' === $vars['orderby'] ) {
				$vars = array_merge( $vars, array(
					'meta_key' 	=> '_company_location',
					'orderby' 	=> 'meta_value'
				) );
			}
		}
		return $vars;
	}

	/**
	 * custom_columns function.
	 * @param string $column
	 */
	public function custom_columns( $column ) {
		global $post;

		switch ( $column ) {
			case "company_listings" :
				echo '<a href="' . admin_url('post.php?post=' . $post->ID . '&action=edit') . '" class="tips company_name" data-tip="' . sprintf( __( 'Company ID: %d', 'wp-job-manager-company-listings' ), $post->ID ) . '">' . $post->post_title . '</a>';
				echo '<div class="company_title">';
				the_company_metatitle();
				echo '</div>';
				the_company_metaphoto();
			break;
			case 'company_location' :
				the_company_metalocation( true, $post );
			break;
			case "company_skills" :
				if ( ! $terms = get_the_term_list( $post->ID, 'company_skill', '', ', ', '' ) ) echo '<span class="na">&ndash;</span>'; else echo $terms;
			break;
			case "company_category" :
				if ( ! $terms = get_the_term_list( $post->ID, $column, '', ', ', '' ) ) echo '<span class="na">&ndash;</span>'; else echo $terms;
			break;
			case "company_posted" :
				echo '<strong>' . date_i18n( __( 'M j, Y', 'wp-job-manager-company-listings' ), strtotime( $post->post_date ) ) . '</strong><span>';
				echo ( empty( $post->post_author ) ? __( 'by a guest', 'wp-job-manager-company-listings' ) : sprintf( __( 'by %s', 'wp-job-manager-company-listings' ), '<a href="' . get_edit_user_link( $post->post_author ) . '">' . get_the_author() . '</a>' ) ) . '</span>';
			break;
			case "featured_company" :
				if ( is_company_featured( $post ) ) echo '&#10004;'; else echo '&ndash;';
			break;
			case "company_status" :
				echo '<span data-tip="' . esc_attr( get_the_company_metastatus( $post ) ) . '" class="tips status-' . esc_attr( $post->post_status ) . '">' . get_the_company_metastatus( $post ) . '</span>';
			break;
			case "company_actions" :
				echo '<div class="actions">';
				$admin_actions           = array();

				if ( $post->post_status == 'pending' ) {
					$admin_actions['approve']   = array(
						'action'  => 'approve',
						'name'    => __( 'Approve', 'wp-job-manager-company-listings' ),
						'url'     =>  wp_nonce_url( add_query_arg( 'approve_company', $post->ID ), 'approve_company' )
					);
				}

				if ( $post->post_status !== 'trash' ) {
					$admin_actions['view']   = array(
						'action'  => 'view',
						'name'    => __( 'View', 'wp-job-manager-company-listings' ),
						'url'     => get_permalink( $post->ID )
					);
					if ( $email = get_post_meta( $post->ID, '_company_email', true ) ) {
						$admin_actions['email']   = array(
							'action'  => 'email',
							'name'    => __( 'Email Company', 'wp-job-manager-company-listings' ),
							'url'     =>  'mailto:' . esc_attr( $email )
						);
					}
					$admin_actions['edit']   = array(
						'action'  => 'edit',
						'name'    => __( 'Edit', 'wp-job-manager-company-listings' ),
						'url'     => get_edit_post_link( $post->ID )
					);
					$admin_actions['delete'] = array(
						'action'  => 'delete',
						'name'    => __( 'Delete', 'wp-job-manager-company-listings' ),
						'url'     => get_delete_post_link( $post->ID )
					);
				}

				$admin_actions = apply_filters( 'company_listings_admin_actions', $admin_actions, $post );

				foreach ( $admin_actions as $action ) {
					printf( '<a class="icon-%s button tips" href="%s" data-tip="%s">%s</a>', esc_attr( $action['action'] ), esc_url( $action['url'] ), esc_attr( $action['name'] ), esc_attr( $action['name'] ) );
				}

				echo '</div>';

			break;
		}
	}

    /**
	 * Adds post status to the "submitdiv" Meta Box and post type WP List Table screens. Based on https://gist.github.com/franz-josef-kaiser/2930190
	 *
	 * @return void
	 */
	public function extend_submitdiv_post_status() {
		global $wp_post_statuses, $post, $post_type;

		// Abort if we're on the wrong post type, but only if we got a restriction
		if ( 'company_listings' !== $post_type ) {
			return;
		}

		// Get all non-builtin post status and add them as <option>
		$options = $display = '';
		foreach ( get_company_post_statuses() as $status => $name ) {
			$selected = selected( $post->post_status, $status, false );

			// If we one of our custom post status is selected, remember it
			$selected AND $display = $name;

			// Build the options
			$options .= "<option{$selected} value='{$status}'>{$name}</option>";
		}
		?>
		<script type="text/javascript">
			jQuery( document ).ready( function($) {
				<?php if ( ! empty( $display ) ) : ?>
					jQuery( '#post-status-display' ).html( '<?php echo $display; ?>' );
				<?php endif; ?>

				var select = jQuery( '#post-status-select' ).find( 'select' );
				jQuery( select ).html( "<?php echo $options; ?>" );
			} );
		</script>
		<?php
	}
}

new WP_Job_Manager_Company_Listings_CPT();

<?php
/**
Plugin Name: WP Job Manager - Company Listings
Plugin URI: https://store.techbrise.com/downloads/wp-job-manager-company-listings/
Description: Outputs a list of all companies that have submitted jobs with links to their listings and profile.
Version: 1.0.8
Author: TechBrise Solutions
Author URI: https://techbrise.com
Requires at least: 4.4
Tested up to: 5.3
License: GNU General Public License v3.0
License URI: http://www.gnu.org/licenses/gpl-3.0.html
*/

/**
WP Job Manager - Company Listings
Copyright (C) 2019 TechBrise Solutions Private Limited
This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.
This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.
You should have received a copy of the GNU General Public License along
with this program; if not, write to the Free Software Foundation, Inc.,
51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * WP_Job_Manager_Company_Listings class.
 */
class WP_Job_Manager_Company_Listings {

	/**
	 * __construct function.
	 */
	public function __construct() {
		// Define constants
		define( 'COMPANY_LISTINGS_VERSION', '1.0.8' );
		define( 'COMPANY_LISTINGS_PLUGIN_DIR', untrailingslashit( plugin_dir_path( __FILE__ ) ) );
		define( 'COMPANY_LISTINGS_PLUGIN_FILE', __FILE__ );
		define( 'COMPANY_LISTINGS_PLUGIN_URL', untrailingslashit( plugins_url( basename( plugin_dir_path( __FILE__ ) ), basename( __FILE__ ) ) ) );

		// Includes
		require_once( ABSPATH . 'wp-admin/includes/plugin.php' );

		include( 'includes/wp-job-manager-company-listings-functions.php' );
		include( 'includes/wp-job-manager-company-listings-template.php' );
		include( 'includes/wp-job-manager-company-listings-template-functions.php' );
		include( 'includes/wp-job-manager-company-listings-hooks.php' );
		include( 'includes/class-wp-job-manager-company-listings-post-types.php' );
		include( 'includes/class-wp-job-manager-company-listings-forms.php' );
		include( 'includes/class-wp-job-manager-company-listings-ajax.php' );
		include( 'includes/class-wp-job-manager-company-listings-shortcodes.php' );
		include( 'includes/class-wp-job-manager-company-listings-geocode.php' );
		include( 'includes/class-wp-job-manager-company-listings-email-notification.php' );
		include( 'includes/class-wp-job-manager-company-listings-bookmarks.php' );
		include( 'includes/class-wp-job-manager-company-listings-mapping.php' );

		// Init classes
		$this->forms      = new WP_Job_Manager_Company_Listings_Forms();
		$this->post_types = new WP_Job_Manager_Company_Listings_Post_Types();

		// Activation - works with symlinks
		register_activation_hook( basename( dirname( __FILE__ ) ) . '/' . basename( __FILE__ ), array( $this->post_types, 'register_post_types' ), 10 );
		register_activation_hook(
			basename( dirname( __FILE__ ) ) . '/' . basename( __FILE__ ),
			function() {
				include_once( 'includes/class-wp-job-manager-company-listings-install.php' );
			},
			10
		);
		register_activation_hook( basename( dirname( __FILE__ ) ) . '/' . basename( __FILE__ ), 'flush_rewrite_rules', 15 );

		// Actions
		add_action( 'admin_notices', array( $this, 'version_check' ) );
		add_action( 'plugins_loaded', array( $this, 'admin' ) );
		add_action( 'widgets_init', array( $this, 'widgets_init' ), 12 );
		add_action( 'plugins_loaded', array( $this, 'load_plugin_textdomain' ) );
		add_action( 'switch_theme', array( $this->post_types, 'register_post_types' ), 10 );
		add_action( 'switch_theme', 'flush_rewrite_rules', 15 );
		add_action( 'wp_enqueue_scripts', array( $this, 'frontend_scripts' ) );

		// Filters
		add_filter( 'rewrite_rules_array', array( $this, 'insert_rewrite_rules' ) );
		add_filter( 'query_vars', array( $this, 'insert_query_variable' ) );
	}

	/**
	 * Check JM version
	 */
	public function version_check() {
		$required_jm_version = '1.22.0';
		if ( ! defined( 'JOB_MANAGER_VERSION' ) ) {
			?><div class="error"><p><?php _e( 'Company Manager requires WP Job Manager to be installed!', 'wp-job-manager-company-listings' ); ?></p></div>
			<?php
		} elseif ( version_compare( JOB_MANAGER_VERSION, $required_jm_version, '<' ) ) {
			?>
			<div class="error"><p><?php printf( __( 'Company Manager requires WP Job Manager %1$s (you are using %2$s)', 'wp-job-manager-company-listings' ), $required_jm_version, JOB_MANAGER_VERSION ); ?></p></div>
												<?php
		}
	}

	/**
	 * Include admin
	 */
	public function admin() {
		if ( is_admin() && class_exists( 'WP_Job_Manager' ) ) {
			include( 'includes/admin/class-wp-job-manager-company-listings-admin.php' );
		}
	}

	// Tell WordPress to accept our custom query variable
	public function insert_query_variable( $vars ) {
		array_push( $vars, 'cdpage' );
		return $vars;
	}

	// Adding fake pages' rewrite rules for company-directory
	public function insert_rewrite_rules( $rules ) {

		$upper = array(
			'company-numeric',
			'A',
			'B',
			'C',
			'D',
			'E',
			'F',
			'G',
			'H',
			'I',
			'J',
			'K',
			'L',
			'M',
			'N',
			'O',
			'P',
			'Q',
			'R',
			'S',
			'T',
			'U',
			'V',
			'W',
			'X',
			'Y',
			'Z',
		);

		$company_directory_page_id = get_option( 'company_listings_company_directory_page_id' );

		$newrules = array();
		foreach ( $upper as $slug ) {
			$newrules[ '([^/]+)/' . $slug . '/?$' ] =
			'index.php?page_id=' . $company_directory_page_id . '&$matches[1]&cdpage=' . $slug;
		}

		// Company directory search url
		$newrules['([^/]+)/search/([^/]+)/?$'] = 'index.php?page_id=' . $company_directory_page_id . '&search=$matches[2]';

		return $newrules + $rules;
	}

	/**
	 * Includes once plugins are loaded
	 */
	public function widgets_init() {
		include_once( 'includes/class-wp-job-manager-company-listings-widgets.php' );
	}

	/**
	 * Localisation
	 *
	 * @access private
	 * @return void
	 */
	public function load_plugin_textdomain() {
		$locale = apply_filters( 'plugin_locale', get_locale(), 'wp-job-manager-company-listings' );

		load_textdomain( 'wp-job-manager-company-listings', WP_LANG_DIR . "/wp-job-manager-company-listings/wp-job-manager-company-listings-$locale.mo" );
		load_plugin_textdomain( 'wp-job-manager-company-listings', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
	}

	/**
	 * frontend_scripts function.
	 *
	 * @access public
	 * @return void
	 */
	public function frontend_scripts() {
		$ajax_url                         = admin_url( 'admin-ajax.php', 'relative' );
		$ajax_filter_deps                 = array( 'jquery' );
		$company_field_minimumInputLength = jmcl_company_field_minimumInputLength();

		// WPML workaround until this is standardized
		if ( defined( 'ICL_LANGUAGE_CODE' ) ) {
			$ajax_url = add_query_arg( 'lang', ICL_LANGUAGE_CODE, $ajax_url );
		}

		if ( apply_filters( 'job_manager_chosen_enabled', true ) ) {
			$ajax_filter_deps[] = 'chosen';
		}

		/*-- REGISTER SCRIPTS AND STYLES ------------------------------------------------*/

		$suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';

		/**  STYLES ***/
		wp_register_style( 'wp-job-manager-company-listings-select2', COMPANY_LISTINGS_PLUGIN_URL . '/assets/css/select2' . $suffix . '.css', array(), '4.0.5' );
		wp_register_style( 'wp-job-manager-company-frontend', COMPANY_LISTINGS_PLUGIN_URL . '/assets/css/frontend' . $suffix . '.css' );

		/** SCRIPTS *******************************************/
		wp_register_script( 'wp-job-manager-company-listings-ajax-filters', COMPANY_LISTINGS_PLUGIN_URL . '/assets/js/ajax-filters' . $suffix . '.js', $ajax_filter_deps, COMPANY_LISTINGS_VERSION, true );
		wp_register_script( 'wp-job-manager-company-listings-company-dashboard', COMPANY_LISTINGS_PLUGIN_URL . '/assets/js/company-dashboard' . $suffix . '.js', array( 'jquery' ), COMPANY_LISTINGS_VERSION, true );
		wp_register_script( 'wp-job-manager-company-listings-company-submission', COMPANY_LISTINGS_PLUGIN_URL . '/assets/js/company-submission' . $suffix . '.js', array( 'jquery', 'jquery-ui-sortable' ), COMPANY_LISTINGS_VERSION, true );
		wp_register_script( 'wp-job-manager-company-listings-company-contact-details', COMPANY_LISTINGS_PLUGIN_URL . '/assets/js/contact-details' . $suffix . '.js', array( 'jquery' ), COMPANY_LISTINGS_VERSION, true );
		wp_register_script( 'wp-job-manager-company-listings-company-directory', COMPANY_LISTINGS_PLUGIN_URL . '/assets/js/company-directory' . $suffix . '.js', array( 'jquery' ), COMPANY_LISTINGS_VERSION, true );
		wp_register_script( 'wp-job-manager-company-listings-select2', COMPANY_LISTINGS_PLUGIN_URL . '/assets/js/select2/select2' . $suffix . '.js', array( 'jquery' ), '4.0.5', true );
		wp_register_script( 'wp-job-manager-company-listings-job-edit', COMPANY_LISTINGS_PLUGIN_URL . '/assets/js/job-edit' . $suffix . '.js', array( 'jquery' ), COMPANY_LISTINGS_VERSION, true );
		wp_register_script( 'wp-job-manager-company-listings-main', COMPANY_LISTINGS_PLUGIN_URL . '/assets/js/wp-job-manager-company-listings' . $suffix . '.js', array( 'jquery' ), COMPANY_LISTINGS_VERSION, true );

		/*-- ENQUEUE SCRIPTS AND STYLES ------------------------------------------------*/

		/** STYLES ********************************************/
		wp_enqueue_style( 'wp-job-manager-company-frontend' );

		/** SCRIPTS ********************************************/
		wp_enqueue_script( 'wp-job-manager-company-listings-main' );

		/*-- LOCALIZE SCRIPTS ------------------------------------------------*/

		wp_localize_script(
			'wp-job-manager-company-listings-job-edit',
			'company_listings_company_field',
			array(
				'company_field_enable_select2_search' => jmcl_company_field_enable_select2_search(),
				'company_field_selector'              => apply_filters( 'jmcl_company_field_selector', 'select#company_id' ),
				'company_field_allowclear'            => apply_filters( 'jmcl_company_field_allowclear', true ),
				'company_field_minimumInputLength'    => $company_field_minimumInputLength,
				'select2_errorLoading'                => esc_html__( 'The results could not be loaded.', 'wp-job-manager-company-listings' ),
				'select2_inputTooShort'               => sprintf( esc_html__( 'Please enter %s or more characters', 'wp-job-manager-company-listings' ), $company_field_minimumInputLength ),
				'select2_loadingMore'                 => esc_html__( 'Loading more results...', 'wp-job-manager-company-listings' ),
				'select2_noResults'                   => esc_html__( 'No results found', 'wp-job-manager-company-listings' ),
				'select2_searching'                   => esc_html__( 'Searching...', 'wp-job-manager-company-listings' ),
			)
		);

		wp_localize_script(
			'wp-job-manager-company-listings-company-submission',
			'company_listings_company_submission',
			array(
				'i18n_navigate'       => __( 'If you wish to edit the posted details use the "edit company" button instead, otherwise changes may be lost.', 'wp-job-manager-company-listings' ),
				'i18n_confirm_remove' => __( 'Are you sure you want to remove this item?', 'wp-job-manager-company-listings' ),
				'i18n_remove'         => __( 'remove', 'wp-job-manager-company-listings' ),
			)
		);

		wp_localize_script(
			'wp-job-manager-company-listings-ajax-filters',
			'company_listings_ajax_filters',
			array(
				'ajax_url' => $ajax_url,
			)
		);

		wp_localize_script(
			'wp-job-manager-company-listings-company-dashboard',
			'company_listings_company_dashboard',
			array(
				'i18n_confirm_delete' => __( 'Are you sure you want to delete this company?', 'wp-job-manager-company-listings' ),
			)
		);
	}
}

$GLOBALS['company_listings'] = new WP_Job_Manager_Company_Listings();

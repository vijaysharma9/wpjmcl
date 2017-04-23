<?php
/**
 * Template Functions
 *
 * Template functions specifically created for bp job manager
 *
 * @category 	Core
 * @package 	BP Job Manager/Template
 * @version     1.1
 */

/**
 * Get and include template files.
 *
 * @param mixed $template_name
 * @param array $args (default: array())
 * @param string $template_path (default: '')
 * @param string $default_path (default: '')
 * @return void
 */
function get_company_listings_template( $template_name, $args = array(), $template_path = 'company_listings', $default_path = '' ) {
    if ( $args && is_array( $args ) ) {
        extract( $args );
    }
    include( locate_company_listings_template( $template_name, $template_path, $default_path ) );
}

/**
 * Locate a template and return the path for inclusion.
 *
 * This is the load order:
 *
 *		yourtheme		/	$template_path	/	$template_name
 *		yourtheme		/	$template_name
 *		$default_path	/	$template_name
 *
 * @param string $template_name
 * @param string $template_path (default: 'job_manager')
 * @param string|bool $default_path (default: '') False to not load a default
 * @return string
 */
function locate_company_listings_template( $template_name, $template_path = 'company_listings', $default_path = '' ) {
    // Look within passed path within the theme - this is priority
    $template = locate_template(
        array(
            trailingslashit( $template_path ) . $template_name,
            $template_name
        )
    );

    // Get default template
    if ( ! $template && $default_path !== false ) {
        $default_path = $default_path ? $default_path : COMPANY_LISTINGS_PLUGIN_DIR . '/templates/';
        if ( file_exists( trailingslashit( $default_path ) . $template_name ) ) {
            $template = trailingslashit( $default_path ) . $template_name;
        }
    }

    // Return what we found
    return apply_filters( 'locate_company_listings_template', $template, $template_name, $template_path );
}

/**
 * Get template part (for templates in loops).
 *
 * @param string $slug
 * @param string $name (default: '')
 * @param string $template_path (default: 'company_listings')
 * @param string|bool $default_path (default: '') False to not load a default
 */
function get_company_listings_template_part( $slug, $name = '', $template_path = 'company_listings', $default_path = '' ) {
    $template = '';

    if ( $name ) {
        $template = locate_company_listings_template( "{$slug}-{$name}.php", $template_path, $default_path );
    }

    // If template file doesn't exist, look in yourtheme/slug.php and yourtheme/company_listings/slug.php
    if ( ! $template ) {
        $template = locate_company_listings_template( "{$slug}.php", $template_path, $default_path );
    }

    if ( $template ) {
        load_template( $template, false );
    }
}

/**
 * Get the default filename for a template.
 *
 */
function jmcl_get_template_loader_default_file() {
    if ( is_singular( 'company' ) ) {
        $default_file = 'single-company.php';
    } else {
        $default_file = '';
    }
    return $default_file;
}

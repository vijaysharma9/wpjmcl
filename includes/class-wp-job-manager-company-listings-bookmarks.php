<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * WP_Job_Manager_Company_Listings_Bookmarks class.
 *
 * Handles company bookmarks integration with bookmarks plugin if installed.
 */
class WP_Job_Manager_Company_Listings_Bookmarks {
	
	/**
	 * WP_Job_Manager_Company_Listings_Bookmarks constructor.
	 *
	 */
	public function __construct() {
		add_action( 'init', array( $this, 'init' ) );
		add_action( 'wp', array( $this, 'bookmark_handler' ) );
	}
	
	/**
	 * Init actions
	 */
	public function init() {
		global $job_manager_bookmarks;
		add_action( 'jmcl_before_single_company', array( $job_manager_bookmarks, 'bookmark_form' ) );
	}
	
	/**
	 * See if a post is bookmarked by ID
	 * @param  int post ID
	 * @return boolean
	 */
	public function is_bookmarked( $post_id ) {
		global $wpdb;
		
		return $wpdb->get_var( $wpdb->prepare( "SELECT id FROM {$wpdb->prefix}job_manager_bookmarks WHERE post_id = %d AND user_id = %d;", $post_id, get_current_user_id() ) ) ? true : false;
	}
	
	/**
	 * Handle the book mark form
	 */
	public function bookmark_handler() {
		global $wpdb;
		
		if ( ! is_user_logged_in() ) {
			return;
		}
		
		if ( ! empty( $_POST['submit_bookmark'] ) && wp_verify_nonce( $_REQUEST['_wpnonce'], 'update_bookmark' ) ) {
			$post_id = absint( $_POST['bookmark_post_id'] );
			$note    = wp_kses_post( stripslashes( $_POST['bookmark_notes'] ) );
			
			if ( $post_id && in_array( get_post_type( $post_id ), array( 'company_listings' ) ) ) {
				if ( ! $this->is_bookmarked( $post_id ) ) {
					$wpdb->insert(
						"{$wpdb->prefix}job_manager_bookmarks",
						array(
							'user_id'       => get_current_user_id(),
							'post_id'       => $post_id,
							'bookmark_note' => $note,
							'date_created'  => current_time( 'mysql' )
						)
					);
				} else {
					$wpdb->update(
						"{$wpdb->prefix}job_manager_bookmarks",
						array(
							'bookmark_note' => $note
						),
						array(
							'post_id'       => $post_id,
							'user_id'       => get_current_user_id()
						)
					);
				}
				
				delete_transient( 'bookmark_count_' . $post_id );
			}
		}
		
		if ( ! empty( $_GET['remove_bookmark'] ) && wp_verify_nonce( $_REQUEST['_wpnonce'], 'remove_bookmark' ) ) {
			$post_id = absint( $_GET['remove_bookmark'] );
			
			$wpdb->delete(
				"{$wpdb->prefix}job_manager_bookmarks",
				array(
					'post_id'       => $post_id,
					'user_id'       => get_current_user_id()
				)
			);
			
			delete_transient( 'bookmark_count_' . $post_id );
		}
	}
}

if ( is_plugin_active('wp-job-manager-bookmarks/wp-job-manager-bookmarks.php') ) {
	new WP_Job_Manager_Company_Listings_Bookmarks();
}
<?php
require_once( plugin_dir_path( __FILE__ ) . 'bs_register_pages_route.php' );
require_once( ABSPATH . 'wp-admin/includes/media.php' );
require_once( ABSPATH . 'wp-admin/includes/file.php' );
require_once( ABSPATH . 'wp-admin/includes/image.php' );

// POST endpoint for creating a new page or sub page
// Include parent_page_id for child_page creation
function blogstorm_get_or_create_page( $request ): WP_Error|array {
	$page_id          = $request['page_id'];
	$title            = sanitize_text_field( $request['title'] );
	$content          = wp_kses_post( $request['content'] );
	$meta_description = sanitize_text_field( $request['excerpt'] );
	$parent_page_id   = $request['parent_page_id']; // ID of parent page for subpages
	$post_status      = $request['post_status'];

	if ( $page_id ) {
		$existing_page = get_post( $page_id );

		if ( $existing_page && $existing_page->post_type === 'page' ) {
			$updated_page = array(
				'ID'           => $page_id,
				'post_title'   => $title,
				'post_content' => $content,
				'post_excerpt' => $meta_description,
				'post_status'  => $post_status ?: 'publish',
			);

			$page_id = wp_update_post( $updated_page );

			if ( is_wp_error( $page_id ) ) {
				return new WP_Error( 'error', 'Failed to update the existing page' );
			}

			return array(
				'message'     => 'Page updated successfully',
				'page_id'     => $page_id,
				'page_url'    => get_permalink( $page_id ),
				'page_status' => get_post_status( $page_id )
			);
		}
	}

	// If no existing page with the provided ID or if an ID is not provided, create a new page
	$new_page = array(
		'post_title'   => $title,
		'post_content' => $content,
		'post_excerpt' => $meta_description,
		'post_type'    => 'page',
		'post_status'  => $post_status ?: 'publish',
		'post_parent'  => $parent_page_id ?: 0 // Optional parent page ID
	);

	$page_id = wp_insert_post( $new_page );

	if ( is_wp_error( $page_id ) ) {
		return new WP_Error( 'error', 'Failed to create a new page' );
	}

	return array(
		'message'     => 'Page created successfully',
		'page_id'     => $page_id,
		'page_url'    => get_permalink( $page_id ),
		'page_status' => get_post_status( $page_id )
	);
}
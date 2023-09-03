<?php
require_once(plugin_dir_path(__FILE__) . 'bs_register_posts_route.php');
require_once(ABSPATH . 'wp-admin/includes/media.php');
require_once(ABSPATH . 'wp-admin/includes/file.php');
require_once(ABSPATH . 'wp-admin/includes/image.php');


// GET endpoint for fetching posts with pagination
function blogstorm_get_posts($request): array
{
    $per_page = isset($request['per_page']) ? absint($request['per_page']) : 10;
    $page = isset($request['page']) ? absint($request['page']) : 1;

    $args = array(
        'post_type' => 'post',
        'orderby' => 'date',
        'order' => 'DESC',
        'posts_per_page' => $per_page,
        'paged' => $page,
    );

    $query = new WP_Query($args);
    $posts = $query->posts;

    $result = array(
        'total_posts' => $query->found_posts,
        'posts' => array(),
    );

    foreach ($posts as $post) {
        $result['posts'][] = array(
            'id' => $post->ID,
            'title' => $post->post_title,
            'content' => $post->post_content,
            'excerpt' => $post->post_excerpt,
            'featured_image' => get_the_post_thumbnail_url($post->ID),
            'categories' => wp_get_post_categories($post->ID),
            'tags' => wp_get_post_tags($post->ID),
        );
    }

    return $result;
}

// POST endpoint for creating a new post
function blogstorm_create_post($request): WP_Error|array
{
    $title = sanitize_text_field($request['title']);
    $content = wp_kses_post($request['content']);
    $excerpt = sanitize_text_field($request['excerpt']);
    $featured_image_url = esc_url($request['featured_image_url']);
    $categories = $request['categories']; // An array of category IDs
    $tags = $request['tags']; // An array of tag IDs

    $new_post = array(
        'post_title' => $title,
        'post_content' => $content,
        'post_excerpt' => $excerpt,
        'post_type' => 'post',
        'post_status' => 'publish',
    );

    $post_id = wp_insert_post($new_post);

    $featured_image = media_sideload_image($featured_image_url, $post_id, $title, 'id');

    if (!is_wp_error($post_id)) {
        // Set featured image
        if ($featured_image) {
            set_post_thumbnail($post_id, thumbnail_id: $featured_image);
        }

        // Set categories and tags
        wp_set_post_categories($post_id, $categories);
        wp_set_post_tags($post_id, $tags);

        return array('message' => 'Post created successfully', 'post_id' => $post_id, 'post_url' => get_permalink($post_id));
    } else {
        return new WP_Error('error', 'Failed to create a new post');
    }
}

function blogstorm_get_post_by_id($request): WP_Error|array
{
    $post_id = isset($request['id']) ? absint($request['id']) : 0;

    if ($post_id > 0) {
        $post = get_post($post_id);

        if ($post) {
            return array(
                'id' => $post->ID,
                'title' => $post->post_title,
                'content' => $post->post_content,
                'excerpt' => $post->post_excerpt,
                'featured_image' => get_the_post_thumbnail_url($post->ID),
                'categories' => wp_get_post_categories($post->ID),
                'tags' => wp_get_post_tags($post->ID),
            );
        } else {
            return new WP_Error('post_not_found', 'Post not found', array('status' => 404));
        }
    } else {
        return new WP_Error('invalid_id', 'Invalid post ID', array('status' => 400));
    }
}

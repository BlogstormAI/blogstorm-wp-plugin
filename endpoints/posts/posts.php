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

// Function to check and cleanup broken links in the post content
function check_and_cleanup_links($content)
{
    $dom = new DOMDocument;

    libxml_use_internal_errors(true); // Prevent HTML5 errors from displaying
    // Load the content to the DOM
    $dom->loadHTML(mb_convert_encoding('<div>' . $content . '</div>', 'HTML-ENTITIES', 'UTF-8'), LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
    libxml_clear_errors();

    // Get all links
    $links = $dom->getElementsByTagName('a');

    // For each link
    for ($i = $links->length - 1; $i >= 0; $i--) {
        $linkNode = $links->item($i);
        $href = $linkNode->getAttribute('href');

        // If it's a broken link, make the changes
        if (!is_link_working($href)) {
            unset($href);
            $linkNode->removeAttribute('href');
        }
    }

    $html = $dom->saveHTML($dom->documentElement);
    // Exclude added outer div
    return substr($html, 5, -6);
}

// Function to check if a link is working or not
function is_link_working($url)
{
    $ch = curl_init($url);

    // Don't get the contents of the url
    curl_setopt($ch, CURLOPT_NOBODY, true);

    curl_exec($ch);

    // If the response is not 200, consider it a broken link
    if (curl_getinfo($ch, CURLINFO_HTTP_CODE) != 200) {
        $is_working = false;
    } else {
        $is_working = true;
    }

    curl_close($ch);

    return $is_working;
}

// POST endpoint for creating a new post
function blogstorm_get_or_create_post($request)
{
    $post_id = $request['post_id'];
    $title = sanitize_text_field($request['title']);
    $content = wp_kses_post($request['content']);
    $excerpt = sanitize_text_field($request['excerpt']);
    $featured_image_url = $request['featured_image_url'];
    $categories = $request['categories'];
    $tags = $request['tags'];
    $post_status = $request['post_status'];
    $publish_date = $request['publish_date'];

    $content = check_and_cleanup_links($content);
    error_log($content);

    if ($post_id) {
        $existing_post = get_post($post_id);

        if ($existing_post && $existing_post->post_type === 'post') {
            $updated_post = array(
                'ID' => $post_id,
                'post_title' => $title,
                'post_content' => $content,
                'post_excerpt' => $excerpt,
                'post_status' => $post_status ?: 'publish',
                'post_date' => $publish_date,
                'post_date_gmt' => $publish_date,
            );

            $post_id = wp_update_post($updated_post);

            if (is_wp_error($post_id)) {
                return new WP_Error('error', 'Failed to update the existing post');
            }

            if ($featured_image_url) {
                $featured_image = media_sideload_image($featured_image_url, $existing_post, $title, 'id');
                if ($featured_image) {
                    set_post_thumbnail($existing_post, $featured_image);
                }
            }

            wp_set_post_categories($post_id, $categories);
            wp_set_post_tags($post_id, $tags);

            return array(
                'message' => 'Post updated successfully',
                'post_id' => $post_id,
                'post_url' => get_permalink($post_id),
                'post_status' => get_post_status($post_id)
            );
        }
    }

    // If no existing post with the provided ID or if an ID is not provided, create a new post
    $new_post = array(
        'post_title' => $title,
        'post_content' => $content,
        'post_excerpt' => $excerpt,
        'post_type' => 'post',
        'post_status' => $post_status ?: 'publish',
        'post_date' => $publish_date,
        'post_date_gmt' => $publish_date,
    );

    $post_id = wp_insert_post($new_post);

    if (is_wp_error($post_id)) {
        return new WP_Error('error', 'Failed to create a new post');
    }

    // Set featured image, categories, and tags for the new post
    if ($featured_image_url) {
        $featured_image = media_sideload_image($featured_image_url, $post_id, $title, 'id');

        if ($featured_image) {
            set_post_thumbnail($post_id, $featured_image);
        }
    }

    wp_set_post_categories($post_id, $categories);
    wp_set_post_tags($post_id, $tags);

    return array(
        'message' => 'Post created successfully',
        'post_id' => $post_id,
        'post_url' => get_permalink($post_id),
        'post_status' => get_post_status($post_id)
    );
}


function blogstorm_get_post_by_id($request): mixed
{
    $post_id = isset($request['id']) ? absint($request['id']) : null;

    if ($post_id) {
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

function remove_h1_tags_from_single_post($request)
{
    $post_id = isset($request['id']) ? absint($request['id']) : null;

    if ($post_id) {
        $post = get_post($post_id);

        if ($post) {
            $updated_content = preg_replace('/<h1[^>]*>([\s\S]*?)<\/h1[^>]*>/', '', $post->post_content);
            $updated_post = array(
                'ID' => $post->ID,
                'post_content' => $updated_content,
            );

            // Use wp_update_post to apply the updated content
            $result = wp_update_post($updated_post);

            if (!is_wp_error($result)) {
                return array(
                    'message' => 'Post h1 removed successfully',
                    'post_id' => $post_id,
                    'post_url' => get_permalink($post_id),
                    'post_status' => get_post_status($post_id)
                );
            } else {
                return new WP_Error('update_failed', 'Failed to update post', array('status' => 500));
            }
        } else {
            return new WP_Error('invalid_id', 'Invalid post ID', array('status' => 404));
        }
    } else {
        return new WP_Error('missing_id', 'Missing post ID', array('status' => 400));
    }
}

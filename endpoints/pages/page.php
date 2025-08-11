<?php
// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Check if we're in a WordPress environment
if (!function_exists('add_action')) {
    return;
}

require_once(plugin_dir_path(__FILE__) . 'bs_register_page_route.php');
require_once(ABSPATH . 'wp-admin/includes/media.php');
require_once(ABSPATH . 'wp-admin/includes/file.php');
require_once(ABSPATH . 'wp-admin/includes/image.php');
require_once(ABSPATH . WPINC . '/formatting.php');
require_once(ABSPATH . WPINC . '/post.php');

function process_page_content($content, $page_id = null) {
    // Extract meta tags
    $meta_tags = array();
    $meta_data = array();
    if (preg_match_all('/<meta[^>]+>/', $content, $matches)) {
        foreach ($matches[0] as $meta_tag) {
            $meta_tags[] = $meta_tag;
            // Store meta information
            if (preg_match('/name="([^"]+)".*content="([^"]+)"/', $meta_tag, $meta_match)) {
                $meta_data[$meta_match[1]] = $meta_match[2];
                if ($page_id && function_exists('update_post_meta')) {
                    update_post_meta($page_id, $meta_match[1], $meta_match[2]);
                }
            }
        }
        // Remove meta tags from content
        $content = str_replace($meta_tags, '', $content);
    }

    // Extract and store title tag
    $title = '';
    if (preg_match('/<title>(.*?)<\/title>/', $content, $title_match)) {
        $title = $title_match[1];
        if ($page_id && function_exists('update_post_meta')) {
            update_post_meta($page_id, '_page_title_tag', $title);
        }
        // Remove title tag from content
        $content = preg_replace('/<title>.*?<\/title>/', '', $content);
    }

    // Convert Google Maps iframe to secure shortcode
    $content = preg_replace_callback(
        '/<iframe[^>]*src="[^"]*google\.com\/maps\/embed[^"]*"[^>]*>(?:[^<]*<\/iframe>)?/i',
        function($matches) {
            $iframe = $matches[0];
            $attributes = array();
            
            // Extract all attributes - improved regex to handle various quote styles
            preg_match_all('/(\w+)\s*=\s*["\']([^"\']*)["\']/', $iframe, $attr_matches, PREG_SET_ORDER);
            foreach ($attr_matches as $attr_match) {
                $attributes[$attr_match[1]] = $attr_match[2];
            }
            
            // Also handle attributes without quotes
            preg_match_all('/(\w+)\s*=\s*([^\s>]+)/', $iframe, $unquoted_matches, PREG_SET_ORDER);
            foreach ($unquoted_matches as $attr_match) {
                if (!isset($attributes[$attr_match[1]])) {
                    $attributes[$attr_match[1]] = $attr_match[2];
                }
            }
            
            // Build shortcode with secure attributes
            $shortcode_attrs = '';
            $allowed_attrs = ['src', 'width', 'height', 'frameborder', 'style', 'allowfullscreen', 'loading', 'referrerpolicy'];
            foreach ($attributes as $key => $value) {
                if (in_array(strtolower($key), $allowed_attrs)) {
                    $shortcode_attrs .= sprintf(' %s="%s"', 
                        function_exists('esc_attr') ? esc_attr($key) : htmlspecialchars($key, ENT_QUOTES, 'UTF-8'),
                        function_exists('esc_attr') ? esc_attr($value) : htmlspecialchars($value, ENT_QUOTES, 'UTF-8')
                    );
                }
            }
            
            return sprintf('[blogstorm_iframe%s]', $shortcode_attrs);
        },
        $content
    );

    // Clean up any empty paragraphs or multiple newlines
    $content = preg_replace('/(<br[^>]*>\s*){2,}/', '<br>', $content);
    $content = preg_replace('/\n\s*\n\s*\n/', "\n\n", $content);

    return array(
        'content' => $content,
        'meta_data' => $meta_data,
        'title_tag' => $title
    );
}

function sanitize_content_with_shortcodes($content) {
    // Create custom allowed tags that include our shortcode format
    if (function_exists('wp_kses')) {
        $allowed_tags = wp_kses_allowed_html('post');
        
        // Add support for div with class attribute (for map-container)
        if (!isset($allowed_tags['div'])) {
            $allowed_tags['div'] = array();
        }
        $allowed_tags['div']['class'] = true;
        
        return wp_kses($content, $allowed_tags);
    }
    
    // Fallback: just strip dangerous tags but preserve basic HTML and shortcodes
    $content = strip_tags($content, '<p><br><div><h1><h2><h3><h4><h5><h6><a><ul><li><strong><em><span>');
    return $content;
}

// POST endpoint for creating a new page or sub page
// Include parent_page_id for child_page creation
function blogstorm_get_or_create_page($request)
{
    $page_id = $request['page_id'];
    $title = function_exists('sanitize_text_field') ? sanitize_text_field($request['title']) : strip_tags($request['title']);
    
    // Process content BEFORE sanitization to preserve iframes for conversion
    $raw_content = $request['content'];
    $processed = process_page_content($raw_content);
    
    // Now sanitize the processed content (which should have shortcodes instead of iframes)
    $final_content = sanitize_content_with_shortcodes($processed['content']);
    
    $meta_description = function_exists('sanitize_text_field') ? sanitize_text_field($request['excerpt']) : strip_tags($request['excerpt']);
    $parent_page_id = $request['parent_page_id']; // ID of parent page for subpages
    $post_status = $request['post_status'];
    $post_slug = $request['post_slug'];
    $publish_date = $request['publish_date'];
    $full_slug = $request['full_slug'];
    
    // Use get_page_by_path with fallback
    $b_page = function_exists('get_page_by_path') ? get_page_by_path($full_slug, OBJECT, 'page') : null;

    if ($b_page) {
        // For updates, re-process with page ID for meta storage
        $processed_with_meta = process_page_content($raw_content, $b_page->ID);
        $final_content = sanitize_content_with_shortcodes($processed_with_meta['content']);
        
        $updated_page = array(
            'ID' => $b_page->ID,
            'post_title' => $title,
            'post_name' => $post_slug,
            'post_parent' => $parent_page_id,
            'post_content' => $final_content,
            'post_excerpt' => $meta_description,
            'post_status' => $post_status ?: 'publish',
            'post_date' => $publish_date,
            'post_date_gmt' => $publish_date,
        );

        $page_id = wp_update_post($updated_page);

        if (is_wp_error($page_id)) {
            return new WP_Error('error', 'Failed to update the existing page');
        }

        return array(
            'message' => 'Page updated successfully',
            'page_id' => $page_id,
            'page_url' => get_permalink($page_id),
            'page_status' => get_post_status($page_id),
            'meta_data' => $processed_with_meta['meta_data']
        );
    }

    // If no existing page with the provided slug, create a new page
    $new_page = array(
        'post_title' => $title,
        'post_name' => $post_slug,
        'post_content' => $final_content,
        'post_excerpt' => $meta_description,
        'post_type' => 'page',
        'post_status' => $post_status ?: 'publish',
        'post_parent' => $parent_page_id ?: 0, // Optional parent page ID
        'post_date' => $publish_date,
        'post_date_gmt' => $publish_date,
    );

    $page_id = wp_insert_post($new_page);

    if (is_wp_error($page_id)) {
        return new WP_Error('error', 'Failed to create a new page');
    }

    // Store meta data for the new page
    if (function_exists('update_post_meta')) {
        foreach ($processed['meta_data'] as $key => $value) {
            update_post_meta($page_id, $key, $value);
        }
        if ($processed['title_tag']) {
            update_post_meta($page_id, '_page_title_tag', $processed['title_tag']);
        }
    }

    return array(
        'message' => 'Page created successfully',
        'page_id' => $page_id,
        'page_url' => get_permalink($page_id),
        'page_status' => get_post_status($page_id),
        'meta_data' => $processed['meta_data']
    );
}

?>
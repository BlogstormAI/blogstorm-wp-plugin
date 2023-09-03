<?php
require_once(plugin_dir_path(__FILE__) . 'bs_register_tags_route.php');

function blogstorm_get_tags($request): array
{
    $per_page = isset($request['per_page']) ? absint($request['per_page']) : 10;
    $page = isset($request['page']) ? absint($request['page']) : 1;

    $tags = get_tags(array(
        'orderby' => 'name',
        'order' => 'ASC',
        'hide_empty' => false,
        'number' => $per_page,
        'offset' => ($page - 1) * $per_page,
    ));

    $total_tags = wp_count_terms('post_tag', array('hide_empty' => false));

    $result = array(
        'total_tags' => $total_tags,
        'tags' => array(),
    );

    foreach ($tags as $tag) {
        $result['tags'][] = array(
            'id' => $tag->term_id,
            'name' => $tag->name,
            'slug' => $tag->slug,
        );
    }

    return $result;
}

function blogstorm_get_or_create_tag(WP_REST_Request $request): array
{
    $slug = $request->get_param('slug');
    $name = $request->get_param('name');
    $tag_description = $request->get_param('tag_description');

    // Check if the tag exists by slug.
    $tag = get_term_by('slug', $slug, 'post_tag');
    if ($tag) {
        return array(
            'id' => $tag->term_id,
            'name' => $tag->name,
            'slug' => $tag->slug,
        );
    }

    // Check if the tag exists by name.
    $tag = get_term_by('name', $name, 'post_tag');
    if ($tag) {
        return array(
            'id' => $tag->term_id,
            'name' => $tag->name,
            'slug' => $tag->slug,
        );
    }

    // If the tag doesn't exist, create a new one.
    $tag_id = wp_insert_term($name, 'post_tag', array(
        'slug' => $slug,
        'description' => $tag_description ? $tag_description : '',
    ));

    if (!is_wp_error($tag_id)) {
        $tag = get_term($tag_id['term_id'], 'post_tag');
        return array(
            'id' => $tag->term_id,
            'name' => $tag->name,
            'slug' => $tag->slug,
        );
    } else {
        return array(
            'error' => 'Unable to create tag',
        );
    }
}

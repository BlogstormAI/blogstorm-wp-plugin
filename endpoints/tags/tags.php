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

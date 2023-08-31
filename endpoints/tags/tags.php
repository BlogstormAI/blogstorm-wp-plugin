<?php
require_once(plugin_dir_path(__FILE__) . 'bs_register_tags_route.php');

function blogstorm_get_tags(): array
{
    $tags = get_tags(array(
        'orderby' => 'name',
        'order' => 'ASC',
        'hide_empty' => false,
    ));

    $result = array();
    foreach ($tags as $tag) {
        $result[] = array(
            'id' => $tag->term_id,
            'name' => $tag->name,
            'slug' => $tag->slug,
        );
    }

    return $result;
}

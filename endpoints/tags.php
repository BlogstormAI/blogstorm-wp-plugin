<?php
// Register the tags endpoint
function blogstorm_register_tags_route()
{
    register_rest_route('blogstorm/v1', 'tags', array(
        'methods' => 'GET',
        'callback' => 'blogstorm_get_tags',
    ));
}

// Callback function for the tags endpoint
function blogstorm_get_tags()
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
<?php
// Register the tags endpoint
function blogstorm_register_tags_route(): void
{
    register_rest_route('blogstorm/v1', 'tags', array(
        'methods' => 'GET',
        'callback' => 'blogstorm_get_tags',
    ));
}

add_action('rest_api_init', 'blogstorm_register_tags_route');

// Callback function for the tags endpoint
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
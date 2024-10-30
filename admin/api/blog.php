<?php
// بسم الله الرحمن الرحيم

namespace PixelShadow\Blogify;

require_once BLOGIFY_PLUGIN_DIR . 'admin/api/authentication.php';


if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

function blogify_fetch_blog(string $blog_id)
{
    $response = wp_remote_get(
        BLOGIFY_SERVER_BASEURL . "public-api/v1/blogs/" . $blog_id,
        [
            'headers' => [
                'Authorization' => sprintf('Bearer %s', blogify_get_access_token()),
            ],
            'timeout' => 10,
        ]
    );
    if (is_wp_error($response)) {
        throw new \Exception(esc_textarea($response->get_error_message()));
    }
    return json_decode(wp_remote_retrieve_body($response), true, 512, JSON_THROW_ON_ERROR);
}

function blogify_get_blogs(int $page_number, int $page_size): array
{
    $response = wp_remote_get(
        BLOGIFY_SERVER_BASEURL . "public-api/v1/blogs?" . http_build_query([
            'offset' => ($page_number - 1) * $page_size,
            'limit' => $page_size,
        ]),
        [
            'headers' => [
                'Authorization' => sprintf('Bearer %s', blogify_get_access_token()),
            ],
            'timeout' => 10,
        ]
    );

    if (is_wp_error($response)) {
        throw new \Exception(esc_textarea($response->get_error_message()));
    }

    $body = wp_remote_retrieve_body($response);
    $blogs = json_decode($body, true);

    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new \Exception('Failed to decode response data' . __FUNCTION__);
    }

    return $blogs;
}

function blogify_get_publish_status_count(): array
{
    $statuses = ['draft', 'scheduled', 'published'];

    $results = \Requests::request_multiple(array_map(
        fn(string $status): array => [
            'type' => \Requests::GET,
            'url' => BLOGIFY_SERVER_BASEURL . "public-api/v1/blogs?publish-status=$status",
            'headers' => ['Authorization' => sprintf('Bearer %s', blogify_get_access_token())],
            'timeout' => 10,
        ],
        array_combine($statuses, $statuses)
    ));

    return array_map(fn($response) => json_decode($response->body, true, 512, JSON_THROW_ON_ERROR)['pagination']['totalResults'], $results);
}
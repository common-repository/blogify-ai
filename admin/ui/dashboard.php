<?php

namespace PixelShadow\Blogify;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

require_once BLOGIFY_PLUGIN_DIR . 'admin/api/blog.php';
require_once BLOGIFY_PLUGIN_DIR . 'admin/api/authentication.php';

$counts = blogify_get_publish_status_count();
$blogs = blogify_get_blogs(1, 5);
$totalBlogCount = $blogs['pagination']['totalResults'];
$nonce = wp_create_nonce('blogify-pagination');

if (
    $index = wp_verify_nonce(sanitize_text_field(wp_unslash($_GET['blogify-pagination-nonce'] ?? null)), 'blogify-pagination') && is_numeric(sanitize_text_field(wp_unslash($_POST['blog_id']))) ? sanitize_text_field(wp_unslash($_POST['blog_id'])) : null
) {
    $blog = $blogs['data'][$index];
    $post_id = wp_insert_post([
        'post_title' => sanitize_text_field($blog['title']),
        'post_content' => wp_kses_post($blog['content']),
        'post_status' => 'publish',
    ], true);
    if (is_wp_error($post_id)) {
        throw new \Exception('Failed to create post: ' . esc_textarea($post_id->get_error_message()));
    } else { ?>
        <div class="notice notice-success is-dismissible">
            <p>✅ The
                <a target="_blank" href="<?php echo esc_url(get_permalink($post_id)) ?>">post</a>
                has been published successfully!
            </p>
        </div>
    <?php }
}

?>

<div class="wrap">
    <div class="blogify">
        <?php require_once 'components/header.php'; ?>
        <main>
            <article class="blogify-status-bar">
                <?php foreach ($counts as $publish_status => $count): ?>
                    <article class='blogify-status-card'>
                        <span class='blogify-title'> <?php echo esc_html(ucfirst($publish_status)) ?> </span>
                        <span class='blogify-value'> <?php echo esc_html($count) ?> </span>
                        <span class='blogify-info'> <span>
                    </article>
                <?php endforeach; ?>
            </article>
            <article class="blogify-blog-list">
                <section class="blogify-header">
                    <span class="blogify-left">
                        <span class="blogify-title">My Blogs</span>
                    </span>
                    <span class="blogify-right">
                        <a href="<?php echo esc_url(get_admin_url() . 'admin.php?page=blogify-all-blogs') ?>">
                            <button type="button" class="blogify-primary">View All</button>
                        </a>
                        <a href="<?php echo esc_url(BLOGIFY_CLIENT_BASEURL . 'dashboard/blogs/select-source') ?>"
                            target="_blank">
                            <button type="button" class="blogify-primary">Create</button>
                        </a>
                    </span>
                </section>
                <section class="blogify-items">
                    <?php require_once 'components/blog-item.php';
                    if ($blogs['pagination']['totalResults']):
                        foreach ($blogs['data'] as $index => $blog):
                            blogify_blog_item($index, $blog['_id'], $blog['title'], $blog['image'], $blog['publishStatus'], $blog['wordCount'] ?? null, $nonce, 1, );
                        endforeach;
                    else: ?>
                        <p style="text-align: center; width: 100%;">
                            No Blogs Found
                        </p>
                    <?php endif; ?>
                </section>
            </article>
        </main>
    </div>
</div>
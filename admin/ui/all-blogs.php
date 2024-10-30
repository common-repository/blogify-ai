<?php

namespace PixelShadow\Blogify;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

require_once BLOGIFY_PLUGIN_DIR . 'admin/api/authentication.php';
require_once BLOGIFY_PLUGIN_DIR . 'admin/api/blog.php';

$nonce = wp_create_nonce('blogify-pagination');
$page_number = wp_verify_nonce(sanitize_text_field(wp_unslash($_GET['blogify-pagination-nonce'] ?? null)), 'blogify-pagination') && is_numeric(sanitize_text_field(wp_unslash($_GET['page-number']))) ? sanitize_text_field(wp_unslash($_GET['page-number'])) : 1;
$page_size = 20;
$blogs = blogify_get_blogs($page_number, $page_size);
if (
    $blog_id = wp_verify_nonce(sanitize_text_field(wp_unslash($_GET['blogify-pagination-nonce'] ?? null)), 'blogify-pagination') ? sanitize_text_field(wp_unslash($_POST['blog_id'] ?? null)) : null
) {
    $blog = blogify_fetch_blog($blog_id);
    $post_id = wp_insert_post([
        'post_title' => sanitize_text_field($blog['title']),
        'post_content' => wp_kses_post($blog['content']),
        'post_status' => sanitize_text_field($_POST['post_status']),
        'post_type' => sanitize_text_field($_POST['post_type']),
        'post_author' => sanitize_text_field($_POST['author']),
        'post_category' => array_map('sanitize_text_field', $_POST['categories'] ?? []),
    ], true);
    if (is_wp_error($post_id)) {
        throw new \Exception('Failed to create post: ' . esc_textarea($post_id->get_error_message()));
    } else { ?>
        <div style="background: white; padding: 10px;">
            <h1>✅ The
                <a target="_blank" href="<?php echo esc_url(get_permalink($post_id)) ?>">post</a>
                has been published successfully!
            </h1>
        </div>
    <?php }
} ?>
<div class="wrap">
    <div class="blogify">
        <?php require_once 'components/header.php' ?>
        <main>
            <article class="blogify-blog-list">
                <section class="blogify-header">
                    <span class="blogify-left">
                        <span class="blogify-title">My Blogs</span>
                    </span>
                    <span class="blogify-right">
                        <a href="<?php echo esc_url(BLOGIFY_CLIENT_BASEURL . 'dashboard/blogs') ?>" target="_blank">
                            <button hidden type="button" class="blogify-primary">View All</button>
                        </a>
                        <a href="<?php echo esc_url(BLOGIFY_CLIENT_BASEURL . 'dashboard/blogs/select-source') ?>"
                            target="_blank">
                            <button hidden type="button" class="blogify-primary">Create</button>
                        </a>
                    </span>
                </section>
                <section class="blogify-items">
                    <?php require_once 'components/blog-item.php';
                    if ($blogs['data']):
                        foreach ($blogs['data'] as $index => $blog):
                            blogify_blog_item($index, $blog['_id'], $blog['title'], $blog['image'] ?? null, $nonce, 'blogify-ai', $page_number);
                        endforeach; ?>
                    <?php else: ?>
                        <p style="text-align: center; width: 100%;">
                            No Blogs Found
                        </p>
                    <?php endif; ?>
                </section>
            </article>
        </main>
        <footer>
            <?php require_once 'components/pagination.php';
            blogify_pagination(
                $page_number,
                $blogs['total'],
                $page_size,
                ceil($blogs['total'] / $page_size),
                $nonce,
                'blogify-ai'
            );
            ?>
        </footer>
    </div>
</div>
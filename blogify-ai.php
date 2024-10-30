<?php
// بسم الله الرحمن الرحيم
/**
 * Blogify-AI
 *
 * @package           Blogify-AI
 * @author            Fida Waseque Choudhury
 * @copyright         PixelShadow
 * @license           GPL-2.0-or-later
 *
 * @wordpress-plugin
 * Plugin Name:       Blogify-AI
 * Plugin URI:        https://blogify.ai/
 * Description:       Seamlessly publish AI-generated blog posts from Blogify.ai to your WordPress site with ease, enhancing content management and SEO optimization in a few clicks.
 * Version:           1.1.1
 * Requires at least: 6.0
 * Requires PHP:      7.4
 * Author:            PixelShadow
 * Developer:         Fida Waseque Choudhury
 * Developer URI:     https://www.linkedin.com/in/u3kkasha/
 * Text Domain:       blogify-ai
 * License:           GPL v2 or later
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 */

namespace PixelShadow\Blogify;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

// Constants
DEFINE('BLOGIFY_VERSION', '1.1.1');

DEFINE('BLOGIFY_PLUGIN_DIR', plugin_dir_path(__FILE__));
DEFINE('BLOGIFY_UI_PAGES_DIR', BLOGIFY_PLUGIN_DIR . 'admin/ui/');
DEFINE('BLOGIFY_UI_COMPONENTS_DIR', BLOGIFY_PLUGIN_DIR . 'admin/ui/components');

DEFINE('BLOGIFY_SERVER_BASEURL',  "https://api.blogify.ai/");
DEFINE('BLOGIFY_CLIENT_BASEURL',  "https://blogify.ai/");

DEFINE('BLOGIFY_ASSETS_URL', plugins_url('/admin/assets/', __FILE__));
DEFINE('BLOGIFY_IMAGES_URL', BLOGIFY_ASSETS_URL . 'images/');
DEFINE('BLOGIFY_CSS_URL', BLOGIFY_ASSETS_URL . 'css/');
DEFINE('BLOGIFY_JS_URL', BLOGIFY_ASSETS_URL . 'js/');

// All hooks
require_once BLOGIFY_PLUGIN_DIR . 'admin/actions/index.php';

if (get_option('blogify_access_token', null)) /* This branch executes when the user has already connected this site to Blogify.ai */{

    add_filter('plugin_action_links_' . plugin_basename(__FILE__), function ($actions) {
        $actions[] = '<a href="' . esc_url(get_admin_url(null, 'admin.php?page=blogify-ai')) . '">Dashboard</a>';
        return $actions;
    });

    add_action(
        'admin_menu', fn() => add_menu_page(
            'Blogify-AI Turn Anything into A Blog!',
            'Blogify-AI 📝',
            'manage_options',
            'blogify-ai',
            fn() => require_once BLOGIFY_UI_PAGES_DIR . 'all-blogs.php',
            BLOGIFY_IMAGES_URL . 'icons/blogify-navigation.svg',
        )
    );

    // add_action(
    //     'admin_menu',
    //     fn() => add_submenu_page(
    //         'blogify-ai',
    //         'All Blogs on Blogify',
    //         'All Blogs',
    //         'manage_options',
    //         'blogify-all-blogs',
    //         fn() => require_once BLOGIFY_UI_PAGES_DIR . 'all-blogs.php',
    //     )
    // );

    add_action(
        'admin_menu',
        fn() => add_submenu_page(
            'blogify-ai',
            'Blogify Subscription',
            'Subscription',
            'manage_options',
            'blogify-subscription',
            fn() => require_once BLOGIFY_UI_PAGES_DIR . 'subscription.php',
        )
    );
} else /* This branch executes when the user has not yet connected this site with his Blogify.ai account */{

    add_filter('plugin_action_links_' . plugin_basename(__FILE__), function ($actions) {
        $actions[] = '<a href="' . esc_url(get_admin_url(null, 'options-general.php?page=blogify')) . '">Connect this site to Blogify.ai</a>';
        return $actions;
    });
}

add_action(
    'admin_enqueue_scripts', function () {
        wp_enqueue_style(
            'blogify-theme',
            BLOGIFY_CSS_URL . 'theme.css',
            [], BLOGIFY_VERSION
        );
        wp_enqueue_style(
            'blogify-header',
            BLOGIFY_CSS_URL . 'header.css',
            ['blogify-theme'], BLOGIFY_VERSION
        );
        wp_enqueue_style(
            'blogify-buttons',
            BLOGIFY_CSS_URL . 'button.css',
            ['blogify-theme'], BLOGIFY_VERSION
        );
        wp_enqueue_style(
            'blogify-status-card',
            BLOGIFY_CSS_URL . 'status-card.css',
            ['blogify-theme'], BLOGIFY_VERSION
        );
        wp_enqueue_style(
            'publish-dialog',
            BLOGIFY_CSS_URL . 'dialog.css',
            ['blogify-theme'], BLOGIFY_VERSION
        );
        wp_enqueue_style(
            'blogify-blog-list',
            BLOGIFY_CSS_URL . 'blog-list.css',
            ['blogify-theme', 'blogify-buttons', 'publish-dialog'], BLOGIFY_VERSION
        );
        wp_enqueue_style(
            'blogify-pagination',
            BLOGIFY_CSS_URL . 'pagination.css',
            ['blogify-theme', 'blogify-buttons'], BLOGIFY_VERSION
        );
    }
);

// This is intentional to allow users to reset their connection with their Blogify.ai account and start over again without having to delete the plugin.
add_action("deactivate_" . plugin_basename(__FILE__), fn() => delete_option('blogify_access_token'));

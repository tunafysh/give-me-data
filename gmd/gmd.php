<?php
/*
* Plugin Name: Give Me Data
* Description: A plugin that allows you to retrieve all posts or a specific amount.
* Version: 1.0
* Author: tunafysh
* Author URI: https://github.com/tunafysh
*/
// Add rewrite rule
function gmd_rewrite_rule() {
    add_rewrite_rule('^give-me-data/?', 'index.php?give_me_data=1', 'top');
}
add_action('init', 'gmd_rewrite_rule');

// Add query variable
function gmd_query_vars($vars) {
    $vars[] = 'give_me_data';
    return $vars;
}
add_filter('query_vars', 'gmd_query_vars');

// Handle the request
function gmd_template_redirect() {
    if (get_query_var('give_me_data')) {
        $posts_param = isset($_GET['posts']) ? $_GET['posts'] : '1';

        // Determine the number of posts to fetch
        $posts_per_page = ($posts_param === 'all') ? -1 : intval($posts_param);

        // Fetch posts data
        $args = array(
            'posts_per_page' => $posts_per_page,
            'post_status' => 'publish',
        );
        $query = new WP_Query($args);
        $posts_data = array();

        if ($query->have_posts()) {
            while ($query->have_posts()) {
                $query->the_post();
                $content = get_the_content();

                // Strip off <!-- wp:paragraph --> tags
                $content = preg_replace('/<!-- wp:paragraph -->|<!-- \/wp:paragraph -->/', '', $content);

                // Translate \n to <br>
                $content = preg_replace('/\n/', '<br>', $content);

                $posts_data[] = array(
                    'ID' => get_the_ID(),
                    'title' => get_the_title(),
                    'content' => $content,
                    'date' => get_the_date(),
                );
            }
            wp_reset_postdata();
        }


        // Return JSON response
        header('Content-Type: application/json');
        echo json_encode($posts_data);
        exit;

    }
}
add_action('template_redirect', 'gmd_template_redirect');

// Flush rewrite rules on activation
function init() {
    gmd_rewrite_rule();
    flush_rewrite_rules();
}
register_activation_hook(__FILE__, 'init');

// Flush rewrite rules on deactivation
function deactivate() {
    flush_rewrite_rules();
}
register_deactivation_hook(__FILE__, 'deactivate');
?>
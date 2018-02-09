<?php
/*
Plugin Name: WP ReStructuredText
Plugin URI: https://github.com/imliuda/wp-rst
Description: Use reStructuredText to write WordPress posts and pages.
Version: 1.0
Author: liuda
Author URI: http://imliuda.com/
Text Domain: wp-rst
Domain Path: /languages
License: GPL2

{Plugin Name} is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 2 of the License, or
any later version.
 
{Plugin Name} is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.
 
You should have received a copy of the GNU General Public License
along with {Plugin Name}. If not, see {URI to Plugin License}.
*/

register_activation_hook(__FILE__, 'wprst_activation');
register_uninstall_hook(__FILE__, 'wprst_uninstall');

add_filter('save_post', 'wprst_save_post', 0, 3);
add_action('admin_menu', 'wprst_admin_menu');

function wprst_activation() {
    add_option('wprst_rst2html_bin', '/usr/bin/rst2html');
    add_option('wprst_rst2html_args', '--link-stylesheet --toc-entry-backlinks --initial-header-level=2  --no-doc-title --no-footnote-backlinks --syntax-highlight=short');
}

function wprst_uninstall() {
    add_option('wprst_rst2html_bin');
    add_option('wprst_rst2html_args');
}

function rst_to_html($source) {
    $bin = get_option('wprst_rst2html_bin', false);
    $args = get_option('wprst_rst2html_args', false);
    if (!$bin || !$args) { return; }
    $cmd = "{$bin} {$args}";
    $descriptors = array(
        0 => array('pipe', 'r'),
        1 => array('pipe', 'w'),
        2 => array('pipe', 'w')
    );
    $proc = proc_open($cmd, $descriptors, $pipes);
    if (!is_resource($proc)) {
        return 'Error opening process.';
    }
    $stdin = $pipes[0];
    $stdout = $pipes[1];
    $stderr = $pipes[2];
    fwrite($stdin, $source);
    fflush($stdin);
    fclose($stdin);
    fflush($stdout);
    $content = stream_get_contents($stdout);
    fclose($stdout);
    fflush($stderr);
    $errors = stream_get_contents($stderr);
    fclose($stderr);
    $ret = proc_close($proc);
    if ($ret != 0) {
        $msg = "Command: {$cmd} <br/>\nExit code: {$ret} <br/>\n{$errors} <br/>\n{$content} <br/>\n";
        print_r($msg);
        die();
    }
    $content = preg_replace('/(.*)<\/body>.*/ms', '$1', $content);
    $content = preg_replace('/.*<body>[\n\s]+(.*)/ms', '$1', $content);
    $content = str_replace('<!-- more -->', '<!--more-->', $content);
    return $content;
}

function wprst_save_post($post_ID, $post, $update){
    global $wpdb;
    $post_ID = $post->ID;
    // Retrieve reST source.
    $source = $post->post_content;
    if (get_magic_quotes_gpc()){
        $source = stripslashes($source);
    }
    // Save source as meta.
    update_post_meta($post_ID, 'post_rst', $source);
    // Convert rst to html.
    $content = rst_to_html($source);
    // Save to the Database
    $where = array( 'ID' => $post_ID );
    $wpdb->update($wpdb->posts, array( 'post_content' => $content), $where);
    clean_post_cache($post_ID);
    $post = get_post($post_ID);
}


function wprst_admin_menu() {
    add_options_page(
        'reStructuredText',
        'reStructuredText',
        'manage_options',
        'wprst',
        'wprst_options_page'
    );
}

function wprst_options_page() {
    // check user capabilities
    if (!current_user_can('manage_options')) {
        return;
    }
    ?>
    <div class="wrap">
        <h1><?= esc_html(get_admin_page_title()); ?></h1>
        <form action="options.php" method="post">
        </form>
    </div>
    <?php
}

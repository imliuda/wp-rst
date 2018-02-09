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

add_action('init', 'wprst_init');
add_action('admin_menu', 'wprst_admin_init');

function wprst_activation() {

}

function wprst_uninstall() {

}

function wprst_init() {

}

function wprst_admin_init() {
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

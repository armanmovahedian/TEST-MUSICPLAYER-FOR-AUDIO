<?php
/**
 * Plugin Name: Arman's Audio Playlist
 * Description: A custom plugin to upload, manage, and display audio files with custom attributes on product pages.
 * Version: 1.0
 * Author: Arman Movahedian
 * Author URI: https://www.linkedin.com/in/arman-movahedian-5393662b1/
 * License: GPL2
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
    die;
}

// Define plugin path and URL constants.
define( 'AAP_PLUGIN_PATH', plugin_dir_path( __FILE__ ) );
define( 'AAP_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

// Include the custom post type and meta box functions.
require_once AAP_PLUGIN_PATH . 'includes/post-types.php';
require_once AAP_PLUGIN_PATH . 'includes/meta-boxes.php';
require_once AAP_PLUGIN_PATH . 'includes/shortcodes.php';
require_once AAP_PLUGIN_PATH . 'includes/playlist-management.php';
require_once AAP_PLUGIN_PATH . 'includes/scripts.php';
require_once AAP_PLUGIN_PATH . 'includes/security.php';

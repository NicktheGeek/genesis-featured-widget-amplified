<?php
/**
 * Plugin Name: Genesis Featured Widget Amplified
 * Plugin URI: http://DesignsByNicktheGeek.com
 * Version: 0.9.0
 * Author: Nick_theGeek
 * Author URI: http://DesignsByNicktheGeek.com
 * Description: Replaces Genesis Featured Post widget for additional functionality which allows support for custom post types, taxonomies, and extends the flexibility of the widget via action hooks to allow the elements to be repositioned or other elements to be added. This requires WordPress 3.5+ and Genesis 1.9+.
 * Text Domain: gfwa
 * Domain Path /languages/
 *
 * @package gfwa
 */

/*
 * To Do:
 *      Create and setup screen shots
 */

/**
 * Load textdomain for translation
 */
load_plugin_textdomain( 'gfwa', false, basename( dirname( __FILE__ ) ) . '/languages/' );

define( 'GFWA_PLUGIN_DIR', dirname( __FILE__ ) );


/**
 * Prevent direct access to the plugin
 */
if ( ! defined( 'ABSPATH' ) ) {
	wp_die( esc_html__( 'Sorry, you are not allowed to access this page directly.', 'gfwa' ) );
}

add_action( 'genesis_init', 'gfwa_init' );
/**
 * Requires plugin files.
 */
function gfwa_init() {
	require_once GFWA_PLUGIN_DIR . '/inc/functions/helpers.php';
	require_once GFWA_PLUGIN_DIR . '/inc/classes/class-genesis-featured-widget-amplified.php';
}

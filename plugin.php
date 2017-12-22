<?php
/**
 * Genesis Featured Widget Amplified
 *
 * @package     NickTheGeek\GenesisFeaturedWidgetAmplified
 * @author      Nick Croft
 * @copyright   2011 Nick Croft
 * @license     GPL-2.0+
 *
 * @wordpress-plugin
 * Plugin Name:       Genesis Featured Widget Amplified
 * Plugin URI:        https://github.com/NicktheGeek/genesis-featured-widget-amplified
 * Description:       Replaces Genesis Featured Post widget for additional functionality which allows support for custom post types, taxonomies, and extends the flexibility of the widget via action hooks to allow the elements to be repositioned or other elements to be added. This requires WordPress 3.3+ and Genesis 1.9+.
 * Version:           0.9.2
 * Author:            Nick_theGeek
 * Author URI:        https://designsbynickthegeek.com/
 * Text Domain:       gfwa
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * GitHub Plugin URI: https://github.com/NicktheGeek/genesis-featured-widget-amplified
 * Requires PHP:      5.2
 * Requires WP:       3.3
 */

/*
 * To Do:
 *      Create and setup screen shots
 */

/*
 * Load textdomain for translation
 */
load_plugin_textdomain( 'gfwa', false, basename( dirname( __FILE__ ) ) . '/languages/' );

define( 'GFWA_PLUGIN_DIR', dirname( __FILE__ ) );


/*
 * Prevent direct access to the plugin
 */
if ( ! defined( 'ABSPATH' ) ) {
	wp_die( esc_html__( 'Sorry, you are not allowed to access this page directly.', 'gfwa' ) );
}

add_action( 'genesis_init', 'gfwa_init' );
/**
 * Requires the plugin files
 */
function gfwa_init() {
	require_once GFWA_PLUGIN_DIR . '/inc/functions/helpers.php';
	require_once GFWA_PLUGIN_DIR . '/inc/classes/class-genesis-featured-widget-amplified.php';
}

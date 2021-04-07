<?php
/**
 * Register and unregister actions for GFWA widget.
 *
 * @package gfwa
 */

/**
 * Prevent direct access to the plugin
 */
if ( ! defined( 'ABSPATH' ) ) {
	// WP functions aren't available since someone is doing something funky.
	die( 'Sorry, you are not allowed to access this page directly.' );
}

// Remove the current widget.
add_action( 'widgets_init', 'gfwa_unregister_widgets', 20 );

/**
 * Removes Genesis Featured Post Widget.
 */
function gfwa_unregister_widgets() {
	unregister_widget( 'Genesis_Featured_Post' );
}

add_action( 'widgets_init', 'gfwa_register_widgets' );

/**
 * Adds Genesis Featured Widget Amplified.
 */
function gfwa_register_widgets() {
	register_widget( 'Genesis_Featured_Widget_Amplified' );
}

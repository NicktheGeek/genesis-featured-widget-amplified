<?php

/*
  Plugin Name: Genesis Featured Widget Amplified
  Plugin URI: http://DesignsByNicktheGeek.com
  Version: 0.8.1
  Author: Nick_theGeek
  Contributor: GaryJ
  Author URI: http://DesignsByNicktheGeek.com
  Description: Replaces Genesis Featured Post widget for additional functionality which allows support for custom post types, taxonomies, and extends the flexibility of the widget via action hooks to allow the elements to be repositioned or other elements to be added. This requires WordPress 3.0+ and Genesis 1.4+.
  Text Domain: gfwa
  Domain Path /languages/
 */

/*
 * To Do:
 *      Create and setup screen shots
 */

/** Load textdomain for translation */
load_plugin_textdomain( 'gfwa', false, basename( dirname( __FILE__ ) ) . '/languages/' );

define( 'GFWA_PLUGIN_DIR', dirname( __FILE__ ) );


/* Prevent direct access to the plugin */
if ( !defined( 'ABSPATH' ) ) {
    wp_die( __( "Sorry, you are not allowed to access this page directly.", 'gfwa' ) );
}

register_activation_hook( __FILE__, 'gfwa_activation_check' );

/**
 * Checks for minimum Genesis Theme version before allowing plugin to activate
 *
 * @author Nathan Rice
 * @uses gfwa_truncate()
 * @since 0.1
 * @version 0.2
 */
function gfwa_activation_check() {

    $latest = '1.8';

    $theme_info = get_theme_data( TEMPLATEPATH . '/style.css' );

    if ( basename( TEMPLATEPATH ) != 'genesis' ) {
        deactivate_plugins( plugin_basename( __FILE__ ) ); // Deactivate ourself
        wp_die( sprintf( __( 'Sorry, you can\'t activate unless you have installed %1$sGenesis%2$s', 'gfwa' ), '<a href="http://designsbynickthegeek.com/go/genesis">', '</a>' ) );
    }

    $version = gfwa_truncate( $theme_info['Version'], 3 );

    if ( version_compare( $version, $latest, '<' ) ) {
        deactivate_plugins( plugin_basename( __FILE__ ) ); // Deactivate ourself
        wp_die( sprintf( __( 'Sorry, you can\'t activate without %1$sGenesis %2$s%3$s or greater', 'gfwa' ), '<a href="http://designsbynickthegeek.com/go/genesis">', $latest, '</a>' ) );
    }
}

/**
 *
 * Used to cutoff a string to a set length if it exceeds the specified length
 *
 * @author Nick Croft
 * @since 0.1
 * @version 0.2
 * @param string $str Any string that might need to be shortened
 * @param string $length Any whole integer
 * @return string
 */
function gfwa_truncate( $str, $length=10 ) {

    if ( strlen( $str ) > $length ) {
        return substr( $str, 0, $length );
    } else {
        $res = $str;
    }

    return $res;
}

/**
 * Does Widget Action "gfwa_before_loop"
 *
 * @author Nick Croft
 * @since 0.1
 * @version 0.2
 * @param array $instance Values set in widget isntance
 */
function gfwa_before_loop( $instance ) {
    do_action( 'gfwa_before_loop', $instance );
}

/**
 * Does Widget Action "gfwa_before_post_content"
 *
 * @author Nick Croft
 * @since 0.1
 * @version 0.2
 * @param array $instance Values set in widget isntance
 */
function gfwa_before_post_content( $instance ) {
    do_action( 'gfwa_before_post_content', $instance );
}

/**
 * Does Widget Action "gfwa_post_content"
 *
 * @author Nick Croft
 * @since 0.1
 * @version 0.2
 * @param array $instance Values set in widget isntance
 */
function gfwa_post_content( $instance ) {
    do_action( 'gfwa_post_content', $instance );
}

/**
 * Does Widget Action "gfwa_after_post_content"
 *
 * @author Nick Croft
 * @since 0.1
 * @version 0.2
 * @param array $instance Values set in widget isntance
 */
function gfwa_after_post_content( $instance ) {
    do_action( 'gfwa_after_post_content', $instance );
}

/**
 * Does Widget Action "gfwa_endwhile"
 *
 * @author Nick Croft
 * @since 0.1
 * @version 0.2
 * @param array $instance Values set in widget isntance
 */
function gfwa_endwhile( $instance ) {
    do_action( 'gfwa_endwhile', $instance );
}

/**
 * Does Widget Action "gfwa_after_loop"
 *
 * @author Nick Croft
 * @since 0.1
 * @version 0.2
 * @param array $instance Values set in widget isntance
 */
function gfwa_after_loop( $instance ) {
    do_action( 'gfwa_after_loop', $instance );
}

/**
 * Does Widget Action "gfwa_list_items"
 *
 * @author Nick Croft
 * @since 0.1
 * @version 0.2
 * @param array $instance Values set in widget isntance
 */
function gfwa_list_items( $instance ) {
    do_action( 'gfwa_list_items', $instance );
}

/**
 * Does Widget Action "gfwa_print_list_items"
 *
 * @author Nick Croft
 * @since 0.1
 * @version 0.2
 * @param array $instance Values set in widget isntance
 */
function gfwa_print_list_items( $instance ) {
    do_action( 'gfwa_print_list_items', $instance );
}

/**
 * Does Widget Action "gfwa_category_more"
 *
 * @author Nick Croft
 * @since 0.1
 * @version 0.2
 * @param array $instance Values set in widget isntance
 */
function gfwa_category_more( $instance ) {
    do_action( 'gfwa_category_more', $instance );
}

/**
 * Does Widget Action "gfwa_after_category_more"
 *
 * @author Nick Croft
 * @since 0.1
 * @version 0.2
 * @param array $instance Values set in widget isntance
 */
function gfwa_after_category_more( $instance ) {
    do_action( 'gfwa_after_category_more', $instance );
}

/**
 * Does Widget Action "gfwa_form_first_colum"
 *
 * @author Nick Croft
 * @since 0.1
 * @version 0.2
 * @param array $instance Values set in widget isntance
 */
function gfwa_form_first_column( $instance ) {
    do_action( 'gfwa_form_first_colum', $instance );
}

/**
 * Does Widget Action "gfwa_form_second_colum"
 *
 * @author Nick Croft
 * @since 0.1
 * @version 0.2
 * @param array $instance Values set in widget isntance
 */
function gfwa_form_second_column( $instance ) {
    do_action( 'gfwa_form_second_colum', $instance );
}

/**
 * Used to exclude taxonomies and related terms from list of available terms/taxonomies in widget form()
 *
 * @author Nick Croft
 * @since 0.1
 * @version 0.2
 * @param string $taxonomy 'taxonomy' being tested
 * @return string
 */
function gfwa_exclude_taxonomies( $taxonomy ) {
    $filters = array( '', 'nav_menu' );
    $filters = apply_filters( 'gfwa_exclude_taxonomies', $filters );
    return(!in_array( $taxonomy->name, $filters ));
}

/**
 * Used to exclude post types from list of available post_types in widget form()
 *
 * @author Nick Croft
 * @since 0.1
 * @version 0.2
 * @param string $type 'post_type' being tested
 * @return string
 */
function gfwa_exclude_post_types( $type ) {
    $filters = array( '', 'attachment' );
    $filters = apply_filters( 'gfwa_exclude_post_types', $filters );
    return(!in_array( $type, $filters ));
}

/**
 * Filters the Post Limit to allow pagination with offset
 *
 * @author Nick Croft
 * @since 0.3
 * @version 0.3
 * @global int $paged
 * @global string $myOffset 'integer'
 * @param string $limit
 * @return string
 */
function gfwa_post_limit( $limit ) {
    global $paged, $myOffset;
    if ( empty( $paged ) ) {
        $paged = 1;
    }
    $postperpage = intval( get_option( 'posts_per_page' ) );
    $pgstrt = ((intval( $paged ) - 1) * $postperpage) + $myOffset . ', ';
    $limit = 'LIMIT ' . $pgstrt . $postperpage;
    return $limit;
}

// Include files
require_once(GFWA_PLUGIN_DIR . '/widget.php');
<?php

/*
  Plugin Name: Genesis Featured Widget Amplified
  Plugin URI: http://DesignsByNicktheGeek.com
  Version: 0.3b
  Author: Nick Croft
  Author URI: http://DesignsByNicktheGeek.com
  Description: Adds additional Featured Post widget to the Genesis Theme Framework which allows support for custom post types, taxonomies, and extends the flexibility of the widget via action hooks to allow the elements to be repositioned or other elements to be added. This requires WordPress 3.0+ and Genesis 1.4+.
 */

//To Do: Fix all text strings to be translatable

define( 'GFWA_PLUGIN_DIR', dirname( __FILE__ ) );
define( 'GFWA_TEXTDOMAIN', 'GFWA' );

/* Prevent direct access to the plugin */
if ( !defined( 'ABSPATH' ) ) {
    wp_die( __( "Sorry, you are not allowed to access this page directly.", GFWA_TEXTDOMAIN ) );
}

register_activation_hook( __FILE__, 'gfwa_activation_check' );

/**
 * Checks for minimum Genesis Theme version before allowing plugin to activate
 *
 * @author Nathan Rice
 * @uses get_theme_data()
 * @uses gfwa_truncate()
 * @uses version_compare()
 * @since 0.1
 * @version 0.2
 */
function gfwa_activation_check() {

    $latest = '1.4';

    $theme_info = get_theme_data( TEMPLATEPATH . '/style.css' );

    if ( basename( TEMPLATEPATH ) != 'genesis' ) {
        deactivate_plugins( plugin_basename( __FILE__ ) ); // Deactivate ourself
        wp_die( __('Sorry, you can\'t activate unless you have installed', GFWA_TEXTDOMAIN) . ' <a href="http://www.studiopress.com/themes/genesis">' .__('Genesis', GFWA_TEXTDOMAIN) .'</a>' );
    }

    $version = gfwa_truncate( $theme_info['Version'], 3 );

    if ( version_compare( $version, $latest, '<' ) ) {
        deactivate_plugins( plugin_basename( __FILE__ ) ); // Deactivate ourself
        wp_die( __('Sorry, you can\'t activate without', GFWA_TEXTDOMAIN) . ' <a href="http://www.studiopress.com/themes/genesis">' .__('Genesis', GFWA_TEXTDOMAIN) . $latest .'</a> '. __('or greater', GFWA_TEXTDOMAIN) );
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
 * @uses do_action()
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
 * @uses do_action()
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
 * @uses do_action()
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
 * @uses do_action()
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
 * @uses do_action()
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
 * @uses do_action()
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
 * @uses do_action()
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
 * @uses do_action()
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
 * @uses do_action()
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
 * @uses do_action()
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
 * @uses do_action()
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
 * @uses do_action()
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
 * @uses apply_filters()
 * @since 0.1
 * @version 0.2
 * @param string $taxonomy 'taxonomy' being tested
 * @return string
 */
function gfwa_exclude_taxonomies( $taxonomy ) {
    $filters = array( '', 'nav_menu' );
    $filters = apply_filters( 'gfwa_exclude_taxonomies', $filters );
    return(!in_array( $taxonomy, $filters ));
}

/**
 * Used to exclude post types from list of available post_types in widget form()
 *
 * @author Nick Croft
 * @uses apply_filters()
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
function gfwa_post_limit($limit) {
	global $paged, $myOffset;
	if (empty($paged)) {
			$paged = 1;
	}
	$postperpage = intval(get_option('posts_per_page'));
	$pgstrt = ((intval($paged) -1) * $postperpage) + $myOffset . ', ';
	$limit = 'LIMIT '.$pgstrt.$postperpage;
	return $limit;
} 

// Include files
require_once(GFWA_PLUGIN_DIR . '/widget.php');
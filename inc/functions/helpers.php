<?php
/**
 * Helper functions for the GFWA widget.
 *
 * @package gfwa
 */

/**
 *
 * Used to cutoff a string to a set length if it exceeds the specified length
 *
 * @author Nick Croft
 * @since 0.1
 * @version 0.2
 * @param  string $str    Any string that might need to be shortened.
 * @param  int    $length Any whole integer.
 * @return string
 */
function gfwa_truncate( $str, $length = 10 ) {
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
 * @param array $instance Values set in widget instance.
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
 * @param array $instance Values set in widget instance.
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
 * @param array $instance Values set in widget instance.
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
 * @param array $instance Values set in widget instance.
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
 * @param array $instance Values set in widget instance.
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
 * @param array $instance Values set in widget instance.
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
 * @param array $instance Values set in widget instance.
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
 * @param array $instance Values set in widget instance.
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
 * @param array $instance Values set in widget instance.
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
 * @param array $instance Values set in widget instance.
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
 * @param array $instance Values set in widget instance.
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
 * @param array $instance Values set in widget instance.
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
 * @param  WP_Term $taxonomy 'taxonomy' being tested.
 * @return string
 */
function gfwa_exclude_taxonomies( $taxonomy ) {
	$filters = array( '', 'nav_menu' );
	$filters = apply_filters( 'gfwa_exclude_taxonomies', $filters );
	return( ! in_array( $taxonomy->name, $filters, true ) );
}

/**
 * Used to exclude post types from list of available post_types in widget form()
 *
 * @author Nick Croft
 * @since 0.1
 * @version 0.2
 * @param string $type 'post_type' being tested.
 * @return string
 */
function gfwa_exclude_post_types( $type ) {
	$filters = array( '', 'attachment' );
	$filters = apply_filters( 'gfwa_exclude_post_types', $filters );
	return( ! in_array( $type, $filters, true ) );
}

/**
 * Filters the Post Limit to allow pagination with offset
 *
 * @author Nick Croft
 * @since 0.3
 * @version 0.3
 * @global int $paged
 * @global string $gfwa_offset 'integer'
 * @return string
 */
function gfwa_post_limit() {
	global $paged, $gfwa_offset;
	if ( empty( $paged ) ) {
		$paged = 1; // phpcs:ignore
	}
	$postperpage = (int) get_option( 'posts_per_page' );
	$pgstrt      = ( ( (int) $paged - 1 ) * $postperpage ) + $gfwa_offset . ', ';
	$limit       = 'LIMIT ' . $pgstrt . $postperpage;
	return $limit;
}

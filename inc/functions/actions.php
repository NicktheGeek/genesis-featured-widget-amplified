<?php
/**
 * All the default actions.
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

/**
 *  Adds number class, and odd/even class to widget output
 *
 * @author Nick Croft
 * @since 0.7
 * @version 0.7
 * @global integer $gfwa_counter
 * @param  array $classes The post classes array.
 * @return array
 */
function gfwa_post_class( $classes ) {
	global $gfwa_counter;

	$classes[] = sprintf( 'gfwa-%s', $gfwa_counter + 1 );
	$classes[] = $gfwa_counter + 1 & 1 ? 'gfwa-odd' : 'gfwa-even';

	return $classes;
}

add_action( 'gfwa_before_post_content', 'gfwa_do_post_image', 5, 1 );
add_action( 'gfwa_post_content', 'gfwa_do_post_image', 5, 1 );
add_action( 'gfwa_after_post_content', 'gfwa_do_post_image', 10, 1 );

/**
 * Inserts Post Image
 *
 * @author Nick Croft
 * @since 0.1
 * @version 0.5
 * @param array $instance Values set in widget $instance.
 */
function gfwa_do_post_image( $instance ) {
	$align = $instance['image_alignment'] ? esc_attr( $instance['image_alignment'] ) : 'alignnone';
	$link  = $instance['link_image_field'] && genesis_get_custom_field( $instance['link_image_field'] ) ? genesis_get_custom_field( $instance['link_image_field'] ) : get_permalink();

	$link_image = 1 === (int) $instance['link_image'];

	$image = ! empty( $instance['show_image'] ) ? genesis_get_image(
		array(
			'format' => 'html',
			'size'   => $instance['image_size'],
			'attr'   => array( 'class' => $link_image ? '' : $align ),
		)
	) : '';
	$image = $link_image ? sprintf( '<a href="%s" title="%s" class="%s">%s</a>', esc_url( $link ), the_title_attribute( 'echo=0' ), esc_attr( $align ), $image ) : $image;

	echo current_filter() === 'gfwa_before_post_content' && 'before-title' === $instance['image_position'] && ! empty( $instance['show_image'] ) ? $image : ''; // XSS ok.
	echo current_filter() === 'gfwa_post_content' && 'after-title' === $instance['image_position'] && ! empty( $instance['show_image'] ) ? $image : ''; // XSS ok.
	echo current_filter() === 'gfwa_after_post_content' && 'after-content' === $instance['image_position'] && ! empty( $instance['show_image'] ) ? $image : ''; // XSS ok.
}

add_action( 'gfwa_before_post_content', 'gfwa_do_gravatar', 10, 1 );

/**
 * Inserts Author Gravatar if option is selected
 *
 * @author Nick Croft
 * @since 0.1
 * @version 0.8
 * @param array $instance Values set in widget $instance.
 */
function gfwa_do_gravatar( $instance ) {
	if ( ! empty( $instance['show_gravatar'] ) ) {
		switch ( $instance['link_gravatar'] ) {
			case 'archive':
				$before = 'a href="' . get_author_posts_url( get_the_author_meta( 'ID' ) ) . '"';
				$after  = 'a';

				break;

			case 'website':
				$before = 'a href="' . get_the_author_meta( 'user_url' ) . '"';
				$after  = 'a';

				break;

			default:
				$before = 'span';
				$after  = 'span';

				break;
		}

		printf( '<%s class="%s">%s</%s>', esc_attr( $before ), esc_attr( $instance['gravatar_alignment'] ), get_avatar( get_the_author_meta( 'ID' ), $instance['gravatar_size'] ), esc_attr( $after ) );
	}
}

add_action( 'gfwa_before_post_content', 'gfwa_do_post_title', 10, 1 );

/**
 * Outputs Post Title if option is selects
 *
 * @author Nick Croft
 * @since 0.1
 * @version 0.2
 * @param array $instance Values set in widget $instance.
 */
function gfwa_do_post_title( $instance ) {
	$link = $instance['link_title_field'] && genesis_get_custom_field( $instance['link_title_field'] ) ? genesis_get_custom_field( $instance['link_title_field'] ) : get_permalink();

	$wrap_open  = 1 === (int) $instance['link_title'] ? sprintf( '<a href="%s" title="%s">', esc_url( $link ), the_title_attribute( 'echo=0' ) ) : '';
	$wrap_close = 1 === (int) $instance['link_title'] ? '</a>' : '';

	if ( ! empty( $instance['show_title'] ) && ! empty( $instance['title_limit'] ) ) {
		printf( '<h2>%s%s%s%s</h2>', $wrap_open, genesis_truncate_phrase( the_title_attribute( 'echo=0' ), $instance['title_limit'] ), esc_html( $instance['title_cutoff'] ), $wrap_close ); // XSS ok.
	} elseif ( ! empty( $instance['show_title'] ) ) {
		printf( '<h2>%s%s%s</h2>', $wrap_open, the_title_attribute( 'echo=0' ), $wrap_close ); // XSS ok.
	}
}

add_action( 'gfwa_before_post_content', 'gfwa_do_byline', 10, 1 );

/**
 * Outputs byline if option is selects and anything is in the post info field
 *
 * @author Nick Croft
 * @since 0.1
 * @version 0.2
 * @param array $instance Values set in widget $instance.
 */
function gfwa_do_byline( $instance ) {
	if ( ! empty( $instance['show_byline'] ) && ! empty( $instance['post_info'] ) ) {
		printf( '<p class="byline post-info">%s</p>', do_shortcode( wp_kses_post( $instance['post_info'] ) ) );
	}
}

add_action( 'gfwa_post_content', 'gfwa_do_post_content', 10, 1 );

/**
 * Outputs the selected content option if any
 *
 * @author Nick Croft
 * @since 0.1
 * @version 0.2
 * @param array $instance Values set in widget $instance.
 */
function gfwa_do_post_content( $instance ) {
	if ( ! empty( $instance['show_content'] ) ) {
		switch ( $instance['show_content'] ) {
			case 'excerpt':
				the_excerpt();
				break;
			case 'content-limit':
				the_content_limit( (int) $instance['content_limit'], esc_html( $instance['more_text'] ) );
				break;
			default:
				the_content( esc_html( $instance['more_text'] ) );
		}
	}
}

add_action( 'gfwa_after_post_content', 'gfwa_do_post_meta', 10, 1 );

/**
 * Outputs post meta if option is selected and anything is in the post meta field
 *
 * @author Nick Croft
 * @since 0.6
 * @version 0.6
 * @param array $instance Values set in widget $instance.
 */
function gfwa_do_post_meta( $instance ) {
	if ( ! empty( $instance['show_archive_line'] ) && ! empty( $instance['post_meta'] ) ) {
		printf( '<p class="post-meta">%s</p>', do_shortcode( wp_kses_post( $instance['post_meta'] ) ) );
	}
}

/**
 * Returns "display: none;" if option and value match, or if they don't match with $standard set to false.
 *
 * @author Nick Croft
 * @since 0.8
 * @version 0.8
 * @param array   $instance Values set in widget $instance.
 * @param mixed   $option instance option to test.
 * @param mixed   $value value to test against.
 * @param boolean $standard echo standard return false for opposite.
 * @return string
 */
function gfwa_get_display_option( $instance, $option = '', $value = '', $standard = true ) {
	$display = '';
	if ( is_array( $option ) ) {
		foreach ( $option as $key ) {
			if ( in_array( $instance[ $key ], $value, false ) ) {
				$display = 'display: none;';
			}
		}
	} elseif ( is_array( $value ) ) {
		if ( in_array( $instance[ $option ], $value, false ) ) {
			$display = 'display: none;';
		}
	} else {
		if ( isset( $instance[ $option ] ) && $instance[ $option ] == $value ) { // WPCS: loose comparison ok.
			$display = 'display: none;';
		}
	}
	if ( false === (bool) $standard ) {
		if ( 'display: none;' === $display ) {
			$display = '';
		} else {
			$display = 'display: none;';
		}
	}
	return $display;
}

/**
 * Outputs "display: none;" if option and value match, or of they don't match with $standard is set to false
 *
 * @author Nick Croft
 * @since 0.6
 * @version 0.9
 * @param array   $instance Values set in widget $instance.
 * @param mixed   $option instance option to test.
 * @param mixed   $value value to test against.
 * @param boolean $standard echo standard return false for opposite.
 */
function gfwa_display_option( $instance, $option = '', $value = '', $standard = true ) {
	echo gfwa_get_display_option( $instance, $option, $value, $standard ); // XSS ok.
}

add_action( 'admin_print_footer_scripts', 'gfwa_form_submit' );
/**
 * Adds script that makes the widget update when certain options change.
 */
function gfwa_form_submit() {
	?>
	<script type="text/javascript">

		(function(a) {
			a( 'body' ).on( 'change', '.gfwa-widget-control-save', function() {
				wpWidgets.save( a(this).closest('div.widget'), 0, 1, 0 );
				return false;
			} );
		} )( jQuery );

	</script>
	<?php
}

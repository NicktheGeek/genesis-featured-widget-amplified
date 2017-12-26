<?php
/**
 * The GWFA Widget Class for Display.
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
 * Builds the GFWA Widget output.
 */
class Genesis_Featured_Widget_Amplified_Display {

	/**
	 * Creates Widget Output
	 *
	 * @author Nick Croft
	 * @since 0.1
	 * @version 0.5
	 * @param array $args     The widget args.
	 * @param array $instance The widget instance.
	 */
	public function widget( $args, $instance ) {
		global $gfwa_counter;
		$gfwa_counter = 0;

		$before_widget = empty( $args['before_widget'] ) ? '' : $args['before_widget'];
		$before_title  = empty( $args['before_title'] ) ? '' : $args['before_title'];
		$after_widget  = empty( $args['after_widget'] ) ? '' : $args['after_widget'];
		$after_title   = empty( $args['after_title'] ) ? '' : $args['after_title'];

		echo $before_widget; // XSS ok.

		add_filter( 'post_class', 'gfwa_post_class' );

		if ( ! empty( $instance['posts_offset'] ) && ! empty( $instance['paged'] ) ) {
			add_filter( 'post_limits', 'gfwa_post_limit' );
		} else {
			remove_filter( 'post_limits', 'gfwa_post_limit' );
		}

		// Set up the author bio.
		if ( ! empty( $instance['title'] ) ) {
			echo $before_title . apply_filters( 'widget_title', $instance['title'] ) . $after_title; // XSS ok.
		}

		$term_args = array();

		if ( ! empty( $instance['page_id'] ) ) {
			$term_args['page_id'] = $instance['page_id'];
		}

		if ( ! empty( $instance['posts_term'] ) ) {
			$posts_term = explode( ',', $instance['posts_term'] );

			switch ( $posts_term['0'] ) {
				case 'category':
					$posts_term['0'] = 'category_name';
					break;
				case 'post_tag':
					$posts_term['0'] = 'tag';
					break;
			}

			if ( isset( $posts_term['1'] ) ) {
				$term_args[ $posts_term['0'] ] = $posts_term['1'];
			}
		}

		if ( ! empty( $posts_term['0'] ) ) {
			switch ( $posts_term['0'] ) {
				case 'category_name':
					$taxonomy = 'category';
					break;
				case 'tag':
					$taxonomy = 'post_tag';
					break;
				default:
					$taxonomy = $posts_term['0'];
			}
		} else {
			$taxonomy = 'category';
		}

		if ( ! empty( $instance['exclude_terms'] ) ) {
			$exclude_terms                       = explode( ',', str_replace( ' ', '', $instance['exclude_terms'] ) );
			$term_args[ $taxonomy . '__not_in' ] = $exclude_terms;
		}

		$page = '';
		if ( ! empty( $instance['paged'] ) ) {
			$page = get_query_var( 'paged' );
		}

		if ( ! empty( $instance['posts_offset'] ) ) {
			global $gfwa_offset;
			$gfwa_offset         = $instance['posts_offset'];
			$term_args['offset'] = $gfwa_offset;
		}

		if ( ! empty( $instance['post_id'] ) ) {
			$ids = explode( ',', str_replace( ' ', '', $instance['post_id'] ) );
			if ( 'include' === $instance['include_exclude'] ) {
				$term_args['post__in'] = $ids;
			} else {
				$term_args['post__not_in'] = $ids;
			}
		}

		gfwa_before_loop( $instance );

		if ( 0 !== (int) $instance['posts_num'] ) {
			$query_args = array_merge(
				$term_args, array(
					'post_type'      => $instance['post_type'],
					'posts_per_page' => $instance['posts_num'],
					'orderby'        => $instance['orderby'],
					'order'          => $instance['order'],
					'meta_key'       => $instance['meta_key'],
					'paged'          => $page,
				)
			);
			$query_args = apply_filters( 'gfwa_query_args', $query_args, $instance );

			$gfwa_posts = new WP_Query( $query_args );
			if ( $gfwa_posts->have_posts() ) {
				while ( $gfwa_posts->have_posts() ) {
					$gfwa_posts->the_post();

					echo '<div ';
					post_class();
					echo '>';

					gfwa_before_post_content( $instance );

					gfwa_post_content( $instance );

					gfwa_after_post_content( $instance );

					echo '</div><!--end post_class()-->' . "\n\n";

					$gfwa_counter ++;
				}

				if ( ! empty( $instance['show_paged'] ) ) {
					genesis_posts_nav();
				}

				gfwa_endwhile( $instance );
			}

			$gfwa_counter = '';

			gfwa_after_loop( $instance );
		}
		// The EXTRA Posts (list).
		if ( $instance['extra_posts'] && $instance['extra_num'] ) {
			if ( ! empty( $instance['extra_title'] ) ) {
				echo str_replace( '>', ' class="additional-posts-title">', $before_title ) . esc_html( $instance['extra_title'] ) . $after_title; // XSS ok.
			}

			$offset           = (int) $instance['posts_num'] + (int) $instance['posts_offset'];
			$extra_posts_args = array_merge(
				$term_args, array(
					'showposts' => $instance['extra_num'],
					'offset'    => $offset,
					'post_type' => $instance['post_type'],
					'orderby'   => $instance['orderby'],
					'order'     => $instance['order'],
					'meta_key'  => $instance['meta_key'],
					'paged'     => $page,
				)
			);
			$extra_posts_args = apply_filters( 'gfwa_extra_post_args', $extra_posts_args, $instance );

			$gfwa_extra_posts = new WP_Query( $extra_posts_args );

			$listitems = '';

			if ( $gfwa_extra_posts->have_posts() ) {
				while ( $gfwa_extra_posts->have_posts() ) {
					$gfwa_extra_posts->the_post();

					gfwa_list_items( $instance );
					if ( 'drop_down' === $instance['extra_format'] ) {
						$listitems .= sprintf( '<option onclick="window.location=\'%s\';" value="%s">%s</option>', get_permalink(), get_permalink(), get_the_title() );
					} else {
						$listitems .= sprintf( '<li><a href="%s" title="%s">%s</a></li>', get_permalink(), the_title_attribute( 'echo=0' ), get_the_title() );
					}
				}

				if ( strlen( $listitems ) > 0 ) {
					if ( 'drop_down' === $instance['extra_format'] ) {
						printf( '<select id="%s"><option value="none">%s %s</option>%s</select>', $this->get_field_id( 'extra_format' ), esc_html__( 'Select', 'gfwa' ), esc_attr( $instance['post_type'] ), $listitems ); // XSS ok.
					} else {
						printf( '<%s>%s</%s>', esc_attr( $instance['extra_format'] ), $listitems, esc_attr( $instance['extra_format'] ) ); // XSS ok.
					}
				}

				gfwa_print_list_items( $instance );
			}
		}

		if ( ! empty( $instance['archive_link'] ) ) {
			echo '<p class="more-from-category"><a href="' . esc_url( $instance['archive_link'] ) . '" title="' . esc_html( $instance['more_from_category_text'] ) . '">' . esc_html( $instance['more_from_category_text'] ) . '</a></p>';
		} elseif ( ! empty( $instance['more_from_category'] ) && ! empty( $posts_term['1'] ) ) {
			gfwa_category_more( $instance );
			$term = get_term_by( 'slug', $posts_term['1'], $taxonomy );
			echo '<p class="more-from-category"><a href="' . esc_url( get_term_link( $posts_term['1'], $taxonomy ) ) . '" title="' . esc_attr( $term->name ) . '">' . esc_html( $instance['more_from_category_text'] ) . '</a></p>';
		}

		gfwa_after_category_more( $instance );

		echo $after_widget; // XSS ok.

		wp_reset_postdata();
		remove_filter( 'post_class', 'gfwa_post_class' );
		remove_filter( 'post_limits', 'gfwa_post_limit' );
	}

}

<?php
/**
 * The GWFA Widget Class.
 *
 * @package gfwa
 */

/**
 * To Do:
 *      Add support for Grid Loop (0.9)
 *      Make content float options with 2, 3, or 4 side by side clearing after the row (v0.9)
 *      Create custom class from the widget custom field (v0.9)
 *      Add support for post_status (v0.9)
 *      Add support for Post Formats (v0.9)
 *      Create Simple Hooks interface (1.0)
 *      Edit html to allow external style sheet instead of inline styles
 *      Add support for child pages (selected or default to current page)
 *      Add option for showing image via custom field
 *      Add support for sticky posts
 *      Add support for post_status
 *      Add support for Post Formats
 *      Create external stylesheet for widget
 *      Create new widget for creating category thumbnails.
 */

/**
 * Prevent direct access to the plugin
 */
if ( ! defined( 'ABSPATH' ) ) {
	wp_die( esc_html__( 'Sorry, you are not allowed to access this page directly.', 'gfwa' ) );
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

/**
 * Builds the GFWA Widget admin and output.
 */
class Genesis_Featured_Widget_Amplified extends WP_Widget {

	/**
	 * Holds widget settings defaults, populated in constructor.
	 *
	 * @var array
	 */
	protected $defaults;

	/**
	 * Constructor. Set the default widget options and create widget.
	 *
	 * @since 0.1.8
	 */
	public function __construct() {
		$this->defaults = array(
			'count'                   => 0,
			'title'                   => '',
			'post_type'               => 'post',
			'page_id'                 => '',
			'posts_term'              => '',
			'exclude_terms'           => '',
			'exclude_cat'             => '',
			'include_exclude'         => '',
			'post_id'                 => '',
			'posts_num'               => 1,
			'posts_offset'            => 0,
			'orderby'                 => '',
			'order'                   => '',
			'meta_key'                => '',
			'show_sticky'             => '',
			'paged'                   => '',
			'show_paged'              => '',
			'post_align'              => '',
			'show_image'              => 0,
			'link_image'              => 1,
			'image_position'          => 'before-title',
			'image_alignment'         => '',
			'image_size'              => '',
			'link_image_field'        => '',
			'show_gravatar'           => 0,
			'gravatar_alignment'      => '',
			'gravatar_size'           => '',
			'link_gravatar'           => 0,
			'show_title'              => 0,
			'link_title'              => 1,
			'link_title_field'        => '',
			'title_limit'             => '',
			'title_cutoff'            => '&hellip;',
			'show_byline'             => 0,
			'post_info'               => '[post_date] ' . __( 'By', 'gfwa' ) . ' [post_author_posts_link] [post_comments]',
			'show_content'            => 'excerpt',
			'show_archive_line'       => 0,
			'archive_link'            => '',
			'post_meta'               => '[post_categories] [post_tags]',
			'content_limit'           => '',
			'more_text'               => __( '[Read More...]', 'gfwa' ),
			'extra_posts'             => '',
			'extra_num'               => '',
			'extra_title'             => '',
			'extra_format'            => 'ul',
			'more_from_category'      => '',
			'more_from_category_text' => __( 'More Posts from this Taxonomy', 'gfwa' ),
			'custom_field'            => '',
		);

		$widget_ops = array(
			'classname'   => 'featured-content featuredpost',
			'description' => __( 'Displays featured posts types with thumbnails', 'gfwa' ),
		);

		$control_ops = array(
			'id_base' => 'featured-post',
			'width'   => 505,
			'height'  => 350,
		);

		parent::__construct( 'featured-post', __( 'Genesis - Featured Widget Amplified', 'gfwa' ), $widget_ops, $control_ops );
	}


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

		// Merge with defaults.
		$instance = wp_parse_args( (array) $instance, $this->defaults );

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

	/**
	 * Updates Widget Instance
	 *
	 * @author Nick Croft
	 * @since 0.1
	 * @version 0.2
	 * @param array $new_instance The new instance.
	 * @param array $old_instance The old instance.
	 * @return array
	 */
	public function update( $new_instance, $old_instance ) {
		return $new_instance;
	}

	/**
	 * Creates Widget Form
	 *
	 * @author Nick Croft
	 * @since 0.1
	 * @version 0.5
	 * @param array $instance Values set in widget instance.
	 * @return void
	 */
	public function form( $instance ) {

		/** Merge with defaults */
		$instance = wp_parse_args( (array) $instance, $this->defaults );

		$sizes = wp_get_additional_image_sizes();

		$image_size_opt['thumbnail'] = 'thumbnail (' . get_option( 'thumbnail_size_w' ) . 'x' . get_option( 'thumbnail_size_h' ) . ')';

		foreach ( (array) $sizes as $name => $size ) {
			$image_size_opt[ $name ] = esc_html( $name ) . ' (' . $size['width'] . 'x' . $size['height'] . ')';
		}

		$columns = array(
			'col1' => array(
				array(
					'post_type'       => array(
						'label'       => __( 'Post Type', 'gfwa' ),
						'description' => '',
						'type'        => 'post_type_select',
						'save'        => true,
						'requires'    => '',
					),
					'page_id'         => array(
						'label'       => __( 'Page', 'gfwa' ),
						'description' => '',
						'type'        => 'page_select',
						'save'        => true,
						'requires'    => array(
							'post_type',
							'page',
							false,
						),
					),
					'posts_term'      => array(
						'label'       => __( 'Taxonomy and Terms', 'gfwa' ),
						'description' => '',
						'type'        => 'select_taxonomy',
						'save'        => false,
						'requires'    => array(
							'post_type',
							'page',
							true,
						),
					),
					'exclude_terms'   => array(
						// Translators: The placeholder is for an HTML break to force a new line.
						'label'       => sprintf( __( 'Exclude Terms by ID %s (comma separated list)', 'gfwa' ), '<br />' ),
						'description' => '',
						'type'        => 'text',
						'save'        => false,
						'requires'    => array(
							'post_type',
							'page',
							true,
						),
					),
					'include_exclude' => array(
						'label'       => '',
						'description' => '',
						'type'        => 'select',
						'options'     => array(
							''        => __( 'Select', 'gfwa' ),
							'include' => __( 'Include', 'gfwa' ),
							'exclude' => __( 'Exclude', 'gfwa' ),
						),
						'save'        => true,
						'requires'    => array(
							'page_id',
							'',
							false,
						),
					),
					'post_id'         => array(
						'label'       => $instance['post_type'] . ' ' . __( 'ID', 'gfwa' ),
						'description' => '',
						'type'        => 'text',
						'save'        => false,
						'requires'    => array(
							'include_exclude',
							'',
							true,
						),
					),
					'posts_num'       => array(
						'label'       => __( 'Number of Posts to Show', 'gfwa' ),
						'description' => '',
						'type'        => 'text_small',
						'save'        => false,
						'requires'    => array(
							'page_id',
							'',
							false,
						),
					),
					'posts_offset'    => array(
						'label'       => __( 'Number of Posts to Offset', 'gfwa' ),
						'description' => '',
						'type'        => 'text_small',
						'save'        => false,
						'requires'    => array(
							'page_id',
							'',
							false,
						),
					),
					'orderby'         => array(
						'label'       => __( 'Order By', 'gfwa' ),
						'description' => '',
						'type'        => 'select',
						'options'     => array(
							'date'           => __( 'Date', 'gfwa' ),
							'title'          => __( 'Title', 'gfwa' ),
							'parent'         => __( 'Parent', 'gfwa' ),
							'ID'             => __( 'ID', 'gfwa' ),
							'comment_count'  => __( 'Comment Count', 'gfwa' ),
							'rand'           => __( 'Random', 'gfwa' ),
							'meta_value'     => __( 'Meta Value', 'gfwa' ),
							'meta_value_num' => __( 'Numeric Meta Value', 'gfwa' ),
						),
						'save'        => false,
						'requires'    => array(
							'page_id',
							'',
							false,
						),
					),
					'order'           => array(
						'label'       => __( 'Sort Order', 'gfwa' ),
						'description' => '',
						'type'        => 'select',
						'options'     => array(
							'DESC' => __( 'Descending (3, 2, 1)', 'gfwa' ),
							'ASC'  => __( 'Ascending (1, 2, 3)', 'gfwa' ),
						),
						'save'        => false,
						'requires'    => array(
							'page_id',
							'',
							false,
						),
					),
					'meta_key'        => array(
						'label'       => __( 'Meta Key', 'gfwa' ),
						'description' => '',
						'type'        => 'text',
						'save'        => false,
						'requires'    => array(
							'page_id',
							'',
							false,
						),
					),
					'paged'           => array(
						'label'       => __( 'Work with Pagination', 'gfwa' ),
						'description' => '',
						'type'        => 'checkbox',
						'save'        => false,
						'requires'    => array(
							'post_type',
							'page',
							true,
						),
					),
					'show_paged'      => array(
						'label'       => __( 'Show Page Navigation', 'gfwa' ),
						'description' => '',
						'type'        => 'checkbox',
						'save'        => false,
						'requires'    => array(
							'post_type',
							'page',
							true,
						),
					),
				),
				array(
					'show_gravatar'      => array(
						'label'       => __( 'Show Author Gravatar', 'gfwa' ),
						'description' => '',
						'type'        => 'checkbox',
						'save'        => true,
						'requires'    => '',
					),
					'gravatar_size'      => array(
						'label'       => __( 'Gravatar Size', 'gfwa' ),
						'description' => '',
						'type'        => 'select',
						'options'     => array(
							'45'  => __( 'Small (45px)', 'gfwa' ),
							'65'  => __( 'Medium (65px)', 'gfwa' ),
							'85'  => __( 'Large (85px)', 'gfwa' ),
							'125' => __( 'Extra Large (125px)', 'gfwa' ),
						),
						'save'        => false,
						'requires'    => array(
							'show_gravatar',
							'',
							true,
						),
					),
					'link_gravatar'      => array(
						'label'       => '',
						'description' => '',
						'type'        => 'select',
						'options'     => array(
							''        => __( 'Do not link gravatar', 'gfwa' ),
							'archive' => __( 'Link to author archive', 'gfwa' ),
							'website' => __( 'Link to author website', 'gfwa' ),
						),
						'save'        => false,
						'requires'    => array(
							'show_gravatar',
							'',
							true,
						),
					),
					'gravatar_alignment' => array(
						'label'       => __( 'Gravatar Alignment', 'gfwa' ),
						'description' => '',
						'type'        => 'select',
						'options'     => array(
							''           => __( 'None', 'gfwa' ),
							'alignleft'  => __( 'Left', 'gfwa' ),
							'alignright' => __( 'Right', 'gfwa' ),
						),
						'save'        => false,
						'requires'    => array(
							'show_gravatar',
							'',
							true,
						),
					),
				),
			),
			'col2' => array(
				array(
					'show_image'       => array(
						'label'       => __( 'Show Featured Image', 'gfwa' ),
						'description' => '',
						'type'        => 'checkbox',
						'save'        => true,
						'requires'    => '',
					),
					'link_image'       => array(
						'label'       => '',
						'description' => '',
						'type'        => 'select',
						'options'     => array(
							'1' => __( 'Link Image to Post', 'gfwa' ),
							'2' => __( 'Don\'t Link Image', 'gfwa' ),
						),
						'save'        => true,
						'requires'    => array(
							'show_image',
							'',
							true,
						),
					),
					'link_image_field' => array(
						'label'       => __( 'Custom Field for Link ( Defaults to Permalink )', 'gfwa' ),
						'description' => '',
						'type'        => 'text',
						'save'        => false,
						'requires'    => array(
							'link_image',
							'1',
							false,
						),
					),
					'image_size'       => array(
						'label'       => '',
						'description' => '',
						'type'        => 'select',
						'options'     => $image_size_opt,
						'save'        => false,
						'requires'    => array(
							'show_image',
							'',
							true,
						),
					),
					'image_position'   => array(
						'label'       => __( 'Image Placement', 'gfwa' ),
						'description' => '',
						'type'        => 'select',
						'options'     => array(
							'before-title'  => __( 'Before Title', 'gfwa' ),
							'after-title'   => __( 'After Title', 'gfwa' ),
							'after-content' => __( 'After Content', 'gfwa' ),
						),
						'save'        => false,
						'requires'    => array(
							'show_image',
							'',
							true,
						),
					),
					'image_alignment'  => array(
						'label'       => '',
						'description' => '',
						'type'        => 'select',
						'options'     => array(
							''            => __( 'None', 'gfwa' ),
							'alignleft'   => __( 'Left', 'gfwa' ),
							'alignright'  => __( 'Right', 'gfwa' ),
							'aligncenter' => __( 'Center', 'gfwa' ),
						),
						'save'        => false,
						'requires'    => array(
							'show_image',
							'',
							true,
						),
					),
				),
				array(
					'show_title'        => array(
						'label'       => __( 'Show Post Title', 'gfwa' ),
						'description' => '',
						'type'        => 'checkbox',
						'save'        => true,
						'requires'    => '',
					),
					'title_limit'       => array(
						'label'       => __( 'Limit title to', 'gfwa' ),
						'description' => __( 'characters', 'gfwa' ),
						'type'        => 'text_small',
						'save'        => false,
						'requires'    => array(
							'show_title',
							'',
							true,
						),
					),
					'title_cutoff'      => array(
						'label'       => __( 'Title Cutoff Symbol', 'gfwa' ),
						'description' => '',
						'type'        => 'text_small',
						'save'        => false,
						'requires'    => array(
							'show_title',
							'',
							true,
						),
					),
					'link_title'        => array(
						'label'       => '',
						'description' => '',
						'type'        => 'select',
						'options'     => array(
							'1' => __( 'Link Title to Post', 'gfwa' ),
							'2' => __( 'Don\'t Link Title', 'gfwa' ),
						),
						'save'        => true,
						'requires'    => array(
							'show_title',
							'',
							true,
						),
					),
					'link_title_field'  => array(
						'label'       => __( 'Custom Field for Link ( Defaults to Permalink )', 'gfwa' ),
						'description' => '',
						'type'        => 'text',
						'save'        => false,
						'requires'    => array(
							'link_title',
							'1',
							false,
						),
					),
					'show_byline'       => array(
						'label'       => __( 'Show Post Info', 'gfwa' ),
						'description' => '',
						'type'        => 'checkbox',
						'save'        => true,
						'requires'    => '',
					),
					'post_info'         => array(
						'label'       => '',
						'description' => '',
						'type'        => 'text',
						'save'        => false,
						'requires'    => array(
							'show_byline',
							'',
							true,
						),
					),
					'show_content'      => array(
						'label'       => __( 'Content Type', 'gfwa' ),
						'description' => '',
						'type'        => 'select',
						'options'     => array(
							'content'       => __( 'Show Content', 'gfwa' ),
							'excerpt'       => __( 'Show Excerpt', 'gfwa' ),
							'content-limit' => __( 'Show Content Limit', 'gfwa' ),
							''              => __( 'No Content', 'gfwa' ),
						),
						'save'        => true,
						'requires'    => '',
					),
					'content_limit'     => array(
						'label'       => __( 'Limit content to', 'gfwa' ),
						'description' => __( 'characters', 'gfwa' ),
						'type'        => 'text_small',
						'save'        => false,
						'requires'    => array(
							'show_content',
							'content-limit',
							false,
						),
					),
					'show_archive_line' => array(
						'label'       => __( 'Show Post Meta', 'gfwa' ),
						'description' => '',
						'type'        => 'checkbox',
						'save'        => true,
						'requires'    => array(
							'post_type',
							'page',
							true,
						),
					),

					'post_meta'         => array(
						'label'       => '',
						'description' => '',
						'type'        => 'text',
						'save'        => false,
						'requires'    => array(
							'show_archive_line',
							'',
							true,
						),
					),
					'more_text'         => array(
						'label'       => __( 'More Text (if applicable)', 'gfwa' ),
						'description' => '',
						'type'        => 'text',
						'save'        => false,
						'requires'    => '',
					),
				),
				array(
					'extra_posts'  => array(
						'label'       => __( 'Display List of Additional Posts', 'gfwa' ),
						'description' => '',
						'type'        => 'checkbox',
						'save'        => true,
						'requires'    => array(
							'post_type',
							'page',
							true,
						),
					),
					'extra_title'  => array(
						'label'       => __( 'Title', 'gfwa' ),
						'description' => '',
						'type'        => 'text',
						'save'        => false,
						'requires'    => array(
							'extra_posts',
							'',
							true,
						),
					),
					'extra_num'    => array(
						'label'       => __( 'Number of Posts to Show', 'gfwa' ),
						'description' => '',
						'type'        => 'text_small',
						'save'        => false,
						'requires'    => array(
							'extra_posts',
							'',
							true,
						),
					),
					'extra_format' => array(
						'label'       => __( 'Extra Post Format', 'gfwa' ),
						'description' => '',
						'type'        => 'select',
						'options'     => array(
							'ul'        => __( 'Unordered List', 'gfwa' ),
							'ol'        => __( 'Ordered List', 'gfwa' ),
							'drop_down' => __( 'Drop Down', 'gfwa' ),
						),
						'save'        => false,
						'requires'    => array(
							'extra_posts',
							'',
							true,
						),
					),
				),
				array(
					'more_from_category'      => array(
						'label'       => __( 'Show Category Archive Link', 'gfwa' ),
						'description' => '',
						'type'        => 'checkbox',
						'save'        => true,
						'requires'    => array(
							'post_type',
							'page',
							true,
						),
					),
					'more_from_category_text' => array(
						'label'       => __( 'Link Text', 'gfwa' ),
						'description' => '',
						'type'        => 'text',
						'save'        => false,
						'requires'    => array(
							'more_from_category',
							'',
							true,
						),
					),
					'archive_link'            => array(
						'label'       => __( 'Fill in this value with a URL if you wish to display an archive link when showing all terms or to override the normal archive link to another URL', 'gfwa' ),
						'description' => '',
						'type'        => 'text',
						'save'        => false,
						'requires'    => array(
							'more_from_category',
							'',
							true,
						),
					),
				),
				array(
					'custom_field' => array(
						'label'       => __( 'Instance Identification Field', 'gfwa' ),
						'description' => __( 'Fill in this field if you need to test against an $instance value not included in the form', 'gfwa' ),
						'type'        => 'text',
						'save'        => false,
						'requires'    => '',
					),
				),
			),
		);

		echo '<p><label for="' . esc_attr( $this->get_field_id( 'title' ) ) . '">' . esc_html__( 'Title', 'gfwa' ) . ':</label>
            <input type="text" id="' . esc_attr( $this->get_field_id( 'title' ) ) . '" name="' . esc_attr( $this->get_field_name( 'title' ) ) . '" value="' . esc_attr( $instance['title'] ) . '" style="width:99%;" /></p>';

		foreach ( $columns as $column => $boxes ) {
			if ( 'col1' === $column ) {
				echo '<div style="float: left; width: 250px;">';
			} else {
				echo '<div style="float: right; width: 250px;">';
			}

			foreach ( $boxes as $box ) {
				echo '<div style="background: #f1f1f1; border: 1px solid #DDD; padding: 10px 10px 0 10px; margin-bottom: 5px;">';

				foreach ( $box as $field_id => $args ) {
					$class = $args['save'] ? 'gfwa-widget-control-save' : '';
					$style = $args['requires'] ? ' style="' . gfwa_get_display_option( $instance, $args['requires'][0], $args['requires'][1], $args['requires'][2] ) . '"' : '';

					switch ( $args['type'] ) {
						case 'post_type_select':
							echo '<p><label for="' . esc_attr( $this->get_field_id( $field_id ) ) . '">' . str_replace( esc_html( '<br />' ), '<br />', esc_html( $args['label'] ) ) . ':</label>
								<select class="' . esc_html( $class ) . '" id="' . esc_attr( $this->get_field_id( $field_id ) ) . '" name="' . esc_attr( $this->get_field_name( $field_id ) ) . '">'; // WPCS: XSS ok.

							$args       = array(
								'public' => true,
							);
							$output     = 'names';
							$operator   = 'and';
							$post_types = get_post_types( $args, $output, $operator );
							$post_types = array_filter( $post_types, 'gfwa_exclude_post_types' );

							foreach ( $post_types as $post_type ) {
								echo '<option style="padding-right:10px;" value="' . esc_attr( $post_type ) . '" ' . selected( esc_attr( $post_type ), $instance['post_type'], false ) . '>' . esc_attr( $post_type ) . '</option>';
							}

							echo '<option style="padding-right:10px;" value="any" ' . selected( 'any', $instance['post_type'], false ) . '>' . esc_html__( 'any', 'gfwa' ) . '</option>';

							echo '</select></p>';

							break;

						case 'page_select':
							echo '<p' . $style . '><label for="' . esc_attr( $this->get_field_id( $field_id ) ) . '">' . str_replace( esc_html( '<br />' ), '<br />', esc_html( $args['label'] ) ) . ':</label>
								<select class="' . esc_html( $class ) . '" id="' . esc_attr( $this->get_field_id( $field_id ) ) . '" name="' . esc_attr( $this->get_field_name( $field_id ) ) . '">
									<option value="" ' . selected( '', $instance['page_id'], false ) . '>' . esc_html__( 'Select page', 'gfwa' ) . '</option>'; // XSS ok.

							$pages = get_pages();
							foreach ( $pages as $page ) {
								echo '<option style="padding-right:10px;" value="' . esc_attr( $page->ID ) . '" ' . selected( esc_attr( $page->ID ), $instance['page_id'], false ) . '>' . esc_html( $page->post_title ) . '</option>';
							}

							echo '</select>
							</p>';

							break;

						case 'select_taxonomy':
							echo '<p' . $style . '><label for="' . esc_attr( $this->get_field_id( $field_id ) ) . '">' . str_replace( esc_html( '<br />' ), '<br />', esc_html( $args['label'] ) ) . ':</label>

								<select id="' . esc_attr( $this->get_field_id( $field_id ) ) . '" name="' . esc_attr( $this->get_field_name( $field_id ) ) . '">
									<option style="padding-right:10px;" value="" ' . selected( '', $instance['posts_term'], false ) . '>' . esc_html__( 'All Taxonomies and Terms', 'gfwa' ) . '</option>'; // XSS ok.

							$taxonomies = get_taxonomies( array( 'public' => true ), 'objects' );

							$taxonomies = array_filter( $taxonomies, 'gfwa_exclude_taxonomies' );

							foreach ( $taxonomies as $taxonomy ) {
								if ( ! empty( $taxonomy->query_var ) ) {
									$query_label = $taxonomy->query_var;
								} else {
									$query_label = $taxonomy->name;
								}

								echo '<optgroup label="' . esc_attr( $taxonomy->labels->name ) . '">
											<option style="margin-left: 5px; padding-right:10px;" value="' . esc_attr( $query_label ) . '" ' . selected( esc_attr( $query_label ), $instance['posts_term'], false ) . '>' . esc_html( $taxonomy->labels->all_items ) . '</option>';

								$terms = get_terms( $taxonomy->name, 'orderby=name&hide_empty=1' );

								foreach ( $terms as $term ) {
									echo '<option style="margin-left: 8px; padding-right:10px;" value="' . esc_attr( $query_label . ',' . $term->slug ) . '" ' . selected( esc_attr( $query_label ) . ',' . $term->slug, $instance['posts_term'], false ) . '>-' . esc_html( $term->name ) . '</option>';
								}

								echo '</optgroup>';
							}

							echo '</select></p>';

							break;

						case 'text':
							echo $args['description'] ? '<p>' . esc_html( $args['description'] ) . '</p>' : '';

							echo '<p' . $style . '><label for="' . esc_attr( $this->get_field_id( $field_id ) ) . '">' . str_replace( esc_html( '<br />' ), '<br />', esc_html( $args['label'] ) ) . ':</label> 
									<input type="text" id="' . esc_attr( $this->get_field_id( $field_id ) ) . '" name="' . esc_attr( $this->get_field_name( $field_id ) ) . '" value="' . esc_attr( $instance[ $field_id ] ) . '" style="width:95%;" /></p>'; // XSS ok.

							break;

						case 'text_small':
							echo '<p' . $style . '><label for="' . esc_attr( $this->get_field_id( $field_id ) ) . '">' . str_replace( esc_html( '<br />' ), '<br />', esc_html( $args['label'] ) ) . ':</label>
									<input type="text" id="' . esc_attr( $this->get_field_id( $field_id ) ) . '" name="' . esc_attr( $this->get_field_name( $field_id ) ) . '" value="' . esc_attr( $instance[ $field_id ] ) . '" size="2" />' . esc_html( $args['description'] ) . '</p>'; // XSS ok.

							break;

						case 'select':
							echo '<p' . $style . '><label for="' . esc_attr( $this->get_field_id( $field_id ) ) . '">' . str_replace( esc_html( '<br />' ), '<br />', esc_html( $args['label'] ) ) . ' </label>
								<select class="' . esc_html( $class ) . '" id="' . esc_attr( $this->get_field_id( $field_id ) ) . '" name="' . esc_attr( $this->get_field_name( $field_id ) ) . '">'; // XSS ok.

							foreach ( $args['options'] as $value => $label ) {
								echo '<option style="padding-right:10px;" value="' . esc_attr( $value ) . '" ' . selected( $value, $instance[ $field_id ], false ) . '>' . esc_html( $label ) . '</option>';
							}

							echo '</select></p>';

							break;

						case 'checkbox':
							echo '<p' . $style . '><input class="' . esc_html( $class ) . '" id="' . esc_attr( $this->get_field_id( $field_id ) ) . '" type="checkbox" name="' . esc_attr( $this->get_field_name( $field_id ) ) . '" value="1" ' . checked( 1, $instance[ $field_id ], false ) . '/> <label for="' . esc_attr( $this->get_field_id( $field_id ) ) . '">' . str_replace( esc_html( '<br />' ), '<br />', esc_html( $args['label'] ) ) . '</label></p>'; // XSS ok.

							break;
					}
				}

				echo '</div>';
			}

			echo '</div>';
		}
	}

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

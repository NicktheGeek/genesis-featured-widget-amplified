<?php
/**
 * The GWFA Widget Class.
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
	 * Instatiates the Genesis_Featured_Widget_Amplified_Display object and generates the widget.
	 *
	 * @author Nick Croft
	 * @since 0.1
	 * @version 0.5
	 * @param array $args     The widget args.
	 * @param array $instance The widget instance.
	 */
	public function widget( $args, $instance ) {

		// Merge with defaults.
		$instance = wp_parse_args( (array) $instance, $this->defaults );

		$output = new Genesis_Featured_Widget_Amplified_Display();

		$output->widget( $args, $instance );

		unset( $output );
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
	 * Instatiates the Genesis_Featured_Widget_Amplified_Display object and generates the widget settings.
	 *
	 * @author Nick Croft
	 * @since 0.1
	 * @version 0.5
	 * @param array $instance Values set in widget instance.
	 * @return void
	 */
	public function form( $instance ) {
		$admin = new Genesis_Featured_Widget_Amplified_Admin( $instance, $this->defaults, $this );
		$admin->form();

		unset( $admin );
	}

}

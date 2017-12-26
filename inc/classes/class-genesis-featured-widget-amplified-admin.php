<?php
/**
 * The GWFA Widget Class for Admin.
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
 * Builds the GFWA Widget admin.
 */
class Genesis_Featured_Widget_Amplified_Admin {

	/**
	 * The instance values for this widget.
	 *
	 * @var array|string
	 */
	public $instance = array();

	/**
	 * The current WP_Widget object.
	 *
	 * @var WP_Widget
	 */
	public $widget;

	/**
	 * Sets the instance property and ensure widget isn't registered twice..
	 *
	 * @param array     $instance Values set in widget instance.
	 * @param string    $defaults The widget defaults.
	 * @param WP_Widget $widget   The current WP_Widget object.
	 */
	public function __construct( $instance, $defaults, $widget ) {
		$this->instance = wp_parse_args( (array) $instance, $defaults );
		$this->widget   = $widget;
	}

	/**
	 * Gets the columns array.
	 *
	 * @return array
	 */
	public function get_columns() {
		$sizes = wp_get_additional_image_sizes();

		$image_size_opt['thumbnail'] = 'thumbnail (' . get_option( 'thumbnail_size_w' ) . 'x' . get_option( 'thumbnail_size_h' ) . ')';

		foreach ( (array) $sizes as $name => $size ) {
			$image_size_opt[ $name ] = esc_html( $name ) . ' (' . $size['width'] . 'x' . $size['height'] . ')';
		}

		return array(
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
						'label'       => $this->instance['post_type'] . ' ' . __( 'ID', 'gfwa' ),
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
	}
	/**
	 * Creates Widget Form
	 *
	 * @author Nick Croft
	 * @since 0.1
	 * @version 1.0.0
	 * @return void
	 */
	public function form() {
		$fields = new Genesis_Featured_Widget_Amplified_Fields( $this->instance, $this->widget );

		echo '<p><label for="' . esc_attr( $this->widget->get_field_id( 'title' ) ) . '">' . esc_html__( 'Title', 'gfwa' ) . ':</label>
            <input type="text" id="' . esc_attr( $this->widget->get_field_id( 'title' ) ) . '" name="' . esc_attr( $this->widget->get_field_name( 'title' ) ) . '" value="' . esc_attr( $this->instance['title'] ) . '" style="width:99%;" /></p>';

		foreach ( $this->get_columns() as $column => $boxes ) {
			if ( 'col1' === $column ) {
				echo '<div style="float: left; width: 250px;">';
			} else {
				echo '<div style="float: right; width: 250px;">';
			}

			foreach ( $boxes as $box ) {
				echo '<div style="background: #f1f1f1; border: 1px solid #DDD; padding: 10px 10px 0 10px; margin-bottom: 5px;">';

				foreach ( $box as $field_id => $args ) {
					$fields->do_field( $field_id, $args );
				}

				echo '</div>';
			}

			echo '</div>';
		}
	}

}

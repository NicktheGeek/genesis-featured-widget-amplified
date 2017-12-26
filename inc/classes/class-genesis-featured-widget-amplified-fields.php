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
class Genesis_Featured_Widget_Amplified_Fields {

	/**
	 * The instance values for this widget.
	 *
	 * @var array|string
	 */
	public $instance = array();

	/**
	 * The ID for the current field.
	 *
	 * @var int
	 */
	public $field_id = 0;

	/**
	 * The args for the current field.
	 *
	 * @var array
	 */
	public $args = array();

	/**
	 * The current WP_Widget object.
	 *
	 * @var WP_Widget
	 */
	public $widget;

	/**
	 * The class for the current field
	 *
	 * @var string
	 */
	public $class = '';

	/**
	 * The style for the current field.
	 *
	 * @var string
	 */
	public $style = '';

	/**
	 * Sets the instance property and ensure widget isn't registered twice..
	 *
	 * @param array     $instance Values set in widget instance.
	 * @param WP_Widget $widget   The current widget object.
	 */
	public function __construct( $instance, $widget ) {
		$this->instance = $instance;
		$this->widget   = $widget;
	}

	/**
	 * Creates Widget Form
	 *
	 * @author Nick Croft
	 * @since 1.0.0
	 * @version 1.0.0
	 *
	 * @param string $field_id The field ID.
	 * @param array  $args     The field args.
	 * @return void
	 */
	public function do_field( $field_id, $args ) {
		$this->field_id = $field_id;
		$this->args     = $args;

		$this->class = $this->args['save'] ? 'gfwa-widget-control-save' : '';
		$this->style = $this->args['requires'] ? ' style="' . gfwa_get_display_option( $this->instance, $this->args['requires'][0], $this->args['requires'][1], $this->args['requires'][2] ) . '"' : '';

		$method = $args['type'];

		if ( method_exists( $this, $method ) ) {
			echo '<p ' . $this->style . '><label for="' . esc_attr( $this->widget->get_field_id( $this->field_id ) ) . '">' . str_replace( esc_html( '<br />' ), '<br />', esc_html( $this->args['label'] ) ) . '</label>'; // WPCS: XSS ok.
			$this->$method();
			echo '</p>';
		}
	}

	/**
	 * Outputs a select field.
	 *
	 * @author Nick Croft
	 * @since 1.0.0
	 * @version 1.0.0
	 *
	 * @return void
	 */
	public function select() {
		echo '<select class="' . esc_html( $this->class ) . '" id="' . esc_attr( $this->widget->get_field_id( $this->field_id ) ) . '" name="' . esc_attr( $this->widget->get_field_name( $this->field_id ) ) . '">';

		foreach ( $this->args['options'] as $value => $label ) {
			echo '<option style="padding-right:10px;" value="' . esc_attr( $value ) . '" ' . selected( $value, $this->instance[ $this->field_id ], false ) . '>' . esc_html( $label ) . '</option>';
		}

		echo '</select>';
	}

	/**
	 * Outputs a select field for post types.
	 *
	 * @author Nick Croft
	 * @since 1.0.0
	 * @version 1.0.0
	 *
	 * @return void
	 */
	public function post_type_select() {
		echo '<select class="' . esc_html( $this->class ) . '" id="' . esc_attr( $this->widget->get_field_id( $this->field_id ) ) . '" name="' . esc_attr( $this->widget->get_field_name( $this->field_id ) ) . '">';

		$this->args = array(
			'public' => true,
		);
		$output     = 'names';
		$operator   = 'and';
		$post_types = get_post_types( $this->args, $output, $operator );
		$post_types = array_filter( $post_types, 'gfwa_exclude_post_types' );

		foreach ( $post_types as $post_type ) {
			echo '<option style="padding-right:10px;" value="' . esc_attr( $post_type ) . '" ' . selected( esc_attr( $post_type ), $this->instance['post_type'], false ) . '>' . esc_attr( $post_type ) . '</option>';
		}

		echo '<option style="padding-right:10px;" value="any" ' . selected( 'any', $this->instance['post_type'], false ) . '>' . esc_html__( 'any', 'gfwa' ) . '</option>';

		echo '</select>';
	}

	/**
	 * Outputs a select field for pages.
	 *
	 * @author Nick Croft
	 * @since 1.0.0
	 * @version 1.0.0
	 *
	 * @return void
	 */
	public function page_select() {
		echo '<select class="' . esc_html( $this->class ) . '" id="' . esc_attr( $this->widget->get_field_id( $this->field_id ) ) . '" name="' . esc_attr( $this->widget->get_field_name( $this->field_id ) ) . '">
						<option value="" ' . selected( '', $this->instance['page_id'], false ) . '>' . esc_html__( 'Select page', 'gfwa' ) . '</option>';

		$pages = get_pages();
		foreach ( $pages as $page ) {
			echo '<option style="padding-right:10px;" value="' . esc_attr( $page->ID ) . '" ' . selected( esc_attr( $page->ID ), $this->instance['page_id'], false ) . '>' . esc_html( $page->post_title ) . '</option>';
		}

		echo '</select>';
	}

	/**
	 * Outputs a select field for taxonomies and terms.
	 *
	 * @author Nick Croft
	 * @since 1.0.0
	 * @version 1.0.0
	 *
	 * @return void
	 */
	public function select_taxonomy() {
		echo '<select id="' . esc_attr( $this->widget->get_field_id( $this->field_id ) ) . '" name="' . esc_attr( $this->widget->get_field_name( $this->field_id ) ) . '">
						<option style="padding-right:10px;" value="" ' . selected( '', $this->instance['posts_term'], false ) . '>' . esc_html__( 'All Taxonomies and Terms', 'gfwa' ) . '</option>';

		$taxonomies = get_taxonomies( array( 'public' => true ), 'objects' );

		$taxonomies = array_filter( $taxonomies, 'gfwa_exclude_taxonomies' );

		foreach ( $taxonomies as $taxonomy ) {
			if ( ! empty( $taxonomy->query_var ) ) {
				$query_label = $taxonomy->query_var;
			} else {
				$query_label = $taxonomy->name;
			}

			echo '<optgroup label="' . esc_attr( $taxonomy->labels->name ) . '">
								<option style="margin-left: 5px; padding-right:10px;" value="' . esc_attr( $query_label ) . '" ' . selected( esc_attr( $query_label ), $this->instance['posts_term'], false ) . '>' . esc_html( $taxonomy->labels->all_items ) . '</option>';

			$terms = get_terms( $taxonomy->name, 'orderby=name&hide_empty=1' );

			foreach ( $terms as $term ) {
				echo '<option style="margin-left: 8px; padding-right:10px;" value="' . esc_attr( $query_label . ',' . $term->slug ) . '" ' . selected( esc_attr( $query_label ) . ',' . $term->slug, $this->instance['posts_term'], false ) . '>-' . esc_html( $term->name ) . '</option>';
			}

			echo '</optgroup>';
		}

		echo '</select>';
	}

	/**
	 * Outputs a text field.
	 *
	 * @author Nick Croft
	 * @since 1.0.0
	 * @version 1.0.0
	 *
	 * @return void
	 */
	public function text() {
		echo $this->args['description'] ? '<p>' . esc_html( $this->args['description'] ) . '</p>' : '';

		echo '<input type="text" id="' . esc_attr( $this->widget->get_field_id( $this->field_id ) ) . '" name="' . esc_attr( $this->widget->get_field_name( $this->field_id ) ) . '" value="' . esc_attr( $this->instance[ $this->field_id ] ) . '" style="width:95%;" />'; // XSS ok.

	}

	/**
	 * Outputs a small text field.
	 *
	 * @author Nick Croft
	 * @since 1.0.0
	 * @version 1.0.0
	 *
	 * @return void
	 */
	public function text_small() {
		echo '<input type="text" id="' . esc_attr( $this->widget->get_field_id( $this->field_id ) ) . '" name="' . esc_attr( $this->widget->get_field_name( $this->field_id ) ) . '" value="' . esc_attr( $this->instance[ $this->field_id ] ) . '" size="2" />' . esc_html( $this->args['description'] ); // XSS ok.

	}

	/**
	 * Outputs a checkbox.
	 *
	 * @author Nick Croft
	 * @since 1.0.0
	 * @version 1.0.0
	 *
	 * @return void
	 */
	public function checkbox() {
		echo ' <input class="' . esc_html( $this->class ) . '" id="' . esc_attr( $this->widget->get_field_id( $this->field_id ) ) . '" type="checkbox" name="' . esc_attr( $this->widget->get_field_name( $this->field_id ) ) . '" value="1" ' . checked( 1, $this->instance[ $this->field_id ], false ) . '/>'; // XSS ok.
	}
}

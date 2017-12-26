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
			printf(
				'<p %1$s><label for="%2$s">%3$s</label>%4$s</p>',
				$this->style,
				esc_attr( $this->widget->get_field_id( $this->field_id ) ),
				str_replace( esc_html( '<br />' ), '<br />', esc_html( $this->args['label'] ) ),
				$this->$method()
			); // WPCS: XSS ok.
		}
	}

	/**
	 * Returns a select field.
	 *
	 * @author Nick Croft
	 * @since 1.0.0
	 * @version 1.0.0
	 *
	 * @return string
	 */
	public function select() {
		return sprintf(
			'<select class="%1$s" id="%2$s" name="%3$s">%4$s</select>',
			esc_html( $this->class ),
			esc_attr( $this->widget->get_field_id( $this->field_id ) ),
			esc_attr( $this->widget->get_field_name( $this->field_id ) ),
			$this->get_select_opts()
		);
	}

	/**
	 * Gets the options HTML for a select field.
	 *
	 * @return string
	 */
	public function get_select_opts() {
		$opt_args = $this->args['options'];
		$opts     = '';

		foreach ( $opt_args as $key => $value ) {
			if ( is_array( $value ) ) {
				$this->args['options'] = $value;

				$opts .= sprintf(
					'<optgroup label="%1$s">%2$s</optgroup>',
					esc_attr( $key ),
					$this->get_select_opts()
				);
			} else {
				$opts .= sprintf(
					'<option style="padding-right:10px;" value="%1$s" %2$s>%3$s</option>',
					esc_attr( $key ),
					selected( $key, $this->instance[ $this->field_id ], false ),
					esc_html( $value )
				);
			}
		}

		return $opts;
	}

	/**
	 * Returns a select field for post types.
	 *
	 * @author Nick Croft
	 * @since 1.0.0
	 * @version 1.0.0
	 *
	 * @return string
	 */
	public function post_type_select() {
		$args       = array(
			'public' => true,
		);
		$output     = 'names';
		$operator   = 'and';
		$post_types = get_post_types( $args, $output, $operator );
		$post_types = array_filter( $post_types, 'gfwa_exclude_post_types' );

		$post_type_opts = array();

		foreach ( $post_types as $post_type ) {
			$post_type_opts[ $post_type ] = $post_type;
		}

		$this->args['options'] = $post_type_opts;

		return $this->select();
	}

	/**
	 * Returns a select field for pages.
	 *
	 * @author Nick Croft
	 * @since 1.0.0
	 * @version 1.0.0
	 *
	 * @return string
	 */
	public function page_select() {
		$page_opts = array(
			'' => __( 'Select page', 'gfwa' ),
		);

		$pages = get_pages();

		foreach ( $pages as $page ) {
			$page_opts[ $page->ID ] = $page->post_title;
		}

		$this->args['options'] = $page_opts;

		return $this->select();
	}

	/**
	 * Returns a select field for taxonomies and terms.
	 *
	 * @author Nick Croft
	 * @since 1.0.0
	 * @version 1.0.0
	 *
	 * @return string
	 */
	public function select_taxonomy() {
		$tax_opts = array(
			'' => __( 'All Taxonomies and Terms', 'gfwa' ),
		);

		$taxonomies = get_taxonomies( array( 'public' => true ), 'objects' );
		$taxonomies = array_filter( $taxonomies, 'gfwa_exclude_taxonomies' );

		foreach ( $taxonomies as $taxonomy ) {
			if ( ! empty( $taxonomy->query_var ) ) {
				$query_label = $taxonomy->query_var;
			} else {
				$query_label = $taxonomy->name;
			}

			$tax_opts[ $taxonomy->labels->name ] = array(
				$query_label => $taxonomy->labels->all_items,
			);

			$terms = get_terms( $taxonomy->name, 'orderby=name&hide_empty=1' );

			foreach ( $terms as $term ) {
				$tax_opts[ $taxonomy->labels->name ][ $query_label . ',' . $term->slug ] = $term->name;
			}
		}

		$this->args['options'] = $tax_opts;

		return $this->select();
	}

	/**
	 * Outputs a text field.
	 *
	 * @author Nick Croft
	 * @since 1.0.0
	 * @version 1.0.0
	 *
	 * @return string
	 */
	public function text() {
		$description = empty( $this->args['description'] ) ? '' : sprintf( '<p class="description">%s</p>', esc_html( $this->args['description'] ) );

		return sprintf(
			'%4$s<input type="text" id="%1$s" name="%2$s" value="%3$s" style="width:95%;" />',
			esc_attr( $this->widget->get_field_id( $this->field_id ) ),
			esc_attr( $this->widget->get_field_name( $this->field_id ) ),
			esc_attr( $this->instance[ $this->field_id ] ),
			$description
		);
	}

	/**
	 * Returns a small text field.
	 *
	 * @author Nick Croft
	 * @since 1.0.0
	 * @version 1.0.0
	 *
	 * @return string
	 */
	public function text_small() {
		return sprintf(
			'<input type="text" id="%1$s" name="%2$s" value="%3$s" size="2" /> %4$s',
			esc_attr( $this->widget->get_field_id( $this->field_id ) ),
			esc_attr( $this->widget->get_field_name( $this->field_id ) ),
			esc_attr( $this->instance[ $this->field_id ] ),
			empty( $this->args['description'] ) ? '' : esc_html( $this->args['description'] )
		);
	}

	/**
	 * Returns a checkbox.
	 *
	 * @author Nick Croft
	 * @since 1.0.0
	 * @version 1.0.0
	 *
	 * @return string
	 */
	public function checkbox() {
		return sprintf(
			' <input class="%5$s" type="checkbox" id="%1$s" name="%2$s" value="1" %3$s/> %4$s',
			esc_attr( $this->widget->get_field_id( $this->field_id ) ),
			esc_attr( $this->widget->get_field_name( $this->field_id ) ),
			checked( 1, $this->instance[ $this->field_id ], false ),
			empty( $this->args['description'] ) ? '' : esc_html( $this->args['description'] ),
			esc_html( $this->class )
		);
	}
}

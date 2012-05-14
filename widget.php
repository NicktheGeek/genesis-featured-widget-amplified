<?php
/**
 * To Do:
 *      Add support for Grid Loop (0.9)
 *      Make content float options with 2, 3, or 4 side by side clearing after the row (v0.9)
 *      Create Simple Hooks interface (1.0)
 *      Edit html to allow external style sheet instead of inline styles 
 *      Add support for child pages (selected or default to current page)
 *      Add option for showing image via custom field
 *      Add support for sticky posts 
 *      Add support for post_status 
 *      Add support for Post Formats 
 *      Create external stylesheet for widget
 *      Create new widget for creating category thumbnails.
 *
 */
/* Prevent direct access to the plugin */
if ( !defined( 'ABSPATH' ) ) {
    wp_die( __( "Sorry, you are not allowed to access this page directly.", 'gfwa' ) );
}

// Remove the current widget
add_action( 'widgets_init', 'gfwa_unregister_widgets', 20 );

/**
 * Removes Genesis Featured Post Widget
 */
function gfwa_unregister_widgets() {
    unregister_widget( 'Genesis_Featured_Post' );
}

add_action( 'widgets_init', create_function( '', "register_widget('Genesis_Featured_Widget_Amplified');" ) );

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
	function __construct() {

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
			'custom_field'            => ''
		);

		$widget_ops = array(
			'classname'   => 'featuredpost',
			'description' => __( 'Displays featured posts types with thumbnails', 'gfwa' ),
		);

		$control_ops = array(
			'id_base' => 'featured-post',
			'width'   => 505,
			'height'  => 350,
		);

		$this->WP_Widget( 'featured-post', __( 'Genesis - Featured Widget Amplified', 'gfwa' ), $widget_ops, $control_ops );

	}


	/**
     * Creates Widget Output
     *
     * @author Nick Croft
     * @since 0.1
     * @version 0.5
     * @param array $args
     * @param array $instance
     */
    function widget( $args, $instance ) {
        global $gfwa_counter;
        $gfwa_counter = 0;

        extract( $args );

        /** Merge with defaults */
		$instance = wp_parse_args( (array) $instance, $this->defaults );

        echo $before_widget;

        add_filter( 'post_class', 'gfwa_post_class' );

        if ( !empty( $instance['posts_offset'] ) && !empty( $instance['paged'] ) )
            add_filter( 'post_limits', 'gfwa_post_limit' );
        else
            remove_filter( 'post_limits', 'gfwa_post_limit' );

        // Set up the author bio
        if ( !empty( $instance['title'] ) )
            echo $before_title . apply_filters( 'widget_title', $instance['title'] ) . $after_title;



        $term_args = array( );

        if ( !empty( $instance['page_id'] ) )
            $term_args['page_id'] = $instance['page_id'];

        if ( !empty( $instance['posts_term'] ) ) {
            $posts_term = explode( ',', $instance['posts_term'] );
            if ( $posts_term['0'] == 'category' )
                $posts_term['0'] = 'category_name';
            if ( $posts_term['0'] == 'post_tag' )
                $posts_term['0'] = 'tag';
            if ( isset( $posts_term['1'] ) )
                $term_args[$posts_term['0']] = $posts_term['1'];
        }

        if ( !empty( $posts_term['0'] ) ) {
            if ( $posts_term['0'] == 'category_name' )
                $taxonomy = 'category';
            elseif ( $posts_term['0'] == 'tag' )
                $taxonomy = 'post_tag';
            else
                $taxonomy = $posts_term['0'];
        }
        else
            $taxonomy = 'category';

        if ( !empty( $instance['exclude_terms'] ) ) {
            $exclude_terms = explode( ',', str_replace( ' ', '', $instance['exclude_terms'] ) );
            $term_args[$taxonomy . '__not_in'] = $exclude_terms;
        }

        $page = '';
        if ( !empty( $instance['paged'] ) )
            $page = get_query_var( 'paged' );

        if ( !empty( $instance['posts_offset'] ) ) {
            global $myOffset;
            $myOffset = $instance['posts_offset'];
            $term_args['offset'] = $myOffset;
        }

        if ( !empty( $instance['post_id'] ) ) {
            $IDs = explode( ',', str_replace( ' ', '', $instance['post_id'] ) );
            if ( $instance['include_exclude'] == 'include' )
                $term_args['post__in'] = $IDs;
            else
                $term_args['post__not_in'] = $IDs;
        }

        gfwa_before_loop( $instance );

        if ( $instance['posts_num'] != 0 ) {
            $query_args = array_merge( $term_args, array( 'post_type' => $instance['post_type'], 'posts_per_page' => $instance['posts_num'], 'orderby' => $instance['orderby'], 'order' => $instance['order'], 'meta_key' => $instance['meta_key'], 'paged' => $page ) );
            $query_args = apply_filters( 'gfwa_query_args', $query_args, $instance );

            query_posts( $query_args );
            if ( have_posts ( ) ) : while ( have_posts ( ) ) : the_post();

                    echo '<div ';
                    post_class();
                    echo '>';


                    gfwa_before_post_content( $instance );

                    gfwa_post_content( $instance );

                    gfwa_after_post_content( $instance );

                    echo '</div><!--end post_class()-->' . "\n\n";

                    $gfwa_counter++;

                endwhile;

                if ( !empty( $instance['show_paged'] ) )
                    genesis_posts_nav();

                gfwa_endwhile( $instance );

            endif;

            $gfwa_counter = '';

            gfwa_after_loop( $instance );
        }
        // The EXTRA Posts (list)
        if ( $instance['extra_posts'] && $instance['extra_num'] ) {

            if ( !empty( $instance['extra_title'] ) )
                echo str_replace( '>', ' class="additional-posts-title">', $before_title ) . esc_html( $instance['extra_title'] ) . $after_title;

            $offset = intval( $instance['posts_num'] ) + intval( $instance['posts_offset'] );
            $extra_posts_args = array_merge( $term_args, array( 'showposts' => $instance['extra_num'], 'offset' => $offset, 'post_type' => $instance['post_type'], 'orderby' => $instance['orderby'], 'order' => $instance['order'], 'meta_key' => $instance['meta_key'], 'paged' => $page ) );
            $extra_posts_args = apply_filters( 'gfwa_extra_post_args', $extra_posts_args, $instance );
            query_posts( $extra_posts_args );

            $listitems = '';

            if ( have_posts ( ) ) :

                while ( have_posts ( ) ) :

                    the_post();

                    gfwa_list_items( $instance );
                    if ( 'drop_down' != $instance['extra_format'] )
                        $listitems .= sprintf( '<li><a href="%s" title="%s">%s</a></li>', get_permalink(), the_title_attribute( 'echo=0' ), get_the_title() );
                    else
                        $listitems .= sprintf( '<option onclick="javascript:window.location=\'%s\';" value="%s">%s</option>', get_permalink(), get_permalink(), get_the_title() );


                endwhile;

                if ( strlen( $listitems ) > 0 && ('drop_down' != $instance['extra_format']) )
                    printf( '<%s>%s</%s>', $instance['extra_format'], $listitems, $instance['extra_format'] );
                elseif ( strlen( $listitems ) > 0 ) {
                    printf( '<select id="%s" value="%s"><option value="none">%s %s</option>%s</select>', $this->get_field_id( 'extra_format' ), get_permalink(), __( 'Select', 'gfwa' ), $instance['post_type'], $listitems );
                }

                gfwa_print_list_items( $instance );

            endif;
        }

        if ( !empty( $instance['archive_link']) ){
            echo '<p class="more-from-category"><a href="' . $instance['archive_link'] . '" title="' . esc_html( $instance['more_from_category_text'] ) . '">' . esc_html( $instance['more_from_category_text'] ) . '</a></p>';
        }
        elseif ( !empty( $instance['more_from_category'] ) && !empty( $posts_term['1'] ) ) {
            gfwa_category_more( $instance );
            $term = get_term_by( 'slug', $posts_term['1'], $taxonomy );
            echo '<p class="more-from-category"><a href="' . get_term_link( $posts_term['1'], $taxonomy ) . '" title="' . $term->name . '">' . esc_html( $instance['more_from_category_text'] ) . '</a></p>';
        }

        gfwa_after_category_more( $instance );

        echo $after_widget;

        wp_reset_query();
        remove_filter( 'post_class', 'gfwa_post_class' );
        remove_filter( 'post_limits', 'gfwa_post_limit' );
    }

    /**
     * Updates Widget Instance
     *
     * @author Nick Croft
     * @since 0.1
     * @version 0.2
     * @param <type> $new_instance
     * @param <type> $old_instance
     * @return <type>
     */
    function update( $new_instance, $old_instance ) {
        return $new_instance;
    }

    /**
     * Creates Widget Form
     *
     * @author Nick Croft
     * @since 0.1
     * @version 0.5
     * @param array $instance Values set in widget isntance
     */
    function form( $instance ) {
		
		
    
        /** Merge with defaults */
		$instance = wp_parse_args( (array) $instance, $this->defaults ); 
		
		$sizes = genesis_get_additional_image_sizes();
		
			$imageSize_opt['thumbnail'] = 'thumbnail ('. get_option( 'thumbnail_size_w' ) . 'x' . get_option( 'thumbnail_size_h' ) . ')';

		foreach( ( array )$sizes as $name => $size ) 
			$imageSize_opt[$name] = esc_html( $name ) . ' (' . $size['width'] . 'x' . $size['height'] . ')';

		$columns = array(
			'col1' => array(
				array(
					'post_type'               => array(
						'label'       => __( 'Post Type', 'gfwa' ),
						'description' => '',
						'type'        => 'post_type_select',
						'save'        => true,
						'requires'    => '',
					),
					'page_id'                 => array(
						'label'       => __( 'Page', 'gfwa' ),
						'description' => '',
						'type'        => 'page_select',
						'save'        => true,
						'requires'    => array(
							'post_type',
							'page',
							false
						),
					),
					'posts_term'              => array(
						'label'       => __( 'Taxonomy and Terms', 'gfwa' ),
						'description' => '',
						'type'        => 'select_taxonomy',
						'save'        => false,
						'requires'    => array(
							'post_type',
							'page',
							true
						),
					),
					'exclude_terms'           => array(
						'label'       => sprintf( __( 'Exclude Terms by ID %s (comma separated list)', 'gfwa' ), '<br />' ),
						'description' => '',
						'type'        => 'text',
						'save'        => false,
						'requires'    => array(
							'post_type',
							'page',
							true
						),
					),
					'include_exclude'         => array(
						'label'       => '',
						'description' => '',
						'type'        => 'select',
						'options'     => array(
							''        => __( 'Select'  , 'gfwa' ),
							'include' => __( 'Include' , 'gfwa' ),
							'exclude' => __( 'Exclude' , 'gfwa' ),
						),
						'save'        => true,
						'requires'    => array(
							'page_id',
							'',
							false
						),
					),
					'post_id'                 => array(
						'label'       => $instance['post_type'] . ' ' . __( 'ID', 'gfwa' ),
						'description' => '',
						'type'        => 'text',
						'save'        => false,
						'requires'    => array(
							'include_exclude',
							'',
							true
						),
					),
					'posts_num'               => array(
						'label'       => __( 'Number of Posts to Show', 'gfwa' ),
						'description' => '',
						'type'        => 'text_small',
						'save'        => false,
						'requires'    => array(
							'page_id',
							'',
							false
						),
					),
					'posts_offset'            => array(
						'label'       => __( 'Number of Posts to Offset', 'gfwa' ),
						'description' => '',
						'type'        => 'text_small',
						'save'        => false,
						'requires'    => array(
							'page_id',
							'',
							false
						),
					),
					'orderby'                 => array(
						'label'       => __( 'Order By', 'gfwa' ),
						'description' => '',
						'type'        => 'select',
						'options'     => array(
							'date'           => __( 'Date'              , 'gfwa' ),
							'title'          => __( 'Title'             , 'gfwa' ),
							'parent'         => __( 'Parent'            , 'gfwa' ),
							'ID'             => __( 'ID'                , 'gfwa' ),
							'comment_count'  => __( 'Comment Count'     , 'gfwa' ),
							'rand'           => __( 'Random'            , 'gfwa' ),
							'meta_value'     => __( 'Meta Value'        , 'gfwa' ),
							'meta_value_num' => __( 'Numeric Meta Value', 'gfwa' ),
						),
						'save'        => false,
						'requires'    => array(
							'page_id',
							'',
							false
						),
					),
					'order'                   => array(
						'label'       => __( 'Sort Order', 'gfwa' ),
						'description' => '',
						'type'        => 'select',
						'options'     => array(
							'DESC'    => __( 'Descending (3, 2, 1)', 'gfwa' ),
							'ASC'     => __( 'Ascending (1, 2, 3)' , 'gfwa' ),
						),
						'save'        => false,
						'requires'    => array(
							'page_id',
							'',
							false
						),
					),
					'meta_key'               => array(
						'label'       => __( 'Meta Key', 'gfwa' ),
						'description' => '',
						'type'        => 'text',
						'save'        => false,
						'requires'    => array(
							'page_id',
							'',
							false
						),
					),
					'paged'                   => array(
						'label'       => __( 'Work with Pagination', 'gfwa' ),
						'description' => '',
						'type'        => 'checkbox',
						'save'        => false,
						'requires'    => array(
							'post_type',
							'page',
							true
						),
					),
					'show_paged'              => array(
						'label'       => __( 'Show Page Navigation', 'gfwa' ),
						'description' => '',
						'type'        => 'checkbox',
						'save'        => false,
						'requires'    => array(
							'post_type',
							'page',
							true
						),
					),
				),
				array(
					'show_gravatar'           => array(
						'label'       => __( 'Show Author Gravatar', 'gfwa' ),
						'description' => '',
						'type'        => 'checkbox',
						'save'        => true,
						'requires'    => '',
					),
					'gravatar_size'          => array(
						'label'       => __( 'Gravatar Size', 'gfwa' ),
						'description' => '',
						'type'        => 'select',
						'options'     => array(
							'45'      => __( 'Small (45px)'       , 'gfwa' ),
							'65'      => __( 'Medium (65px)'      , 'gfwa' ),
							'85'      => __( 'Large (85px)'       , 'gfwa' ),
							'125'     => __( 'Extra Large (125px)', 'gfwa' ),
						),
						'save'        => false,
						'requires'    => array(
							'show_gravatar',
							'',
							true
						),
					),
					'link_gravatar'          => array(
						'label'       => '',
						'description' => '',
						'type'        => 'select',
						'options'     => array(
							''            => __( 'Do not link gravatar'  , 'gfwa' ),
							'archive'     => __( 'Link to author archive', 'gfwa' ),
							'website'     => __( 'Link to author website', 'gfwa' ),
						),
						'save'        => false,
						'requires'    => array(
							'show_gravatar',
							'',
							true
						),
					),
					'gravatar_alignment'      => array(
						'label'       => __( 'Gravatar Alignment', 'gfwa' ),
						'description' => '',
						'type'        => 'select',
						'options'     => array(
							''           => __( 'None' , 'gfwa' ),
							'alignleft'  => __( 'Left' , 'gfwa' ),
							'alignright' => __( 'Right', 'gfwa' ),
						),
						'save'        => false,
						'requires'    => array(
							'show_gravatar',
							'',
							true
						),
					),
				),
			),
			'col2' => array(
				array(
					'show_image'              => array(
						'label'       => __( 'Show Featured Image', 'gfwa' ),
						'description' => '',
						'type'        => 'checkbox',
						'save'        => true,
						'requires'    => '',
					),
					'link_image'              => array(
						'label'       => '',
						'description' => '',
						'type'        => 'select',
						'options'     => array(
							'1' => __( 'Link Image to Post', 'gfwa' ),
							'2' => __( 'Don\'t Link Image' , 'gfwa' ),
						),
						'save'        => true,
						'requires'    => array(
							'show_image',
							'',
							true
						),
					),
					'link_image_field'              => array(
						'label'       => __( 'Custom Field for Link ( Defaults to Permalink )'),
						'description' => '',
						'type'        => 'text',
						'save'        => false,
						'requires'    => array(
							'link_image',
							'1',
							false
						),
					),
					'image_size'              => array(
						'label'       => '',
						'description' => '',
						'type'        => 'select',
						'options'     => $imageSize_opt,
						'save'        => false,
						'requires'    => array(
							'show_image',
							'',
							true
						),
					),
					'image_position'          => array(
						'label'       => __( 'Image Placement', 'gfwa' ),
						'description' => '',
						'type'        => 'select',
						'options'     => array(
							'before-title'  => __( 'Before Title' , 'gfwa' ),
							'after-title'   => __( 'After Title'  , 'gfwa' ),
							'after-content' => __( 'After Content', 'gfwa' ),
						),
						'save'        => false,
						'requires'    => array(
							'show_image',
							'',
							true
						),
					),
					'image_alignment'         => array(
						'label'       => '',
						'description' => '',
						'type'        => 'select',
						'options'     => array(
							''            => __( 'None'  , 'gfwa' ),
							'alignleft'   => __( 'Left'  , 'gfwa' ),
							'alignright'  => __( 'Right' , 'gfwa' ),
							'aligncenter' => __( 'Center', 'gfwa' ),
						),
						'save'        => false,
						'requires'    => array(
							'show_image',
							'',
							true
						),
					),
				),
				array(
					'show_title'              => array(
						'label'       => __( 'Show Post Title', 'gfwa' ),
						'description' => '',
						'type'        => 'checkbox',
						'save'        => true,
						'requires'    => '',
					),
					'title_limit'             => array(
						'label'       => __( 'Limit title to', 'gfwa' ),
						'description' => __( 'characters', 'gfwa' ),
						'type'        => 'text_small',
						'save'        => false,
						'requires'    => array(
							'show_title',
							'',
							true
						),
					),
					'title_cutoff'             => array(
						'label'       => __( 'Title Cutoff Symbol', 'gfwa' ),
						'description' => '',
						'type'        => 'text_small',
						'save'        => false,
						'requires'    => array(
							'show_title',
							'',
							true
						),
					),
					'link_title'              => array(
						'label'       => '',
						'description' => '',
						'type'        => 'select',
						'options'     => array(
							'1' => __( 'Link Title to Post', 'gfwa' ),
							'2' => __( 'Don\'t Link Title' , 'gfwa' ),
						),
						'save'        => true,
						'requires'    => array(
							'show_title',
							'',
							true
						),
					),
					'link_title_field'              => array(
						'label'       => __( 'Custom Field for Link ( Defaults to Permalink )'),
						'description' => '',
						'type'        => 'text',
						'save'        => false,
						'requires'    => array(
							'link_title',
							'1',
							false
						),
					),
					'show_byline'             => array(
						'label'       => __( 'Show Post Info', 'gfwa' ),
						'description' => '',
						'type'        => 'checkbox',
						'save'        => true,
						'requires'    => '',
					),
					'post_info'               => array(
						'label'       => '',
						'description' => '',
						'type'        => 'text',
						'save'        => false,
						'requires'    => array(
							'show_byline',
							'',
							true
						),
					),
					'show_content'            => array(
						'label'       => __( 'Content Type', 'gfwa' ),
						'description' => '',
						'type'        => 'select',
						'options'     => array(
							'content'       => __( 'Show Content'      , 'gfwa' ),
							'excerpt'       => __( 'Show Excerpt'      , 'gfwa' ),
							'content-limit' => __( 'Show Content Limit', 'gfwa' ),
							''              => __( 'No Content'        , 'gfwa' ),
						),
						'save'        => true,
						'requires'    => '',
					),
					'content_limit'           => array(
						'label'       => __( 'Limit content to', 'gfwa' ),
						'description' => __( 'characters', 'gfwa' ),
						'type'        => 'text_small',
						'save'        => false,
						'requires'    => array(
							'show_content',
							'content-limit',
							false
						),
					),
					'show_archive_line'       => array(
						'label'       => __( 'Show Post Meta', 'gfwa' ),
						'description' => '',
						'type'        => 'checkbox',
						'save'        => true,
						'requires'    => array(
							'post_type',
							'page',
							true
						),
					),

					'post_meta'               => array(
						'label'       => '',
						'description' => '',
						'type'        => 'text',
						'save'        => false,
						'requires'    => array(
							'show_archive_line',
							'',
							true
						),
					),
					'more_text'               => array(
						'label'       => __( 'More Text (if applicable)', 'gfwa' ),
						'description' => '',
						'type'        => 'text',
						'save'        => false,
						'requires'    => '',
					),
				),
				array(
					'extra_posts'             => array(
						'label'       => __( 'Display List of Additional Posts', 'gfwa' ),
						'description' => '',
						'type'        => 'checkbox',
						'save'        => true,
						'requires'    => array(
							'post_type',
							'page',
							true
						),
					),
					'extra_title'             => array(
						'label'       => __( 'Title', 'gfwa' ),
						'description' => '',
						'type'        => 'text',
						'save'        => false,
						'requires'    => array(
							'extra_posts',
							'',
							true
						),
					),
					'extra_num'               => array(
						'label'       => __( 'Number of Posts to Show', 'gfwa' ),
						'description' => '',
						'type'        => 'text_small',
						'save'        => false,
						'requires'    => array(
							'extra_posts',
							'',
							true
						),
					),
					'extra_format'            => array(
						'label'       => __( 'Extra Post Format', 'gfwa' ),
						'description' => '',
						'type'        => 'select',
						'options'     => array(
							'ul'        => __( 'Unordered List', 'gfwa' ),
							'ol'        => __( 'Ordered List'  , 'gfwa' ),
							'drop_down' => __( 'Drop Down'     , 'gfwa' ),
						),
						'save'        => false,
						'requires'    => array(
							'extra_posts',
							'',
							true
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
							true
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
							true
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
							true
						),
					),
				),
				array(
					'custom_field'            => array(
						'label'       => __( 'Instance Identification Field', 'gfwa' ),
						'description' => __( 'Fill in this field if you need to test against an $instance value not included in the form', 'gfwa' ),
						'type'        => 'text',
						'save'        => false,
						'requires'    => '',
					),
				)
			),
		);
		
		echo '<p><label for="'. $this->get_field_id( 'title' ) .'">'. __( 'Title', 'gfwa' ) .':</label>
            <input type="text" id="'. $this->get_field_id( 'title' ) .'" name="'. $this->get_field_name( 'title' ) .'" value="'. esc_attr( $instance['title'] ) .'" style="width:99%;" /></p>';
		
		foreach( $columns as $column => $boxes ) {
			if( 'col1' == $column )
				echo '<div style="float: left; width: 250px;">';
				
			else 
				echo '<div style="float: right; width: 250px;">';
			
			foreach( $boxes as $box ){
				
				echo '<div style="background: #f1f1f1; border: 1px solid #DDD; padding: 10px 10px 0px 10px; margin-bottom: 5px;">';
				
				foreach( $box as $fieldID => $args ){
					
					$class = $args['save']     ? 'class="widget-control-save" ' : '';
					$style = $args['requires'] ? ' style="'. gfwa_get_display_option( $instance, $args['requires'][0], $args['requires'][1], $args['requires'][2] ) .'"' : '';
					
					switch( $args['type'] ) {
						
						case 'post_type_select' :
							
							echo '<p><label for="'. $this->get_field_id( $fieldID ) .'">'. $args['label'] .':</label>
								<select '. $class .'id="'. $this->get_field_id( $fieldID ) .'" name="'. $this->get_field_name( $fieldID ) .'">';
							
							$args = array(
								'public' => true
							);
							$output = 'names';
							$operator = 'and';
							$post_types = get_post_types( $args, $output, $operator );
							$post_types = array_filter( $post_types, 'gfwa_exclude_post_types' );

							foreach ( $post_types as $post_type ) 
								echo '<option style="padding-right:10px;" value="'. esc_attr( $post_type ) .'" '. selected( esc_attr( $post_type ), $instance['post_type'], false ) .'>'. esc_attr( $post_type ) .'</option>'; 

								echo '<option style="padding-right:10px;" value="any" '. selected( 'any', $instance['post_type'], false ) .'>'. __( 'any', 'gfwa' ) .'</option>'; 
								
							echo '</select></p>';
							
							break;
							
						case 'page_select' :
							
							echo '<p'. $style .'><label for="'. $this->get_field_id( $fieldID ) .'">'. $args['label'] .':</label>
								<select '. $class .' id="'. $this->get_field_id( $fieldID ) .'" name="'. $this->get_field_name( $fieldID ) .'">
									<option value="" '. selected( '', $instance['page_id'], false ) .'>'. attribute_escape( __( 'Select page', 'gfwa' ) ) .'</option>';

									$pages = get_pages();
									foreach ( $pages as $page ) 
										echo '<option style="padding-right:10px;" value="'. esc_attr( $page->ID ) .'" '. selected( esc_attr( $page->ID ), $instance['page_id'], false ) .'>'. esc_attr( $page->post_title ) .'</option>';
									
							echo '</select>
							</p>';
							
							break;
						
						case 'select_taxonomy' :
							
							echo '<p'. $style .'"><label for="'. $this->get_field_id( $fieldID ) .'">'. $args['label'] .':</label>

								<select id="'. $this->get_field_id( $fieldID ) .'" name="'. $this->get_field_name( $fieldID ) .'">
									<option style="padding-right:10px;" value="" '. selected( '', $instance['posts_term'], false ) .'>'. __( 'All Taxonomies and Terms', 'gfwa' ) .'</option>';
									
									$taxonomies = get_taxonomies( array( 'public' => true ), 'objects' );

									$taxonomies = array_filter( $taxonomies, 'gfwa_exclude_taxonomies' );
									$test = get_taxonomies( array( 'public' => true ), 'objects' );

									foreach ( $taxonomies as $taxonomy ) {
										$query_label = '';
										if ( !empty( $taxonomy->query_var ) )
											$query_label = $taxonomy->query_var;
										else
											$query_label = $taxonomy->name;
										
										echo '<optgroup label="'. esc_attr( $taxonomy->labels->name ) .'">
											<option style="margin-left: 5px; padding-right:10px;" value="'. esc_attr( $query_label ) .'" '. selected( esc_attr( $query_label ), $instance['posts_term'], false ) .'>'. $taxonomy->labels->all_items .'</option>';
										
										$terms = get_terms( $taxonomy->name, 'orderby=name&hide_empty=1' );
										
										foreach ( $terms as $term )
											echo '<option style="margin-left: 8px; padding-right:10px;" value="'. esc_attr( $query_label ) . ',' . $term->slug .'" '. selected( esc_attr( $query_label ) . ',' . $term->slug, $instance['posts_term'], false ) .'>-' . esc_attr( $term->name ) .'</option>';
											
									echo '</optgroup>'; 
									
									}
									
								echo '</select></p>';
							
							break;
							
						case 'text' :
							
							echo $args['description'] ? '<p>'. $args['description'] .'</p>' : '';

							echo '<p'. $style .'><label for="'. $this->get_field_id( $fieldID ) .'">'. $args['label'] .':</label>
									<input type="text" id="'. $this->get_field_id( $fieldID ) .'" name="'. $this->get_field_name( $fieldID ) .'" value="'. esc_attr( $instance[$fieldID] ) .'" style="width:95%;" /></p>';

							break;
						
						case 'text_small' :
							
							echo '<p'. $style .'><label for="'. $this->get_field_id( $fieldID ) .'">'. $args['label'] .':</label>
									<input type="text" id="'. $this->get_field_id( $fieldID ) .'" name="'. $this->get_field_name( $fieldID ) .'" value="'. esc_attr( $instance[$fieldID] ) .'" size="2" />'. $args['description'] .'</p>';
						
							break;
							
						case 'select' :
							
							echo '<p'. $style .'"><label for="'. $this->get_field_id( $fieldID ) .'">'. $args['label'] .' </label>
								<select '. $class .'id="'. $this->get_field_id( $fieldID ) .'" name="'. $this->get_field_name( $fieldID ) .'">';
							
								foreach( $args['options'] as $value => $label )
									echo '<option style="padding-right:10px;" value="'. $value .'" '. selected( $value, $instance[$fieldID], false ) .'>'. $label .'</option>';
								
								echo '</select></p>';
							
							break;
							
						case 'checkbox' :
							
							echo '<p'. $style .'><input '. $class .'id="'. $this->get_field_id( $fieldID ).'" type="checkbox" name="'. $this->get_field_name( $fieldID ) .'" value="1" '. checked( 1, $instance[$fieldID], false ) .'/> <label for="'. $this->get_field_id( $fieldID ) .'">'. $args['label'] .'</label></p>';
							
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
         * @param array $classes
         * @return array
         */
        function gfwa_post_class( $classes ) {
        global $gfwa_counter;
        //if (  in_array( current_filter(), array( 'gfwa_before_post_content', 'gfwa_post_content', 'gfwa_after_post_content' ) )  ) {
            $classes[] = sprintf( 'gfwa-%s', $gfwa_counter + 1 );
            $classes[] = $gfwa_counter + 1 & 1 ? 'gfwa-odd' : 'gfwa-even';
        //}

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
         * @param array $instance Values set in widget isntance
         */
        function gfwa_do_post_image( $instance ) {
			
			$align = $instance['image_alignment'] ? esc_attr( $instance['image_alignment'] ) : 'alignnone';
			$link = $instance['link_image_field'] && genesis_get_custom_field( $instance['link_image_field'] ) ? genesis_get_custom_field( $instance['link_image_field'] ) : get_permalink();
			
            $image = !empty( $instance['show_image'] ) ? genesis_get_image( array( 'format' => 'html', 'size' => $instance['image_size'], 'attr' => array( 'class' => $align ) ) ) : '';
            $image = $instance['link_image'] == 1 ? sprintf( '<a href="%s" title="%s" class="%s">%s</a>', $link, the_title_attribute( 'echo=0' ), $align, $image ) : $image;
			
            echo current_filter() == 'gfwa_before_post_content' && $instance['image_position'] == 'before-title' && !empty( $instance['show_image'] ) ? $image : '';
            echo current_filter() == 'gfwa_post_content' && $instance['image_position'] == 'after-title' && !empty( $instance['show_image'] ) ? $image : '';
            echo current_filter() == 'gfwa_after_post_content' && $instance['image_position'] == 'after-content' && !empty( $instance['show_image'] ) ? $image : '';
        }

        add_action( 'gfwa_before_post_content', 'gfwa_do_gravatar', 10, 1 );

        /**
         * Inserts Author Gravatar if option is selected
         *
         * @author Nick Croft
         * @since 0.1
         * @version 0.8
         * @param array $instance Values set in widget isntance
         */
        function gfwa_do_gravatar( $instance ) {
            if ( !empty( $instance['show_gravatar'] ) ) {
				
				switch( $instance['link_gravatar'] ) {
					
					case 'archive' :
						
						$before = 'a href="'. get_author_posts_url( get_the_author_meta( 'ID' ) ) .'"';
						$after = 'a';
						
						break;
					
					case 'website' :
						
						$before = 'a href="'. get_the_author_meta( 'user_url' ) .'"';
						$after = 'a';
						
						break;
					
					default :
						
						$before = 'span';
						$after = 'span';
						
						break;
					
				}
				
				printf( '<%s class="%s">%s</%s>', $before, esc_attr( $instance['gravatar_alignment'] ), get_avatar( get_the_author_meta( 'ID' ), $instance['gravatar_size'] ), $after );
				
            }
        }

        add_action( 'gfwa_before_post_content', 'gfwa_do_post_title', 10, 1 );

        /**
         * Outputs Post Title if option is selects
         *
         * @author Nick Croft
         * @since 0.1
         * @version 0.2
         * @param array $instance Values set in widget isntance
         */
        function gfwa_do_post_title( $instance ) {
			
			$link = $instance['link_title_field'] && genesis_get_custom_field( $instance['link_title_field']) ? genesis_get_custom_field( $instance['link_title_field']) : get_permalink();

            $wrap_open = $instance['link_title'] == 1 ? sprintf( '<a href="%s" title="%s">', $link, the_title_attribute( 'echo=0' ) ) : '';
            $wrap_close = $instance['link_title'] == 1 ? '</a>' : '';

            if ( !empty( $instance['show_title'] ) && !empty( $instance['title_limit'] ) )
                printf( '<h2>%s%s%s%s</h2>', $wrap_open, genesis_truncate_phrase( the_title_attribute( 'echo=0' ) , $instance['title_limit'] ), $instance['title_cutoff'], $wrap_close );
            elseif ( !empty( $instance['show_title'] ) )
                printf( '<h2>%s%s%s</h2>', $wrap_open, the_title_attribute( 'echo=0' ), $wrap_close );
        }

        add_action( 'gfwa_before_post_content', 'gfwa_do_byline', 10, 1 );

        /**
         * Outputs byline if option is selects and anything is in the post info field
         *
         * @author Nick Croft
         * @since 0.1
         * @version 0.2
         * @param array $instance Values set in widget isntance
         */
        function gfwa_do_byline( $instance ) {
            if ( !empty( $instance['show_byline'] ) && !empty( $instance['post_info'] ) )
                printf( '<p class="byline post-info">%s</p>', do_shortcode( esc_html( $instance['post_info'] ) ) );
        }

        add_action( 'gfwa_post_content', 'gfwa_do_post_content', 10, 1 );

        /**
         * Outputs the selected content option if any
         *
         * @author Nick Croft
         * @since 0.1
         * @version 0.2
         * @param array $instance Values set in widget isntance
         */
        function gfwa_do_post_content( $instance ) {
            if ( !empty( $instance['show_content'] ) ) {

                if ( $instance['show_content'] == 'excerpt' )
                    the_excerpt();
                elseif ( $instance['show_content'] == 'content-limit' )
                    the_content_limit( ( int ) $instance['content_limit'], esc_html( $instance['more_text'] ) );
                else
                    the_content( esc_html( $instance['more_text'] ) );
            }
        }

        add_action( 'gfwa_after_post_content', 'gfwa_do_post_meta', 10, 1 );

        /**
         * Outputs post meta if option is selected and anything is in the post meta field
         *
         * @author Nick Croft
         * @since 0.6
         * @version 0.6
         * @param array $instance Values set in widget isntance
         */
        function gfwa_do_post_meta( $instance ) {
            if ( !empty( $instance['show_archive_line'] ) && !empty( $instance['post_meta'] ) )
                printf( '<p class="post-meta">%s</p>', do_shortcode( esc_html( $instance['post_meta'] ) ) );
        }

        add_action( 'admin_print_footer_scripts', 'gfwa_form_submit' );

        function gfwa_form_submit() {
?>
            <script type="text/javascript">

                (function(a) {
                    a('select.widget-control-save').live('change', function(){
                        wpWidgets.save( a(this).closest('div.widget'), 0, 1, 0 );
                        return false;
                    });
                })(jQuery);

            </script>
<?php
        }
		
		/**
         * Returns "display: none;" if option and value match, or of they don't match with $standard is set to false
         *
         * @author Nick Croft
         * @since 0.8
         * @version 0.8
         * @param array $instance Values set in widget isntance
         * @param mixed $option instance option to test
         * @param mixed $value value to test against
         * @param boolean $standard echo standard return false for oposite
         */
		function gfwa_get_display_option( $instance, $option='', $value='', $standard=true ) {
			$display = '';
            if ( is_array( $option ) ) {
                foreach ( $option as $key ) {
                    if ( in_array( $instance[$key], $value ) )
                        $display = 'display: none;';
                }
            }
            elseif ( is_array( $value ) ) {
                if ( in_array( $instance[$option], $value ) )
                    $display = 'display: none;';
            }
            else {
                if ( $instance[$option] == $value )
                    $display = 'display: none;';
            }
            if ( $standard == false ) {
                if ( $display == 'display: none;' )
                    $display = '';
                else
                    $display = 'display: none;';
            }
            return $display;
		}

        /**
         * Outputs "display: none;" if option and value match, or of they don't match with $standard is set to false
         *
         * @author Nick Croft
         * @since 0.6
         * @version 0.6
         * @param array $instance Values set in widget isntance
         * @param mixed $option instance option to test
         * @param mixed $value value to test against
         * @param boolean $standard echo standard return false for oposite
         */
        function gfwa_display_option( $instance, $option='', $value='', $standard=true ) {
            $display = '';
            if ( is_array( $option ) ) {
                foreach ( $option as $key ) {
                    if ( in_array( $instance[$key], $value ) )
                        $display = 'display: none;';
                }
            }
            elseif ( is_array( $value ) ) {
                if ( in_array( $instance[$option], $value ) )
                    $display = 'display: none;';
            }
            else {
                if ( $instance[$option] == $value )
                    $display = 'display: none;';
            }
            if ( $standard == false ) {
                if ( $display == 'display: none;' )
                    $display = '';
                else
                    $display = 'display: none;';
            }
            echo $display;
        }
		

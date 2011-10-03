<?php
/**
 * To Do:
 *      Edit html to allow external style sheet instead of inline styles
 *      Create external stylesheet for widget
 *      Fix extra posts showing if number of posts is filled in but check box isn't checked
 *      Add support for sticky posts 
 *      Add support for post_status 
 *      Add support for Post Formats (v0.8)
 *      Add support for child pages (selected or default to current page)(v0.8)
 *      Add support for Grid Loop (0.8)
 *      Add option for "any" on post types. (0.8)
 *      Add option for linking the gravatar
 *      Add additional option for linking post title/image via custom field
 *      Add option for showing image via custom field
 *      Make content float options with 2, 3, or 4 side by side clearing after the row (v0.9)
 *      Create Simple Hooks interface (1.0)
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
     * Creates Widget
     *
     * @author Nick Croft
     * @since 0.1
     * @version 0.2
     */
    function Genesis_Featured_Widget_Amplified() {
        $widget_ops = array( 'classname' => 'featuredpost', 'description' => __( 'Displays featured posts types with thumbnails', 'gfwa' ) );
        $control_ops = array( 'width' => 505, 'height' => 350, 'id_base' => 'featured-post' );
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

        // defaults
        $instance = wp_parse_args( ( array ) $instance, array(
                    'count' => 0,
                    'title' => '',
                    'post_type' => 'post',
                    'page_id' => '',
                    'posts_term' => '',
                    'exclude_terms' => '',
                    'exclude_cat' => '',
                    'include_exclude' => '',
                    'post_id' => '',
                    'posts_num' => 1,
                    'posts_offset' => 0,
                    'orderby' => '',
                    'order' => '',
                    'meta_key' => '',
                    'show_sticky' => '',
                    'paged' => '',
                    'show_paged' => '',
                    'post_align' => '',
                    'show_image' => 0,
                    'link_image' => 1,
                    'image_position' => 'before-title',
                    'image_alignment' => '',
                    'image_size' => '',
                    'show_gravatar' => 0,
                    'gravatar_alignment' => '',
                    'gravatar_size' => '',
                    'show_title' => 0,
                    'link_title' => 1,
                    'title_limit' => '',
                    'show_byline' => 0,
                    'post_info' => '[post_date] ' . __( 'By', 'gfwa' ) . ' [post_author_posts_link] [post_comments]',
                    'show_content' => 'excerpt',
                    'show_archive_line' => 0,
                    'archive_link' => '',
                    'post_meta' => '[post_categories] [post_tags]',
                    'content_limit' => '',
                    'more_text' => __( '[Read More...]', 'gfwa' ),
                    'extra_posts' => '',
                    'extra_num' => '',
                    'extra_title' => '',
                    'extra_format' => 'ul',
                    'more_from_category' => '',
                    'more_from_category_text' => __( 'More Posts from this Taxonomy', 'gfwa' ),
                    'custom_field' => ''
                        ) );

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
        if ( !empty( $instance['extra_num'] ) ) {

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
    
        // ensure value exists
        $instance = wp_parse_args( ( array ) $instance, array(
                    'title' => '',
                    'post_type' => 'post',
                    'page_id' => '',
                    'posts_term' => '',
                    'exclude_terms' => '',
                    'exclude_cat' => '',
                    'include_exclude' => '',
                    'post_id' => '',
                    'posts_num' => 1,
                    'posts_offset' => 0,
                    'orderby' => '',
                    'order' => '',
                    'meta_key' => '',
                    'show_sticky' => '',
                    'paged' => '',
                    'show_paged' => '',
                    'post_align' => '',
                    'show_image' => 0,
                    'link_image' => 1,
                    'image_position' => 'before-title',
                    'image_alignment' => '',
                    'image_size' => '',
                    'show_gravatar' => 0,
                    'gravatar_alignment' => '',
                    'gravatar_size' => '',
                    'show_title' => 0,
                    'link_title' => 1,
                    'title_limit' => '',
                    'show_byline' => 0,
                    'post_info' => '[post_date] ' . __( 'By', 'gfwa' ) . ' [post_author_posts_link] [post_comments]',
                    'show_content' => 'excerpt',
                    'show_archive_line' => 0,
                    'post_meta' => '[post_categories] [post_tags]',
                    'content_limit' => '',
                    'more_text' => __( '[Read More...]', 'gfwa' ),
                    'extra_posts' => '',
                    'extra_num' => '',
                    'extra_title' => '',
                    'extra_format' => 'ul',
                    'more_from_category' => '',
                    'more_from_category_text' => __( 'More Posts from this Taxonomy', 'gfwa' ),
                    'archive_link' => '',
                    'custom_field' => ''
                        ) );
        
?>

        <p><label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title', 'gfwa' ); ?>:</label>
            <input type="text" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" value="<?php echo esc_attr( $instance['title'] ); ?>" style="width:99%;" /></p>

        <div style="float: left; width: 250px;">

            <div style="background: #f1f1f1; border: 1px solid #DDD; padding: 10px 10px 0px 10px;">

                <p><label for="<?php echo $this->get_field_id( 'post_type' ); ?>"><?php _e( 'Post Type', 'gfwa' ); ?>:</label>
                    <select class="widget-control-save" id="<?php echo $this->get_field_id( 'post_type' ); ?>" name="<?php echo $this->get_field_name( 'post_type' ); ?>">
                <?php
                $args = array(
                    'public' => true
                );
                $output = 'names';
                $operator = 'and';
                $post_types = get_post_types( $args, $output, $operator );
                $post_types = array_filter( $post_types, 'gfwa_exclude_post_types' );

                foreach ( $post_types as $post_type ) {
                ?>
                    <option style="padding-right:10px;" value="<?php echo esc_attr( $post_type ); ?>" <?php selected( esc_attr( $post_type ), $instance['post_type'] ); ?>><?php echo esc_attr( $post_type ); ?></option><?php } ?>

            </select></p>

        <p style="<?php gfwa_display_option( $instance, 'post_type', 'page', false ); ?>"><label for="<?php echo $this->get_field_id( 'page_id' ); ?>"><?php _e( 'Page', 'genesis' ); ?>:</label>
            <select class="widget-control-save" id="<?php echo $this->get_field_id( 'page_id' ); ?>" name="<?php echo $this->get_field_name( 'page_id' ); ?>">
                <option value="" <?php selected( '', $instance['page_id'] ); ?>><?php echo attribute_escape( __( 'Select page', 'gfwa' ) ); ?></option>
                <?php
                $pages = get_pages();
                foreach ( $pages as $page ) {
                ?>
                    <option style="padding-right:10px;" value="<?php echo esc_attr( $page->ID ); ?>" <?php selected( esc_attr( $page->ID ), $instance['page_id'] ); ?>><?php echo esc_attr( $page->post_title ); ?></option><?php
                }
                ?>
            </select>
        </p>

        <p style="<?php gfwa_display_option( $instance, 'post_type', 'page' ); ?>"><label for="<?php echo $this->get_field_id( 'posts_term' ); ?>"><?php _e( 'Taxonomy and Terms', 'gfwa' ); ?>:</label>

            <select id="<?php echo $this->get_field_id( 'posts_term' ); ?>" name="<?php echo $this->get_field_name( 'posts_term' ); ?>">
                <option style="padding-right:10px;" value="" <?php selected( '', $instance['posts_term'] ); ?>><?php _e( 'All Taxonomies and Terms', 'gfwa' ); ?></option>
                <?php
                $taxonomies = get_taxonomies( array( 'public' => true ), 'objects' );

                $taxonomies = array_filter( $taxonomies, 'gfwa_exclude_taxonomies' );
                $test = get_taxonomies( array( 'public' => true ), 'objects' );

                foreach ( $taxonomies as $taxonomy ) {
                    $query_label = '';
                    if ( !empty( $taxonomy->query_var ) )
                        $query_label = $taxonomy->query_var;
                    else
                        $query_label = $taxonomy->name;
                ?>


                    <optgroup label="<?php echo esc_attr( $taxonomy->labels->name ); ?>">
                        <option style="margin-left: 5px; padding-right:10px;" value="<?php echo esc_attr( $query_label ); ?>" <?php selected( esc_attr( $query_label ), $instance['posts_term'] ); ?>><?php echo $taxonomy->labels->all_items; ?></option><?php
                    $terms = get_terms( $taxonomy->name, 'orderby=name&hide_empty=1' );
                    foreach ( $terms as $term ) {
                ?>
                        <option style="margin-left: 8px; padding-right:10px;" value="<?php echo esc_attr( $query_label ) . ',' . $term->slug; ?>" <?php selected( esc_attr( $query_label ) . ',' . $term->slug, $instance['posts_term'] ); ?>><?php echo '-' . esc_attr( $term->name ); ?></option><?php } ?>
                </optgroup> <?php
                }
                ?>
            </select></p>

        <p style="<?php gfwa_display_option( $instance, 'post_type', 'page' ); ?>"><label for="<?php echo $this->get_field_id( 'exclude_terms' ); ?>"><?php printf( __( 'Exclude Terms by ID %s (comma separated list)', 'gfwa' ), '<br />' ); ?>:</label>
            <input type="text" id="<?php echo $this->get_field_id( 'exclude_terms' ); ?>" name="<?php echo $this->get_field_name( 'exclude_terms' ); ?>" value="<?php echo esc_attr( $instance['exclude_terms'] ); ?>" style="width:95%;" /></p>

        <p style="<?php gfwa_display_option( $instance, 'page_id', '', false ); ?>"><label for="<?php echo $this->get_field_id( 'include_exclude' ); ?>"><?php printf( __( 'Include or Exclude by %s ID', 'gfwa' ), $instance['post_type'] ); ?>:</label>
            <select class="widget-control-save" id="<?php echo $this->get_field_id( 'include_exclude' ); ?>" name="<?php echo $this->get_field_name( 'include_exclude' ); ?>">
                <option style="padding-right:10px;" value="" <?php selected( '', $instance['include_exclude'] ); ?>><?php _e( 'Select', 'gfwa' ); ?></option>
                <option style="padding-right:10px;" value="include" <?php selected( 'include', $instance['include_exclude'] ); ?>><?php _e( 'Include', 'gfwa' ); ?></option>
                <option style="padding-right:10px;" value="exclude" <?php selected( 'exclude', $instance['include_exclude'] ); ?>><?php _e( 'Exclude', 'gfwa' ); ?></option>
            </select></p>

        <p style="<?php gfwa_display_option( $instance, 'page_id', '', false );
                gfwa_display_option( $instance, 'include_exclude' ); ?>"><label for="<?php echo $this->get_field_id( 'post_id' ); ?>"><?php echo $instance['post_type'] . ' ' . __( 'ID', 'gfwa' ); ?>:</label>
            <input type="text" id="<?php echo $this->get_field_id( 'post_id' ); ?>" name="<?php echo $this->get_field_name( 'post_id' ); ?>" value="<?php echo esc_attr( $instance['post_id'] ); ?>" style="width:95%;" /></p>

             <p style="<?php gfwa_display_option( $instance, 'page_id', '', false ); ?>"><label for="<?php echo $this->get_field_id( 'meta_key' ); ?>"><?php _e( 'Meta Key', 'gfwa' ); ?>:</label>
                 <input type="text" id="<?php echo $this->get_field_id( 'meta_key' ); ?>" name="<?php echo $this->get_field_name( 'meta_key' ); ?>" value="<?php echo esc_attr( $instance['meta_key'] ); ?>" style="width:95%;" /></p>

             <p style="<?php gfwa_display_option( $instance, 'page_id', '', false ); ?>"><label for="<?php echo $this->get_field_id( 'posts_num' ); ?>"><?php _e( 'Number of Posts to Show', 'gfwa' ); ?>:</label>
                 <input type="text" id="<?php echo $this->get_field_id( 'posts_num' ); ?>" name="<?php echo $this->get_field_name( 'posts_num' ); ?>" value="<?php echo esc_attr( $instance['posts_num'] ); ?>" size="2" /></p>

             <p style="<?php gfwa_display_option( $instance, 'page_id', '', false ); ?>"><label for="<?php echo $this->get_field_id( 'posts_offset' ); ?>"><?php _e( 'Number of Posts to Offset', 'gfwa' ); ?>:</label>
                 <input type="text" id="<?php echo $this->get_field_id( 'posts_offset' ); ?>" name="<?php echo $this->get_field_name( 'posts_offset' ); ?>" value="<?php echo esc_attr( $instance['posts_offset'] ); ?>" size="2" /></p>

             <p style="<?php gfwa_display_option( $instance, 'page_id', '', false ); ?>"><label for="<?php echo $this->get_field_id( 'orderby' ); ?>"><?php _e( 'Order By', 'gfwa' ); ?>:</label>
                 <select id="<?php echo $this->get_field_id( 'orderby' ); ?>" name="<?php echo $this->get_field_name( 'orderby' ); ?>">
                     <option style="padding-right:10px;" value="date" <?php selected( 'date', $instance['orderby'] ); ?>><?php _e( 'Date', 'gfwa' ); ?></option>
                     <option style="padding-right:10px;" value="title" <?php selected( 'title', $instance['orderby'] ); ?>><?php _e( 'Title', 'gfwa' ); ?></option>
                     <option style="padding-right:10px;" value="parent" <?php selected( 'parent', $instance['orderby'] ); ?>><?php _e( 'Parent', 'gfwa' ); ?></option>
                     <option style="padding-right:10px;" value="ID" <?php selected( 'ID', $instance['orderby'] ); ?>><?php _e( 'ID', 'gfwa' ); ?></option>
                     <option style="padding-right:10px;" value="comment_count" <?php selected( 'comment_count', $instance['orderby'] ); ?>><?php _e( 'Comment Count', 'gfwa' ); ?></option>
                     <option style="padding-right:10px;" value="rand" <?php selected( 'rand', $instance['orderby'] ); ?>><?php _e( 'Random', 'gfwa' ); ?></option>
                     <option style="padding-right:10px;" value="meta_value" <?php selected( 'meta_value', $instance['orderby'] ); ?>><?php _e( 'Meta Value', 'gfwa' ); ?></option>
                     <option style="padding-right:10px;" value="meta_value_num" <?php selected( 'meta_value_num', $instance['orderby'] ); ?>><?php _e( 'Numeric Meta Value', 'gfwa' ); ?></option>
                 </select></p>

             <p style="<?php gfwa_display_option( $instance, 'page_id', '', false ); ?>"><label for="<?php echo $this->get_field_id( 'order' ); ?>"><?php _e( 'Sort Order', 'gfwa' ); ?>:</label>
                 <select id="<?php echo $this->get_field_id( 'order' ); ?>" name="<?php echo $this->get_field_name( 'order' ); ?>">
                     <option style="padding-right:10px;" value="DESC" <?php selected( 'DESC', $instance['order'] ); ?>><?php _e( 'Descending (3, 2, 1)', 'gfwa' ); ?></option>
                     <option style="padding-right:10px;" value="ASC" <?php selected( 'ASC', $instance['order'] ); ?>><?php _e( 'Ascending (1, 2, 3)', 'gfwa' ); ?></option>
                 </select></p>

             <p style="<?php gfwa_display_option( $instance, 'post_type', 'page' ); ?>"><input id="<?php echo $this->get_field_id( 'paged' ); ?>" type="checkbox" name="<?php echo $this->get_field_name( 'paged' ); ?>" value="1" <?php checked( 1, $instance['paged'] ); ?>/> <label for="<?php echo $this->get_field_id( 'paged' ); ?>"><?php _e( 'Work with Pagination', 'gfwa' ); ?></label></p>

             <p style="<?php gfwa_display_option( $instance, 'post_type', 'page' ); ?>"><input id="<?php echo $this->get_field_id( 'show_paged' ); ?>" type="checkbox" name="<?php echo $this->get_field_name( 'show_paged' ); ?>" value="1" <?php checked( 1, $instance['show_paged'] ); ?>/> <label for="<?php echo $this->get_field_id( 'show_paged' ); ?>"><?php _e( 'Show Page Navigation', 'gfwa' ); ?></label></p>

         </div>
         <div style="background: #f1f1f1; border: 1px solid #DDD; padding: 10px 10px 0px 10px; margin-top: 10px;">

             <p><input class="widget-control-save" id="<?php echo $this->get_field_id( 'show_gravatar' ); ?>" type="checkbox" name="<?php echo $this->get_field_name( 'show_gravatar' ); ?>" value="1" <?php checked( 1, $instance['show_gravatar'] ); ?>/> <label for="<?php echo $this->get_field_id( 'show_gravatar' ); ?>"><?php _e( 'Show Author Gravatar', 'gfwa' ); ?></label></p>

             <p style="<?php gfwa_display_option( $instance, 'show_gravatar' ); ?>"><label for="<?php echo $this->get_field_id( 'gravatar_size' ); ?>"><?php _e( 'Gravatar Size', 'gfwa' ); ?>:</label>
                 <select id="<?php echo $this->get_field_id( 'gravatar_size' ); ?>" name="<?php echo $this->get_field_name( 'gravatar_size' ); ?>">
                     <option style="padding-right:10px;" value="45" <?php selected( 45, $instance['gravatar_size'] ); ?>><?php _e( 'Small (45px)', 'gfwa' ); ?></option>
                     <option style="padding-right:10px;" value="65" <?php selected( 65, $instance['gravatar_size'] ); ?>><?php _e( 'Medium (65px)', 'gfwa' ); ?></option>
                     <option style="padding-right:10px;" value="85" <?php selected( 85, $instance['gravatar_size'] ); ?>><?php _e( 'Large (85px)', 'gfwa' ); ?></option>
                     <option style="padding-right:10px;" value="125" <?php selected( 105, $instance['gravatar_size'] ); ?>><?php _e( 'Extra Large (125px)', 'gfwa' ); ?></option>
                 </select></p>

             <p style="<?php gfwa_display_option( $instance, 'show_gravatar' ); ?>"><label for="<?php echo $this->get_field_id( 'gravatar_alignment' ); ?>"><?php _e( 'Gravatar Alignment', 'gfwa' ); ?>:</label>
                 <select id="<?php echo $this->get_field_id( 'gravatar_alignment' ); ?>" name="<?php echo $this->get_field_name( 'gravatar_alignment' ); ?>">
                     <option style="padding-right:10px;" value="">- <?php _e( 'None', 'gfwa' ); ?> -</option>
                     <option style="padding-right:10px;" value="alignleft" <?php selected( 'alignleft', $instance['gravatar_alignment'] ); ?>><?php _e( 'Left', 'gfwa' ); ?></option>
                     <option style="padding-right:10px;" value="alignright" <?php selected( 'alignright', $instance['gravatar_alignment'] ); ?>><?php _e( 'Right', 'gfwa' ); ?></option>
                 </select></p>

         </div>

<?php gfwa_form_first_column( $instance ); ?>

     </div>

     <div style="float: left; width: 250px; margin-left: 10px;">
         <div style="background: #f1f1f1; border: 1px solid #DDD; padding: 10px 10px 0px 10px;">

             <p><input class="widget-control-save" id="<?php echo $this->get_field_id( 'show_image' ); ?>" type="checkbox" name="<?php echo $this->get_field_name( 'show_image' ); ?>" value="1" <?php checked( 1, $instance['show_image'] ); ?>/> <label for="<?php echo $this->get_field_id( 'show_image' ); ?>"><?php _e( 'Show Featured Image', 'gfwa' ); ?></label></p>

                    <p style="<?php gfwa_display_option( $instance, 'show_image' ); ?>"><select id="<?php echo $this->get_field_id( 'link_image' ); ?>" name="<?php echo $this->get_field_name( 'link_image' ); ?>">
                     <option style="padding-right:10px;" value="1" <?php selected( '1', $instance['link_image'] ); ?>><?php _e( 'Link Image to Post', 'gfwa' ); ?></option>
                     <option style="padding-right:10px;" value="2" <?php selected( '2', $instance['link_image'] ); ?>><?php _e( 'Don\'t Link Image', 'gfwa' ); ?></option>
                 </select></p>

                    <p style="<?php gfwa_display_option( $instance, 'show_image' ); ?>"><label for="<?php echo $this->get_field_id( 'image_size' ); ?>"><?php _e( 'Image Size', 'gfwa' ); ?>:</label>
<?php $sizes = genesis_get_additional_image_sizes(); ?>
                        <select id="<?php echo $this->get_field_id( 'image_size' ); ?>" name="<?php echo $this->get_field_name( 'image_size' ); ?>">
                    <option style="padding-right:10px;" value="thumbnail">thumbnail (<?php echo get_option( 'thumbnail_size_w' ); ?>x<?php echo get_option( 'thumbnail_size_h' ); ?>)</option>
<?php
                foreach ( ( array ) $sizes as $name => $size ) {
                    echo '<option style="padding-right: 10px;" value="' . esc_attr( $name ) . '" ' . selected( $name, $instance['image_size'], FALSE ) . '>' . esc_html( $name ) . ' (' . $size['width'] . 'x' . $size['height'] . ')</option>';
                }
?>
            </select></p>

        <p style="<?php gfwa_display_option( $instance, 'show_image' ); ?>"><label for="<?php echo $this->get_field_id( 'image_position' ); ?>"><?php _e( 'Image Placement', 'gfwa' ); ?>:</label>
            <select id="<?php echo $this->get_field_id( 'image_position' ); ?>" name="<?php echo $this->get_field_name( 'image_position' ); ?>">
                <option style="padding-right:10px;" value="before-title" <?php selected( 'before-title', $instance['image_position'] ); ?>><?php _e( 'Before Title', 'gfwa' ); ?></option>
                <option style="padding-right:10px;" value="after-title" <?php selected( 'after-title', $instance['image_position'] ); ?>><?php _e( 'After Title', 'gfwa' ); ?></option>
                <option style="padding-right:10px;" value="after-content" <?php selected( 'after-content', $instance['image_position'] ); ?>><?php _e( 'After Content', 'gfwa' ); ?></option>
            </select></p>

        <p style="<?php gfwa_display_option( $instance, 'show_image' ); ?>"><label for="<?php echo $this->get_field_id( 'image_alignment' ); ?>"><?php _e( 'Image Alignment', 'gfwa' ); ?>:</label>
            <select id="<?php echo $this->get_field_id( 'image_alignment' ); ?>" name="<?php echo $this->get_field_name( 'image_alignment' ); ?>">
                <option style="padding-right:10px;" value="">- <?php _e( 'None', 'gfwa' ); ?> -</option>
                <option style="padding-right:10px;" value="alignleft" <?php selected( 'alignleft', $instance['image_alignment'] ); ?>><?php _e( 'Left', 'gfwa' ); ?></option>
                <option style="padding-right:10px;" value="alignright" <?php selected( 'alignright', $instance['image_alignment'] ); ?>><?php _e( 'Right', 'gfwa' ); ?></option>
            </select></p>

    </div>

    <div style="background: #f1f1f1; border: 1px solid #DDD; padding: 10px 10px 0px 10px; margin-top: 10px;">

        <p><input class="widget-control-save" id="<?php echo $this->get_field_id( 'show_title' ); ?>" type="checkbox" name="<?php echo $this->get_field_name( 'show_title' ); ?>" value="1" <?php checked( 1, $instance['show_title'] ); ?>/> <label for="<?php echo $this->get_field_id( 'show_title' ); ?>"><?php _e( 'Show Post Title', 'gfwa' ); ?></label>
            <span  style="<?php gfwa_display_option( $instance, 'show_title' ); ?>">
                <br /><label for="<?php echo $this->get_field_id( 'title_limit' ); ?>"><?php _e( 'Limit title to', 'gfwa' ); ?></label> <input type="text" id="<?php echo $this->get_field_id( 'title_limit' ); ?>" name="<?php echo $this->get_field_name( 'title_limit' ); ?>" value="<?php echo esc_attr( intval( $instance['title_limit'] ) ); ?>" size="3" /> <?php _e( 'characters', 'gfwa' ); ?></span></p>
        <p style="<?php gfwa_display_option( $instance, 'show_title' ); ?>"><select id="<?php echo $this->get_field_id( 'link_title' ); ?>" name="<?php echo $this->get_field_name( 'link_title' ); ?>">
                     <option style="padding-right:10px;" value="1" <?php selected( '1', $instance['link_title'] ); ?>><?php _e( 'Link Title to Post', 'gfwa' ); ?></option>
                     <option style="padding-right:10px;" value="2" <?php selected( '2', $instance['link_title'] ); ?>><?php _e( 'Don\'t Link Title', 'gfwa' ); ?></option>
                 </select></p>

        <p><input class="widget-control-save" id="<?php echo $this->get_field_id( 'show_byline' ); ?>" type="checkbox" name="<?php echo $this->get_field_name( 'show_byline' ); ?>" value="1" <?php checked( 1, $instance['show_byline'] ); ?>/> <label for="<?php echo $this->get_field_id( 'show_byline' ); ?>"><?php _e( 'Show Post Info', 'gfwa' ); ?></label>
            <span  style="<?php gfwa_display_option( $instance, 'show_byline' ); ?>">
                <br /><input type="text" id="<?php echo $this->get_field_id( 'post_info' ); ?>" name="<?php echo $this->get_field_name( 'post_info' ); ?>" value="<?php echo esc_attr( $instance['post_info'] ); ?>" style="width: 99%;" />
            </span>
        </p>

        <p><label for="<?php echo $this->get_field_id( 'show_content' ); ?>"><?php _e( 'Content Type', 'gfwa' ); ?>:</label>
            <select class="widget-control-save" id="<?php echo $this->get_field_id( 'show_content' ); ?>" name="<?php echo $this->get_field_name( 'show_content' ); ?>">
                <option value="content" <?php selected( 'content', $instance['show_content'] ); ?>><?php _e( 'Show Content', 'gfwa' ); ?></option>
                <option value="excerpt" <?php selected( 'excerpt', $instance['show_content'] ); ?>><?php _e( 'Show Excerpt', 'gfwa' ); ?></option>
                <option value="content-limit" <?php selected( 'content-limit', $instance['show_content'] ); ?>><?php _e( 'Show Content Limit', 'gfwa' ); ?></option>
                <option value="" <?php selected( '', $instance['show_content'] ); ?>><?php _e( 'No Content', 'gfwa' ); ?></option>
            </select>
            <span  style="<?php gfwa_display_option( $instance, 'show_content', 'content-limit', false ); ?>">
                <br /><label for="<?php echo $this->get_field_id( 'content_limit' ); ?>"><?php _e( 'Limit content to', 'gfwa' ); ?></label> <input type="text" id="<?php echo $this->get_field_id( 'image_alignment' ); ?>" name="<?php echo $this->get_field_name( 'content_limit' ); ?>" value="<?php echo esc_attr( intval( $instance['content_limit'] ) ); ?>" size="3" /> <?php _e( 'characters', 'gfwa' ); ?></span></p>

        <p style="<?php gfwa_display_option( $instance, 'post_type', 'page' ); ?>"><input class="widget-control-save" id="<?php echo $this->get_field_id( 'show_archive_line' ); ?>" type="checkbox" name="<?php echo $this->get_field_name( 'show_archive_line' ); ?>" value="1" <?php checked( 1, $instance['show_archive_line'] ); ?>/> <label for="<?php echo $this->get_field_id( 'show_archive_line' ); ?>"><?php _e( 'Show Post Meta', 'gfwa' ); ?></label>
            <span  style="<?php gfwa_display_option( $instance, 'show_archive_line' ); ?>">
                <br /><input type="text" id="<?php echo $this->get_field_id( 'post_meta' ); ?>" name="<?php echo $this->get_field_name( 'post_meta' ); ?>" value="<?php echo esc_attr( $instance['post_meta'] ); ?>" style="width: 99%;" />
            </span>
        </p>

        <p><label for="<?php echo $this->get_field_id( 'more_text' ); ?>"><?php _e( 'More Text (if applicable)', 'gfwa' ); ?>:</label>
            <input type="text" id="<?php echo $this->get_field_id( 'more_text' ); ?>" name="<?php echo $this->get_field_name( 'more_text' ); ?>" value="<?php echo esc_attr( $instance['more_text'] ); ?>" /></p>

    </div>
    <div style="<?php gfwa_display_option( $instance, 'post_type', 'page' ); ?> background: #f1f1f1; border: 1px solid #DDD; padding: 10px 10px 0px 10px; margin-top: 10px;">

        <p><input class="widget-control-save" id="<?php echo $this->get_field_id( 'extra_posts' ); ?>" type="checkbox" name="<?php echo $this->get_field_name( 'extra_posts' ); ?>" value="1" <?php checked( 1, $instance['extra_posts'] ); ?>/> <label for="<?php echo $this->get_field_id( 'extra_posts' ); ?>"><?php _e( 'Display List of Additional Posts', 'gfwa' ); ?></label></p>

        <p style="<?php gfwa_display_option( $instance, 'extra_posts' ); ?>"><label for="<?php echo $this->get_field_id( 'extra_title' ); ?>"><?php _e( 'Title', 'gfwa' ); ?>:</label>
            <input type="text" id="<?php echo $this->get_field_id( 'extra_title' ); ?>" name="<?php echo $this->get_field_name( 'extra_title' ); ?>" value="<?php echo esc_attr( $instance['extra_title'] ); ?>" style="width:95%;" /></p>

        <p style="<?php gfwa_display_option( $instance, 'extra_posts' ); ?>"><label for="<?php echo $this->get_field_id( 'extra_num' ); ?>"><?php _e( 'Number of Posts to Show', 'gfwa' ); ?>:</label>
            <input type="text" id="<?php echo $this->get_field_id( 'extra_num' ); ?>" name="<?php echo $this->get_field_name( 'extra_num' ); ?>" value="<?php echo esc_attr( $instance['extra_num'] ); ?>" size="2" /></p>

        <p style="<?php gfwa_display_option( $instance, 'extra_posts' ); ?>"><label for="<?php echo $this->get_field_id( 'extra_format' ); ?>"><?php _e( 'Extra Post Format', 'gfwa' ); ?>:</label>
            <select class="widget-control-save" id="<?php echo $this->get_field_id( 'extra_format' ); ?>" name="<?php echo $this->get_field_name( 'extra_format' ); ?>">
                <option value="ul" <?php selected( 'ul', $instance['extra_format'] ); ?>><?php _e( 'Unordered List', 'gfwa' ); ?></option>
                <option value="ol" <?php selected( 'ol', $instance['extra_format'] ); ?>><?php _e( 'Ordered List', 'gfwa' ); ?></option>
                <option value="drop_down" <?php selected( 'drop_down', $instance['extra_format'] ); ?>><?php _e( 'Drop Down', 'gfwa' ); ?></option>
            </select>
        </p>

    </div>

    <div style="<?php gfwa_display_option( $instance, 'post_type', 'page' ); ?> background: #f1f1f1; border: 1px solid #DDD; padding: 10px 10px 0px 10px; margin: 10px 0;">

        <p><input class="widget-control-save" id="<?php echo $this->get_field_id( 'more_from_category' ); ?>" type="checkbox" name="<?php echo $this->get_field_name( 'more_from_category' ); ?>" value="1" <?php checked( 1, $instance['more_from_category'] ); ?>/> <label for="<?php echo $this->get_field_id( 'more_from_category' ); ?>"><?php _e( 'Show Category Archive Link', 'gfwa' ); ?></label></p>

        <p style="<?php gfwa_display_option( $instance, 'more_from_category' ); ?>"><label for="<?php echo $this->get_field_id( 'more_from_category_text' ); ?>"><?php _e( 'Link Text', 'gfwa' ); ?>:</label>
            <input type="text" id="<?php echo $this->get_field_id( 'more_from_category_text' ); ?>" name="<?php echo $this->get_field_name( 'more_from_category_text' ); ?>" value="<?php echo esc_attr( $instance['more_from_category_text'] ); ?>" style="width:95%;" /></p>

        <p style="<?php gfwa_display_option( $instance, 'more_from_category' ); ?>"><label for="<?php echo $this->get_field_id( 'archive_link' ); ?>"><?php _e( 'Optional Archive Link', 'gfwa' ); ?><abbr title="<?php _e( 'Fill in this value with a URL if you wish to display an archive link when showing all terms or to override the normal archive link to another URL', 'gfwa' ); ?>">*</abbr>:</label>
            <input type="text" id="<?php echo $this->get_field_id( 'archive_link' ); ?>" name="<?php echo $this->get_field_name( 'archive_link' ); ?>" value="<?php echo esc_attr( $instance['archive_link'] ); ?>" style="width:95%;" /></p>

    </div>

     <div style="background: #f1f1f1; border: 1px solid #DDD; padding: 10px 10px 0px 10px; margin: 10px 0;">
        <p>Fill in this field if you need to test against an $instance value not included in the form</p>
        <p><label for="<?php echo $this->get_field_id( 'custom_field' ); ?>"><?php _e( 'Instance Identification Field', 'gfwa' ); ?>:</label>
            <input type="text" id="<?php echo $this->get_field_id( 'custom_field' ); ?>" name="<?php echo $this->get_field_name( 'custom_field' ); ?>" value="<?php echo esc_attr( $instance['custom_field'] ); ?>" style="width:95%;" /></p>

    </div>

<?php gfwa_form_second_column( $instance ); ?>
</div>

<?php
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

            $image = !empty( $instance['show_image'] ) ? genesis_get_image( array( 'format' => 'html', 'size' => $instance['image_size'] ) ) : '';
            $image = $instance['link_image'] == 1 ? sprintf( '<a href="%s" title="%s" class="%s">%s</a>', get_permalink(), the_title_attribute( 'echo=0' ), esc_attr( $instance['image_alignment'] ), $image ) : $image;
            echo current_filter() == 'gfwa_before_post_content' && $instance['image_position'] == 'before-title' && !empty( $instance['show_image'] ) ? $image : '';
            echo current_filter() == 'gfwa_post_content' && $instance['image_position'] == 'after-title' && !empty( $instance['show_image'] ) ? $image : '';
            echo current_filter() == 'gfwa_after_post_content' && $instance['image_position'] == 'after-content' && !empty( $instance['show_image'] ) ? $image : '';
        }

        add_action( 'gfwa_before_post_content', 'gfwa_do_gravatar', 10, 1 );

        /**
         * Inserts Author Gravatar is option is selects
         *
         * @author Nick Croft
         * @since 0.1
         * @version 0.2
         * @param array $instance Values set in widget isntance
         */
        function gfwa_do_gravatar( $instance ) {
            if ( !empty( $instance['show_gravatar'] ) ) {
                echo '<span class="' . esc_attr( $instance['gravatar_alignment'] ) . '">' .
                get_avatar( get_the_author_meta( 'ID' ), $instance['gravatar_size'] ) .
                '</span>';
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
            $wrap_open = $instance['link_title'] == 1 ? sprintf( '<a href="%s" title="%s">', get_permalink(), the_title_attribute( 'echo=0' ) ) : '';
            $wrap_close = $instance['link_title'] == 1 ? '</a>' : '';

            if ( !empty( $instance['show_title'] ) && !empty( $instance['title_limit'] ) )
                printf( '<h2>%s%s%s</h2>', $wrap_open, genesis_truncate_phrase( the_title_attribute( 'echo=0' ), $instance['title_limit'] ), $wrap_close );
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
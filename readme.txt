=== Plugin Name ===
Contributors: Nick_theGeek
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=RGUXZDAKT7BDW
Tags: genesis, genesiswp, studiopress, featured post, custom post type, pagination
Requires at least: 3.3
Tested up to: 3.3.1
Stable tag: 0.8.1

Genesis Featured Posts with support for custom post types, taxonomies, and so much more

== Description ==

Genesis Featured Widget Amplified adds additional functionality to the Genesis Featured Posts Widget.  Specifically it:

* Supports Custom Post Types
* Supports Custom Taxonomies
* Exclude Term by ID field
* Supports Pagination
* Supports Meta Key Values
* Supports Sorting by Meta Key
* Multiple Hooks and Filters for adding additional content

This plugin requires the [Genesis Theme Framework](http://designsbynickthegeek.com/go/genesis)

Thanks to David Decker, this plugin is translation ready.  German translation files included.

== Installation ==

1. Upload the entire `genesis-featured-widget-amplified` folder to the `/wp-content/plugins/` directory
1. Activate the plugin through the 'Plugins' menu in WordPress
1. Go Widget Screen
1. Drag Widget to desired sidebar
1. Fill in widget settings

== Frequently Asked Questions ==
= What Hooks are available? =
1. gfwa_before_loop - before the query is formulated
1. gfwa_before_post_content - before the content
1. gfwa_post_content - standard content output
1. gfwa_after_post_content - after content
1. gfwa_endwhile - after the endwhile but before the endif
1. gfwa_after_loop - after the loop endif
1. gfwa_list_items - within additional list item loop
1. gfwa_print_list_items - outside the additional list item loop, where list items are output
1. gfwa_category_more - within the archive more link conditional block
1. gfwa_after_category_more - after the archive more conditional block
1. gfwa_form_first_column within the widget form, end of first column
1. gfwa_form_second_column within the widget form, end of second column
= What Filters are available? =
1. gfwa_exclude_post_types - used to prevent post types from appearing in the post type list in the widget form
1. gfwa_exclude_taxonomies - used to prevent taxonomies and related terms from appearing in the terms and taxonomies list in the widget form

== Change Log ==
0.8.1 (5*14-2012 : current)

 * Fixed thumbnail image size option

0.8 (2-27-2012)

 * Fixed image alignment when not linked
 * Added alignnone to output if no alignment is selected
 * Added aligncenter to image options
 * Dropped support for php 4, now requires php 5.2 like WordPress
 * Simplified defaults to a single list instead of 2 lists
 * Simplified widget form creation to allow easier option updates
 * Fixed extra posts showing if number of posts is filled in but check box isn't checked
 * Added option for "any" on post types.
 * Added option for linking the gravatar
 * Fixed some strings that were not internationalized correctly
 * Added additional option for linking post title/image via custom field
 * Added title cutoff symbol 

0.7.2 (10-3-2011)

* Fixed link to post image option
* Fixed link to post title option
* Added German Translation Files

0.7.1 (9-20-2011)

* Fixed text domain for localization support
* Fixed undefined index extra_posts notice

0.7 (9-20-2011)

* Added option to link/not link post title
* Added option to link/not link post image
* Fixed tag archive link
* Fixed All %taxonomy% not showing "selected"
* Fixed exclude post by ID not working
* Added custom field for $instance test on user added actions (v0.7) Complete
* Added even/odd class (v0.7) Complete
* Added counter variable to loop $gfwa_counter (v0.7) Complete
* Added Archive link url for all-taxonomies


0.6.6 (3-21-2011)

* Bug Fix: Corrected taxonomy name for Categories, which resolves several smaller bugs including, exclude terms not working with specific category selected
* Bug Fix: Corrected archive link creation

0.6.5 (2-12-2011)

* Update to taxonomy output to compensate for 3.0 and 3.1 differences

0.6.4 (2-12-2011)

* Changed Terms and Taxonomies drop down output to reduce errors in terms not being built in Query and make output more user friendly
* Fixed the tags query.

0.6.3 (1-26-2011)

* Fixed Ajax widget control loading for IE, Safari, and Chrome
* Fixed multiple image bug when showing more than one image position in different widgets on a page
* Updated Post Extras Dropdown list script

0.6.2 (1-26-2011)

* Fixed extra list output typo

0.6.1 (1-26-2011)

* Fixed extra list default

0.6 (1-26-2011)

* Ajaxified Widget Form
* Form no longer shows options that will not be supported based on other option selections
* Added support for post meta (category and tags)
* Added drop down list of pages if page is selected
* Allowed the first loop to be skipped by setting "Number of Posts to Show" to 0
* Added additional post list format option (ul, ol,  or dropdown)


0.5 (1-18-2011)

* Fixed pagination issue due to different reading setting and widget setting
* Fixed Archive link from not showing when enabled and category selected
* Added include/exclude fields for post_type ID
* Added easy position selector for the image relative to the title
* Added class for additional post title
* Added Title Limit

0.4 (1-12-2011)

* Changed Widget to replace Genesis Featured Posts Widget instead of working along side of it
* Removed Beta Designation

0.3b (1-12-2011)

* Improved internal documentation
* Made all text strings translatable
* Added Support for Pagination and Offset to work at the same time

0.2b (1-7-2011)

* First Public Release


== Upgrade Notice ==

0.6.5 Make sure you resave widget settings for terms and taxonomies

0.6.3 Image Action Hooks were changed. `add_action( 'gfwa_before_post_content', 'gfwa_do_post_image', 15, 1 );` is replaced with `add_action( 'gfwa_post_content', 'gfwa_do_post_image', 5, 1 );`

0.5 Image action hooks were changed. `add_action( 'gfwa_before_post_content', 'gfwa_do_post_image', 10, 1 );` is replaced with `add_action( 'gfwa_before_post_content', 'gfwa_do_post_image', 5, 1 );



== Screenshots ==
To Do: Take and add screen shots

== Special Thanks ==
I owe a huge debt of gratitude to all the folks at StudioPress, their themes make my life easier.

Gary Jones (aka GaryJ) provided guidance on several issues related to debugging and documentation
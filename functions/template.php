<?php
/**
 * TheGrio custom template tags
 *
 * Any functions that emit markup should go in this file,
 * even those who call methods on singleton class instances.
 *
 * This is so we have a single go-to for all things template-related,
 * without having to mix class definitions with top-level functions
 * in files that should only declare a class.
 */

/**
 * Prints HTML with meta information for the current post-date/time and author.
 *
 * @uses the_author_posts_link, the_permalink, the_title_attribute, esc_attr, get_the_date, the_time
 * @return string
 */
function thegrio_posted_on() {
?>
	<span class="by-author">
		<span class="sep"> by </span>
		<span class="author vcard"><?php is_author() ? the_author() : the_author_posts_link(); ?></span>
	</span>
	<span class="sep"> | </span>
	<a href="<?php the_permalink(); ?>" title="<?php the_title_attribute(); ?>" rel="bookmark">
		<time class="entry-date" datetime="<?php echo esc_attr( get_the_date( 'c' ) ); ?>" pubdate><?php the_time( get_option( 'date_format' ) ); ?> at <?php the_time(); ?></time>
	</a>
<?php
}

/**
 * Base the navigation menu active state for single posts on the primary category
 *
 * @param array $classes
 * @param object $item
 * @uses thegrio_get_primary_category
 * @filter nav_menu_css_class
 * @return array
 */
function thegrio_nav_menu_css_class( $classes, $item ) {
	if ( is_singular() && 'category' == $item->object ) {
		$primary_category = thegrio_get_primary_category( null, false );

		if ( is_int( $primary_category ) && $item->object_id == $primary_category ) {
			$classes[] = 'current-menu-primary';
		}
	}

	return $classes;
}
add_filter( 'nav_menu_css_class', 'thegrio_nav_menu_css_class', 10, 2 );

/**
 * Display categories and tags assigned to the current post
 *
 * @uses get_post_type, get_the_category_list, get_the_tag_list
 * @return string or null
 */
function thegrio_posted_in() {
	$show_sep = false;

	if ( 'post' == get_post_type() ) :
		$categories_list = get_the_category_list( ', ' );
		if ( $categories_list ):
			?>
			<span class="cat-links">
				<span class="entry-utility-prep entry-utility-prep-cat-links">Filed in:</span> <?php echo esc_html( $categories_list ); ?>
			</span>
			<?php
			$show_sep = true;
		endif; // End if categories

		$tags_list = get_the_tag_list( '', ', ' );
		if ( $tags_list ):
			if ( $show_sep ) : ?>
				<span class="sep"> | </span>
			<?php endif; // End if $show_sep ?>
			<span class="tag-links">
				<span class="entry-utility-prep entry-utility-prep-tag-links">Related Topics:</span> <?php echo esc_html( $tags_list ); ?>
			</span>
		<?php endif; // End if $tags_list
	endif; // End if 'post' == get_post_type()
}

/**
 * Get caption below post thumbnail
 *
 * @uses get_post_thumbnail_id, get_the_ID, get_post_field, esc_html
 * @return string
 */
function thegrio_get_post_thumbnail_caption() {
	if ( $thumb_id = get_post_thumbnail_id( get_the_ID() ) ) {
		$caption = get_post_field( 'post_excerpt', $thumb_id );

		if ( is_string( $caption ) && ! empty( $caption ) ) {
			return '<p class="wp-caption">' . esc_html( $caption ) . '</p>';
		}
	}

	// return an empty string if no caption found
	return '';
}

/**
 * Display caption below post thumbnail
 *
 * @uses thegrio_get_post_thumbnail_caption
 * @return string
 */
function thegrio_the_post_thumbnail_caption() {
	echo wp_kses_post( thegrio_get_post_thumbnail_caption() );
}

/**
 * Display meta for posts in the Loop
 *
 * @uses the_permalink, the_title_attribute, comments_link, esc_url, get_permalink
 * @return string
 */
if ( ! function_exists( 'thegrio_river_meta' ) ) {
	function thegrio_river_meta() { ?>
		<span class="read-more">
		<a href="<?php the_permalink(); ?>" title="Permalink to <?php the_title_attribute(); ?>" rel="bookmark">Read
			More</a>
	</span>
		<span class="sep"> | </span>
		<span class="leave-comment">
		<a href="<?php comments_link(); ?>" title="Comment on <?php the_title_attribute(); ?>" rel="bookmark"><span
				class="generic-comment-text">Leave Comment</span><span class="fb-comment-count-wrapper">Comments (<fb:comments-count
					href="<?php echo esc_url( function_exists( 'wpcom_is_vip' ) ? get_permalink() : 'http://example.com/' ); ?>"
					class="fbml-comment-count">-
				</fb:comments-count>)</span></a>
	</span>

	<?php }
}

/**
 * Output primary category tag for current post
 *
 * @uses in_the_loop, wpcom_vip_get_category_by_slug, thegrio_get_primary_category, is_wp_error, esc_attr, esc_html
 * @return string or null
 */
function thegrio_primary_category_tag() {
	if ( ! in_the_loop() ) {
		return;
	}

	$primary_category = wpcom_vip_get_category_by_slug( thegrio_get_primary_category() );

	if ( is_object( $primary_category ) && ! is_wp_error( $primary_category ) ) :
		?><span class="primary-category-title-tag primary-category-title-tag-<?php echo esc_attr( strtolower( $primary_category->name ) ); ?> "><?php echo esc_html( $primary_category->name ); ?></span><?php
	endif;
}

/**
 * Render default image, if size is available
 *
 * @param int $size
 * @uses esc_url, get_stylesheet_directory_uri
 * @return string or null
 */
function thegrio_default_image( $size = 300 ) {
	$default_image_sizes = array(
		'800' => array(
			'src' => 'default-image.png',
			'width' => 800,
			'height' => 600,
		),
		'650' => array(
			'src' => 'default-image-650.png',
			'width' => 650,
			'height' => 488,
		),
		'420' => array(
			'src' => 'default-image-420.png',
			'width' => 420,
			'height' => 315,
		),
		'300' => array(
			'src' => 'default-image-wide-300.png',
			'width' => 300,
			'height' => 169,
		),
		'280' => array(
			'src' => 'default-image-wide-280.png',
			'width' => 280,
			'height' => 158,
		),
		'144' => array(
			'src' => 'default-image-wide-144.png',
			'width' => 144,
			'height' => 81,
		),
		'96' => array(
			'src' => 'default-image-wide-96.png',
			'width' => 96,
			'height' => 54,
		)
	);

	if ( ! array_key_exists( $size, $default_image_sizes ) ) {
		return;
	}

	?><img class="default-image" src="<?php echo esc_url( get_stylesheet_directory_uri() . '/images/' .  $default_image_sizes[ $size ]['src'] ); ?>" alt="theGrio" width="<?php echo esc_attr( $default_image_sizes[ $size ]['width'] ); ?>" height="<?php echo esc_attr( $default_image_sizes[ $size ]['height'] ); ?>" /><?php
}

/**
 * Adds thegrio_after_singular_content action below individual posts
 *
 * @global $post
 * @uses is_singular, thegrio_posted_in, do_action
 * @return string or null
 */
function thegrio_single_post_footer() {
	if ( is_singular() ) :
	?>
	<footer class="entry-footer">
		<?php
		if ( is_singular( array( 'page', 'post' ) ) ) {
			do_action( 'thegrio_after_singular_content' );
		} ?>

		<div class="entry-meta"><?php thegrio_posted_in(); ?></div>

	</footer><!-- #entry-meta -->
	<?php endif;
}

/**
 * Output comment count and text, pulling count from Facebook
 *
 * @uses in_the_loop, get_comments_link, esc_url, get_permalink
 * @return string or null
 */
function thegrio_comment_count() {
	if ( ! in_the_loop() ) {
		return;
	}

	$link = get_comments_link();

	?><table class="comment-count">
		<tr>
			<td class="comment-text"><a href="<?php echo esc_url( $link ); ?>">Comments</a></td>
			<td class="comment-num">
				<span class="point-gray"></span>
				<span class="point-white"></span>
				<a href="<?php echo esc_url( $link ); ?>">
					<fb:comments-count href="<?php echo esc_url( function_exists( 'wpcom_is_vip' ) ? get_permalink() : 'http://example.com/' ); ?>"></fb:comments-count>
				</a>
			</td>
		</tr>
	</table><?php
}

/**
 * THEGRIO OMNITURE IMPLEMENTATION
 */
class TheGrio_Omniture {
	/**
	 * Register output method
	 *
	 * @uses add_action
	 * @return null
	 */
	public function __construct() {
		add_action( 'wp_footer', array( $this, 'action_wp_footer' ) );
	}

	/**
	 * Render Omniture tracking script
	 * Outside of WordPress.com VIP, tracking variables will be written to JavaScript console and tracking will not occur. Facilitates debugging without impacting client's analytics data.
	 *
	 * @uses wpcom_vip_theme_url, esc_js, this::get_channel, is_user_logged_in, this::get_content_group, this::get_site_subfeature, this::get_secondary_site_subfeature, this::get_author
	 * @return string
	 */
	public function action_wp_footer() {
	?>
		<!-- Start of Async HubSpot Analytics Code -->
		<script type="text/javascript">
			(function(d,s,i,r) {
			    if (d.getElementById(i)){return;}
			    var n=d.createElement(s),e=d.getElementsByTagName(s)[0];
			    n.id=i;n.src='//js.hubspot.com/analytics/'+(Math.ceil(new Date()/r)*r)+'/248743.js';
			    e.parentNode.insertBefore(n, e);
			})(document,"script","hs-analytics",300000);
		</script>
		<!-- End of Async HubSpot Analytics Code -->
		<!-- SiteCatalyst code version: H.17.1 Copyright 1997-2009 Omniture, Inc. and NBC Universal -->
		<!-- More info available at http://www.omniture.com -->
		<?php if ( true === WPCOM_IS_VIP_ENV ) : ?>
		<script language="JavaScript" src="<?php echo esc_url( wpcom_vip_theme_url( '/js/s_code.js' ) ); ?>"></script>
		<?php else : ?>
		<script>
			var s = {};
			s.t = function() { console.log( s ); };
		</script>
		<?php endif; ?>
		<script language="JavaScript">
			s.pageName=document.title; // auto reads page name from TITLE tag of page
			s.channel="<?php echo esc_js( $this->get_channel() ); ?>"; // site sections - top nav
			s.server=""; // ignore, auto completed by
			s.pageType=""; // used for 404 error page only

			s.prop1="<?php echo esc_attr( is_user_logged_in() ? 'Registered' : 'Not-Registered' ); ?>";  // Registered or Not Registered
			s.prop2="";    // content type
			s.prop3="<?php echo esc_js( $this->get_content_group() ); ?>";  // content group
			s.prop4="<?php echo esc_js( $this->get_site_subfeature() ); ?>";  // site sub feature
			s.prop5="<?php echo esc_js( $this->get_secondary_site_subfeature() ); ?>";  // site sub feature II
			s.prop6="<?php echo esc_js( $this->get_author() ); ?>";

			s.prop8="News";
			s.prop9="NBC News";
			s.prop10="theGrio"; // Site Show Name

			/* Conversion Variables */

			s.evar8=""; // Contact Method
			s.events=""; // per spec code

			/************* DO NOT ALTER ANYTHING BELOW THIS LINE ! **************/
			var s_code=s.t();if(s_code)document.write(s_code);
		</script>
		<!-- End SiteCatalyst code version: H.17.1 -->
	<?php
	}

	/**
	 * Retrieve ID of topmost parent in a hierarchical post type.
	 * For example, if Primary > Secondary > Tertiary is a page tree and this function is called on the ID of page Tertiary, the ID of page Primary is returned.
	 *
	 * @param int $parent
	 * @uses wp_get_post_parent_id
	 * @return int or false
	 */
	private function get_topmost_parent( $parent ) {
		$parent = intval( $parent );

		if ( ! $parent ) {
			return false;
		}

		$top_parent = false;

		while ( $top_parent == false ) {
			$current = $parent;
			$parent = wp_get_post_parent_id( $current );

			if ( 0 == $parent ) {
				$top_parent = (int) $current;
			}
			elseif ( ! is_numeric( $parent ) ) {
				break;
			}
		}

		return $top_parent;
	}

	/**
	 * Determine channel for current page
	 *
	 * @global $post
	 * @uses is_home, is_front_page, is_search, is_page, this::get_topmost_parent, get_post_field
	 * @return string
	 */
	private function get_channel() {
		if ( is_home() || is_front_page() ) {
			return 'Home';
		}
		elseif ( is_search() ) {
			return 'Search';
		}
		elseif ( is_page() ) {
			global $post;

			if ( 0 == $post->post_parent ) {
				return $post->post_title;
			}
			else {
				$top_parent = $this->get_topmost_parent( $post->post_parent );

				if ( is_numeric( $top_parent ) ) {
					return get_post_field( 'post_title', $top_parent );
				}
				else {
					return 'Page';
				}
			}
		}
		else {
			return 'Stories';
		}
	}

	/**
	 * Determine content group for current page
	 *
	 * @uses is_home, is_front_page, is_search, is_single
	 * @return string
	 */
	private function get_content_group() {
		if ( is_home() || is_front_page() ) {
			return 'Homepage';
		}
		elseif ( is_search() ) {
			return 'Search';
		}
		elseif ( is_single() ) {
			return 'Article';
		}
		else {
			return '';
		}
	}

	/**
	 * Determine subfeature for current page
	 *
	 * @global $post
	 * @uses is_page, is_single
	 * @return string
	 */
	private function get_site_subfeature() {
		if ( is_page() ) {
			return $GLOBALS['post']->post_title;
		}
		elseif ( is_single() ) {
			return 'Top Stories';
		}
		else {
			return '';
		}
	}

	/**
	 * Determine secondary subfeature for current page
	 *
	 * @uses is_atx, single_term_title, is_category, single_cat_title, is_tag, single_tag_title, is_single, thegrio_get_primary_category
	 * @return string
	 */
	private function get_secondary_site_subfeature() {
		if ( is_tax() ) {
			return single_term_title( '', false );
		}
		elseif ( is_category() ) {
			return single_cat_title( '', false );
		}
		elseif ( is_tag() ) {
			return single_tag_title( '', false );
		}
		elseif ( is_single() ) {
			return ucfirst( thegrio_get_primary_category() );
		}
		else {
			return '';
		}
	}

	/**
	 * Determine author string for current page
	 *
	 * @uses is_single, thegrio_get_byline, get_the_ID, thegrio_get_custom_user_meta, get_the_author_meta
	 * @return string
	 */
	private function get_author() {
		if ( is_single() ) {
			if ( $byline = thegrio_get_byline( get_the_ID() ) ) {
				return $byline;
			}
			else {
				return thegrio_get_custom_user_meta( get_the_author_meta( 'ID' ), 'display_name' );
			}
		}
		else {
			return '';
		}
	}
}
thegrio_singleton( 'TheGrio_Omniture' );

/**
 * Share Buttons
 * @return html
 */
function thegrio_share_buttons(){
		?>
		<div class="post-share-buttons" align="center">
			<a href="http://www.facebook.com/sharer/sharer.php?<?php echo esc_url( get_permalink() ); ?>">
				<img src="<?php echo esc_url( get_stylesheet_directory_uri() ) . '/images/FB_Share_Btn1.png'; ?>" width="310" height="53" alt="Share The Grio"/>
			</a>
			<a href="https://twitter.com/share?url=<?php echo esc_url( get_permalink() ); ?>">
				<img src="<?php echo esc_url( get_stylesheet_directory_uri() ) . '/images/TW_Share_Btn1.png'; ?>" width="310" height="53" alt="Share The Grio"/>
			</a>
		</div>
		<?php
}

/**
 * Related Posts
 * @uses wpcom_vip_get_flaptor_related_posts
 *       In non-WP.com environments, the function uses tags.  On WP.com, it uses the Flaptor indexing.
 * @return html
 */
function thegrio_related_posts(){
	$output = '';
	if ( function_exists( 'wpcom_vip_get_flaptor_related_posts' ) ){
		$related = wpcom_vip_get_flaptor_related_posts( 5, '', true );
		if ( ! empty( $related ) ) {
			$output .= '<div id="thegrio-related" class="widgetcontainer">';
			$output .= '<span class="border-gradient"></span>';
			$output .= '<h3 class="widgettitle">Related Posts</h3>';
			$output .= '<ul class="related-posts-content">';
			foreach ( $related as $key => $post ) {
				$output .= '<li><a href="'. $post['url'] .'" rel="bookmark">'. $post['title'] .'</a></li>';
			}
			$output .= '</ul>';
			$output .= '</div>';
		}
	}
	return $output;
}

/**
 * Filter function for the_content to place post thumbnail into content before
 * all filters are run. Allows SEO Friendly Images to run on featured image.
 *
 * @uses get_the_post_thumbnail, get_the_ID, thegrio_get_post_thumbnail_caption, wp_kses
 * @return string
 */
function thegrio_embed_post_thumbnail($content) {
	$caption_allowed_html = array(
		'p' => array(
			'class' => array()
		)
	);

	// making sure nothing funny slipped into the caption field
	$caption = wp_kses( thegrio_get_post_thumbnail_caption(), $caption_allowed_html );

	$image_content = '';

	$image_content .= '<div class="the-post-thumbnail-wrapper" id="embedded-thumbnail">';
	$image_content .= '<div class="the-post-thumbnail">';
	$image_content .= get_the_post_thumbnail( get_the_ID(), 'single' );
	$image_content .= $caption;
	$image_content .= '</div>';
	$image_content .= '</div>';

	// Insert related posts section so layout is not lost
	if ( function_exists( 'thegrio_related_posts' ) ) {
		$image_content .= thegrio_related_posts();
	}

	// Insert share buttons under post thumbnail
	if ( function_exists( 'thegrio_share_buttons' ) ) {
		$share_buttons = thegrio_share_buttons();
	}

	return $image_content . $share_buttons . $content;
}

/*
 * Taboola header script
 */
function grio_taboola_header() {
	$page_view = 'page_view';
	if ( is_single() && 'gallery' == get_post_format()  ) {
		$page_view = 'photo_page_view';
	} elseif ( is_single() && 'video' == get_post_format() ) {
		$page_view = 'video_page_view';
	} elseif ( is_single() ) {
		$page_view = 'article_page_view';
	} elseif ( is_home() || is_front_page() ) {
		$page_view = 'home_page_view';
	} elseif ( is_search() ) {
		$page_view = 'search_page_view';
	} elseif ( is_archive() ) {
		$page_view = 'category_page_view';
	}
	?>
	<script type="text/javascript">
	window._tfa = window._tfa || [];
	_tfa.push({
		notify:"action",
		name: <?php echo json_encode( $page_view ); ?>
	});
	</script>
	<?php
}

add_action( 'wp_head', 'grio_taboola_header' );
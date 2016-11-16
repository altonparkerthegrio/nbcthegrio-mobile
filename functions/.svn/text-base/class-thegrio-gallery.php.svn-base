<?php
/**
 * TheGrio Gallery
 * When a user adds images to a post and selects the gallery format the gallery slideshow will be created.
 */

class TheGrio_Gallery {
	/**
	 * Class Variables
	 */
	var $image_size = 'thegrio_gallery';
	var $shortcode_tag = 'thegrio_gallery';

	var $cache_key = 'thegrio_gallery_';

	/**
	 * Register actions and shortcode
	 * @uses add_shortcode, add_action
	 * @return null
	 */
	function __construct() {
		add_shortcode( $this->shortcode_tag, array( $this, 'do_shortcode' ) );

		add_action( 'wp_enqueue_scripts', array( $this, 'action_wp_enqueue_scripts' ) );
		add_action( 'after_setup_theme', array( $this, 'action_after_setup_theme' ), 11 );

		add_action( 'transition_post_status', array( $this, 'action_transition_post_status' ), 20, 3 );
		add_action( 'edit_attachment', array( $this, 'action_edit_attachment' ), 20 );
	}

	/**
	 * Enqueue gallery scripts if viewing a post that needs them
	 *
	 * @uses is_singular, get_post_format, get_post_field, get_the_ID, wp_enqueue_script, get_stylesheet_directory_uri
	 * @action wp_enqueue_scripts
	 * @return null
	 */
	function action_wp_enqueue_scripts() {
		if ( is_singular() && ( 'gallery' == get_post_format() || false !== strpos( get_post_field( 'post_content', get_the_ID() ) , $this->shortcode_tag ) ) ) {
			wp_enqueue_script( 'thegrio-iframe-autoheight' );
			wp_enqueue_script( 'jquery-cycle' );
			wp_enqueue_script( 'thegrio-gallery' );
			wp_enqueue_style( 'thegrio-gallery' );
		}
	}

	/**
	 * Register image size
	 * @uses add_image_size
	 * @action after_setup_theme
	 * @return null
	 */
	function action_after_setup_theme() {
		add_image_size( $this->image_size, 560, 99999, false );
	}

	/**
	 * Clear cache when post is updated
	 *
	 * @param string $new_status
	 * @param string $old_status
	 * @param object $post
	 * @uses wp_cache_delete
	 * @action transition_post_status
	 * @return null
	 */
	function action_transition_post_status( $new_status, $old_status, $post ) {
		if ( in_array( 'publish', array( $new_status, $old_status ) ) && 'post' == $post->post_type ) {
			wp_cache_delete( $this->cache_key . $post->ID, $GLOBALS['thegrio_cache_group'] );
		}
	}

	/**
	 * Clear cache when an attachment is updated
	 *
	 * @param int $post_id
	 * @uses get_post_field, wp_cache_delete
	 * @action edit_attachment
	 * @return null
	 */
	function action_edit_attachment( $post_id ) {
		$attachment_parent = get_post_field( 'post_parent', $post_id );

		if ( 0 < $attachment_parent ) {
			wp_cache_delete( $this->cache_key . $attachment_parent, $GLOBALS['thegrio_cache_group'] );
		}
	}

	/**
	 * Render gallery
	 *
	 * @param string $post_id
	 * @global $post
	 * @uses wp_cache_get, is_preview, get_post, WP_Query, remove_filter, esc_attr, wp_get_attachment_image, apply_filters, add_filter, wp_reset_query, wp_cache_set
	 * @return string or false
	 */
	function render_gallery( $post_id ) {
		$post_id = intval( $post_id );

		if ( ! $post_id ) {
			$post_id = $GLOBALS['post']->ID;
		}

		if ( false == ( $output = wp_cache_get( $this->cache_key . $post_id, $GLOBALS['thegrio_cache_group'] ) ) || is_preview() ) {
			$output = '';

			$gallery_parent = get_post( $post_id );

			if ( is_object( $gallery_parent ) && ( 'publish' == $gallery_parent->post_status || is_preview() ) ) {
				$gallery_members = new WP_Query( array(
					'post_type' => 'attachment',
					'post_status' => 'inherit',
					'post_parent' => $post_id,
					'posts_per_page' => '50',
					'orderby' => 'menu_order',
					'order' => 'ASC',
					'cache_results' => true,
					'update_post_term_cache' => false,
					'no_found_rows' => true,
				) );

				if ( $gallery_members->have_posts() ) {
					//Remove filter that appends attachment images to post content
					remove_filter( 'the_content', 'prepend_attachment' );

					global $post;

					$output = '<div class="thegrio-gallery" id="thegrio-gallery-' . esc_attr( $post_id ) . '">';
					$output .= '<div class="thegrio-gallery-pager">';
					$output .= '<a class="previous" onclick="refreshFirstSlot();refreshAdLeftsidebar();refreshAdRightsidebar();" href="#">&laquo; Previous</a><!-- .previous -->';
					$output .= '<a class="next" onclick="refreshFirstSlot();refreshAdLeftsidebar();refreshAdRightsidebar();" href="#">Next &raquo;</a><!-- .next -->';
					$output .= '</div> <!-- .thegrio-gallery-pager -->';
					$output .= '<div class="cycle-wrapper">';

					while ( $gallery_members->have_posts() ) {
						$gallery_members->the_post();

						//Get image tag. If empty, continue to next slide
						$image = wp_get_attachment_image( $post->ID, $this->image_size, false );

						if ( ! is_string( $image ) || 0 == strlen( $image ) ) {
							continue;
						}

						//Open slide
						$output .= '<div class="cycle-slide" id="' . esc_attr( $post->post_name ) . '">';

						//Image tag
						$output .= '<div class="thegrio-gallery-image">' . $image . '</div><!-- .thegrio-gallery-image -->';

						//Caption
						$output .= '<div class="caption">';
						$output .= apply_filters( 'the_excerpt', $post->post_excerpt );
						$output .= '</div> <!-- .caption -->';

						//Close slide
						$output .= '</div><!-- .cycle-slide#' . esc_attr( $post->post_name ) . ' -->';
					}

					//Close cycle wrapper
					$output .= '</div><!-- .cycle-wrapper -->';

					//Open-Close Count
					$output .= '<div class="count"><span>-</span> of ' . intval( $gallery_members->post_count ) . '</div><!-- .count -->';
					$output .= '</div> <!-- .thegrio-gallery #thegrio-gallery-'. esc_attr( $post_id ) .' -->';

					//Restore filter that appends attachment images to post content
					add_filter( 'the_content', 'prepend_attachment' );

					wp_reset_postdata();
				}
			}

			wp_cache_set( $this->cache_key . $post_id, $output, $GLOBALS['thegrio_cache_group'], 604800 );
		}

		return isset( $output ) && ! empty( $output ) ? $output : false;
	}

	/**
	 * Gallery Shortcode
	 * @uses in_the_loop, this::render_gallery, get_the_id
	 * @return string or false
	 */
	function do_shortcode() {
		if ( ! in_the_loop() ) {
			return false;
		}
		else {
			return $this->render_gallery( get_the_ID() );
		}
	}
}
thegrio_singleton( 'TheGrio_Gallery' );
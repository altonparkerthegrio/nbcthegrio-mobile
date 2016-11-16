<?php
/**
 ** THEGRIO TEMPLATE QUERIES
 ** Used primarily for homepage and primary category layouts, though two sitewide sidebar elements are also generated herein.
 **/
class TheGrio_Template_Post_Queries {
	/**
	 * Class variables
	 */
	private static $excluded_posts = array(); //NBCNEWS-335: Staticize exclusion array to avoid duplicates when multiple rendering functions reference same variable.

	private $incrementor = null;

	private $taxonomy_specials = 'specials';

	//Cache keys - if any are added, this::action_transition_post_status() must be updated
	private $cache_key_lead_story = 'thegrio_lead_story_';
	private $cache_key_top_story = 'thegrio_top_story_';
	private $cache_key_sidebar_story = 'thegrio_sidebar_story_';
	private $cache_key_around_thegrio = 'thegrio_around_thegrio_';
	private $cache_key_opinions = 'thegrio_opinions_';
	private $cache_key_top_news = 'thegrio_top_news_';
	private $cache_key_latest_news_story = 'thegrio_latest_news_story_';
	private $cache_key_latest_news_sidebar = 'thegrio_latest_news_sidebar_';
	private $cache_key_special_lead_story = 'thegrio_specials_lead_story_';
	private $cache_key_subcategory_lead_story = 'thegrio_subcategory_lead_story_';

	private $cache_key_specials_lead_story_ids = 'thegrio_specials_lead_story_ids';
	private $specials_lead_story_ids = null;
	private $cache_key_category_lead_story_ids = 'thegrio_subcategory_lead_story_ids';
	private $category_lead_story_ids = null;

	private $cache_key_around_thegrio_single = 'thegrio_around_thegrio_single';
	private $cache_key_incrementor = 'thegrio_template_queries_incrementor';

	private $meta_key_primary_category = null;

	var $get_page_content;

	/**
	 * Register action
	 *
	 * @uses add_action
	 * @return null
	 */
	public function __construct() {
		add_action( 'transition_post_status', array( $this, 'action_transition_post_status' ), 100, 3 );
		add_action( 'pre_get_posts', array( $this, 'action_pre_get_posts' ) );

		$this->meta_key_primary_category = thegrio_singleton( 'TheGrio_Primary_Category' )->meta_key;

		add_action( 'wp_before_admin_bar_render', array( $this, 'add_admin_bar_cache_clear_button' ) );
		add_action( 'init', array( $this, 'admin_clear_cache' ) );
		add_action( 'admin_notices', array( $this, 'admin_notices' ) );


		add_filter( 'infinite_scroll_excluded_ids', array( $this, 'get_excluded_ids' ) );
	}

	/**
	 * Clear caches in this module by popping incrementor
	 */
	function admin_clear_cache( $force = false ) {
		if( current_user_can( 'edit_posts' ) && ( isset( $_GET['thegrio_cache_clear'] ) || true === $force ) ) {
			$this->get_cache_incrementor( true );
			update_option( 'cache_clear_admin_notice', true );
			do_action( 'thegrio_admin_clear_cache' );
		}
	}

	function admin_notices() {
		if( current_user_can( 'edit_posts' ) && get_option( 'cache_clear_admin_notice' ) ) { ?>
			<div class="updated"><p>Cleared front page post caches</p></div>
		<?php
			delete_option( 'cache_clear_admin_notice' );
		}
	}

	function add_admin_bar_cache_clear_button() {
		global $wp_admin_bar;

		if( !current_user_can( 'edit_posts' ) )
			return;

		$wp_admin_bar->add_menu( array(
			'id' => 'thegrio_template_post_cache_clear',
			'title' => 'Clear Caches',
			'href' => '?thegrio_cache_clear'
		) );
	}

	/**
	 * Clear caches generated within this class and prime updated versions.
	 * Clears and primes both the homepage and primary-category archive caches, as well as the single page cache and caches for any Specials assigned to the current post.
	 *
	 * @param string $new_status
	 * @param string $old_status
	 * @param object $post
	 * @uses WP_IMPORTING, this::get_page_content, thegrio_get_primary_categories, get_the_category, wp_list_pluck, this::setup_taxonomy_lead_story_ids_exclusion, this::subcategory_lead_story, this::store_taxonomy_lead_story_ids_exclusion, this::purge_taxonomy_lead_story_ids_exclusion, get_the_terms, this::specials_lead_story, wp_cache_set
	 * @action transition_post_status
	 * @return null
	 */
	public function action_transition_post_status( $new_status, $old_status, $post ) {
		//Don't run this when importing, otherwise the import process takes forever.
		if ( defined( 'WP_IMPORTING' ) && WP_IMPORTING )
			return;

		//Rebuild caches if proper conditions are met.
		if ( in_array( 'publish', array( $new_status, $old_status ) ) && 'post' == $post->post_type ) {
			//Generate a new incrementor without setting in cache. Value will be cached after content is rendered.
			$this->incrementor = time();

			//First, regenerate homepage
			$this->get_page_content( 'home' );

			//Second, regenerate cache used on single pages
			$this->get_page_content( 0 );

			//Third, regenerate all primary-category archives with new cache incrementor value.
			$primary_categories = thegrio_get_primary_categories( true );

			if ( is_array( $primary_categories ) && ! empty( $primary_categories ) ) {
				foreach ( $primary_categories as $primary_category ) {
					$this->get_page_content( (int) $primary_category );
				}
			}

			//Fourth, regenerate any associated subcategory lead story caches, as well as cached lead story IDs
			$categories = get_the_category( $post->ID );
			$categories = wp_list_pluck( $categories, 'term_id' );
			$categories = array_diff( $categories, $primary_categories );

			if ( ! empty( $categories ) ) {
				$this->setup_taxonomy_lead_story_ids_exclusion( 'category' );

				foreach ( $categories as $category ) {
					$this->subcategory_lead_story( (int) $category, true );
				}

				$this->store_taxonomy_lead_story_ids_exclusion( 'category' );
			}

			$this->purge_taxonomy_lead_story_ids_exclusion( 'category' );

			//Lastly, refresh any associated Specials caches, as well as cached lead story IDs
			$specials = get_the_terms( $post->ID, $this->taxonomy_specials );

			if ( is_array( $specials ) ) {
				$this->setup_taxonomy_lead_story_ids_exclusion( $this->taxonomy_specials );

				foreach ( $specials as $special ) {
					$this->specials_lead_story( (int) $special->term_id, true );
				}

				$this->store_taxonomy_lead_story_ids_exclusion( $this->taxonomy_specials );
			}

			$this->purge_taxonomy_lead_story_ids_exclusion( $this->taxonomy_specials );

			//Cache new incrementor
			if ( ! is_null( $this->incrementor ) )
				wp_cache_set( $this->cache_key_incrementor, $this->incrementor, $GLOBALS[ 'thegrio_cache_group' ], 3600 );

			//Reset incrementor, just to be safe
			$this->incrementor = null;
		}
	}

	/**
	 * Retrieve various elements of the current page and, if necessary, prime the caches.
	 * Necessary because a single post should never appear more than once on a given page, and some page elements are output in other template files.
	 *
	 * @uses this::lead_story, this::top_story, this::sidebar_story, this::around_thegrio, this::opinions, this::latest_news_story, this::latest_news_sidebar, this::top_news, wp_parse_args
	 * @return array
	 */
	public function get_page_content( $context = null ) {

		if( ! empty( $this->get_page_content ) ) {
			return $this->get_page_content;
		}

		//Validate context
		$context = is_int( $context ) || 'home' == $context ? $context : null;

		//No post should ever appear more than once on a given page
		self::$excluded_posts = array();

		//Start with the array we're building, which will be populated with data from caches
		$content = array();

		//Consistent elements found on all pages using this function
		$content[ 'lead_story' ] = $this->lead_story( $context );
		$content[ 'top_story' ] = $this->top_story( $context );
		$content[ 'sidebar_story' ] = $this->sidebar_story( $context );

		//Contextual additions
		if ( is_home() || is_front_page() || in_array( $context, array( 'home', 0 ) ) )
			$content[ 'around_thegrio' ] = $this->around_thegrio( $context );

		//Consistent elements found on all pages using this function
		$content[ 'opinions' ] = $this->opinions( $context );
		$content[ 'latest_news' ] = $this->latest_news_story( $context );
		$content[ 'latest_news_sidebar' ] = $this->latest_news_sidebar( $context );
		$content[ 'top_news' ] = $this->top_news( $context );

		//This populates the excluded IDS from the infinity scroller into a site option if its not empty
		if( count( self::$excluded_posts ) )
			update_option( 'thegrio_excluded_posts_', self::$excluded_posts );

		$this->get_page_content = wp_parse_args( $content, array(
			'lead_story' => '',
			'top_story' => '',
			'sidebar_story' => '',
			'around_thegrio' => '',
			'opinions' => '',
			'latest_news' => '',
			'latest_news_sidebar' => '',
			'top_news' => ''
		) );

		//Always return an array containing all possibilities
		return $this->get_page_content;
	}

	/**
	 * Get context-specific cache key part
	 *
	 * @param mixed $context
	 * @uses this::get_cache_incrementor, is_singular, get_term_field, thegrio_is_primary_category, get_query_var
	 * @return string
	 */
	private function template_cache_key_part( $context ) {
		$part = '';

		//Incrementor
		if ( ! is_null( $this->incrementor ) )
			$part .= $this->incrementor . '_';
		else
			$part .= $this->get_cache_incrementor() . '_';

		//Contextual element
		if ( 0 === $context || is_singular() )
			$part .= 0;
		elseif ( is_int( $context ) )
			$part .= get_term_field( 'slug', $context, 'category' );
		elseif ( thegrio_is_primary_category() )
			$part .= get_query_var( 'category_name' );
		else
			$part .= 'home';

		return $part;
	}

	/**
	 * Determine if request is for a primary category
	 *
	 * @param mixed $context
	 * @uses thegrio_is_primary_category
	 * @return bool
	 */
	private function is_primary_category( $context ) {
		return is_int( $context ) || thegrio_is_primary_category();
	}

	/**
	 * Determine term ID for given request
	 *
	 * @param mixed $context
	 * @uses thegrio_is_primary_category, get_queried_object_id
	 * @return int
	 */
	private function get_primary_category_term_id( $context ) {
		if ( is_int( $context ) )
			return $context;
		elseif ( thegrio_is_primary_category() )
			return (int) get_queried_object_id();
		else
			return 0;
	}

	/**
	 * Determine term slug for given request
	 * Needed when querying posts in same primary category when on a primary category archive.
	 *
	 * @param mixed $context
	 * @uses this::get_primary_category_term_id, get_term_field
	 * @return string
	 */
	private function get_primary_category_term_slug( $context ) {
		$term_id = $this->get_primary_category_term_id( $context );

		if ( 0 == $term_id )
			return md5( 'NoTermFound' );
		else
			return get_term_field( 'slug', (int) $term_id, 'category' );
	}

	/**
	 * Get or reset cache incrementor
	 *
	 * @param bool $force_refresh
	 * @uses wp_cache_get, wp_cache_set
	 * @return int
	 */
	private function get_cache_incrementor( $force_refresh = false ) {
		//If incrementor set in class, use that
		if ( ! is_null( $this->incrementor ) )
			return $this->incrementor;

		//Otherwise, check the cache
		$incrementor = wp_cache_get( $this->cache_key_incrementor, $GLOBALS[ 'thegrio_cache_group' ] );

		if ( false == $incrementor || (bool) $force_refresh ) {
			$incrementor = time();
			wp_cache_set( $this->cache_key_incrementor, $incrementor, $GLOBALS[ 'thegrio_cache_group' ], 3600 );
		}

		return $incrementor;
	}

	/**
	 * Render Lead Story
	 * Content herein is specific to the context in which it appears.
	 *
	 * @global $post
	 * @param mixed $context
	 * @uses this::template_cache_key_part, wp_cache_get, this::is_primary_category, this::get_primary_category_term_slug, WP_Query, get_the_ID, the_ID, post_class, the_permalink, the_title_attribute, has_post_thumbnail, the_post_thumbnail, thegrio_default_image, thegrio_get_short_title, thegrio_short_title, the_title, thegrio_river_meta, wp_reset_query, wp_cache_set
	 * @return string
	 */
	 private function lead_story( $context ) {

		$cache_key = md5( $this->cache_key_lead_story . $this->template_cache_key_part( $context ) );
		$excluded_posts = array();

		if ( false === ( $values = wp_cache_get( $cache_key, $GLOBALS[ 'thegrio_cache_group' ] ) ) || ! is_array( $values ) ) {
			ob_start();

			global $post;

			// The Query
			$query = array(
				'posts_per_page' => count( self::$excluded_posts ) + 1,
				'post_type' => 'post',
				'post_status' => 'publish',
				'orderby' => 'date',
				'order' => 'DESC',
				'cache_results' => true,
				'no_found_rows' => true
			);

			if ( $this->is_primary_category( $context ) ) {
				$query[ 'meta_query' ] = array(
					array(
						'key' => 'category_lead_story',
						'value' => 'on'
					),
					array(
						'key' => $this->meta_key_primary_category,
						'value' => $this->get_primary_category_term_slug( $context )
					),
					'relation' => 'AND'
				);
			}
			else {
				$query[ 'meta_query' ] = array( array(
					'key' => 'lead_story',
					'value' => 'on'
				) );
			}
			$term_id = ( $this->is_primary_category( $context ) ) ? $this->get_primary_category_term_slug( $context ) : 'home';
			do_action( 'vr_before_query', $term_id, 'lead_story' );
			$the_query = new WP_Query( $query );
			do_action( 'vr_after_query' );

			if ( $the_query->have_posts() ) :
				$posts_shown = 0;

				while ( $the_query->have_posts() ) : $the_query->the_post();
					//Don't show more than one post
					if ( 1 <= $posts_shown )
						break;

					//Prevent future queries from returning this post
					if ( in_array( get_the_ID(), self::$excluded_posts ) )
						continue;

					$excluded_posts[] = get_the_ID();
				?>
				<article id="post-<?php the_ID(); ?>" <?php post_class(); ?> data-vr-contentbox="">
					<header class="entry-header">
						<div class="the-post-thumbnail">
							<a href="<?php the_permalink(); ?>" title="Permalink to <?php the_title_attribute(); ?>" rel="bookmark">
								<?php
									if ( $this->is_primary_category( $context ) ) {
										if ( has_post_thumbnail() )
											the_post_thumbnail( 'single' );
										else
											thegrio_default_image( 650 );
									}
									else {
										if ( has_post_thumbnail() )
											the_post_thumbnail( 'lead-story' );
										else
											thegrio_default_image( 420 );
									}
								 ?>
							</a>
						</div>
					</header><!-- .entry-header -->
					<div class="entry-summary-wrapper">
						<div class="entry-summary">
							<h2 class="entry-title">
								<a href="<?php the_permalink(); ?>" title="Permalink to <?php the_title_attribute(); ?>" rel="bookmark"><?php
									if ( thegrio_get_short_title() )
										thegrio_short_title();
									else
										the_title();
								?></a>
							</h2>
							<footer class="entry-footer">
								<div class="entry-meta">
									<?php thegrio_river_meta(); ?>
									
								</div><!-- .entry-meta -->
							</footer>
						</div><!-- .entry-summary -->
					</div><!-- .entry-summary-wrapper -->
				</article>
				<?php
					$posts_shown++;
				endwhile;
			endif;

			// Reset main query and unset custom query
			wp_reset_query();

			unset( $the_query );

			// capture and cache output from buffer
			$content = ob_get_clean();
			wp_cache_set( $cache_key, array( $content, $excluded_posts ), $GLOBALS[ 'thegrio_cache_group' ], 3660 );
		}
		else {
			list( $content, $excluded_posts ) = $values;
		}

		self::$excluded_posts = array_merge( self::$excluded_posts, $excluded_posts );
		return $content;
	}

	/**
	 * Render Top Story
	 * Content herein is specific to the context in which it appears.
	 *
	 * @global $post
	 * @param mixed $context
	 * @uses this::template_cache_key_part, wp_cache_get, this::is_primary_category, this::get_primary_category_term_slug, WP_Query, get_the_ID, the_permalink, the_title_attribute, thegrio_get_short_title, thegrio_short_title, the_title, wp_reset_query, wp_cache_set
	 */
	 private function top_story( $context ) {
		$cache_key = md5( $this->cache_key_top_story . $this->template_cache_key_part( $context ) );
		$excluded_posts = array();

		if ( false === ( $values = wp_cache_get( $cache_key, $GLOBALS[ 'thegrio_cache_group' ] ) ) || ! is_array( $values ) ) {
			ob_start();

			global $post;

			// The Query
			$query = array(
				'posts_per_page' => count( self::$excluded_posts ) + 7, //need 7
				'post_type' => 'post',
				'post_status' => 'publish',
				'orderby' => 'date',
				'order' => 'DESC',
				'cache_results' => true,
				'no_found_rows' => true
			);

			if ( $this->is_primary_category( $context ) ) {
				$query[ 'meta_query' ] = array(
					array(
						'key' => 'category_top_story',
						'value' => 'on'
					),
					array(
						'key' => $this->meta_key_primary_category,
						'value' => $this->get_primary_category_term_slug( $context )
					),
					'relation' => 'AND'
				);
			}
			else {
				$query[ 'meta_query' ] = array( array(
					'key' => 'top_story',
					'value' => 'on'
				) );
			}

			$term_id = ( $this->is_primary_category( $context ) ) ? $this->get_primary_category_term_slug( $context ) : 'home';
			do_action( 'vr_before_query', $term_id, 'top_story' );
			$the_query = new WP_Query( $query );
			do_action( 'vr_after_query' );

			// The Loop
			if ( $the_query->have_posts() ) :
				$posts_shown = 0;

				?> <div id="vr-zone-Top-Stories" class="vr-automation-module"> <?php
				while ( $the_query->have_posts() ) : $the_query->the_post();

					//Don't show more than one post
					if ( ( $this->is_primary_category( $context ) && 4 <= $posts_shown ) || 7 <= $posts_shown )
						break;

					//Prevent future queries from returning this post
					if ( in_array( get_the_ID(), self::$excluded_posts ) )
						continue;

					$excluded_posts[] = get_the_ID();
				?>
				<div class="cat-top-story" data-vr-contentbox="">
				<?php if ( has_post_thumbnail() && $this->is_primary_category( $context ) ) {
					?>
						<div class="cat-top-story-thumb">
							<?php the_post_thumbnail( 'widget-thumb' ); ?>
						</div>
					<?php
					} ?>
					<h2 class="entry-title">
							<a class="vr-title" href="<?php the_permalink(); ?>" title="Permalink to <?php the_title_attribute(); ?>" rel="bookmark"><?php
							if ( thegrio_get_short_title() )
								strtoupper(thegrio_short_title());
							else
								strtoupper(the_title());
						?></a>
					</h2>
				</div>
				<?php
					$posts_shown++;
				endwhile;
				?> </div> <?php
			endif;

			// Reset main query and unset custom query
			wp_reset_query();

			unset( $the_query );

			// capture and cache output from buffer
			$content = ob_get_clean();
			wp_cache_set( $cache_key, array( $content, $excluded_posts ), $GLOBALS[ 'thegrio_cache_group' ], 3660 );
		}
		else {
			list( $content, $excluded_posts ) = $values;
		}

		self::$excluded_posts = array_merge( self::$excluded_posts, $excluded_posts );
		return $content;
	}

	/**
	 * Render Sidebar Lead Story
	 * Content herein is universal within site.
	 *
	 * @global $post
	 * @param mixed $context
	 * @uses this::template_cache_key_part, wp_cache_get, WP_Query, get_the_ID, the_ID, post_class, the_permalink, the_title_attribute, has_post_thumbnail, the_post_thumbnail, thegrio_default_image, thegrio_primary_category_tag, thegrio_get_short_title, thegrio_short_title, the_title, thegrio_river_meta, wp_reset_query, wp_cache_set
	 * @return string
	 */
	private function sidebar_story( $context ) {
		$cache_key = md5( $this->cache_key_sidebar_story . $this->template_cache_key_part( $context ) );
		$excluded_posts = array();

		if ( false === ( $values = wp_cache_get( $cache_key, $GLOBALS[ 'thegrio_cache_group' ] ) ) || ! is_array( $values ) ) {
			ob_start();

			global $post;

			$term_id = ( $this->is_primary_category( $context ) ) ? $this->get_primary_category_term_slug( $context ) : 'home';
			do_action( 'vr_before_query', $term_id, 'sidebar_story' );
			// The Query
			$the_query = new WP_Query( array(
				'posts_per_page' => count( self::$excluded_posts ) + 1, //need 1
				'post_type' => 'post',
				'post_status' => 'publish',
				'orderby' => 'date',
				'order' => 'DESC',
				'cache_results' => true,
				'no_found_rows' => true,
				'meta_query' => array( array(
					'key' => 'sidebar_story',
					'value' => 'on'
				) )
			) );
			do_action( 'vr_after_query' );

			if ( $the_query->have_posts() ) :
				echo '<ul>';

				$posts_shown = 0;

				// The Loop
				while ( $the_query->have_posts() ) : $the_query->the_post();
					//Don't show more than one post
					if ( 1 <= $posts_shown )
						break;

					//Prevent future queries from returning this post
					if ( in_array( get_the_ID(), self::$excluded_posts ) )
						continue;

					$excluded_posts[] = get_the_ID();

					?>
					<article id="post-<?php the_ID(); ?>" <?php post_class(); ?> data-vr-contentbox="">
						<header class="entry-header">
							<div class="the-post-thumbnail">
								<a href="<?php the_permalink(); ?>" title="Permalink to <?php the_title_attribute(); ?>" rel="bookmark">
									<?php has_post_thumbnail() ? the_post_thumbnail( 'river-300' ): thegrio_default_image(300); ?>
								</a>
							</div>
							<?php //thegrio_primary_category_tag(); ?>
							<h3 class="entry-title"><a href="<?php the_permalink(); ?>" title="Permalink to <?php the_title_attribute(); ?>" rel="bookmark"><?php
							if ( thegrio_get_short_title() )
								thegrio_short_title();
							else
								the_title();
						?></a></h3>
						</header><!-- .entry-header -->
						<footer class="entry-footer">
							<div class="entry-meta">
								<?php thegrio_river_meta(); ?>
							</div><!-- .entry-meta -->
						</footer>
					</article>
					<?php
					$posts_shown++;
				endwhile;

				echo '</ul>';

			endif;

			// Reset main query and unset custom query
			wp_reset_query();

			unset( $the_query );

			// capture and cache output from buffer
			$content = ob_get_clean();
			wp_cache_set( $cache_key, array( $content, $excluded_posts ), $GLOBALS[ 'thegrio_cache_group' ], 3660 );
		}
		else {
			list( $content, $excluded_posts ) = $values;
		}

		self::$excluded_posts = array_merge( self::$excluded_posts, $excluded_posts );
		return $content;
	}

	/**
	 * Render "Around the Grio"
	 * @param mixed $context
	 * Content herein is universal within site.
	 *
	 * @global $post
	 * @uses this::template_cache_key_part, wp_cache_get, get_the_ID, this::render_around_thegrio_item, wp_reset_query, wp_cache_set
	 * @return string
	 */
	private function around_thegrio( $context ) {
		$cache_key = md5( $this->cache_key_around_thegrio . $this->template_cache_key_part( $context ) );
		$excluded_posts = array();

		if ( false === ( $values = wp_cache_get( $cache_key, $GLOBALS[ 'thegrio_cache_group' ] ) ) || ! is_array( $values ) ) {
			ob_start();

			global $post;

			$term_id = ( $this->is_primary_category( $context ) ) ? $this->get_primary_category_term_slug( $context ) : 'home';
			do_action( 'vr_before_query', $term_id, 'around_thegrio' );
			// The Query
			$the_query = new WP_Query( array(
				'posts_per_page' => count( self::$excluded_posts ) + 4, //Need four
				'post_type' => 'post',
				'post_status' => 'publish',
				'orderby' => 'date',
				'order' => 'DESC',
				'cache_results' => true,
				'no_found_rows' => true,
				'meta_query' => array( array(
					'key' => 'filmstrip_story',
					'value' => 'on'
				) )
			) );
			do_action( 'vr_after_query' );

			if ( $the_query->have_posts() ) :
				$posts_shown = 0;
				?>
				<ul id="around-the-grio" class="clearfix" data-vr-zone="Around theGrio">

				<?php
				// The Loop
				while ( $the_query->have_posts() ) : $the_query->the_post();
					//Don't show more than one post
					if ( 4 <= $posts_shown )
						break;

					//Prevent future queries from returning this post
					if ( in_array( get_the_ID(), self::$excluded_posts ) )
						continue;

					$excluded_posts[] = get_the_ID();

					$this->render_around_thegrio_item( $context );

					$posts_shown++;
				endwhile;
				?>
				</ul>
			<?php endif;

			// Reset main query and unset custom query
			wp_reset_query();

			unset( $the_query );

			// capture and cache output from buffer
			$content = ob_get_clean();
			wp_cache_set( $cache_key, array( $content, $excluded_posts ), $GLOBALS[ 'thegrio_cache_group' ], 3660 );
		}
		else {
			list( $content, $excluded_posts ) = $values;
		}

		self::$excluded_posts = array_merge( self::$excluded_posts, $excluded_posts );
		return $content;
	}

	/**
	 * Render the "Around theGrio" template element for individual posts
	 *
	 * @global $post
	 * @uses is_singular, get_the_ID, this::get_cache_incrementor, wp_cache_get, this::render_around_thegrio_item, wp_reset_query, wp_cache_set
	 * @return string
	 */
	public function around_thegrio_single() {
		if ( ! is_singular() )
			return;

		$current_post_id = get_the_ID();

		$cache_key = md5( $this->cache_key_around_thegrio_single . $this->get_cache_incrementor() . $current_post_id );
		$excluded_posts = array();

		if ( false === ( $content = wp_cache_get( $cache_key, $GLOBALS[ 'thegrio_cache_group' ] ) ) || ! is_array( $values ) ) {
			ob_start();

			global $post;

			$query = new WP_Query( array(
				'posts_per_page' => 5, //Need four, one may be this post
				'post_type' => 'post',
				'post_status' => 'publish',
				'orderby' => 'date',
				'order' => 'DESC',
				'cache_results' => true,
				'no_found_rows' => true,
				'meta_query' => array( array(
					'key' => 'filmstrip_story',
					'value' => 'on'
				) )
			) );

			if ( $query->have_posts() ) :
				echo '<ul id="around-the-grio" class="clearfix">';

				$posts_shown = 0;

				while ( $query->have_posts() ) : $query->the_post();
					//Don't show the current post
					if ( get_the_ID() == $current_post_id )
						continue;

					//Don't show more than four post
					if ( 4 <= $posts_shown )
						break;

					$excluded_posts[] = get_the_ID();

					$this->render_around_thegrio_item();

					$posts_shown++;
				endwhile;

				echo '</ul>';

			endif;

			wp_reset_query();

			unset( $query );

			$content = ob_get_clean();
			wp_cache_set( $cache_key, array( $content, $excluded_posts ), $GLOBALS[ 'thegrio_cache_group' ], 3660 );
		}
		else {
			list( $content, $excluded_posts ) = $values;
		}

		self::$excluded_posts = array_merge( (array) self::$excluded_posts, (array) $excluded_posts );
		return $content;
	}

	/**
	 * Render individual stories for display in the "Around theGrio" template element.
	 * Used by this::around_thegrio and this::around_the_grio
	 *
	 * @uses this::template_cache_key_part, the_permalink, the_title_attribute, has_post_thumbnail, the_post_thumbnail, thegrio_default_image, get_post_meta, get_the_ID, thegrio_get_short_title, thegrio_short_title, the_title
	 * @return string
	 */
	private function render_around_thegrio_item( $context = null ) {
	?>
		<li data-vr-contentbox="" >
			<?php if ( is_home() && strpos( get_permalink(), 'living-forward') ) : ?>
				<img src="http://view.atdmt.com/AST/view/410977767/direct/01/" />
			<?php endif; ?>
			<div class="the-post-thumbnail">
				<a href="<?php the_permalink(); ?>" title="Permalink to <?php the_title_attribute(); ?>" rel="bookmark">
					<?php has_post_thumbnail() ? the_post_thumbnail( 'filmstrip' ): thegrio_default_image(144); ?>
				</a>
			</div>

			<?php if ( ! get_post_meta( get_the_ID(), 'filmstrip_hide_overlay', true ) ) : ?>
			<h2 class="post-link-title">
				<a href="<?php the_permalink(); ?>"><?php
					if ( thegrio_get_short_title() )
						thegrio_short_title();
					else
						the_title();
				?></a>
			</h2>
			<?php endif; ?>
		</li>
	<?php
	}

	/**
	 * Render Opinions
	 * Content herein is specific to the context in which it appears.
	 *
	 * @global $post
	 * @param mixed $context
	 * @uses this::template_cache_key_part, wp_cache_get, this::is_primary_category, this::get_primary_category_term_id, get_the_ID, get_post_meta, the_ID, post_class, thegrio_primary_category_tag, the_permalink, the_title_attribute, thegrio_get_short_title, thegrio_short_title, the_title, get_post_type, esc_html, the_author, thegrio_get_avatar, get_the_author_meta, the_advanced_excerpt, thegrio_river_meta, wp_reset_query, wp_cache_set
	 * @return string
	 */
	private function opinions( $context ) {
		//Build cache key
		$cache_key = md5( $this->cache_key_opinions . $this->template_cache_key_part( $context ) );
		$excluded_posts = array();

		//Retrieve content from cache or build
		if ( false === ( $values = wp_cache_get( $cache_key, $GLOBALS[ 'thegrio_cache_group' ] ) ) || ! is_array( $values ) ) {
			ob_start();

			global $post;

			//Build the query based on context requested in
			$query = array(
				'posts_per_page' => count( self::$excluded_posts ) + 6, //Need 6
				'post_type' => 'post',
				'post_status' => 'publish',
				'orderby' => 'date',
				'order' => 'DESC',
				'cache_results' => true,
				'no_found_rows' => true,
				'tax_query' => array( array(
					'taxonomy' => 'category',
					'field' => 'slug',
					'terms' => 'opinion'
				) )
			);

			if ( $this->is_primary_category( $context ) ) {
				$query[ 'tax_query' ][] = array(
					'taxonomy' => 'category',
					'field' => 'id',
					'terms' => $this->get_primary_category_term_id( $context )
				);
				$query[ 'tax_query' ][ 'relation' ] = 'AND';
			}

			$term_id = ( $this->is_primary_category( $context ) ) ? $this->get_primary_category_term_slug( $context ) : 'home';
			do_action( 'vr_before_query', $term_id, 'opinions' );
			$the_opinions_query = new WP_Query( $query );
			do_action( 'vr_after_query' );

			if ( $the_opinions_query->have_posts() ) :
				$posts_shown = 0;

				while ( $the_opinions_query->have_posts() ) : $the_opinions_query->the_post();
					//Don't show more than one post
					if ( 6 <= $posts_shown )
						break;

					//Prevent future queries from returning this post
					if ( in_array( get_the_ID(), self::$excluded_posts ) )
						continue;

					$excluded_posts[] = get_the_ID();

					$byline = get_post_meta( get_the_ID(), 'byline', true );
				?>
				<article id="post-<?php the_ID(); ?>" <?php post_class(); ?> data-vr-contentbox="" >
					<header class="entry-header">
						<?php //thegrio_primary_category_tag(); ?>
						<h3 class="entry-title"><a href="<?php the_permalink(); ?>" title="Permalink to <?php the_title_attribute(); ?>" rel="bookmark"><?php
							if ( thegrio_get_short_title() )
								thegrio_short_title();
							else
								the_title();
						?></a></h3>
						<?php if ( 'post' == get_post_type() ) : ?>
						<div class="entry-meta">
							<?php
								if ( $byline )
									echo esc_html( $byline );
								else
									the_author();
							?>
						</div><!-- .entry-meta -->
						<?php endif; ?>
					</header><!-- .entry-header -->
					<div class="entry-summary clearfix">
						<div class="the-advanced-excerpt"><?php //the_advanced_excerpt('length=130&use_words=0'); ?></div>
					</div><!-- .entry-summary -->
					<footer class="entry-footer">
						<div class="entry-meta">
							<?php thegrio_river_meta(); ?>
						</div><!-- .entry-footer -->
					</footer>
				</article>
				<?php
					$posts_shown++;
				endwhile;
			endif;

			// Reset main query and unset custom query
			wp_reset_query();

			unset( $the_opinions_query );

			// capture and cache output from buffer
			$content = ob_get_clean();
			wp_cache_set( $cache_key, array( $content, $excluded_posts ), $GLOBALS[ 'thegrio_cache_group' ], 3660 );
		}
		else {
			list( $content, $excluded_posts ) = $values;
		}

		self::$excluded_posts = array_merge( self::$excluded_posts, $excluded_posts );
		return $content;
	}

	/**
	 * Render Top News
	 * Content herein is universal within site.
	 *
	 * @global $post
	 * @param mixed $context
	 * @uses this::template_cache_key_part, wp_cache_get, thiss:get_primary_category_term_id, get_category, esc_attr, esc_html, WP_Query, get_the_ID, the_permalink, the_title_attribute, has_post_thumbnail, the_post_thumbnail, thegrio_default_image, thegrio_get_short_title, thegrio_short_title, the_title, esc_url, get_category_link, wp_reset_query, wp_cache_set
	 * @return string
	 */
	private function top_news( $context ) {
		$cache_key = md5( $this->cache_key_top_news . $this->template_cache_key_part( $context ) );
		$excluded_posts = array();

		if ( false === ( $values = wp_cache_get( $cache_key, $GLOBALS[ 'thegrio_cache_group' ] ) ) || ! is_array( $values ) ) {
			ob_start();

			$current_primary_category_id = $this->get_primary_category_term_id( $context );
		?>
			<div id="top-news" data-vr-zone="Top News" >
				<div class="category-heading">
					<h2>Top News</h2>
				</div>
				<?php
				$primary_categories = thegrio_get_primary_categories( true );

				foreach ( $primary_categories as $primary_category_id ) :
					if ( $current_primary_category_id != $primary_category_id ) :
						$category = get_category( $primary_category_id );
						?>

							<div class="top-news-category top-news-<?php echo esc_attr( strtolower( $category->cat_name ) ); ?>">
								<h2 class="top-news-category-title"><?php echo esc_html( $category->cat_name );?></h2>
								<ul class="top-news-category-list">
									<?php
									// The Featured Query

									global $post;

									$featured_query = new WP_Query( array(
										'meta_key' => '_thegrio_primary_category',
										'meta_value' => $category->slug,
										'posts_per_page' => count( array_merge( self::$excluded_posts, $excluded_posts ) ) + 1, //Need one
										'post_type' => 'post',
										'post_status' => 'publish',
										'orderby' => 'date',
										'order' => 'DESC',
										'cache_results' => true,
										'no_found_rows' => true
									) );

									// The Loop
									if ( $featured_query->have_posts() ) :
										$posts_shown = 0;

										while ( $featured_query->have_posts() ) : $featured_query->the_post();
											//Don't show more than one post
											if ( 1 <= $posts_shown )
												break;

											//Prevent future queries from returning this post
											if ( in_array( get_the_ID(), array_merge( self::$excluded_posts, $excluded_posts ) ) )
												continue;

											$excluded_posts[] = get_the_ID();

											?>
											<li class="top-news-category-list-item" data-vr-contentbox="" >
												<div class="the-post-thumbnail">
													<a href="<?php the_permalink(); ?>" title="<?php the_title_attribute(); ?>">
														<?php has_post_thumbnail() ? the_post_thumbnail( 'river-280' ): thegrio_default_image(280); ?>
													</a>
												</div>
												<h3 class="entry-title"><a href="<?php the_permalink(); ?>" title="Permalink to <?php the_title_attribute(); ?>" rel="bookmark"><?php
													if ( thegrio_get_short_title() )
														thegrio_short_title();
													else
														the_title();
												?></a></h3>
											</li>
										<?php
											$posts_shown++;
										endwhile;
									endif;

									wp_reset_query();
									unset( $featured_query );

									// The Subsequent Query
									$the_query = new WP_Query( array(
										'meta_key' => '_thegrio_primary_category',
										'meta_value' => $category->slug,
										'posts_per_page' => count( array_merge( self::$excluded_posts, $excluded_posts ) ) + 3, //Need three
										'post_type' => 'post',
										'post_status' => 'publish',
										'orderby' => 'date',
										'order' => 'DESC',
										'cache_results' => true,
										'no_found_rows' => true
									) );

									// The Subsequent Loop
									if ( $the_query->have_posts() ) :
										$posts_shown = 0;

										while ( $the_query->have_posts() ) : $the_query->the_post();
											//Don't show more than one post
											if ( 3 <= $posts_shown )
												break;

											if ( in_array( get_the_ID(), array_merge( self::$excluded_posts, $excluded_posts ) ) )
												continue;

											//Prevent future queries from returning this post
											$excluded_posts[] = get_the_ID();

											?>
											<li class="top-news-category-list-item" data-vr-contentbox="" >
												<h3 class="entry-title"><a href="<?php the_permalink(); ?>" title="Permalink to <?php the_title_attribute(); ?>" rel="bookmark"><?php
													if ( thegrio_get_short_title() )
														thegrio_short_title();
													else
														the_title();
												?></a></h3>
											</li>
										<?php
											$posts_shown++;
										endwhile;
									endif;

									// Reset main query and unset custom query
									wp_reset_query();
									unset( $the_query );
									?>
								</ul>
								<a class="top-news-read-more"  href="<?php echo esc_url( get_category_link( $primary_category_id ) ); ?>" title="<?php echo esc_attr( $category->cat_name ); ?>">&raquo; Read More in <?php echo esc_html( $category->cat_name );?></a>
							</div>
						<?php
					endif;
				endforeach;
				?>
			</div><!--/top-news-->
	<?php
			// capture and cache output from buffer
			$content = ob_get_clean();
			wp_cache_set( $cache_key, array( $content, $excluded_posts ), $GLOBALS[ 'thegrio_cache_group' ], 3660 );
		}
		else {
			list( $content, $excluded_posts ) = $values;
		}

		self::$excluded_posts = array_merge( self::$excluded_posts, $excluded_posts );
		return $content;
	}

	/**
	 * Render Latest News
	 * Content herein is specific to the context in which it appears.
	 *
	 * @global $post
	 * @param mixed $context
	 * @uses this::template_cache_key_part, wp_cache_get, this::is_primary_category, this::get_primary_category_term_id, WP_Query, get_the_ID, the_ID, post_class, the_permalink, the_title_attribute, has_post_thumbnail, the_post_thumbnail, thegrio_default_image, thegrio_primary_category_tag, thegrio_get_short_title, thegrio_short_title, the_title, the_advanced_excerpt, thegrio_river_meta, wp_reset_query, wp_cache_set
	 * @return string
	 */
	private function latest_news_story( $context ) {
		$cache_key = md5( $this->cache_key_latest_news_story . $this->template_cache_key_part( $context ) );
		$excluded_posts = array();

		if ( false === ( $values = wp_cache_get( $cache_key, $GLOBALS[ 'thegrio_cache_group' ] ) ) || ! is_array( $values ) ) {
			ob_start();
			// The Query
			$posts_per_page = 15; //Need 15 if homepage, 12 if primary category archive

			global $post;

			$query = array(
				'post_type' => 'post',
				'post_status' => 'publish',
				'orderby' => 'date',
				'order' => 'DESC',
				'cache_results' => true,
				'no_found_rows' => true
			);

			if ( $this->is_primary_category( $context ) ) {
				$posts_per_page = 12;
				$query[ 'tax_query' ] = array( array(
					'taxonomy' => 'category',
					'field' => 'id',
					'terms' => $this->get_primary_category_term_id( $context )
				) );
			}

			$query[ 'posts_per_page' ] = count( self::$excluded_posts ) + $posts_per_page;

			$the_latest_news_query = new WP_Query( $query );

			//The Loop
			if ( $the_latest_news_query->have_posts() ) :
				$posts_shown = 0;

				while ( $the_latest_news_query->have_posts() ) : $the_latest_news_query->the_post();
					//Don't show more post than needed
					if ( $posts_per_page <= $posts_shown )
						break;

					if ( in_array( get_the_ID(), self::$excluded_posts ) )
						continue;

					//Prevent future queries from returning this post
					$excluded_posts[] = get_the_ID();
				?>
					<article id="post-<?php the_ID(); ?>" <?php post_class( 'no-excerpt' ); ?> data-vr-contentbox="" >
						<header class="entry-header">
							<div class="the-post-thumbnail">
								<a href="<?php the_permalink(); ?>" title="Permalink to <?php the_title_attribute(); ?>" rel="bookmark">
									<?php has_post_thumbnail() ? the_post_thumbnail( 'river-300' ): thegrio_default_image(300); ?>
								</a>
							</div>
							<?php //thegrio_primary_category_tag(); ?>
							<h3 class="entry-title"><a href="<?php the_permalink(); ?>" title="Permalink to <?php the_title_attribute(); ?>" rel="bookmark"><?php
								if ( thegrio_get_short_title() )
									thegrio_short_title();
								else
									the_title();
							?></a></h3>
						</header><!-- .entry-header -->
						<footer class="entry-footer">
							<div class="entry-meta">
								<?php thegrio_river_meta(); ?>
							</div><!-- .entry-meta -->
						</footer>
					</article>
				<?php
					$posts_shown++;
				endwhile;
			endif;

			// Reset main query and unset custom query
			wp_reset_query();

			unset( $the_latest_news_query );

			// capture and cache output from buffer
			$content = ob_get_clean();
			wp_cache_set( $cache_key, array( $content, $excluded_posts ), $GLOBALS[ 'thegrio_cache_group' ], 3660 );
		}
		else {
			list( $content, $excluded_posts ) = $values;
		}

		self::$excluded_posts = array_merge( self::$excluded_posts, $excluded_posts );
		return $content;
	}

	/**
	 * Render Latest News for sidebar
	 * Content herein is universal within site.
	 *
	 * @global $post
	 * @param mixed $context
	 * @uses this::template_cache_key_part, wp_cache_get, this::is_primary_category, this::get_primary_category_term_id, WP_Query, get_the_ID, the_ID, post_class, the_permalink, the_title_attribute, has_post_thumbnail, the_post_thumbnail, thegrio_default_image, thegrio_primary_category_tag, the_permalink, the_title_attribute, thegrio_get_short_title, thegrio_short_title, the_title, the_advanced_excerpt, thegrio_river_meta, wp_reset_query, wp_cache_set
	 * @return string
	 */
	private function latest_news_sidebar( $context ) {
		$cache_key = md5( $this->cache_key_latest_news_sidebar . $this->template_cache_key_part( $context ) );
		$excluded_posts = array();

		if ( false === ( $values = wp_cache_get( $cache_key, $GLOBALS[ 'thegrio_cache_group' ] ) ) || ! is_array( $values ) ) {
			ob_start();

			global $post;

			// The Query
			$query = array(
				'posts_per_page' => count( self::$excluded_posts ) + 5, //Five needed
				'post_type' => 'post',
				'post_status' => 'publish',
				'orderby' => 'date',
				'order' => 'DESC',
				'cache_results' => true,
				'no_found_rows' => true
			);

			if ( $this->is_primary_category( $context ) ) {
				$query[ 'tax_query' ] = array( array(
					'taxonomy' => 'category',
					'field' => 'id',
					'terms' => $this->get_primary_category_term_id( $context )
				) );
			}

			$the_latest_news_query = new WP_Query( $query );

			if ( $the_latest_news_query->have_posts() ) :
				$posts_shown = 0;

				while ( $the_latest_news_query->have_posts() ) : $the_latest_news_query->the_post();
					//Don't show more than one post
					if ( 5 <= $posts_shown )
						break;

					if ( in_array( get_the_ID(), self::$excluded_posts ) )
						continue;

					//Prevent future queries from returning this post
					$excluded_posts[] = get_the_ID();
				?>
					<article id="post-<?php the_ID(); ?>" <?php post_class( 'no-excerpt' ); ?> data-vr-contentbox="" >
						<header class="entry-header">
							<div class="the-post-thumbnail">
								<a href="<?php the_permalink(); ?>" title="Permalink to <?php the_title_attribute(); ?>" rel="bookmark">
								<?php has_post_thumbnail() ? the_post_thumbnail( 'river-300' ): thegrio_default_image(300); ?></a>
							</div>
							<?php //thegrio_primary_category_tag(); ?>
							<h3 class="entry-title"><a href="<?php the_permalink(); ?>" title="Permalink to <?php the_title_attribute(); ?>" rel="bookmark"><?php
								if ( thegrio_get_short_title() )
									thegrio_short_title();
								else
									the_title();
							?></a></h3>
							<?php if ( 1 == 2 ) : ?>
							<div class="entry-summary">
								<div class="the-advanced-excerpt"><?php //the_advanced_excerpt(); ?></div>
							</div><!-- .entry-summary -->
							<?php endif; ?>
						</header><!-- .entry-header -->
						<footer class="entry-footer">
							<div class="entry-meta">
								<?php thegrio_river_meta(); ?>
							</div>
						</footer><!-- #entry-meta -->
					</article>
				<?php
					$posts_shown++;
				endwhile;
			endif;
			// Reset main query and unset custom query
			wp_reset_query();

			unset( $the_latest_news_query );

			// capture and cache output from buffer
			$content = ob_get_clean();
			wp_cache_set( $cache_key, array( $content, $excluded_posts ), $GLOBALS[ 'thegrio_cache_group' ], 3660 );
		}
		else {
			list( $content, $excluded_posts ) = $values;
		}

		self::$excluded_posts = array_merge( self::$excluded_posts, $excluded_posts );
		return $content;
	}

	/**
	 * Determine, based on taxonomy archive on which this function is referenced, which taxonomy lead story method should be rendered and returned.
	 * Currently used on sub- (non-primary) categories and Specials
	 *
	 * @uses is_category, thegrio_is_primary_category, this::category_lead_story, is_tax, this::specials_lead_story
	 * @return string or false
	 */
	public function taxonomy_lead_story() {
		if ( is_category() && ! thegrio_is_primary_category() )
			return $this->subcategory_lead_story();
		elseif ( is_tax( $this->taxonomy_specials ) )
			return $this->specials_lead_story();
		else
			return false;
	}

	/**
	 * Render subcategory lead story
	 *
	 * @param int $term_id
	 * @param bool $force_refresh
	 * @uses is_category, get_queried_object_id, this::render_taxonomy_lead_story, this::get_cache_incrementor
	 * @return string or false
	 */
	private function subcategory_lead_story( $term_id = false, $force_refresh = false ) {
		//Get the term ID
		if ( is_numeric( $term_id ) )
			$term_id = (int) $term_id;
		elseif ( is_category() )
			$term_id = (int) get_queried_object_id();
		else
			$term_id = false;

		//Ensure that we have a term ID
		if ( ! $term_id )
			return false;

		return $this->render_taxonomy_lead_story( $term_id, 'category', $this->cache_key_subcategory_lead_story . $this->get_cache_incrementor() . '_', 'subcategory', (bool) $force_refresh );
	}

	/**
	 * Render Specials lead story
	 *
	 * @param int $term_id
	 * @param bool $force_refresh
	 * @uses is_category, get_queried_object_id, this::render_taxonomy_lead_story, this::get_cache_incrementor
	 * @return string or false
	 */
	private function specials_lead_story( $term_id = false, $force_refresh = false ) {
		//Get the term ID
		if ( is_numeric( $term_id ) )
			$term_id = (int) $term_id;
		elseif ( is_tax( $this->taxonomy_specials ) )
			$term_id = (int) get_queried_object_id();
		else
			$term_id = false;

		//Ensure that we have a term ID
		if ( ! $term_id )
			return false;

		//Retrieve or build cache via common taxonomy lead story rendering method
		return $this->render_taxonomy_lead_story( $term_id, $this->taxonomy_specials, $this->cache_key_special_lead_story . $this->get_cache_incrementor() . '_', $this->taxonomy_specials, (bool) $force_refresh );
	}

	/**
	 * Render lead story for given taxonomy term
	 *
	 * @global $post
	 * @param int $term_id
	 * @param string $taxonomy
	 * @param string $cache_key_part
	 * @param string $meta_key
	 * @param bool $force_refresh
	 * @uses taxonomy_exists, wp_cache_get, WP_Query, get_term_field, thegrio_get_primary_categories, get_the_ID, the_ID, post_class, the_permalink, the_title_attribute, has_post_thumbnail, the_post_thumbnail, thegrio_default_image, thegrio_get_short_title, thegrio_short_title, the_title, thegrio_river_meta, wp_reset_query, wp_cache_set
	 * @return string
	 */
	private function render_taxonomy_lead_story( $term_id = false, $taxonomy = false, $cache_key_part = false, $meta_key = false, $force_refresh = false ) {
		//Validate arguments and return false if failed
		$term_id = (int) $term_id;

		$taxonomy = preg_replace( '#[^A-Z0-9\-_]#i', '', $taxonomy );

		$cache_key_part = preg_replace( '#[^a-z0-9_]#', '', $cache_key_part );

		$meta_key = preg_replace( '#[^a-z_]#', '', $meta_key );

		if ( ! $term_id || ! taxonomy_exists( $taxonomy ) || empty( $cache_key_part ) || empty( $meta_key ) )
			return false;

		//Build full cache key
		$cache_key = md5( $cache_key_part . $term_id );
		$excluded_posts = array();

		if ( false === ( $values = wp_cache_get( $cache_key, $GLOBALS[ 'thegrio_cache_group' ] ) ) || (bool) $force_refresh || ! is_array( $values ) ) {
			ob_start();

			global $post;

			do_action( 'vr_before_query', $term_id, 'lead_story' );
			// The Query
			$the_query = new WP_Query( array(
				'posts_per_page' => 1,
				'post_type' => 'post',
				'post_status' => 'publish',
				'orderby' => 'date',
				'order' => 'DESC',
				'cache_results' => true,
				'no_found_rows' => true,
				'meta_query' => array( array(
					'key' => $meta_key,
					'value' => 'on'
				) ),
				'tax_query' => array( array(
					'taxonomy' => $taxonomy,
					'field' => 'id',
					'terms' => $term_id
				) )
			) );
			do_action( 'vr_after_query' );

			// The Loop
			if ( $the_query->have_posts() ) :
				//Get term slug for current taxonomy term, for use with lead story exclusion. If a primary category is requested, it will be skipped.
				$term_slug = get_term_field( 'slug', $term_id, $taxonomy );

				if ( 'category' == $taxonomy && in_array( $term_slug, thegrio_get_primary_categories() ) )
					$term_slug = false;

				while ( $the_query->have_posts() ) : $the_query->the_post();
					//Store this ID for use in this::pre_get_posts to exclude this post from the main query
					if ( is_string( $term_slug ) && is_array( $this->{ $taxonomy . '_lead_story_ids' } ) )
						$this->{ $taxonomy . '_lead_story_ids' } = array_merge( $this->{ $taxonomy . '_lead_story_ids' }, array( $term_slug => get_the_ID() ) );

					?>
					<article id="post-<?php the_ID(); ?>" <?php post_class(); ?> data-vr-contentbox="">
						<header class="entry-header">
							<div class="the-post-thumbnail">
								<a href="<?php the_permalink(); ?>" title="Permalink to <?php the_title_attribute(); ?>" rel="bookmark">
									<?php has_post_thumbnail() ? the_post_thumbnail( 'single' ) : thegrio_default_image(800); ?>
								</a>
							</div>
						</header><!-- .entry-header -->
						<div class="entry-summary-wrapper">
							<div class="entry-summary">
								<h2 class="entry-title">
									<a href="<?php the_permalink(); ?>" title="Permalink to <?php the_title_attribute(); ?>" rel="bookmark"><?php
										if ( thegrio_get_short_title() )
											thegrio_short_title();
										else
											the_title();
									?></a>
								</h2>
								<footer class="entry-footer">
									<div class="entry-meta">
										<?php thegrio_river_meta(); ?>
									</div><!-- .entry-meta -->
								</footer>
							</div><!-- .entry-summary -->
						</div><!-- .entry-summary-wrapper -->
					</article>
				<?php endwhile;
			endif;

			// Reset main query and unset custom query
			wp_reset_query();

			unset( $the_query );

			// capture and cache output from buffer
			$content = ob_get_clean();
			wp_cache_set( $cache_key, array( $content, $excluded_posts ), $GLOBALS[ 'thegrio_cache_group' ], 3660 );
		}
		else {
			list( $content, $excluded_posts ) = $values;
		}

		if( isset( self::$excluded_posts ) && is_array( self::$excluded_posts ) )
			self::$excluded_posts = array_merge( self::$excluded_posts, $excluded_posts );

		return $content;
	}

	/**
	 * Prepare to store taxonomy lead story IDs when priming caches for same.
	 * In this::store_taxonomy_lead_story_ids_exclusion, the current values are retrieved, updated, and stored back into the database.
	 *
	 * Stored data consists of an array whose keys are the term slugs and values are the post IDs to be excluded.
	 * This key-value approach ensures that the stored option cannot contain more values than taxonomy terms exist.
	 *
	 * @param string $taxonomy
	 * @return null
	 */
	private function setup_taxonomy_lead_story_ids_exclusion( $taxonomy ) {
		if ( ! is_array( $this->{ $taxonomy . '_lead_story_ids' } ) )
			$this->{ $taxonomy . '_lead_story_ids' } = array();
	}

	/**
	 * Store updated list of taxonomy lead story IDs.
	 * Data is stored in an option to ensure that values never expire out of the cache.
	 *
	 * Stored data consists of an array whose keys are the term slugs and values are the post IDs to be excluded.
	 * This key-value approach ensures that the stored option cannot contain more values than taxonomy terms exist.
	 *
	 * @param string $taxonomy
	 * @uses get_option, update_option
	 * @return null
	 */
	private function store_taxonomy_lead_story_ids_exclusion( $taxonomy ) {
		//Get current option
		$stored_ids = get_option( $this->{ 'cache_key_' . $taxonomy . '_lead_story_ids' } );

		//Merge current values from class variable with those from the database, ensuring that newly-set values overwrite those from the database.
		if( is_array( $stored_ids ) && ! empty( $stored_ids ) )
			$ids = array_merge( $stored_ids, $this->{ $taxonomy . '_lead_story_ids' } );
		else
			$ids = $this->{ $taxonomy . '_lead_story_ids' };

		//Update database with clean list of term-post associations
		update_option( $this->{ 'cache_key_' . $taxonomy . '_lead_story_ids' }, $ids );

		//Reset for next processing
		$this->{ $taxonomy . '_lead_story_ids' } = null;
	}

	/**
	 * Ensure that taxonomy lead story arrays only contain posts in the associated taxonomies.
	 * If a post is removed from a taxonomy term, this function will purge it from the stored data, otherwise it will persist, resulting in the post not appearing in archives where it should.
	 *
	 * Stored data consists of an array whose keys are the term slugs and values are the post IDs to be excluded.
	 * This key-value approach ensures that the stored option cannot contain more values than taxonomy terms exist.
	 *
	 * @param string $taxonomy
	 * @uses get_option, has_term, update_option
	 * @return null
	 */
	private function purge_taxonomy_lead_story_ids_exclusion( $taxonomy ) {
		//Alias data to store so that we can purge any outdated entries. Can't use bracketed array references with the dynamic variable.
		$lead_story_ids = get_option( $this->{ 'cache_key_' . $taxonomy . '_lead_story_ids' } );

		//Remove any lead story IDs that are no longer valid. Will happen when a post is removed from a taxonomy term.
		if( is_array( $lead_story_ids ) && ! empty( $lead_story_ids ) ) {
			foreach ( $lead_story_ids as $term_slug => $post_id ) {
				if ( ! has_term( $term_slug, $taxonomy, $post_id ) )
					unset( $lead_story_ids[ $term_slug ] );
			}
		}

		//Update option
		update_option( $this->{ 'cache_key_' . $taxonomy . '_lead_story_ids' }, $lead_story_ids );

		//Clean up
		unset( $lead_story_ids );
	}

	/**
	 * Exclude the lead story from any category or Specials archive that it would otherwise appear in.
	 *
	 * @param object $query
	 * @uses is_admin, thegrio_get_primary_categories, get_option
	 * @action pre_get_posts
	 * @return null
	 */
	public function action_pre_get_posts( $query ) {
		if ( ! is_admin() && $query->is_main_query() && ( ( $category_name = $query->get( 'category_name' ) ) || ( $special = $query->get( $this->taxonomy_specials ) ) ) ) {
			//Normalize variables based on context
			if ( isset( $category_name ) && ! empty( $category_name ) ) {
				$taxonomy = 'category';
				$term_slug = $category_name;

				if ( in_array( $term_slug, thegrio_get_primary_categories() ) )
					return;
			}
			elseif ( isset( $special ) && ! empty( $special ) ) {
				$taxonomy = $this->taxonomy_specials;
				$term_slug = $special;
			}
			else {
				return;
			}

			//Dynamic cache key
			$cache_key = $this->{ 'cache_key_' . $taxonomy . '_lead_story_ids' };

			//Retrieve cache and set post__not_in if and ID is available
			$ids = get_option( $cache_key );

			if ( is_array( $ids ) && array_key_exists( $term_slug, $ids ) )
				$query->set( 'post__not_in', array( (int) $ids[ $term_slug ] ) );
		}
	}

	/**
	 * Retrieves the list of excluded post ids for use in the infinity scroller
	 */
	public function get_excluded_ids( $ids ){
		if( is_array( $ids ) )
			$excluded_ids = array_merge( $ids, get_option( 'thegrio_excluded_posts_' ) );
		else
			$excluded_ids = (array) get_option( 'thegrio_excluded_posts_' );

		return $excluded_ids;
	}

}
thegrio_singleton( 'TheGrio_Template_Post_Queries' );

/**
 * Retrieve various elements of this page and, if necessary, prime the caches.
 * Necessary because a single post should never appear more than once on a given page, and some page elements are output in other template files.
 *
 * @uses thegrio_singleton
 * @return array
 */
function thegrio_get_page_content() {
	return thegrio_singleton( 'TheGrio_Template_Post_Queries' )->get_page_content();
}

/**
 * Render the "Around the Grio" template element
 *
 * @uses thegrio_singleton
 * @return string
 */
function thegrio_around_the_grio() {
	thegrio_singleton( 'TheGrio_Template_Post_Queries' )->around_thegrio_single();
}

/**
 * Render the lead story for the taxonomy archive template in which this function is called.
 *
 * @uses thegrio_singleton
 * @return string
 */
function thegrio_taxonomy_lead_story() {
	echo thegrio_singleton( 'TheGrio_Template_Post_Queries' )->taxonomy_lead_story();
}

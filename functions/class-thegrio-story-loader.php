<?php
/**
 * The Grio Story Pre-Loader
 *
 * Inject preloaded markup into the page upon load so stories can be
 * navigated quickly via swiping.
 *
 * By Oomph, Inc.
 * www.thinkoomph.com
 */
class TheGrio_Story_Loader {
	// How may stories to preload. $preload / 2 on each side, if possible.
	var $preload_count = 8;
	var $date_operator = '';
	var $post_date = '';
	var $cache_group = 'grio-story-loader';
	var $preloaded_posts = array();
	var $preload_queue = array();

	function __construct() {
		add_action( 'wp_head', array( $this, 'action_wp_head' ) );
		add_action( 'wp', array( $this, 'enqueue_scripts' ) );

		add_action( 'wp_ajax_nopriv_posts_preload', array( $this, 'get_preload_json' ) );
		add_action( 'wp_ajax_posts_preload', array( $this, 'get_preload_json' ) );
		add_action( 'wp_footer', array( $this, 'preload_script' ) );

		// AJAX HACK: Mobile themes aren't loaded when REQUEST_URI contains wp-admin,
		// see support ticket /t8200 <http://vip-support.automattic.com/tickets/8200>
		// This means we need to handle AJAX in a custom manner, and not go through admin-ajax
		// Just drop-in code from admin-ajax.php here:
		add_action( 'init', array( $this, 'handle_ajax_requests' ) );
	}

	/*
	 * Handle AJAX requests. See note in __construct
	 * @action init
	 */
	function handle_ajax_requests() {
		if ( empty( filter_input( INPUT_POST, 'ajax_action' ) ) ) {
			return;
		}

		do_action( 'wp_ajax_' . filter_input( INPUT_POST, 'ajax_action' ) );
		die( '-1' );
	}

	function action_wp_head() {
		// Only bother doing anything on single articles
		if ( ! is_singular() ) {
			return;
		}

		$path = '/wp-admin/admin-ajax.php';

		if ( is_admin() ) {
			$url = site_url( $path ); // admin uses non-mapped urls like abc.wordpress.com
		}
		else {
			$url = home_url( $path ); // for domain-mapped sites like mysite.com
		}

		// AJAX HACK: See note in __construct. Just pass in 'ajax_action' as a POST
		// variable to trigger ajax behavior
		$url = home_url();

		$post_id = get_queried_object_id();

		$current_category_slug = thegrio_current_category( true );
		$post_list = $this->cache( $post_id, '', $current_category_slug );

		if ( false == $post_list ) {
			$post_list = $this->get_stories( get_queried_object(), '', $current_category_slug );
			$this->cache( $post_id, '', $current_category_slug, $post_list );
		}

		foreach ( $post_list as $post ) {
			$this->preload_post( $post );
		}

		?>
		<script>
			<?php /* First, redirect to any hashed article on load. For pasted URLs */ ?>
			if(window.History.enabled && (matches = location.hash.match(/^#(\/.+)/))) {
				window.location.hash = "";
				window.location.pathname=matches[1];
			}

			document.getElementsByTagName('html')[0].className += " thegrio-swipe";

			var ajaxurl = "<?php echo esc_url( $url ); ?>";</script>
		<?php
	}

	function enqueue_scripts() {
		if ( ! is_singular() ) {
			return;
		}

		wp_enqueue_script( 'jquery-history-js', get_stylesheet_directory_uri() . '/js/jquery.history.js', array( 'jquery' ), '1.7.1' );
	}

	/*
	 * Add postmeta LEFT JOIN as 'epr' to JOIN clause if external_permalinks_redux plugin is
	 * active
	 * @filter posts_join
	 */
	function posts_join( $join ) {
		global $wpdb;

		// Only modify JOIN clause if external permalinks redux code is loaded
		if ( class_exists( 'external_permalinks_redux' ) && method_exists( 'external_permalinks_redux', 'get_instance' ) ) {
			$join .= $wpdb->prepare( " LEFT JOIN {$wpdb->postmeta} epr ON epr.post_id={$wpdb->posts}.ID AND epr.meta_key=%s", external_permalinks_redux::get_instance()->meta_key_target );
		}

		return $join;
	}

	/*
	 * Filter posts for the post loader, filtering on date as well as excluding posts
	 * that have an external permalink set up
	 * @filter posts_where
	 */
	function posts_where( $where ) {
		global $wpdb, $external_permalinks_redux;

		if ( ! empty( $this->where_date ) && ( $this->date_operator == '>' || $this->date_operator == '<' ) ) {
			$where .= $wpdb->prepare( " AND post_date $this->date_operator %s", $this->where_date );
		}

		if ( $external_permalinks_redux && is_a( $external_permalinks_redux, 'external_permalinks_redux' ) ) {
			$where .= ' AND epr.meta_key IS NULL';
		}

		return $where;
	}

	function cache( $source_ID, $direction, $category_slug, $value = false ) {
		$cache_key = 'posts_preload_'.$source_ID.'_'.$category_slug.'_'.$direction;

		if ( $value ) {
			wp_cache_set( $cache_key, $value, 'posts-preload', 120 );
		}

		return wp_cache_get( $cache_key, 'posts-preload' );
	}

	function get_permalink() {
		$permalink = get_permalink();

		/* Hackish. Since there might be plenty of conversions to/from encoded URIs,
		and Javascript's encodeURI creates uppercase hash codes: %2D%2E yet PHP
		creates lower case hash codes %2d%2e, this code smooths over the difference.
		Disgustingly. */
		return preg_replace_callback( '/(%[a-f0-9]{2})/', function( $matches ) { return strtoupper( $matches[1] ); }, $permalink );
	}

	/*
	 * Get a list of $number posts centered around the parameter post, $current
	 */
	function get_stories( $current, $direction = '', $category_slug = '' ) {
		$shownewer = floor( $this->preload_count / 2 );
		$showolder = ceil( $this->preload_count / 2 );

		$query = array(
			'post_status' => 'publish',
			'post_type' => array( 'post' ),
			'ignore_sticky_posts' => 1, // Turn off stickiness of sticky posts
			'posts_per_page' => $shownewer + $showolder + 1,
			'orderby' => 'post_date',
		);

		if ( ! empty( $category_slug ) && in_array( $category_slug, thegrio_get_primary_categories() ) ) {
			set_query_var( 'thegrio_category', $category_slug );
			$query['category_name'] = $category_slug;
		}

		// Feed parameters to nbcs_story_explorer_where with globals (explicit)
		$this->where_date = $current->post_date;

		if ( 'newer' == $direction ) {
			$showolder = 0;
		}
		else if ( 'older' == $direction ) {
			$shownewer = 0;
		}
		else {
			$direction = 'all';
		}

		add_filter( 'posts_where', array( $this, 'posts_where' ) );
		add_filter( 'posts_join', array( $this, 'posts_join' ) );

		if ( 'all' == $direction || 'newer' == $direction ) {
			// Get up to 20 posts newer than this one
			$this->date_operator = '>';
			$query['order'] = 'ASC';
			$newer = new WP_Query( $query );
		}

		if ( 'all' == $direction || 'older' == $direction ) {
			// Get up to 20 posts older than this one
			$this->date_operator = '<';
			$query['order'] = 'DESC';
			$older = new WP_Query( $query );
		}

		// Remove the filters. We're done with them
		remove_filter( 'posts_where', array( $this, 'posts_where' ) );
		remove_filter( 'posts_join', array( $this, 'posts_join' ) );

		$post_list = array();

		global $post;

		// Process the two result lists so that we have a total
		// of $shownewer + $showolder + 1 posts with up to $shownewer "newer" posts and $showolder "older" posts
		$numnewer = $shownewer + ( $showolder - min( $showolder, isset( $older ) ? $older->found_posts : 0 ) );
		while ( isset( $newer ) && $newer->have_posts() ) {
			$newer->the_post();
			$post_list[] = $post;
			if ( count( $post_list ) == $numnewer ) { break; }
		}
		$post_list = array_reverse( $post_list );
		// Insert a marker to designate that this is the first post
		if ( $shownewer > 0 && count( $post_list ) < $numnewer ) {
			array_unshift( $post_list, 'start' );
		}

		$post_list[] = $current;
		while ( isset( $older ) && $older->have_posts() ) {
			$older->the_post();
			$post_list[] = $post;
			if ( count( $post_list ) == $shownewer + $showolder + 1 ) { break; }
		}

		if ( $showolder > 0 && count( $post_list ) - ( $numnewer + 1 ) < $showolder ) {
			array_push( $post_list, 'end' );
		}

		$i = 0;

		wp_reset_postdata();

		return $post_list;
	}

	function preload_post( $post ) {
		if ( 'start' == $post || 'end' == $post ) {
			$this->preload_queue[] = $post;
			return;
		}

		$GLOBALS['post'] = $post;

		setup_postdata( $post );

		$permalink = $this->get_permalink();

		if ( ! empty( filter_input( INPUT_POST, 'exclude' ) ) ) {
			$exclude = explode( ';', filter_input( INPUT_POST, 'exclude' ) );
			if ( in_array( $permalink, $exclude ) ) {
				return;
			}
		}

		$widont_removed = remove_filter( 'the_title', 'widont' );
		$preload['title'] = get_the_title();
		if ( $widont_removed ) { add_filter( 'the_title', 'widont' ); }

		ob_start();
		$GLOBALS['withcomments'] = true;
		get_template_part( 'content', 'single' );
		$preload['wrapper'] = ob_get_contents();
		ob_end_clean();

		$this->preload_queue[] = $permalink;
		$this->preloaded_posts[ $permalink ] = $preload;
	}

	function get_preload_json() {
		if ( empty( filter_input( INPUT_POST, 'url' ) ) ) {
			return;
		}

		if ( 'older' == filter_input( INPUT_POST, 'direction' ) || 'newer' == filter_input( INPUT_POST, 'direction' ) ) {
			$direction = filter_input( INPUT_POST, 'direction' );
		}
		else {
			return;
		}

		global $post;

		$post = get_post( url_to_postid( filter_input( INPUT_POST, 'url' ) ) );

		if ( ! $post ) {
			echo json_encode( array( 'error' => "Couldn't find post at URL" ) );
			die;
		}

		$category_slug = ! empty( filter_input( INPUT_POST, 'category' ) ) ? filter_input( INPUT_POST, 'category' ) : '';

		if ( ! in_array( $category_slug, thegrio_get_primary_categories() ) ) {
			$category_slug = '';
		}

		setup_postdata( $post );

		header( 'Content-Type: application/json' );

		$post_list = $this->cache( $post->ID, $direction, $category_slug );

		// Build cache if not present. Make it short-lived.
		if ( false === $post_list ) {
			$post_list = $this->get_stories( $post, $direction, $category_slug );

			if ( 'newer' == $direction ) {
				$post_list = array_reverse( $post_list );
			}

			$this->cache( $post->ID, $direction, $category_slug, $post_list );
		}
		$preload = array();

		// preload all of the other stories
		foreach ( $post_list as $post ) {
			$this->preload_post( $post );
		}

		echo json_encode( array(
			'preload' => $this->preloaded_posts,
			'queue'   => $this->preload_queue,
		) );

		die;
	}

	function preload_script() {
		if ( ! is_single() ) {
			return;
		}

		?>
		<script>
			var post_preload = <?php echo json_encode( $this->preloaded_posts ); ?>;
			var post_preloadQueue = <?php echo json_encode( $this->preload_queue ); ?>;
			var post_permalink = <?php echo json_encode( get_permalink() ); ?>;
			var post_category = <?php echo json_encode( thegrio_current_category( true ) ); ?>;
		</script>
<?php
	}
}
thegrio_singleton( 'TheGrio_Story_Loader' );

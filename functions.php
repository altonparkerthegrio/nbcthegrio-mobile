<?php
/*
 * VIP functions, per http://lobby.vip.wordpress.com/getting-started/development-environment/
 */
require_once( WP_CONTENT_DIR . '/themes/vip/plugins/vip-init.php' );


/**
 * Include functionality from desktop theme
 */
$grio_environment = ( false !== strpos( site_url(), 'thegriopreprod' ) ) ? 'nbcthegrio-preprod' : 'nbcthegrio';
require_once( trailingslashit( wpcom_vip_theme_dir( $grio_environment ) ) . 'functions-shared.php' );

/**
 * Additional functionality
 */
require_once( __DIR__ . '/functions/template-mobile.php' );
if ( ! class_exists( 'TheGrio_Gallery' ) ) {
	require_once( __DIR__ . '/functions/class-thegrio-gallery.php' );
}
require( __DIR__ . '/functions/class-thegrio-template-post-queries.php' );
require( __DIR__ . '/functions/template.php' );
require( __DIR__ . '/functions/class-thegrio-public-good.php' );
/**
 * Don't automatically load Facebook comments.
 */
remove_action( 'wp_head', 'thegrio_wp_head_init_facebook', 6 );

/**
 * Add mobile-specific image sizes
 *
 * @uses add_image_size
 * @action after_setup_theme
 * @return null
 */
function thegrio_mobile_theme_setup() {
	add_image_size( 'thegrio-mobile-archive', 380, 214, false );
	add_image_size( 'thegrio-mobile-gallery', 380, 9999, false );
	add_image_size( 'widget-thumb', 96, 54, true );
}
add_action( 'after_setup_theme', 'thegrio_mobile_theme_setup', 15 );

/**
 * Register scripts
 *
 * @uses wp_register_script
 * @action init
 * @return null
 */
function thegrio_mobile_register_assets() {
	wp_register_script( 'thegrio-bookmark-bubble', get_stylesheet_directory_uri() . '/js/google-bookmark-bubble.js', array(), 20110104, true );
}
add_action( 'init', 'thegrio_mobile_register_assets' );

/**
 * Enqueue mobile styles and styles, and dequeue certain desktop-specific scripts and styles
 *
 * @uses wp_enqueue_style, get_template_directory_uri, get_stylesheet_directory_uri, wp_add_inline_stlye, wpcom_vip_theme_url, wp_enqueue_script, wp_dequeue_style, wp_dequeue_script
 * @action wp_enqueue_scripts
 * @return null
 */
function thegrio_mobile_load_assets() {
	// Styles
	wp_enqueue_style( 'wptouch-base', get_template_directory_uri() . '/style.css', array(), 1.0 );
	wp_enqueue_style( 'thegrio-mobile', get_stylesheet_directory_uri() . '/css/mobile.css', array( 'wptouch-base' ), 1.3 );
	wp_enqueue_style( 'thegrio-oomph', get_stylesheet_directory_uri() . '/css/oomph.css', array( 'wptouch-base' ), 1.3 );
	wp_enqueue_style( 'thegrio-fancybox', get_stylesheet_directory_uri() . '/css/jquery.fancybox.css', array(), '2.1.5' );
	// Add additional inline style so that image in desktop theme can be reused.
	//commenting out -- it doesn't appear to be working on production
	//wp_add_inline_style( 'thegrio-mobile', '.thegrio-singular .addthis_toolbox a.addthis_button_email { background: transparent url( \'' . wpcom_vip_theme_url( 'images/email.png', 'nbcthegrio' ) . '\' ) no-repeat scroll 0 0; }' );

	// Common script
	wp_enqueue_script( 'thegrio-mobile', get_stylesheet_directory_uri() . '/js/thegrio-mobile.js', array( 'jquery', 'thegrio-bookmark-bubble' ), 1.1, true );
	wp_enqueue_script( 'thegrio-mobile-min', get_stylesheet_directory_uri() . '/js/thegrio-mobile-min.js' );
	wp_enqueue_script( 'thegrio-fancybox', get_stylesheet_directory_uri() . '/js/jquery.fancybox.js', array(), '2.1.5' );
	wp_enqueue_script( 'quant', '//edge.quantserve.com/quant.js#asyncload', array(), '1.0.0' );
	if ( is_home() || is_front_page() || is_archive() ) {

	} else {
		wp_enqueue_script( 'kiosked', '//widgets.kiosked.com/sniffer/get-script/sign/e01fed295e0c2bb4605de2ad62758b35/albumid/10257/co/10733.js#asyncload' );
	}
	wp_enqueue_script( 'tfa', '//cdn.taboola.com/libtrc/nbcu-thegrio-sc/tfa.js', array(), '1.0.0' );
	wp_enqueue_style( 'ropa-sans', '//fonts.googleapis.com/css?family=Roboto+Condensed' );
	wp_enqueue_style( 'Oswald', '//fonts.googleapis.com/css?family=Oswald:400,300,700' );
	//wp_dequeue_script( 'addthis' );

	// Output data for adjacent post swiping
	if ( is_single() && function_exists( 'thegrio_singleton' ) && method_exists( thegrio_singleton( 'TheGrio_Adjacent_Posts' ), 'get_adjacent_posts' ) ) {
		$adjacent_posts = thegrio_singleton( 'TheGrio_Adjacent_Posts' )->get_adjacent_posts( get_the_ID() );

		if ( ( $adjacent_posts['previous'] && ! has_post_format( 'video', $adjacent_posts['previous'] ) ) || ( $adjacent_posts['next'] && ! has_post_format( 'video', $adjacent_posts['next'] ) ) ) {

			// Use wp_localize_script to output a JS variable with the adjacent post URLs
			$adjacent_urls = array(
				'previous' => '',
				'next' => '',
			);

			if ( $adjacent_posts['previous'] && ! has_post_format( 'video', $adjacent_posts['previous'] ) ){
				$adjacent_urls['previous'] = get_permalink( $adjacent_posts['previous'] );
			}

			if ( $adjacent_posts['next'] && ! has_post_format( 'video', $adjacent_posts['next'] ) ){
				$adjacent_urls['next'] = get_permalink( $adjacent_posts['next'] );
			}

			wp_localize_script( 'thegrio-mobile', 'thegrio_adjacent_posts', $adjacent_urls );
		}
	}

	// Dequeue certain scripts and styles
	wp_dequeue_style( 'thegrio-alert' );

	if ( is_single() ) {
		wp_enqueue_script( 'thegrio-iframe-autoheight' );
	}
}
add_action( 'wp_enqueue_scripts', 'thegrio_mobile_load_assets', 100 );

/**
 * Add addthis script information and specific
 * style to header in order to include add this email image
 * Workaround for wp_add_inline_style
 */
function thegrio_mobile_wp_head() {
	global $wp_scripts;
	$addthis_script = (array) $wp_scripts->query( 'addthis' );

	if ( $addthis_script ){
		echo '<script>'.esc_js( $addthis_script['extra']['data'] ).'; var addthis_script = "'. esc_url( $addthis_script['src'] ) . '";</script>';
	}

	echo '<style type="text/css">.thegrio-singular .addthis_toolbox a.addthis_button_email { background: transparent url( \'' . esc_url( wpcom_vip_theme_url( 'images/email.png', 'nbcthegrio' ) ). '\' ) no-repeat scroll 0 0; }</style> ';
}
add_action( 'wp_head', 'thegrio_mobile_wp_head' );

/**
 * Add additional body classes based on context
 *
 * @global $wptouch_defaults
 * @uses is_user_logged_in, is_home, is_front_page, thegrio_is_primary_category, is_singular, is_archive, is_tax, is_attachment
 * @action wp
 * @return null
 */
function thegrio_mobile_body_classes() {
	global $wptouch_defaults;

	//Remove default pinstripe background
	$wptouch_defaults['style-background'] = str_replace( 'classic-wptouch-bg', 'thegrio', $wptouch_defaults['style-background'] );

	//Logged in?
	if ( is_user_logged_in() ){
		$wptouch_defaults['style-background'] .= ' logged-in admin-bar';
	}

	//Add contextual body classes
	if ( is_home() || is_front_page() ){
		$wptouch_defaults['style-background'] .= ' thegrio-home';
	}
	elseif ( function_exists( 'thegrio_is_primary_category' ) && thegrio_is_primary_category() ){
		$wptouch_defaults['style-background'] .= ' thegrio-primary-category';
	}
	elseif ( is_singular() ){
		$wptouch_defaults['style-background'] .= ' thegrio-singular';
	}
	elseif ( is_archive() ){
		$wptouch_defaults['style-background'] .= ' thegrio-archive';
	}

	if ( is_tax( 'specials' ) ){
		$wptouch_defaults['style-background'] .= ' thegrio-special';
	}

	if ( is_attachment() ){
		$wptouch_defaults['style-background'] .= ' thegrio-attachment';
	}

	//Clean up
	$wptouch_defaults['style-background'] = trim( $wptouch_defaults['style-background'] );
}
add_action( 'wp', 'thegrio_mobile_body_classes' );

/**
 * Display primary categories in WP Touch dropdown menu
 *
 * @uses thegrio_get_primary_categories, wp_cache_get, get_cat_name, get_category_link, esc_attr, esc_html, wp_cache_set
 * @return string or null
 */
function thegrio_mobile_menu() {
	$primary_categories = function_exists( 'thegrio_get_primary_categories' ) ? thegrio_get_primary_categories( true ) : array();
	$primary_categories = array_map( 'intval', $primary_categories );

	if ( empty( $primary_categories ) ){
		return;
	}

	if ( false == ( $menu = wp_cache_get( 'thegrio_mobile_menu', THEGRIO_CACHE_GROUP ) ) ) {
		ob_start();

		foreach ( $primary_categories as $primary_category ) :
			$cat_name = get_cat_name( $primary_category );
		?>
			<li>
				<a href="<?php echo esc_url( get_category_link( $primary_category ) ); ?>" title="<?php echo esc_attr( $cat_name ); ?>"><?php echo esc_html( $cat_name ); ?></a>
			</li>
		<?php
			unset( $cat_name );
		endforeach;

		$menu = ob_get_clean();

		wp_cache_set( 'thegrio_mobile_menu', $menu, THEGRIO_CACHE_GROUP, 3600 );
	}

	echo wp_kses_post( $menu );
}

/**
 * Add contextual text to header bar
 *
 * @uses is_home, is_front_page, thegrio_is_primary_category, get_cat_name, get_query_var, is_tax, is_single, thegrio_get_primary_category, get_the_ID, get_category_link, get_cat_name
 */
function thegrio_mobile_headerbar_text() {
	// Determine display text based on context
	$text = '';

	if ( is_home() || is_front_page() ) {
		$text = '<span id="headerbar-section-text">Home</span>';
	}
	elseif ( function_exists( 'thegrio_is_primary_category' ) && thegrio_is_primary_category() ) {
		$text = '<span id="headerbar-section-text">' . esc_html( get_cat_name( (int) get_query_var( 'cat' ) ) ) . '</span>';
	}
	elseif ( $category = thegrio_current_category() ) {
		$text = '<span id="headerbar-section-text">' . esc_html( $category->name ) . '</span>';
	}
	elseif ( is_tax( 'specials' ) ) {
		$text = '<span id="headerbar-section-text">' . esc_html( single_term_title( null, false ) ) . '</a>';
	}
	elseif ( is_single() && function_exists( 'thegrio_get_primary_category' ) ) {
		$cat_id = (int) thegrio_get_primary_category( get_queried_object_id(), false );
		$text = '<a href="' . get_category_link( $cat_id ) . '" id="headerbar-section-text">' . esc_html( get_cat_name( $cat_id ) ) . '</a>';
	}

	if ( ! empty( $text ) ) :
		echo wp_kses_post( $text );
	?>
		<script type="text/javascript">
			if ( jQuery( 'body' ).hasClass( 'logged-in' ) ) {
				var header_text_top = parseInt( jQuery( '#headerbar-section-text' ).css( 'top' ) );
				header_text_top += 28;

				jQuery( '#headerbar-section-text' ).css( 'top', header_text_top + 'px' );
			}

			var header_width = jQuery( '#headerbar' ).width();
			var header_logo = jQuery( '#headerbar-title' ).width();
			var header_menu = jQuery( '#headerbar-menu' ).width();
			var header_text_width = jQuery( '#headerbar-section-text' ).width();
			var header_text_height = jQuery( '#headerbar-section-text' ).height();

			var header_available = header_width - header_logo - header_menu;

			var header_text_left = Math.round( ( header_available - header_text_width ) / 2 );
			header_text_left += header_logo;

			if ( ( header_text_width + header_text_left ) > ( header_width - header_menu ) ) {
				jQuery( '#headerbar-section-text' ).css( {
					width: ( header_available - 20 ) + 'px',
					height: header_text_height + 'px',
					left: ( header_logo + 20 ) + 'px',
					display: 'inline-block',
					overflow: 'hidden',
					whiteSpace: 'nowrap',
					textOverflow: 'ellipsis'
				} );
			}
			else {
				jQuery( '#headerbar-section-text' ).css( 'left', header_text_left + 'px' );
			}
		</script>
	<?php endif;
}

/**
 * Theme footer
 *
 * @uses remove_action, vip_powered_wpcom
 * @action wp_footer
 * @return string
 */
function thegrio_mobile_footer() {
	// Remove default text from theme footer. Shouldn't be in here, but doesn't work otherwise.
	remove_action( 'wp_footer', 'wptouch_footer_credits', 5 );

	// Output copyright and VIP attribution
	echo '&copy;' . esc_html( date( 'Y' ) ) . ' D2M2 LLC';

	if ( function_exists( 'vip_powered_wpcom' ) ){
		//echo '<br />' . esc_html( vip_powered_wpcom() ) . '<br /><br />';
	}
}
add_action( 'wp_footer', 'thegrio_mobile_footer', 4 );

/**
 * Mobile sharing buttons from AddThis
 *
 * @global $post
 * @uses is_singular, is_attachment, thegrio_get_short_title, apply_filters, get_the_title, esc_url, wp_get_shortlink, esc_attr
 * @return string or null
 */
function thegrio_mobile_addthis() {
	//Get post data depending on context. Ensures that attachment URLs themselves aren't shared, but that the parent post is instead
	global $post;

	$post_id = is_attachment() && 0 == $post->post_parent ? (int) $post->ID : (int) $post->post_parent;

	if ( function_exists( 'thegrio_get_short_title' ) && ( $title = thegrio_get_short_title( $post_id ) ) ) {
		//All in the if!
	}
	else {
		$title = apply_filters( 'the_title', get_the_title( $post_id ) );
	}
	?>
	<div class="social-buttons">
            <?php /* ?><div class="load-sharing share-container"><a href="javascript:void(0)" class="social-button share-button">Share</a><div class="sharing-container"></div></div><?php */ ?>

            <div class="load-sharing share-container">
                <div class="addthis_toolbox addthis_default_style" addthis:via="theGrio" addthis:title="Expect division along racial lines with Bill Cosby">

                    <a class="addthis_button_email at300b" target="_blank" title="Email" href="#"><span class="at16nc at300bs at15nc at15t_email at16t_email"><span class="at_a11y">Share on email</span></span></a>

                    <!-- twitter -->
                    <iframe frameborder="0" style="width:59px; height:20px;" scrollbars="no" allowtransparency="true" scrolling="no" role="presentation" title="AddThis | Twitter" src="//s7.addthis.com/static/r07/tweet029.html#href=http%3A%2F%2F54.148.118.215%2F2014%2F11%2F19%2Fcosby-rape-accusations-race%2F%23.VKTumNgdsKA.twitter&amp;dr=&amp;conf=via%3DtheGrio%26title%3DExpect%2520division%2520along%2520racial%2520lines%2520with%2520Bill%2520Cosby%26product%3Dtbx-300%26username%3Dra-4e8faa5b0e073078%26pubid%3Dra-4e8faa5b0e073078&amp;share=via%3DtheGrio%23%40!title%3DExpect%2520division%2520along%2520racial%2520lines%2520with%2520Bill%2520Cosby%23%40!url_transforms%3Dclean%253Dtrue%2523%2540!shorten%253Dtwitter%25253Dbitly%2523%2540!defrag%253D1%2523%2540!remove%253D0%25253Dsms_ss%252523%252540!1%25253Dat_xt%252523%252540!2%25253Dat_pco%252523%252540!3%25253Dfb_ref%252523%252540!4%25253Dfb_source%23%40!imp_url%3D0%23%40!url%3Dhttp%253A%252F%252F54.148.118.215%252F2014%252F11%252F19%252Fcosby-rape-accusations-race%252F%23%40!smd%3Drsi%253D%2523%2540!rxi%253Dundefined%2523%2540!gen%253D0%2523%2540!rsc%253D%2523%2540!dr%253D%2523%2540!sta%253DAT-ra-4e8faa5b0e073078%25252F-%25252F-%25252F54a4ee98acb39a85%25252F1%23%40!passthrough%3D&amp;tw=via%3DtheGrio%23%40!url%3Dhttp%253A%252F%252F54.148.118.215%252F2014%252F11%252F19%252Fcosby-rape-accusations-race%252F%2523.VKTumNgdsKA.twitter%23%40!counturl%3Dhttp%253A%252F%252F54.148.118.215%252F2014%252F11%252F19%252Fcosby-rape-accusations-race%252F%23%40!count%3Dhorizontal%23%40!text%3DExpect%2520division%2520along%2520racial%2520lines%2520with%2520Bill%2520Cosby%23%40!related%3D%23%40!hashtags%3D%23%40!width%3D59"></iframe>

                    <!-- google +1 -->
                    <iframe width="100%" frameborder="0" hspace="0" marginheight="0" marginwidth="0" scrolling="no" style="position: static; top: 0px; width: 35px; margin: 0px; border-style: none; left: 0px; visibility: visible; height: 20px;" tabindex="0" vspace="0" id="I1_1420095131311" name="I1_1420095131311" src="https://apis.google.com/se/0/_/+1/fastbutton?usegapi=1&amp;size=medium&amp;hl=en-US&amp;origin=http%3A%2F%2F54.148.118.215&amp;url=http%3A%2F%2F54.148.118.215%2F2014%2F11%2F19%2Fcosby-rape-accusations-race%2F&amp;gsrc=3p&amp;jsh=m%3B%2F_%2Fscs%2Fapps-static%2F_%2Fjs%2Fk%3Doz.gapi.en_GB.TZhwkSesrY8.O%2Fm%3D__features__%2Fam%3DAQ%2Frt%3Dj%2Fd%3D1%2Ft%3Dzcms%2Frs%3DAGLTcCPBXQkirACLNVZPM43ehW53HRWCfg#_methods=onPlusOne%2C_ready%2C_close%2C_open%2C_resizeMe%2C_renderstart%2Concircled%2Cdrefresh%2Cerefresh&amp;id=I1_1420095131311&amp;parent=http%3A%2F%2F54.148.118.215&amp;pfname=&amp;rpctoken=32590784" data-gapiattached="true" title="+1"></iframe>

                    <!-- facebook like -->
                    <iframe width="50px" height="1000px" frameborder="0" name="f1af8f62ddd796c" allowtransparency="true" scrolling="no" title="fb:like Facebook Social Plugin" style="border: medium none; visibility: visible; width: 50px; height: 20px;" src="http://www.facebook.com/plugins/like.php?action=like&amp;app_id=116550901698269&amp;channel=http%3A%2F%2Fstatic.ak.facebook.com%2Fconnect%2Fxd_arbiter%2F7r8gQb8MIqE.js%3Fversion%3D41%23cb%3Df1f5098bbb5b186%26domain%3D54.148.118.215%26origin%3Dhttp%253A%252F%252F54.148.118.215%252Ff793468b510916%26relation%3Dparent.parent&amp;font=arial&amp;href=http%3A%2F%2F54.148.118.215%2F2014%2F11%2F19%2Fcosby-rape-accusations-race%2F&amp;layout=button_count&amp;locale=en_US&amp;ref=.VKTumG8zhW0.like&amp;sdk=joey&amp;send=false&amp;show_faces=false&amp;width=50" class=""></iframe>

                </div>
            </div>

            <div class="load-comments share-container">
                <a href="#fb-comments" class="social-button comments-button">Comment</a>
            </div>
	</div>
	<?php
}

/**
 * Set attachment page canonical to parent, if present
 *
 * @global $post
 * @uses is_attachment, get_permalink, rel_canonical
 * @action wp_head
 * @return string
 */
function thegrio_mobile_canonical() {
	global $post;

	if ( is_attachment() && 0 != $post->post_parent ){
		echo '<link rel="canonical" href="' . esc_url( get_permalink( $post->post_parent ) ) . '" />' . "\n";
	}
	else {
		rel_canonical();
	}
}
add_action( 'wp_head', 'thegrio_mobile_canonical' );
remove_action( 'wp_head', 'rel_canonical' );

/**
 * Override page title on single pages
 *
 * @filter option_bnc_iphone_pages
 */
function thegrio_mobile_filter_bnc_iphone_pages( $pages ) {
	if ( is_single() ){
		$pages['header-title'] = get_the_title();
	}

	return $pages;
}
add_filter( 'pre_option_bnc_iphone_pages', 'thegrio_mobile_filter_bnc_iphone_pages' );

/**
 * Keep posts within a particular category
 */

/**
 * Add `thegrio_category` query var
 * @filter query_vars
 * @return array
 */
function thegrio_mobile_category_query_var( $vars ) {
	$vars[] = 'thegrio_category';

	return $vars;
}
add_filter( 'query_vars', 'thegrio_mobile_category_query_var' );

/**
 * Add category name to post links when on a category archive
 * or when `thegrio_category` query var is present
 * @filter post_link
 * @return string
 */
function thegrio_mobile_post_link( $permalink, $post, $leavename ) {
	$category_slug = get_query_var( 'thegrio_category' );

	// Don't change post link for posts with EPR
	if ( class_exists( 'external_permalinks_redux' ) && method_exists( 'external_permalinks_redux', 'get_instance' ) && get_post_meta( $post->ID, external_permalinks_redux::get_instance()->meta_key_target, true ) ){
		return $permalink;
	}

	if ( empty( $category_slug ) && is_category() && ( $category = get_queried_object() ) ){
		$category_slug = $category->slug;
	}

	if ( ! empty( $category_slug ) && in_array( $category_slug, thegrio_get_primary_categories() ) ){
		return str_replace( home_url(), home_url( $category_slug ), $permalink );
	}

	return $permalink;
}
add_action( 'post_link', 'thegrio_mobile_post_link', 10, 3 );

/**
 * Insert social script templates
 * @action wp_head
 */
function thegrio_wp_head_social_script_templates() { ?>
	<script id="fb-comment-template" type="x-grio/tmpl">
		<div class="fb-comments" data-href="<?php echo esc_url( function_exists( 'wpcom_is_vip' ) ) ? '__URL__' : 'http://example.com'; ?>" data-num-posts="10" data-width="320"></div>
	</script>

	<script id="addthis-template" type="x-grio/tmpl">
		<div class="share addthis_toolbox addthis_default_style" addthis:url="__URL__" addthis:title="__TITLE__" addthis:via="theGrio">
			<table>
				<tr>
					<td class="table-addthis-email"><a class="addthis_button_email"></a></td>
					<td class="table-addthis-twitter"><a class="addthis_button_tweet" tw:via="theGrio" tw:count="none"></a></td>
					<td class="table-addthis-gplus"><a class="addthis_button_google_plusone" g:plusone:annotation="none" g:plusone:size="medium"></a></td>
					<td class="table-addthis-fb"><a class="addthis_button_facebook_like"></a></td>
				</tr>
			</table>
		</div>
	</script>

	<script>var maximumiFrameHeight = 50;</script>
<?php
}
add_action( 'wp_head', 'thegrio_wp_head_social_script_templates' );


// code for infinite scroll starts
if ( is_category() ) {
	$post_per_page = 3;
}
else {
	$post_per_page = 5;
}

add_theme_support( 'infinite-scroll', array(
	'container' => 'content'.md5( filter_input( INPUT_SERVER, 'REQUEST_URI' ) ),
	'posts_per_page' => $post_per_page,
) );
// code for infinite scroll ends


/*
 * Code starts for infinite scrolling in the category pages
 */

function tweakjp_custom_is_support() {
	$supported = current_theme_supports( 'infinite-scroll' );
	return $supported;
}
add_filter( 'infinite_scroll_archive_supported', 'tweakjp_custom_is_support' );

/*
 * Code ends for infinite scrolling in the category pages
 */

/* Code for async scripts */
function add_async_forscript($url)
{
	if ( false === strpos( $url, '#asyncload' ) ){
		return $url;
	}
	else if ( is_admin() ){
		return str_replace( '#asyncload', '', $url );
	}
	else {
		return str_replace( '#asyncload', '', $url )."' async='true";
	}
}
add_filter( 'clean_url', 'add_async_forscript', 11, 1 );
/* Code ends for async scripts */


/**
 * Adding a shortcode for the custom jwplayer
*/
remove_shortcode( 'jwplayer' );
add_shortcode( 'jwplayer', __NAMESPACE__ . '\\jw_shortcode' );
add_shortcode( 'jwplatform', __NAMESPACE__ . '\\jw_shortcode' );


//posts_nav_link()
//previous_posts_link()
//if ( ! isset( $content_width ) ) $content_width = 900;
//paginate_comments_links()
//previous_comments_link()
//$themecolors

/**
 * The shortcode callback for the `jwplatform` shortcode.
 *
 * @param  array $attrs The shortcode attributes
 * @return string
 */

function jw_shortcode( $attrs ) {
	ob_start();
	$format = get_post_format();
	if (strpos($attrs[0], '-') !== false) {
		$attr_explode_var = explode('-',$attrs[0]);
		$attr_var = $attr_explode_var[1];
		?>
		<script type="text/javascript" src="//content.jwplatform.com/libraries/<?php echo esc_attr($attr_var); ?>.js"></script>
		<?php
	}
	else
	{
		?>
		<script type="text/javascript" src="//content.jwplatform.com/libraries/GqX43ZoG.js"></script>
		<?php
	}
	?>
	<div id="jwcontentarea_<?php echo esc_attr( $attrs[0] ) ?>"></div>
	<div id="jwvideo_<?php echo esc_attr( $attrs[0] ) ?>"></div>
	<script type="text/javascript">// <![CDATA[
	var playerInstance = jwplayer("jwvideo_<?php echo esc_attr( $attrs[0] ) ?>");
	<?php
		if (strpos($attrs[0], '-') !== false) {
			$attr_explode = explode('-',$attrs[0]);
			$attr = $attr_explode[0];
		}
		else
		{
			$attr = $attrs[0];
		}
	?>
	playerInstance.setup({
		"playlist": "http://content.jwplatform.com/feeds/<?php echo esc_attr( $attr ) ?>.json",
		"ph":"2",
	});
	// ]]></script>
	<?php
	return ob_get_clean();
}

add_filter( 'the_content', 'add_public_good_button' );

function add_public_good_button($content) {
	$public_good = new TheGrio_Public_Good;
  	$content .= wp_kses( $public_good->action_after_setup_theme(), $allowedtags );
  	return $content;
}

// Create function which allows more tags within comments
function allow_tags_for_kses() {
  global $allowedtags;
  $allowedtags = array(
		'address' => array(),
		'a' => array(
			'href' => true,
			'rel' => true,
			'rev' => true,
			'name' => true,
			'target' => true,
		),
		'abbr' => array(),
		'acronym' => array(),
		'area' => array(
			'alt' => true,
			'coords' => true,
			'href' => true,
			'nohref' => true,
			'shape' => true,
			'target' => true,
		),
		'article' => array(
			'align' => true,
			'dir' => true,
			'lang' => true,
			'xml:lang' => true,
		),
		'aside' => array(
			'align' => true,
			'dir' => true,
			'lang' => true,
			'xml:lang' => true,
		),
		'audio' => array(
			'autoplay' => true,
			'controls' => true,
			'loop' => true,
			'muted' => true,
			'preload' => true,
			'src' => true,
		),
		'b' => array(),
		'bdo' => array(
			'dir' => true,
		),
		'big' => array(),
		'blockquote' => array(
			'cite' => true,
			'lang' => true,
			'xml:lang' => true,
		),
		'br' => array(),
		'button' => array(
			'disabled' => true,
			'name' => true,
			'type' => true,
			'value' => true,
		),
		'caption' => array(
			'align' => true,
		),
		'cite' => array(
			'dir' => true,
			'lang' => true,
		),
		'code' => array(),
		'col' => array(
			'align' => true,
			'char' => true,
			'charoff' => true,
			'span' => true,
			'dir' => true,
			'valign' => true,
			'width' => true,
		),
		'colgroup' => array(
			'align' => true,
			'char' => true,
			'charoff' => true,
			'span' => true,
			'valign' => true,
			'width' => true,
		),
		'del' => array(
			'datetime' => true,
		),
		'dd' => array(),
		'dfn' => array(),
		'details' => array(
			'align' => true,
			'dir' => true,
			'lang' => true,
			'open' => true,
			'xml:lang' => true,
		),
		'div' => array(
			'align' => true,
			'dir' => true,
			'lang' => true,
			'data-loaded'=>array(),
			'xml:lang' => true,
			'data-pgs-partner-id'=>array(),
			'class'=>array(),
			'data-config-distributor-id'=>array(),
			'data-pgs-keywords'=>array(),
			'data-pgs-target-type'=>array(),
			'data-pgs-target-id'=>array(),
			'data-pgs-location'=>array(),
			'data-pgs-variant'=>array(),
		),
		'dl' => array(),
		'dt' => array(),
		'em' => array(),
		'fieldset' => array(),
		'figure' => array(
			'align' => true,
			'dir' => true,
			'lang' => true,
			'xml:lang' => true,
		),
		'figcaption' => array(
			'align' => true,
			'dir' => true,
			'lang' => true,
			'xml:lang' => true,
		),
		'font' => array(
			'color' => true,
			'face' => true,
			'size' => true,
		),
		'footer' => array(
			'align' => true,
			'dir' => true,
			'lang' => true,
			'xml:lang' => true,
		),
		'form' => array(
			'action' => true,
			'accept' => true,
			'accept-charset' => true,
			'enctype' => true,
			'method' => true,
			'name' => true,
			'target' => true,
		),
		'h1' => array(
			'align' => true,
		),
		'h2' => array(
			'align' => true,
		),
		'h3' => array(
			'align' => true,
		),
		'h4' => array(
			'align' => true,
		),
		'h5' => array(
			'align' => true,
		),
		'h6' => array(
			'align' => true,
		),
		'header' => array(
			'align' => true,
			'dir' => true,
			'lang' => true,
			'xml:lang' => true,
		),
		'hgroup' => array(
			'align' => true,
			'dir' => true,
			'lang' => true,
			'xml:lang' => true,
		),
		'hr' => array(
			'align' => true,
			'noshade' => true,
			'size' => true,
			'width' => true,
		),
		'i' => array(),
		'img' => array(
			'alt' => true,
			'align' => true,
			'border' => true,
			'height' => true,
			'hspace' => true,
			'longdesc' => true,
			'vspace' => true,
			'src' => true,
			'usemap' => true,
			'width' => true,
		),
		'ins' => array(
			'datetime' => true,
			'cite' => true,
		),
		'kbd' => array(),
		'label' => array(
			'for' => true,
		),
		'legend' => array(
			'align' => true,
		),
		'li' => array(
			'align' => true,
			'value' => true,
		),
		'map' => array(
			'name' => true,
		),
		'mark' => array(),
		'menu' => array(
			'type' => true,
		),
		'nav' => array(
			'align' => true,
			'dir' => true,
			'lang' => true,
			'xml:lang' => true,
		),
		'p' => array(
			'align' => true,
			'dir' => true,
			'lang' => true,
			'xml:lang' => true,
		),
		'pre' => array(
			'width' => true,
		),
		'q' => array(
			'cite' => true,
		),
		's' => array(),
		'samp' => array(),
		'span' => array(
			'dir' => true,
			'align' => true,
			'lang' => true,
			'xml:lang' => true,
		),
		'section' => array(
			'align' => true,
			'dir' => true,
			'lang' => true,
			'xml:lang' => true,
		),
		'small' => array(),
		'strike' => array(),
		'strong' => array(),
		'sub' => array(),
		'summary' => array(
			'align' => true,
			'dir' => true,
			'lang' => true,
			'xml:lang' => true,
		),
		'sup' => array(),
		'table' => array(
			'align' => true,
			'bgcolor' => true,
			'border' => true,
			'cellpadding' => true,
			'cellspacing' => true,
			'dir' => true,
			'rules' => true,
			'summary' => true,
			'width' => true,
		),
		'tbody' => array(
			'align' => true,
			'char' => true,
			'charoff' => true,
			'valign' => true,
		),
		'td' => array(
			'abbr' => true,
			'align' => true,
			'axis' => true,
			'bgcolor' => true,
			'char' => true,
			'charoff' => true,
			'colspan' => true,
			'dir' => true,
			'headers' => true,
			'height' => true,
			'nowrap' => true,
			'rowspan' => true,
			'scope' => true,
			'valign' => true,
			'width' => true,
		),
		'textarea' => array(
			'cols' => true,
			'rows' => true,
			'disabled' => true,
			'name' => true,
			'readonly' => true,
		),
		'tfoot' => array(
			'align' => true,
			'char' => true,
			'charoff' => true,
			'valign' => true,
		),
		'th' => array(
			'abbr' => true,
			'align' => true,
			'axis' => true,
			'bgcolor' => true,
			'char' => true,
			'charoff' => true,
			'colspan' => true,
			'headers' => true,
			'height' => true,
			'nowrap' => true,
			'rowspan' => true,
			'scope' => true,
			'valign' => true,
			'width' => true,
		),
		'thead' => array(
			'align' => true,
			'char' => true,
			'charoff' => true,
			'valign' => true,
		),
		'title' => array(),
		'tr' => array(
			'align' => true,
			'bgcolor' => true,
			'char' => true,
			'charoff' => true,
			'valign' => true,
		),
		'track' => array(
			'default' => true,
			'kind' => true,
			'label' => true,
			'src' => true,
			'srclang' => true,
		),
		'tt' => array(),
		'u' => array(),
		'ul' => array(
			'type' => true,
		),
		'ol' => array(
			'start' => true,
			'type' => true,
			'reversed' => true,
		),
		'var' => array(),
		'video' => array(
			'autoplay' => true,
			'controls' => true,
			'height' => true,
			'loop' => true,
			'muted' => true,
			'poster' => true,
			'preload' => true,
			'src' => true,
			'width' => true,
		),
	);
}

// Add WordPress hook to use the function
add_action('init', 'allow_tags_for_kses',11);
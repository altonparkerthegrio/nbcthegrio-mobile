<?php
/**
 * This template file will be used to house all of our social media functions
 * which output template code. This will keep things organized.
 */

/**
 * Enqueue AddThis
 *
 * @global $wp_scripts
 * @uses wp_enqueue_script
 * @action wp_enqueue_scripts
 * @return null
 */
function thegrio_enqueue_addthis() {
	global $wp_scripts;

	wp_enqueue_script( 'addthis', 'http://s7.addthis.com/js/250/addthis_widget.js#pubid=' . ( function_exists( 'wpcom_is_vip' ) ? 'ra-4e73ad8135ca2010' : 'ra-4e8faa5b0e073078' ), array(), null, true );
	$wp_scripts->add_data( 'addthis', 'data', "var addthis_share = {
		url_transforms : {
			clean: true,
			shorten: { twitter: 'bitly' }
		}
	};" );
}
add_action( 'wp_enqueue_scripts', 'thegrio_enqueue_addthis', 15 );

/**
 * Output social icons in the header
 *
 * @uses get_stylesheet_directory_uri
 * @return string
 */
function thegrio_social_header() {
?>
	<ul id="header-social">
		<li><a class="addthis_button_facebook_like fb-like" fb:like:layout="button_count" fb:like:href="http://www.facebook.com/theGrio"></a></li>
		<li><a class="addthis_button_twitter_follow_native twitter-follow-button" tf:screen_name="theGrio" tf:show-count="true" tf:show-screen-name="false"></a></li>
		<li><a class="addthis_button_google_plusone" g:plusone:size="medium" g:plusone:href="https://plus.google.com/100524337621457576556"></a></li>
	</ul>
	<ul id="header-connect">
		<li class="connect-fb"><div class="fb-login-button"></div></li>
	</ul>
<?php
}

/**
 * Output social sharing icons on singular pages
 *
 * @uses is_singular, esc_url, wp_get_shortlink, thegrio_get_short_title, esc_attr, the_title_attribute
 * @return string or null
 */
function thegrio_social_single() {
	if ( ! is_singular() ) {
		return;
	}
?>
	<div class="addthis_toolbox addthis_default_style" addthis:title="<?php
	if ( $short_title = thegrio_get_short_title() ) {
		echo esc_attr( $short_title );
	}
	else {
		the_title_attribute();
	}
	?>" addthis:via="theGrio">
		<a class="addthis_button_facebook_like" fb:like:layout="button_count"></a>
		<a class="addthis_button_tweet" tw:via="theGrio"></a>
		<a class="addthis_button_google_plusone" g:plusone:size="medium"></a>
		<a class="addthis_button_email"></a>
	</div>
<?php
}

/**
 * Output social-related code in document head
 *
 * @uses esc_attr, esc_js
 * @action wp_head
 * @return string
 */
function thegrio_social_wp_head() {
?>
<meta property="fb:app_id" content="<?php echo esc_attr( FB_APP_ID ); ?>"/>
<meta property="fb:admins" content="1727663653" />
<script>
var initFacebook = function() {
	(function(d, s, id) {
		var js, fjs = d.getElementsByTagName(s)[0];
		if (d.getElementById(id)) return;
		js = d.createElement(s); js.id = id;
		js.src = "//connect.facebook.net/en_US/all.js#xfbml=1&oauth=1&status=1&cookie=1&appId=<?php echo esc_js( FB_APP_ID ); ?>";
		fjs.parentNode.insertBefore(js, fjs);
	})(document, 'script', 'facebook-jssdk');
}
</script>
<?php
}
add_action( 'wp_head', 'thegrio_social_wp_head', 5 );

/**
 * Emit Facebook initializion script
 *
 * @uses esc_attr, esc_js, is_mobile
 * @action wp_head
 * @return string
 */
function thegrio_wp_head_init_facebook() {
	if ( ! jetpack_is_mobile() ) {
?>
<script>initFacebook();</script>
<?php
	}
}
add_action( 'wp_head', 'thegrio_wp_head_init_facebook', 6 );
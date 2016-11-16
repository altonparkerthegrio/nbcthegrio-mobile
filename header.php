<?php
include( get_template_directory() . '/core/core-header.php' );
// End WPtouch Core Header
?>

<body class="<?php wptouch_core_body_background(); ?> mobile">

<!-- New noscript check, we need js on now folks -->
<noscript>
<div id="noscript-wrap">
	<div id="noscript">
		<h2><?php _e( 'Notice', 'wptouch' ); ?></h2>
		<p><?php _e( 'JavaScript is currently turned off.', 'wptouch' ); ?></p>
	</div>
</div>
</noscript>
<!--#start The Login Overlay -->
	<div id="wptouch-login">
		<div id="wptouch-login-inner">
			<form name="loginform" id="loginform" action="<?php echo esc_url( wp_login_url() ); ?>" method="post">
				<label><input type="text" name="log" id="log" onFocus="if (this.value == 'username') {this.value = ''}" value="username" /></label>
				<label><input type="password" name="pwd"  onfocus="if (this.value == 'password') {this.value = ''}" id="pwd" value="password" /></label>
				<input type="hidden" name="rememberme" value="forever" />
				<input type="hidden" id="logsub" name="submit" value="<?php esc_attr_e( 'Login' ); ?>" tabindex="9" />
				<input type="hidden" name="redirect_to" value="<?php echo esc_url( filter_input( INPUT_SERVER, 'REQUEST_URI' ) ); ?>"/>
			</form>
		</div>
	</div>

<div id="headerbar">
	<div id="headerbar-title">
		<a href="<?php echo esc_url( home_url( '/' ) ); ?>"><img src="<?php echo esc_url( get_stylesheet_directory_uri() ) . '/images/TheGrioLogoNew.png'; ?>" alt="<?php esc_attr( get_bloginfo( 'name' ) ); ?>" /></a>
	</div>

	<div class="r1f-widget" data-header="false" data-size="medium" data-layout="horizontal">
		<a class="r1f-facebook" href="http://facebook.com/theGrio" data-account="http://facebook.com/theGrio" target="_blank"></a>
		<a class="r1f-twitter" href="http://twitter.com/theGrio" data-account="http://twitter.com/theGrio" target="_blank"></a>
	</div>

	<div id="headerbar-menu">
		<a href="#" onClick="bnc_jquery_menu_drop(); return false;">
			<span class="icon-bar"></span>
			<span class="icon-bar"></span>
			<span class="icon-bar"></span>
		</a>
	</div>

</div>
<div class="clear"></div>
<!-- #start The Search / Menu Drop-Down -->
	<div id="wptouch-menu" class="dropper">
		<div id="wptouch-search-inner">
			<form method="get" id="searchform" action="<?php echo esc_url( home_url( '/' ) ); ?>/">
			<input type="text" value="<?php esc_attr_e( 'Search&hellip;' ); ?>" onFocus="if ( this.value == '<?php echo esc_js( __( 'Search&hellip;' ) ); ?>') {this.value = ''}" name="s" id="s" />
				<input name="submit" type="hidden" tabindex="5" value="<?php esc_attr_e( 'Search' ); ?>"  />
			</form>
		</div>
        <div id="wptouch-menu-inner">
			<ul>
				<li><a href="<?php echo esc_url( home_url( '/' ) ); ?>">Home</a></li>
				<?php thegrio_mobile_menu(); ?>
			</ul>
        </div>
	</div>

<!-- #start the wptouch plugin use check -->
<?php wptouch_core_header_check_use(); ?>

<div id="fb-root"></div>
<div class="m-top" id="m-top">
	<?php dynamic_sidebar( 'mobile-top' ); ?>
</div>
<?php
/**
 * Retrieve page content to be output below
 */
//$topstories = false;
if(is_front_page()){
	$page_content = thegrio_get_page_content();
	if(!isset($topstories) || true != $topstories ){
		$topstories = true;
?>
<div id="top-stories" class="clearfix">
	<?php if ( ! empty( $page_content[ 'lead_story' ] ) ) : ?>
	<div id="lead-story" data-vr-zone="Lead Story">
		<?php echo wp_kses_post( $page_content[ 'lead_story' ] ); ?>
	</div>
	<?php endif; ?>
</div>

<?php }
?>
<div class="m-content" id="m-content">
	<?php dynamic_sidebar( 'lead_story_ad' ); ?>
</div>
<?php
} ?>
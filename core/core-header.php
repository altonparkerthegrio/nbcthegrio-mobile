<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head profile="http://gmpg.org/xfn/11">
	<meta http-equiv="Content-Type" content="<?php bloginfo('html_type'); ?>; charset=<?php bloginfo('charset'); ?>" />
	<meta name="viewport" content="initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />
	<title><?php $str = bnc_get_header_title(); echo stripslashes($str); ?></title>
	<link rel="alternate" type="application/rss+xml" title="<?php bloginfo('name'); ?> RSS Feed" href="<?php bloginfo('rss2_url'); ?>" />
	<link rel="apple-touch-icon" href="<?php echo bnc_get_title_image(); ?>" />
	<link rel="stylesheet" href="<?php bloginfo('stylesheet_directory'); ?>/style.css" type="text/css" media="screen" />
	<?php wptouch_core_header_styles(); wptouch_core_header_enqueue(); ?>
	<?php if (!is_single()) { ?>
<script type="text/javascript">
	addEventListener("load", function() {
		setTimeout(hideURLbar, 0); }, false);
		function hideURLbar(){
		window.scrollTo(0,1);
	}
</script>
<?php } ?>

<?php
	$browser_width = 480;

	$custom_header_url = get_header_image();
	if ( $custom_header_url && 'HEADER_IMAGE' != $custom_header_url ) :
		$custom_header_url .= '?w=' . $browser_width;
		if ( $custom_header_url ) :
			$custom_header_height = get_custom_header()->height();
			// don't show header images smaller than the headerbar height
			if ( 45 > $custom_header_height ) return false;
?>
<style type="text/css">
	#headerbar {
		background: url(<?php echo $custom_header_url; ?>) left top no-repeat;
		<?php if ( 45 < $custom_header_height ) echo 'height:' . $custom_header_height . 'px !important;'; ?>
	}
</style>
<?php endif; endif; ?>
<?php if ( is_singular() ) wp_enqueue_script( 'comment-reply' ); ?>
<!-- stuff -->
<?php
	wp_print_styles();
	wp_enqueue_scripts();
	wp_print_head_scripts();
	wp_generator();
 ?>
<!-- end stuff -->
</head>

<?php
//If a custom byline is set, skip the post. This cannot be done via WP_Query at this time; see http://core.trac.wordpress.org/ticket/18158.
if ( is_author() && function_exists( 'thegrio_get_byline' ) && thegrio_get_byline() ){
	return;
}
?>
<div class="post<?php echo esc_attr( 0 == $GLOBALS['wp_query']->current_post ? ' post-first' : '' ); ?>" id="post-<?php the_ID(); ?>">
	<?php if ( has_post_thumbnail() ) : ?>
		<a href="<?php the_permalink(); ?>" title="<?php the_title_attribute(); ?>"><?php the_post_thumbnail( 'thegrio-mobile-archive', array(
			'title' => the_title_attribute( array( 'echo' => false ) ),
			'alt' => the_title_attribute( array( 'echo' => false ) )
		) ); ?></a>
	<?php endif; ?>

	<a class="h2" href="<?php the_permalink(); ?>"><?php the_title(); ?></a>

	<a class="read-more" href="<?php the_permalink() ?>">Read More</a> | <a class="leave-comment" href="<?php comments_link(); ?>">Leave Comment</a>

</div>
<?php

$current_post_no = ($GLOBALS['wp_query']->current_post + 1);

if ( is_category() ) {
	$incremental_number = 3;
}
else {
	$incremental_number = 5;
}

if ( 0 == $current_post_no % $incremental_number ) { ?><div class="m-top" id="m-top" style='display:block;margin-left:auto;margin-right:auto;margin-top:8px;margin-bottom:8px;width:300px;height:250px;'>

<script type='text/javascript'>
var googletag = googletag || {};
googletag.cmd = googletag.cmd || [];
(function()
{ var gads = document.createElement('script'); gads.async = true; gads.type = 'text/javascript'; var useSSL = 'https:' == document.location.protocol; gads.src = (useSSL ? 'https:' : 'http:') + '//www.googletagservices.com/tag/js/gpt.js'; var node = document.getElementsByTagName('script')[0]; node.parentNode.insertBefore(gads, node); }
)();
</script>

<?php global $post;
if ( is_home() || is_front_page() ) { $targeturl = 'home'; } else { $url = parse_url( get_permalink( $post_id ) ); $targeturl = substr( $url['path'],0,40 ); }
if ( is_home() || is_front_page() ) { $categoryurl = 'home'; } else { $category = get_the_category( $post_id ); $categoryurl = substr( $category['path'],0,40 ); }
?>
<div id='<?php the_ID(); ?>-div-gpt-ad-1427688969360-44' style='width:300px;height:250px;margin-left:auto;margin-right:auto;'>
<script type='text/javascript'>
googletag.cmd.push(function() {
googletag.defineSlot('/92721697/GRIONET1-Multisize-W-Mobile_300x250', [[300, 250], [300, 100], [300, 50]], '<?php the_ID(); ?>-div-gpt-ad-1427688969360-44').addService(googletag.pubads());
googletag.pubads().setTargeting("url","<?php echo esc_url( $targeturl ) ?>");
googletag.pubads().setTargeting("category","<?php echo esc_url( $categoryurl ) ?>");
googletag.pubads().enableSingleRequest();
googletag.enableServices();
googletag.display('<?php the_ID(); ?>-div-gpt-ad-1427688969360-44');
});

</script></div>
</div>
<?php } ?>
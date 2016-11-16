<?php if ( is_attachment() ){
	return;
} ?>
<div class="comments">
	<?php if ( post_password_required() ) : ?>
		<p class="nopassword">This post is password protected. Enter the password to view any comments.</p>
	</div><!-- #comments -->
	<?php
			return;
		endif;
	?>

	<?php /* ?><div class="load-comments share-container comments-bottom"><a href="javascript:void(0)" class="social-button comments-button">Comment</a></div><?php */ ?>

<a name="fb-comments"></a>

<div id="fb-root"></div>
<script>(function(d, s, id) {
  var js, fjs = d.getElementsByTagName(s)[0];
  if (d.getElementById(id)) return;
  js = d.createElement(s); js.id = id;
  js.src = "//connect.facebook.net/en_US/sdk.js#xfbml=1&version=v2.7";
  fjs.parentNode.insertBefore(js, fjs);
}(document, 'script', 'facebook-jssdk'));</script>
<div class="fb-comments" data-href="<?php echo esc_url( get_permalink() ); ?>" data-numposts="5" data-colorscheme="light"></div>

	<div class="facebook-comments"></div>
</div><!-- #comments -->
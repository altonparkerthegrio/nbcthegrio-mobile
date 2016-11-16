<div class="previous_post_button"><?php previous_post_link( '%link', 'Previous Article', true ); ?></div><!-- Previous Post Link -->
<div class="next_post_button"><?php next_post_link( '%link', 'Next Article', true ); ?></div><!-- Next Post Link -->
<div class="clear"></div>

<div class="post post-single" id="post-<?php the_ID(); ?>">

	<a class="sh2" href="<?php the_permalink() ?>" rel="bookmark" title="<?php esc_attr_e( 'Permanent Link to', 'wptouch' ); ?> <?php the_title_attribute(); ?>"><?php the_title(); ?></a>

	<div class="single-post-meta-top">
		by <?php the_author_posts_link(); ?> |
		<?php the_time( 'F j, Y' ); echo ' at '; the_time( 'g:i A' ); ?>
	</div>
	<div  class="addthis_toolbox" >
	  <div class="custom_images" style="text-align:center; height:49px;">
		<a class="addthis_button_facebook pw-button-facebook"></a>
		<a class="addthis_button_twitter pw-button-twitter" tw:via="theGrio"></a>
		<a class="addthis_button_email pw-button-email">&nbsp;</a>
		<a class="comments_pw comments_pw_icon" href="#fb-comments" ><span class=""><fb:comments-count href="<?php echo esc_url( get_permalink() ); ?>"></fb:comments-count></a>
	 </span> </div>
	</div>

	<div class="singlentry">
		<?php
		if ( has_post_format( 'video' ) ) {
				thegrio_video_shortcode();
		}
		//If post is a gallery, use WordPress' gallery shortcode to display images.
		else if ( has_post_format( 'gallery' ) ) {
				echo wp_kses_post( thegrio_singleton( 'TheGrio_Gallery' )->render_gallery( get_the_ID() ) );
		}
		elseif ( has_post_thumbnail() ){
			the_post_thumbnail( 'thegrio-mobile-archive', array(
				'title' => the_title_attribute( array( 'echo' => false ) ),
				'alt' => the_title_attribute( array( 'echo' => false ) )
			) );
		}
		?>

		<div class="m-content" id="m-content">
			<?php dynamic_sidebar( 'mobile_single_page_top_ad' ); ?>
		</div>

		<?php the_content(); ?>

	</div>
	<div  class="addthis_toolbox" >
	  <div class="custom_images" style="text-align:center; height:49px;">
		<a class="addthis_button_facebook pw-button-facebook"></a>
		<a class="addthis_button_twitter pw-button-twitter" tw:via="theGrio"></a>
		<a class="addthis_button_email pw-button-email">&nbsp;</a>
		<a class="comments_pw comments_pw_icon" href="#fb-comments" ><span class=""><fb:comments-count href="<?php echo esc_url( get_permalink() ); ?>"></fb:comments-count></a>
	 </span> </div>
	</div>
	<div class="post-share-buttons" align="center">
		<a href="http://www.facebook.com/sharer/sharer.php?<?php echo esc_url( get_permalink() ); ?>"><img src="<?php echo esc_url( get_stylesheet_directory_uri() ) . '/images/FB_Share_Btn1.png'; ?>" width="310" height="53" alt="Share The Grio"/></a><a href="https://twitter.com/share?url=<?php echo esc_url( get_permalink() ); ?>"><img src="<?php echo esc_url( get_stylesheet_directory_uri() ) . '/images/TW_Share_Btn1.png'; ?>" width="310" height="53" alt="Share The Grio"/></a>
	</div>

	<div class="clearer"></div>

</div><!-- .post#post-<?php the_ID(); ?> -->

<div class="m-middle" id="m-middle">
	<?php dynamic_sidebar( 'mobile_single_page_taboola' ); ?>
</div>


<div class="m-middle" id="m-middle">
	<?php dynamic_sidebar( 'mobile-middle' ); ?>
</div>

<!-- Let's rock the comments -->
<?php comments_template(); ?>

<div class="previous_post_button"><?php previous_post_link( '%link', 'Previous Article', true ); ?></div><!-- Previous Post Link -->
<div class="next_post_button"><?php next_post_link( '%link', 'Next Article', true ); ?></div><!-- Next Post Link -->
<div class="clearer"></div>
<?php
	$next_post = get_next_post();
if ( ! empty( $next_post ) ) {
	?>
	<script type="text/javascript">
	jQuery(window).scroll(function() {
		if(jQuery(window).scrollTop() + jQuery(window).height() == jQuery(document).height()) {
			//alert("bottom!");
			window.location = '<?php echo esc_url( get_permalink( $next_post->ID ) ); ?>';
		}
	});
	</script>
	<?php
}
?>
<?php global $is_ajax; $is_ajax = isset( $_SERVER['HTTP_X_REQUESTED_WITH'] ); if ( ! $is_ajax ){ get_header(); } ?>
<?php $wptouch_settings = bnc_wptouch_get_settings(); ?>

<div class="content" id="content<?php echo esc_attr( md5( filter_input( INPUT_SERVER, 'REQUEST_URI' ) ) ); ?>">
<?php if ( have_posts() ) : while ( have_posts() ) : the_post(); ?>
	<div class="post">
		<a class="sh2" href="<?php echo esc_url( get_permalink( $post->post_parent ) ); ?>" rev="attachment"><?php echo esc_html( apply_filters( 'the_title', get_the_title( $post->post_parent ) ) ); ?></a>

		<div class="single-post-meta-top">
			<?php if ( function_exists( 'thegrio_get_custom_user_meta' ) ) :
				$post_author = get_post_field( 'post_author', $post->post_parent );
			?>
			by <a href="<?php echo esc_url( get_author_posts_url( $post_author ) ); ?>"><?php echo esc_html( thegrio_get_custom_user_meta( $post_author, 'display_name' ) ); ?></a>
			&nbsp;|&nbsp;
			<?php endif;

			echo esc_html( get_the_time( 'F j, Y', $post->post_parent ) . ' at ' . get_the_time( 'g:i A', $post->post_parent ) ); ?>
		</div>

		<?php thegrio_mobile_addthis(); ?>
	</div>
	<div class="clearer"></div>
</div>

<div class="post" id="post-<?php the_ID(); ?>">
	<div id="singlentry" class="<?php echo esc_attr( $wptouch_settings['style-text-size'] ); ?> <?php echo esc_html( $wptouch_settings['style-text-justify'] ); ?>">
		<a href="<?php echo esc_url( wp_get_attachment_url( $post->ID ) ); ?>"><?php echo esc_html( wp_get_attachment_image( $post->ID, 'thegrio-mobile-gallery', false, array( 'alt' => '', 'title' => '' ) ) ); ?></a>
		<?php if ( ! empty( $post->post_excerpt ) ) : ?>
		<p class="caption"></p></p><?php the_excerpt(); ?></p>
		<?php endif;
if ( ! empty( $post->post_content ) ) :
		?>
		<p class="image-description"><?php the_content(); ?></p>
<?php endif; ?>

		<?php if ( $post->post_parent ) : ?>
				<div class="navigation">
						<p class="alignleft"><?php previous_image_link() ?></p>
						<p class="alignright"><?php next_image_link() ?></p>
				</div>
		<?php endif; ?>
	</div>
</div>

	<?php endwhile; else : ?>

<!-- Dynamic test for what page this is. A little redundant, but so what? -->

	<div class="result-text-footer">
		<?php wptouch_core_else_text(); ?>
	</div>

	<?php endif; ?>
</div>

	<!-- Do the footer things -->

<?php global $is_ajax; if ( ! $is_ajax ){ get_footer(); }
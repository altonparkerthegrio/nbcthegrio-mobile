<?php global $is_ajax; $is_ajax = isset($_SERVER['HTTP_X_REQUESTED_WITH']); if ( ! $is_ajax ){ get_header(); } ?>
<?php $wptouch_settings = bnc_wptouch_get_settings(); ?>

<div id="content-container">
<div id="content-slider">
<div id="content" class="content content<?php echo esc_attr( md5( filter_input( INPUT_SERVER, 'REQUEST_URI' ) ) ); ?>" >
	<?php if ( have_posts() ) : while ( have_posts() ) : the_post();
			get_template_part( 'content', 'single' );
		?>
	<?php endwhile; else : ?>

	<div class="result-text-footer">
		<?php wptouch_core_else_text(); ?>
	</div>

	<?php endif; ?>
</div>
</div>
</div>

<!-- Do the footer things -->
<?php global $is_ajax; if ( ! $is_ajax ){ get_footer(); }
<?php global $is_ajax; $is_ajax = isset( $_SERVER['HTTP_X_REQUESTED_WITH'] ); if ( ! $is_ajax ){ get_header(); } ?>
<?php
	$wptouch_settings = bnc_wptouch_get_settings();
	bnc_validate_wptouch_settings( $wptouch_settings );
	$i = 0;
?>

<div class="content" id="content<?php echo esc_attr( md5( filter_input( INPUT_SERVER, 'REQUEST_URI' ) ) ); ?>">


	<div class="result-text"><?php wptouch_core_body_result_text(); ?></div>
<?php
if ( isset( $_GET[ 'pages-list' ] ) ) :
	include ('pages-list.php');
elseif ( ! empty( $_GET[ 'archives-list' ] ) ) :
	include ('archives-list.php');
else :
	if ( have_posts() ) :
		while ( have_posts() ) : the_post();
			get_template_part( 'content' );
		endwhile;

		global $wp_query;

		if ( empty( $max_page ) ){
			$max_page = $wp_query->max_num_pages;
		}
		if ( empty( $paged ) ){ $paged = 1; }

		$nextpage = intval( $paged ) + 1;

		?>
	<div id="ajaxentries<?php echo esc_attr( md5( filter_input( INPUT_SERVER, 'REQUEST_URI' ) ) ); ?>"></div>
</div><!-- #End post -->

<?php else : ?>

	<div class="result-text-footer">
		<?php wptouch_core_else_text(); ?>
	</div>

	<?php endif; endif; ?>

<!-- Here we're establishing whether the page was loaded via Ajax or not, for dynamic purposes. If it's ajax, we're not bringing in footer.php -->
<?php global $is_ajax; if ( ! $is_ajax ){ get_footer(); }
<?php
class TheGrio_Public_Good {

	public function __construct() {

		add_action( 'after_setup_theme', array( $this, 'action_after_setup_theme' ) );
		
	}

	public function action_after_setup_theme() {
		//Public Good
		$content_shortcode = '';
		if(is_category()){
			if( is_category('news') || is_category('health') || is_category('living') ){
				$content_shortcode .= do_shortcode('[takeaction source=thegrio]');
			}
			else if( is_category('living') || is_category('education-living') ){
				$content_shortcode .= do_shortcode('[takeaction targettype=takeaction targetid=literacy source=thegrio]');
			}
			else if( is_category('inspiration') ){
	
			}
			else if( is_category('entertainment') ){
				$content_shortcode .= do_shortcode('[takeaction targettype=takeaction targetid=music source=thegrio]');
			}
		}
		else if(is_single())
		{
			$primary_cat_for_post = thegrio_get_primary_category();
			
			if( $primary_cat_for_post == 'news' || $primary_cat_for_post == 'health' || $primary_cat_for_post == 'living' ){
				$content_shortcode .= do_shortcode('[takeaction source=thegrio]');
			}
			else if( $primary_cat_for_post == 'living' || $primary_cat_for_post == 'education-living' ){
				$content_shortcode .= do_shortcode('[takeaction targettype=takeaction targetid=literacy source=thegrio]');
			}
			else if( $primary_cat_for_post == 'entertainment' ){
				$content_shortcode .= do_shortcode('[takeaction targettype=takeaction targetid=music-2 source=thegrio]');
			}
			else if( $primary_cat_for_post == 'inspiration' ){
			
			}
			else{
				$content_shortcode .= do_shortcode('[takeaction source=thegrio]');
			}		
		}	
		return $content_shortcode;
	}

}
thegrio_singleton( 'TheGrio_Public_Good' );

<?php
/**
 * THEGRIO MOBILE ADS
 *
 * Bulk of functionality is derived from desktop theme's `TheGrio_Ads` class as there are only minor differences between the desktop and mobile ad implementations.
 */

class TheGrio_Mobile_Ads {

	/**
	 * Call the parent's constructor as it contains the action and filter registration needed to make this work
	 */
	public function __construct() {
		//parent::__construct();
		$this->ad_base_url = 'http://n4403ad.doubleclick.net/adj/gn.thegrio.com/';
		// Use hacked AJAX since mobile context isn't loaded when REQUEST_URI contains
		// wp-admin. See note in functions/class-thegrio-story-loader.php
		$this->ajax_url = '?ajax_action='.$this->ajax_action;

		add_action( 'template_redirect', array( $this, 'handle_ajax_requests' ) );
		add_filter( 'thegrio_iframe_ad_width', array( $this, 'filter_iframe_ad_width' ) );
	}

	function filter_iframe_ad_width( $width ) {
		return 320;
	}

	function handle_ajax_requests() {
		if ( empty( filter_input( INPUT_GET, 'ajax_action' ) ) ) {
			return;
		}

		do_action( 'wp_ajax_' . filter_input( INPUT_GET, 'ajax_action' ) );
		die( '-1' );

	}

	/**
	 * Override desktop theme's output in `wp_head`
	 *
	 * @action wp_head
	 * @return null
	 */
	public function action_wp_head() {}

	/**
	 * Set Ad Code Manager URL
	 *
	 * @param string $url
	 * @filter acm_default_url
	 * @return string
	 */
	public function filter_acm_default_url( $url ) {
		if ( 0 === strlen( $url ) ) {
			return path_join( $this->ad_base_url, '%zone1%;sect=%zone1%;mtfInline=%mtfInline%;pos=%pos%;sz=%sz%&kw=%kw%;&forecast=%forecast%&dw=%dw%;ord=' . $this->dart_ord );
		}
		else {
			return $url;
		}
	}

	/**
	 * Set Ad Code Manager ad slot
	 *
	 * @filter acm_ad_tag_ids
	 * @return array

	public function filter_acm_ad_tag_ids() {
		return array(
			// Mobile leaderboard
			array(
				'tag' => 'mobile_leaderboard',
				'url_vars' => array(
					'k' => '',
					'pos' => 1,
					'sz' => '320x50',
					'kw' => '',
					'forecast' => 1,
					'dw' => 1,
					'mtfInline' => 'true'
				)
			),
			// Mobile bottom ad
			array(
				'tag' => 'mobile_bottom',
				'url_vars' => array(
					'k' => '',
					'pos' => 2,
					'sz' => '300x250',
					'kw' => '',
					'forecast' => 1,
					'dw' => 1,
					'mtfInline' => 'true'
				)
			)
		);
	}
	 */

	/**
	 * Set Ad Code Manager URL whitelist
	 *
	 * @param array $whitelisted_urls
	 * @filter acm_whitelisted_script_urls
	 * @return array
	 */
	public function filter_acm_whitelisted_script_urls( $whitelisted_urls ) {
		return array( 'ad.mo.doubleclick.net', 'n4403ad.doubleclick.net' );
	}
}
thegrio_singleton( 'TheGrio_Mobile_Ads' );

/**
 * Render ad for requested slot
 *
 * @param string $slot
 * @uses thegrio_singleton
 * @return string or null
 */
function thegrio_mobile_ad( $slot = false ) {
	thegrio_singleton( 'TheGrio_Mobile_Ads' )->get_ad( $slot );
}
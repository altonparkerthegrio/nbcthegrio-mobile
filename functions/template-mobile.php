<?php
/**
 * Insert markup into HEAD
 * @action wp_head
 */

function thegrio_wp_head_mobile() {
	global $wp_session;
?>

        <!-- BEGIN SELECT HEAD TAG BY PAGE, POST AND CATEGORY -->
        <?php if (is_home() || is_front_page() || is_archive()) { ?>
            <!-- No Direct Ad Tags Here -->

        <?php } elseif (has_tag(array('sponsored', 'sponsor'))) { ?>
            <!-- No Direct Ad Tags Here -->

        <?php } elseif (has_tag(array('video', 'videos', 'slideshow', 'slideshows'))) { ?>
            <!-- NDN JavaScript -->
            <script type="text/javascript" src="//launch.newsinc.com/js/embed.js" id="_nw2e-js"></script>

        <?php } elseif (has_category(array('Travel and Leisure'))) { ?>
            <!-- No Direct Ad Tags Here -->

        <?php } elseif (has_category(array('slideshow', 'video'))) { ?>
            <!-- NDN JavaScript -->
            <script type="text/javascript" src="//launch.newsinc.com/js/embed.js" id="_nw2e-js"></script>

        <?php } elseif (is_single(array('lifetime-bring-it', 'lifetime-bring-it-2', '5-ways-to-love-yourself-now'))) { ?>
            <!-- NDN JavaScript -->
            <script type="text/javascript" src="//launch.newsinc.com/js/embed.js" id="_nw2e-js"></script>

        <?php } elseif (is_single(array(
                    'misty-copeland-takes-ballet-to-new-heights',
                    'blood-sweat-heels-daisy-lewellyn-cancer',
                    '15-richest-nba-players-of-all-time',
                    'infamous-black-celebrity-mugshots-slideshow',
                    'kordale-kaleb-black-gay-dads-fourth-child-movie',
                    'worst-nfl-players-who-won-the-heisman-trophy'))) {
            ?>

            <!-- NDN JavaScript -->
            <script type="text/javascript" src="//launch.newsinc.com/js/embed.js" id="_nw2e-js"></script>

        <?php } else { ?>
            <!-- NDN JavaScript -->
            <script type="text/javascript" src="//launch.newsinc.com/js/embed.js" id="_nw2e-js"></script>

        <?php } ?>
        <!-- END SELECT HEAD TAG BY PAGE, POST AND CATEGORY -->

        <!-- Begin Amazon Tag Code -->
        <script type='text/javascript' src='//c.amazon-adsystem.com/aax2/amzn_ads.js'></script>
        <script type='text/javascript'>
            try {
                amznads.getAds('3175');
            } catch (e) { /*ignore*/
            }
        </script>
        <script type='text/javascript'>
            try {
                amznads.setTargetingForGPTAsync('amznslots');
            } catch (e) { /*ignore*/
            }
        </script>
        <!-- End Amazon Tag Code -->

        <!-- Begin OpenX Bidder Tag Code -->
                <!­­// Begin OpenX Bidder //­­>
				<script type="text/javascript"
				src="//thegrio-d.openx.net/w/1.0/jstag?nc=92721697-thegrio"></script>
				<!­­// End OpenX Bidder //­­>
		<!-- End OpenX Bidder Tag Code -->

		<!-- Prebid Config Section START -->
		  <!-- Make sure this is inserted before your GPT tag -->
		  <script>
			var PREBID_TIMEOUT = 1500;

			var adUnits = [{
				code: 'div-gpt-ad-1427688969360-41',
				sizes: [300, 250],
				bids: [{
					bidder: 'aol',
					params: {
					   network: '10399.1',
                       placement: '4319552'
					}
				}]
			},{
				code: 'div-gpt-ad-1427688969360-44',
				sizes: [300, 250],
				bids: [{
					bidder: 'aol',
					params: {
					   network: '10399.1',
                       placement: '4319552'
					}
				}]
			},{
				code: 'div-gpt-ad-1427688969360-45',
				sizes: [300, 250],
				bids: [{
					bidder: 'aol',
					params: {
					   network: '10399.1',
                       placement: '4319549'
					}
				}]
			},{
				code: 'div-gpt-ad-1427688969360-46',
				sizes: [300, 250],
				bids: [{
					bidder: 'aol',
					params: {
					   network: '10399.1',
                       placement: '4319547'
					}
				}]
			},{
				code: 'div-gpt-ad-1427688969360-47',
				sizes: [300, 250],
				bids: [{
					bidder: 'aol',
					params: {
					   network: '10399.1',
                       placement: '4319546'
					}
				}]
			},{
				code: 'div-gpt-ad-1427688969360-53',
				sizes: [[320, 50], [300, 250]],
				bids: [{
					bidder: 'aol',
					params: {
					   network: '10399.1',
                       placement: '4319551'
					}
				}]
			},{
				code: 'div-gpt-ad-1427688969360-54',
				sizes: [300, 50],
				bids: [{
					bidder: 'aol',
					params: {
					   network: '10399.1',
                       placement: '4319548'
					}
				}]
			},{
				code: 'div-gpt-ad-1427688969360-55',
				sizes: [320, 50],
				bids: [{
					bidder: 'aol',
					params: {
					   network: '10399.1',
                       placement: '4319550'
					}
				}]
			},{
				code: 'div-gpt-ad-1427688969360-56',
				sizes: [320, 50],
				bids: [{
					bidder: 'aol',
					params: {
					   network: '10399.1',
                       placement: '4319545'
					}
				}]
			}];

			var pbjs = pbjs || {};
			pbjs.que = pbjs.que || [];

			pbjs.que.push(function() {
        		pbjs.setPriceGranularity("high");
    		});

    		pbjs.bidderSettings = {
			  aol: {
				bidCpmAdjustment : function(bidCpm){
				  // adjust the bid in real time before the auction takes place
				  return bidCpm * 0.8;
				}
			  }
			};

		  </script>
		<!-- Prebid Config Section END -->

  		<!-- Prebid Boilerplate Section START. No Need to Edit. -->
			<script type="text/javascript" src="<?php echo get_stylesheet_directory_uri(); ?>/js/prebid.js" async></script>

			<script>
			var googletag = googletag || {};
			googletag.cmd = googletag.cmd || [];
			googletag.cmd.push(function() {
				googletag.pubads().disableInitialLoad();
			});

			pbjs.que.push(function() {
				pbjs.addAdUnits(adUnits);
				pbjs.requestBids({
					bidsBackHandler: sendAdserverRequest
				});
			});

			function sendAdserverRequest() {
				if (pbjs.adserverRequestSent) return;
				pbjs.adserverRequestSent = true;
				googletag.cmd.push(function() {
					pbjs.que.push(function() {
						pbjs.setTargetingForGPTAsync();
						googletag.pubads().refresh();
					});
				});
			}

			setTimeout(function() {
				sendAdserverRequest();
			}, PREBID_TIMEOUT);

		  </script>
	  	<!-- Prebid Boilerplate Section END -->

<!-- Begin Google Tag Code -->
<script type='text/javascript'>
	var googletag = googletag || {};
	googletag.cmd = googletag.cmd || [];
	(function() {
	var gads = document.createElement('script');
	gads.async = true;
	gads.type = 'text/javascript';
	var useSSL = 'https:' == document.location.protocol;
	gads.src = (useSSL ? 'https:' : 'http:') +
	'//www.googletagservices.com/tag/js/gpt.js';
	var node = document.getElementsByTagName('script')[0];
	node.parentNode.insertBefore(gads, node);
	})();
</script>

<?php
if ( is_home() || is_front_page() ) {
		$targeturl = 'home';
		$categoryname = 'home';
} else {
		$url = parse_url( get_permalink( $post_id ) );
		$targeturl = substr( $url['path'],0,40 );
		$category = get_the_category( $post_id ); $categoryname = $category[0]->name;
}
?>
<script type='text/javascript'>
var ad42,ad45,ad46,ad47,ad48,ad54,ad55,ad56,ad57;
googletag.cmd.push(function() {
ad42 = googletag.defineSlot('/92721697/GRIONET1-Multisize-Interstitial-Mobile', [300, 250], 'div-gpt-ad-1427688969360-41').addService(googletag.pubads());
ad45 = googletag.defineSlot('/92721697/GRIONET1-Multisize-W-Mobile_300x250', [300, 250], 'div-gpt-ad-1427688969360-44').addService(googletag.pubads());
ad46 = googletag.defineSlot('/92721697/GRIONET1-Multisize-W-Mobile_300x250_BTF', [300, 250], 'div-gpt-ad-1427688969360-45').addService(googletag.pubads());
ad47 = googletag.defineSlot('/92721697/GRIONET1-Multisize-W-Mobile_300x250_BTF2', [300, 250], 'div-gpt-ad-1427688969360-46').addService(googletag.pubads());
ad48 = googletag.defineSlot('/92721697/GRIONET1-Multisize-W-Mobile_300x250_BTF3', [300, 250], 'div-gpt-ad-1427688969360-47').addService(googletag.pubads());
ad54 = googletag.defineSlot('/92721697/GRIONET1-Multisize-W-Mobile_320x50', [[320, 50], [300, 250]], 'div-gpt-ad-1427688969360-53').addService(googletag.pubads());
ad55 = googletag.defineSlot('/92721697/GRIONET1-Multisize-W-Mobile_320x50_BTF', [320, 50], 'div-gpt-ad-1427688969360-54').addService(googletag.pubads());
ad56 = googletag.defineSlot('/92721697/GRIONET1-Multisize-W-Mobile_320x50_BTF1', [320, 50], 'div-gpt-ad-1427688969360-55').addService(googletag.pubads());
ad57 = googletag.defineSlot('/92721697/GRIONET1-Multisize-W-Mobile_320x50_BTF2', [320, 50], 'div-gpt-ad-1427688969360-56').addService(googletag.pubads());
<?php if ( is_home() || is_front_page() ) { ?>

                    googletag.pubads().setTargeting("url",<?php echo wp_json_encode( $targeturl ) ?>);
                    googletag.pubads().setTargeting("category", "home");

<?php } elseif ( has_category( array( 'travel-and-leisure', 'living' ) ) ) { ?>

                    googletag.pubads().setTargeting("url",<?php echo wp_json_encode( $targeturl ) ?>);
                    googletag.pubads().setTargeting("category", "travel");

<?php } elseif ( has_category( array( 'slideshow', 'slideshows' ) ) ) { ?>

                    googletag.pubads().setTargeting("url",<?php echo wp_json_encode( $targeturl ) ?>);
                    googletag.pubads().setTargeting("category", "slideshow");

<?php } elseif ( has_category( array( 'health' ) ) ) { ?>

                    googletag.pubads().setTargeting("url", "/category/health/");
                    googletag.pubads().setTargeting("category", "health");

<?php } elseif ( has_tag( array( 'video', 'videos' ) ) ) { ?>

                    googletag.pubads().setTargeting("url",<?php echo wp_json_encode( $targeturl ) ?>);
                    googletag.pubads().setTargeting("category", "video");

<?php } elseif ( is_archive( array() ) ) { ?>

                    googletag.pubads().setTargeting("url",<?php echo wp_json_encode( $targeturl ) ?>);
                    googletag.pubads().setTargeting("category",<?php echo wp_json_encode( $categoryname ) ?>);

<?php } else { ?>

                    googletag.pubads().setTargeting("url",<?php echo wp_json_encode( $targeturl ) ?>);
                    googletag.pubads().setTargeting("category",<?php echo wp_json_encode( $categoryname ) ?>);
    <?php
	$posttags = get_the_tags();
	if ( ! empty($posttags) ) {
		foreach ( $posttags as $tags ) {
			?>
                            googletag.pubads().setTargeting( "tag", <?php echo wp_json_encode( $tags->name ); ?> );
        <?php
		}
	}
}
?>

googletag.pubads().enableSingleRequest();
googletag.pubads().collapseEmptyDivs();
googletag.enableServices();
});

var refreshFirstSlot = function() {
   googletag.cmd.push(function() {
	googletag.pubads().refresh([ad42]);
	googletag.pubads().refresh([ad45]);
	googletag.pubads().refresh([ad46]);
	googletag.pubads().refresh([ad47]);
	googletag.pubads().refresh([ad48]);
	googletag.pubads().refresh([ad54]);
	googletag.pubads().refresh([ad55]);
	googletag.pubads().refresh([ad56]);
	googletag.pubads().refresh([ad57]);
   });
 };
</script>

<!-- START Google Analytics Javascript -->
<script>
  (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
  (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
  m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
  })(window,document,'script','//www.google-analytics.com/analytics.js','ga');
  	ga('create', 'UA-47779192-1', 'auto');
  	ga('send', 'pageview');
</script>
<!-- END Google Analytics Javascript -->

<!-- End Google Tag Code -->

<!-- Start comScore tag -->
		<script>
			var _comscore = _comscore || [];
			_comscore.push({ c1: "2", c2: "19328551" });
			(function() {
				var s = document.createElement("script"), el = document.getElementsByTagName("script")[0];
				s.async = true;
				s.src = (document.location.protocol == "https:" ? "https://sb" : "http://b") + ".scorecardresearch.com/beacon.js";
				el.parentNode.insertBefore(s, el);
			})();
		</script>
		<noscript><img src="http://b.scorecardresearch.com/p?c1=2&c2=19328551&cv=2.0&cj=1" /></noscript>
<!-- End comScore tag -->

<!-- Begin Mobile Interstitial Waterfall -->
<?php if ( is_home() || is_front_page() || is_archive() ) { ?>
		<!-- No Mobile Direct Ad Tags Here -->
<?php } elseif ( is_single( array( 'lifetime-bring-it', 'lifetime-bring-it-2', '5-ways-to-love-yourself-now' ) ) ) { ?>
		<!-- No Mobile Direct Ad Tags Here -->
<?php } elseif ( in_category( array( 'Politics', 'Sports' ) ) ) { ?>

<!-- Start Kixer - Interstitial -->
<div id='__kx_ad_4333'></div>
<script type="text/javascript" language="javascript">
var __kx_ad_slots = __kx_ad_slots || [];

(function () {
	var slot = 4333;
	var h = false;
	__kx_ad_slots.push(slot);
	if (typeof __kx_ad_start == 'function') {
		__kx_ad_start();
	} else {
		var s = document.createElement('script');
		s.type = 'text/javascript';
		s.async = true;
		s.src = '//cdn.kixer.com/ad/load.js';
		s.onload = s.onreadystatechange = function(){
			if (!h && (!this.readyState || this.readyState == 'loaded' || this.readyState == 'complete')) {
				h = true;
				s.onload = s.onreadystatechange = null;
				__kx_ad_start();
			}
		};
		var x = document.getElementsByTagName('script')[0];
		x.parentNode.insertBefore(s, x);
	}
})();
</script>
<!-- End Kixer - Interstitial -->

<?php } else { ?>

<!-- Start Kixer - Interstitial -->
<div id='__kx_ad_4333'></div>
<script type="text/javascript" language="javascript">
var __kx_ad_slots = __kx_ad_slots || [];

(function () {
	var slot = 4333;
	var h = false;
	__kx_ad_slots.push(slot);
	if (typeof __kx_ad_start == 'function') {
		__kx_ad_start();
	} else {
		var s = document.createElement('script');
		s.type = 'text/javascript';
		s.async = true;
		s.src = '//cdn.kixer.com/ad/load.js';
		s.onload = s.onreadystatechange = function(){
			if (!h && (!this.readyState || this.readyState == 'loaded' || this.readyState == 'complete')) {
				h = true;
				s.onload = s.onreadystatechange = null;
				__kx_ad_start();
			}
		};
		var x = document.getElementsByTagName('script')[0];
		x.parentNode.insertBefore(s, x);
	}
})();
</script>
<!-- End Kixer - Interstitial -->

<?php } ?>
<!-- End Mobile Interstitial Waterfall -->

<!-- START Wibbitz Javascript -->
<script type="text/javascript" src="http://cdn4.wibbitz.com/thegrio/embed.js" async></script>
<!-- END Wibbitz Javascript -->

<?php
$pageName = $post->ID;

if ( ! isset ( $wp_session['pageView'][ $pageName ] ) ) {
	$wp_session['pageView'][ $pageName ] = 1;
}
else {
	if ( isset ( $wp_session['pageView'][ $pageName ] ) ) {
		$wp_session['pageView'][ $pageName ]++;
	}
	else {
		$wp_session['pageView'][ $pageName ] = 1;
	}
}

if ( $wp_session['pageView'][ $pageName ] >= 2 ) {
?>

<!-- GRIONET2-300x250 -->
<div id='div-gpt-ad-1427691918210-10' style='width:300px; height:250px;'>
<script type='text/javascript'>
googletag.cmd.push(function()
{ googletag.defineSlot('/92721697/GRIONET2-300x250', [300, 250], 'div-gpt-ad-1427691918210-10').addService(googletag.pubads()); googletag.pubads().enableSingleRequest(); googletag.pubads().collapseEmptyDivs(); googletag.enableServices(); googletag.display('div-gpt-ad-1427691918210-10'); }

);
</script>
</div>

<?php }
}
add_action( 'wp_head', 'thegrio_wp_head_mobile' );



/**
 * Display meta for posts in the Loop
 *
 * @uses the_permalink, the_title_attribute, comments_link, esc_url, get_permalink
 * @return string
 */
if ( ! function_exists( 'thegrio_river_meta' ) ) {
	function thegrio_river_meta() { ?>
		<span class="read-more">
			<a href="<?php esc_url( the_permalink() ); ?>" title="Permalink to <?php esc_attr( the_title_attribute() ); ?>" rel="bookmark">Read More</a>
		</span>
		<span class="sep"> | </span>
		<span class="leave-comment">
			<a href="<?php esc_url( comments_link() ); ?>" title="Comment on <?php esc_attr( the_title_attribute() ); ?>" rel="bookmark"><span class="generic-comment-text">Leave Comment</span><span class="fb-comment-count-wrapper">Comments (<fb:comments-count href="<?php echo esc_url( function_exists( 'wpcom_is_vip' ) ?  get_permalink() : 'http://example.com/' ); ?>" class="fbml-comment-count">-</fb:comments-count>)</span></a>
		</span>

	<?php }
}
<a class="fancybox" style="display:none;" id="triggerad" href="#inline1">test</a>
<div id="inline1" style="width:300px;height:250px;display: none;">
	<!-- GRIONET1-Multisize-W-Mobile_300x250 -->
	<div id='div-gpt-ad-1427688969360-44'>
	<script type='text/javascript'>
	googletag.cmd.push(function() { googletag.display('div-gpt-ad-1427688969360-44'); });
	</script>
	</div>
</div>
<div class="m-bottom" id="m-bottom" style="display: block; margin-left: auto; margin-right: auto; margin-top: 8px; margin-bottom: 8px; width: 300px;">
	<?php dynamic_sidebar( 'mobile-bottom' ); ?>
</div>

<div class="post-share-buttons" align="center">
		<a href="http://www.facebook.com/sharer/sharer.php?<?php echo esc_url( get_permalink() ); ?>"><img src="<?php echo esc_url( get_stylesheet_directory_uri() ) . '/images/FB_Share_Btn1.png'; ?>" width="310" height="53" alt="Share The Grio"/></a><a href="https://twitter.com/share?url=<?php echo esc_url( get_permalink() ); ?>"><img src="<?php echo esc_url( get_stylesheet_directory_uri() ) . '/images/TW_Share_Btn1.png'; ?>" width="310" height="53" alt="Share The Grio"/></a>
</div>

<div id="footer">
	<div id="wptouch-switch-link">
		<?php wptouch_core_footer_switch_link(); ?>
	</div>

	<?php do_action( 'wp_mobile_theme_footer' ); ?>

	<p>
		<?php if ( ! bnc_wptouch_is_exclusive() ) { wp_footer(); } ?>
	</p>

    <div class="adcontainer">
		<div id="320x50sticky" class="sticky_footer_ad">
			<!-- GRIONET1-Multisize-W-Mobile_320x50_BTF2 Sticky-->
			<div id='div-gpt-ad-1427688969360-56'>
				<script type='text/javascript'>
					googletag.cmd.push(function() { googletag.display('div-gpt-ad-1427688969360-56'); });
				</script>
			</div>
		</div>
	</div>

</div>

<?php //wptouch_get_stats(); ?>
<script language="javascript" type="text/javascript">
<?php if ( ! is_front_page() || ! is_home() ) { ?>
if (sessionStorage.getItem("is_reloaded")) {
	sessionStorage.setItem("is_reloaded", parseInt(sessionStorage.getItem("is_reloaded")) + parseInt(1));
	if(sessionStorage.getItem("is_reloaded") == parseInt(2)){
		jQuery("#triggerad").trigger('click');
	}
}
else
{
	sessionStorage.setItem("is_reloaded", parseInt(1));
}
<?php } ?>
</script>
<!-- BEGIN INSERT TAG BY PAGE, POST AND CATEGORY -->
<?php if ( is_home() || is_front_page() || is_archive() ) { ?>
		<!-- No Direct Ad Tags Here -->

<?php } elseif ( is_single( array( 'lifetime-bring-it', 'lifetime-bring-it-2', '5-ways-to-love-yourself-now' ) ) ) { ?>
		<!-- No Direct Ad Tags Here -->

<?php } elseif ( has_tag( array( 'slideshow', 'slideshows' ) ) ) { ?>
	<!-- Begin Analytics click tracking code -->
	<script>
	if(("onhashchange" in window)){
		window.onhashchange = function(){
		    _gaq.push(["_trackEvent", "Slides", document.location.pathname, document.location.hash]);
		    }
	}
	else{
	    var prevHash = window.location.hash;
	    window.setInterval(function(){
	        if(window.location.hash!=prevHash){
	            _gaq.push(["_trackEvent", "Slides", window.location.pathname, window.location.hash]);
	            prevHash = window.location.hash;
	           }
	    },100);
	}
	</script>

<?php } elseif ( has_category( array( 'slideshow', 'slideshows' ) ) ) { ?>
	<!-- Begin Analytics click tracking code -->
	<script>
	if(("onhashchange" in window)){
		window.onhashchange = function(){
		    _gaq.push(["_trackEvent", "Slides", document.location.pathname, document.location.hash]);
		    }
	}
	else{
	    var prevHash = window.location.hash;
	    window.setInterval(function(){
	        if(window.location.hash!=prevHash){
	            _gaq.push(["_trackEvent", "Slides", window.location.pathname, window.location.hash]);
	            prevHash = window.location.hash;
	           }
	    },100);
	}
	</script>

<?php } elseif ( in_category( array( 'Entertainment', 'Rhymes N Reasons', 'Music', 'The Dish', 'Books' ) ) ) { ?>

	<!-- Begin Kiosked Tag Code -->
	<script language="javascript" type="text/javascript" async="async" src="//widgets.kiosked.com/sniffer/get-script/sign/e01fed295e0c2bb4605de2ad62758b35/albumid/10257/co/10733.js"></script>

<?php } elseif ( in_category( array( 'News' ) ) ) { ?>

	<!-- Begin Kiosked Tag Code -->
	<script language="javascript" type="text/javascript" async="async" src="//widgets.kiosked.com/sniffer/get-script/sign/e01fed295e0c2bb4605de2ad62758b35/albumid/10257/co/10733.js"></script>

<?php } elseif ( in_category( array( 'Sports' ) ) ) { ?>

	<!-- Begin Kiosked Tag Code -->
	<script language="javascript" type="text/javascript" async="async" src="//widgets.kiosked.com/sniffer/get-script/sign/e01fed295e0c2bb4605de2ad62758b35/albumid/10257/co/10733.js"></script>

<?php } else { ?>

	<!-- Begin Kiosked Tag Code -->
	<script language="javascript" type="text/javascript" async="async" src="//widgets.kiosked.com/sniffer/get-script/sign/e01fed295e0c2bb4605de2ad62758b35/albumid/10257/co/10733.js"></script>

<?php } ?>
<!-- END INSERT TAG BY PAGE, POST AND CATEGORY -->

<!-- BEGIN INSERT TAG ROS -->

		<!-- Start Quantcast tag -->
		<script type="text/javascript">
			_qoptions={qacct:"p-fcscPwpQo46kg"};
		</script>
		<script async="true" type="text/javascript" src="http://edge.quantserve.com/quant.js"></script>
		<noscript><img src="http://pixel.quantserve.com/pixel/p-fcscPwpQo46kg.gif" style="display: none;" border="0" height="1" width="1" alt="Quantcast"/></noscript>
		<!-- End Quantcast tag -->

		<!-- Taboola Footer -->
		<script type="text/javascript">
		  window._taboola = window._taboola || [];
		  _taboola.push({flush: true});
		</script>

<!-- END INSERT TAG ROS -->

</body>
</html>
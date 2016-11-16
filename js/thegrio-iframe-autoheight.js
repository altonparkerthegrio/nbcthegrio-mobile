/*
  Plugin: iframe autoheight jQuery Plugin + Oomph / TheGrio modifications
  Version: 1.6.0
  Description: when the page loads set the height of an iframe based on the height of its contents
  see README: http://github.com/house9/jquery-iframe-auto-height 
*/
(function(a){a.fn.iframeAutoHeight=function(b){function d(a){c.debug&&c.debug===!0&&window.console&&console.log(a)}function e(b,c){d("Diagnostics from '"+c+"'");try{d("  "+a(b,window.top.document).contents().find("body")[0].scrollHeight+" for ...find('body')[0].scrollHeight"),d("  "+a(b.contentWindow.document).height()+" for ...contentWindow.document).height()"),d("  "+a(b.contentWindow.document.body).height()+" for ...contentWindow.document.body).height()")}catch(e){d("  unable to check in this state")}d("End diagnostics -> results vary by browser and when diagnostics are requested")}var c=a.extend({heightOffset:0,minHeight:0,callback:function(a){},debug:!1,diagnostics:!1},b);return d(c),this.each(function(){function f(b){c.diagnostics&&e(b,"resizeHeight");var f=a(b,window.top.document).contents().find("body"),g=f[0].scrollHeight+c.heightOffset;g<c.minHeight&&(d("new height is less than minHeight"),g=c.minHeight+c.heightOffset),d("New Height: "+g),b.style.height=g+"px",c.callback.apply(a(b),[{newFrameHeight:g}])}var b=0;d(this),c.diagnostics&&e(this,"each iframe");if(a.browser.safari||a.browser.opera){d("browser is webkit or opera"),a(this).load(function(){var a=0,c=this;c.style.height="0px";var e=function(){f(c)};b===0&&(a=500),d("load delay: "+a),setTimeout(e,a),b++});var g=a(this).attr("src");a(this).attr("src",""),a(this).attr("src",g)}else a(this).load(function(){f(this)})})}})(jQuery); 

function iframeLoaded( frameName, frameHeight ){
	var $ = jQuery;
	var current_iframe = $( "[name=" + frameName + "]" );

	if(typeof maximumiFrameHeight == "number")
		frameHeight = Math.min(frameHeight,maximumiFrameHeight);

	current_iframe.height( frameHeight );
	current_iframe.parents(".ad").height( frameHeight );
	current_iframe.removeData('loading');
}


function Utils() {}
Utils.prototype = {
	constructor: Utils,
	isElementInView: function (element, fullyInView) {
		var pageTop = jQuery(window).scrollTop();
		var pageBottom = pageTop + jQuery(window).height();
		var elementTop = jQuery(element).offset().top;
		var elementBottom = elementTop + jQuery(element).height();

		if (fullyInView === true) {
			return ((pageTop < elementTop) && (pageBottom > elementBottom));
		} else {
			return ((elementTop <= pageBottom) && (elementBottom >= pageTop));
		}
	}
};
var Utils = new Utils();	

function childOf(c,p){
	while((c=c.parentNode)&&c!==p); 
	return !!c; 
}	

function isBehindOtherElement(element) {
	const boundingRect = element.getBoundingClientRect()
	// adjust coordinates to get more accurate results
	const left = boundingRect.left + 1
	const right = boundingRect.right - 1
	const top = boundingRect.top + 1
	const bottom = boundingRect.bottom - 1

	var leftTop = document.elementFromPoint(left, top);
	var rightTop = document.elementFromPoint(right, top);
	var leftBottom = document.elementFromPoint(left, bottom);
	var rightBottom = document.elementFromPoint(right, bottom);

	if( !(leftTop instanceof HTMLElement) ) return false
	if( !(rightTop instanceof HTMLElement) ) return false
	if( !(leftBottom instanceof HTMLElement) ) return false
	if( !(rightBottom instanceof HTMLElement) ) return false

	if( 	(leftTop !== element) && (!childOf(leftTop, element)) &&
			(rightTop !== element) && (!childOf(rightTop, element)) &&
			(leftBottom !== element) && (!childOf(leftBottom, element)) &&
			(rightBottom !== element) && (!childOf(rightBottom, element))	) return true;

	return false
}

'use strict';
let RotateFade = class User {
	constructor(tp, tm, fd, rt) {
		this.tp = tp;
		this.tm = tm;
		this.fd = fd;
		this.rt = rt;
		this.tmi = null;
		this.box = '.'+this.tp+'-'+this.rt;
		var jQuery = jQuery;
	}
	
	onPageScroll() {
		var c = this;
		jQuery(document).ready(function() {
			jQuery(window).scroll(function() {
				jQuery(c.box).each(function() {
					var t = jQuery(this);
					if ( Utils.isElementInView(jQuery(this), false) && !isBehindOtherElement(jQuery(this)[0]) ) {
						if(c.tmi == null) {
							c.tmi = setTimeout(function() { 
								var prev = t.children('div[class$="-'+c.rt+'-inner"]').data('id');
								var shortcode = t.children('div[class$="-'+c.rt+'-inner"]').data('shortcode');
								c.reloadBox(prev,shortcode);
							}, c.tm);
						} else {
						}
					} else {
						clearTimeout(c.tmi);
						c.tmi = null;
					}
				});
			});
		});
	}

	onPageLoad() {
		var c = this;
		jQuery(document).ready(function(){	
			jQuery(c.box).each(function() {
				var t = jQuery(this);
				c.tmi = setTimeout( function() { 
					var prev = t.children('div[class$="-'+c.rt+'-inner"]').data('id');
					var shortcode = t.children('div[class$="-'+c.rt+'-inner"]').data('shortcode');
					c.reloadBox(prev,shortcode); 
				}, c.tm );
			});
			c.onPageScroll();
		});
	}

	reloadBox(prev,shortcode) {	
		var c = this;
// 		console.log('PREV '+prev);
		jQuery.ajax({
			type: 'post',
			url: ajaxurl,
			data: {
				action: 'ajax_load_type',
				type: c.tp,
				prev: prev,
				shortcode: shortcode
			},
			success: function(result) {
				jQuery(c.box).find('.cri').animate({opacity: 0}, c.fd, function() { // hide box, then show more 
					jQuery(c.box).html(result);
					crTruncate(c.box);
					jQuery(c.box).find('.cri').animate({opacity: 1}, c.fd, function() { // show box, then do:
						var t = jQuery(this);
						if (Utils.isElementInView(t, false) && !isBehindOtherElement(t[0])) {
							clearTimeout(c.tmi);
							c.tmi = setTimeout(function() { 
								var prev = t.closest('div[class$="-'+c.rt+'-inner"]').data('id');
								var shortcode = t.closest('div[class$="-'+c.rt+'-inner"]').data('shortcode');
								c.reloadBox(prev,shortcode);
							}, c.tm);
						}
					}); 

				}); 
			}
		})
		return false;
	}

}

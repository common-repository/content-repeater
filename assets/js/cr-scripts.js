$ = jQuery;  
function setCarHeight( slider ) { // slick slider autoheight: adaptiveHeight doesn't work on slidesToShow > 1
	var maxHeight = 0;
	slider.find('.slick-active > *').each(function(){
		var myHeight = $(this)[0].getBoundingClientRect().height;
		if (myHeight > maxHeight) { maxHeight = myHeight; }
	});
	slider.find('.slick-track').animate({height:maxHeight},800);
	slider.find('.slick-list').animate({height:maxHeight},800);
}
function crTruncate(parent = 'body') {
	$(parent+' *[class*="cr-lines-"]').each(function() { // hide then show for smoothness
		var t = $(this);
		var more = t.find('.cr-more').wrap('<div/>').parent().html(); // create temp parent to get html of more link
		t.find('.cr-more').unwrap(); // remove temp parent
		t.find('.cr-more').remove(); // remove more
		t.text(t.text()); // fill with plain text
		var ht = t.outerHeight();
		var ln = t.attr('class').match(/cr-lines-(\d+)/i)[1];
		var m = t.css('line-height').match(/([0-9\.,]+)([a-z]+)/i);
		var lh = m[1];
		var un = m[2];
		if(ht <= (lh * ln)) return; // if fits into max lines
		ht = lh * ln;
		t.append(more); // add more back
		t.find('.cr-more').show().css('display','inline-block'); // needs to be displayed for dotdotdot to work
		t.css({ // set more outer element hight
			'max-height'	: ht + un,
			'height'		: ht + un,
			'margin-bottom'	: lh + un,
		})
		
		setTimeout(function() {	// make asynchronious	
			t.dotdotdot({
				watch: true,
				keep: '.cr-more',
				ellipsis: '...',
				callback: function( isTruncated ) { 
					if(isTruncated == true) {
						t.find('.cr-more').css('visibility','visible'); // show more
					}
				}
			});		
		}, 4);
	});
}

(function($) {
    $.fn.equalHeight = function() {
		if (typeof observeResizes === "function") observeResizes();	
        var maxHeight = 0,
            t = $(this);
		t.css('height','auto'); // reset from previous resizes
        t.each( function() {
			var height = $(this)[0].getBoundingClientRect().height;
			if (height > maxHeight) { maxHeight = height; }
// 			console.log(height);
        });
		if(maxHeight > 1) return t.css('height',maxHeight);
    };
})(jQuery);

function dataEqualHeight() {
	$ = jQuery;
	if($('[data-equal-height]').closest('.cri').length > 0) {
		$('[data-equal-height]').closest('.cri').parent().addClass('cr-equal-height');
	} else {
		$('[data-equal-height]').parent().addClass('cr-equal-height');
	}
}
function doEqualHeight() {
	$ = jQuery;
	$('.cr-equal-height').each(function() {
		var t = $(this);
		t.find('.cri, [data-equal-height]').each(function(i) {
			var tt = $(this);
			if(t.find('img').length) { // if parent has images
				t.imagesLoaded( function() {
// 					tt.parent().children('.cri, [data-equal-height]').equalHeight();
					tt.parent().children('.cri, [data-equal-height]').matchHeight();
				});
			} else {
// 				tt.parent().children('.cri, [data-equal-height]').equalHeight();
				tt.parent().children('.cri, [data-equal-height]').matchHeight();
			}
			return false; // we only need to call equalHeight() on first image
		});
	});
}
var vis = (function(){
    var stateKey, eventKey, keys = {
        hidden: "visibilitychange",
        webkitHidden: "webkitvisibilitychange",
        mozHidden: "mozvisibilitychange",
        msHidden: "msvisibilitychange"
    };
    for (stateKey in keys) {
        if (stateKey in document) {
            eventKey = keys[stateKey];
            break;
        }
    }
    return function(c) {
        if (c) document.addEventListener(eventKey, c);
        return !document[stateKey];
    }
})();
vis(function(){
	if(vis()) {
		setTimeout(function() { 
			doEqualHeight();
			$(window).trigger('resize'); // trigger resize on tab activation to fix layouts
		}, 100);
	} 
});


jQuery(window).load(function($) {
});

jQuery(document).ready(function($){ 

	dataEqualHeight(); // addClass('cr-equal-height') to elements with data-equal-height
	doEqualHeight();

	setTimeout(function() { $('.cri').animate({opacity: 1}, 200); }, 200); // show all items
 	setTimeout(function() { crTruncate(); }, 4);
	
// 	var timerId = setInterval(function() { doEqualHeight(); }, 200); // iterate doEqualHeight()
// 	setTimeout(function() { clearInterval(timerId); }, 3000); // stop doEqualHeight() iteration
	
	$(window).resize(function(event) {
		doEqualHeight(); // equalize heights of sibling elements with .cr-equal-height
	});
	 
});

$ = jQuery;  
function doSlickOptions(box) {
	boxWidth = box.outerWidth();
	var thumbWidth = 150;
	var slidesToShow = Math.round(boxWidth/thumbWidth);
	var options = [{ // main slider
	  lazyLoad: 'progressive',
	  adaptiveHeight: true,
	  slidesToShow: 1,
	  slidesToScroll: 1,
	  arrows: true,
	  dots: true,
	  asNavFor: '#'+box.find('.slider-nav').attr('id')
	},
	{ // nav slider
	  lazyLoad: 'progressive',
	  slidesToShow: slidesToShow,
	  slidesToScroll: slidesToShow,
	  arrows: false,
	  dots: false,
	  centerMode: true,
	  focusOnSelect: true,
	  asNavFor: '#'+box.find('.slider-for').attr('id')
	}];
	if(box.find('.slider-nav').length === 0) {
		options[0]['asNavFor'] = 0;
	}
	if(box.data('fullheight') == 1) {
		options[0]['adaptiveHeight'] = false;
	}
	return options;
}

var boxWidth;

// jQuery(document).ready(function($){ 
$(window).load(function() {

	window.onresize = function(event) {
		$('.cr-slider-wrap').each(function() {
			var options = doSlickOptions($(this)),
				t = $(this);;
			if(t.find('.slider-nav').length) {
				var current = t.find('.slider-nav .slick-current').data('slick-index');
				t.find('.slider-nav').slick('unslick');
				t.find('.slider-nav').slick(options[1]);
			}
		});
	};

	$('.cr-slider-wrap').each(function() {
		var options = doSlickOptions($(this)),
			t = $(this);
		t.find('.slider-for').slick(options[0]);
		if(t.find('.slider-nav').length) {
			t.find('.slider-nav').slick(options[1]);
		}
	});
		
	$('.slider-for .slider-link').click(function(e) { // open PhotoSwipe
		e.preventDefault();
		var items = [];
		var parent = $(this).parents('.slider-for');
		parent.find('.slider-link').each(function(i) {
			items[i] = [];
			items[i]['src'] = $(this).data('href');
			items[i]['w'] = $(this).data('width');
			items[i]['h'] = $(this).data('height');
		});
		var pswpElement = document.querySelectorAll('.pswp')[0];
		var options = {
			index: $(this).data('index')+1
		};
		var gallery = new PhotoSwipe( pswpElement, PhotoSwipeUI_Default, items, options);
		gallery.init();	
	});
	 
});

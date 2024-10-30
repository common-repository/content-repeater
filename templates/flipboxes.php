/* Flipboxes */
<div class="f-wrap">
  <div class="front"> 
	  <div class="front-outer"> 
			<div class="front-inner"> 
				<div class="f-icon">{{Icon::icon}}</div>
				<h2>{{post_title}}</h2>
				<div class="f-text">{{Front Text::editor}}</div>
			</div>
  </div> 
	</div>
  <div class="back">
	  <div class="back-outer"> 
			<div class="back-inner"> 
				<h2>{{Back Heading::text}}</h2>
				<div class="f-text">{{Back Text::editor}}</div>
				<div class="f-link"><a href="{{Link::text}}">Find out more</a></div>
			</div>
		</div>
	</div> 
</div>
======
/* Flipboxes */
.f-wrap {
	text-align: center;
	height: 100%;
}
.f-wrap > div {
	padding: 1em;
	line-height: 1em;
	height: 100%;
	color: #FFF;
}
.f-wrap h2 {
	padding: 0;
	color: #FFF;
	margin-bottom: 0.4em;
}
.f-wrap p {
	margin: 0;
	line-height: 1.4;
}
.f-text {
	margin-bottom: 10px;
	line-height: 1;
}
.f-link {
	font-size: 1.2em;
	line-height: 2.2em;
}
.f-link a {
	color: #FFF;
	background: #ea8080;
	padding: 0.4em 1.5em;
	border-radius: 1em;
	font-size: 0.8em;
	line-height: 1;
	font-weight: bold;
	display: inline-block;
}
.f-link a:hover {
	background: #c15d5d;
	
}
.f-icon {
	font-size: 4em;
	padding: 0.3em;
}
.front {
	background: steelblue;
}
.front-outer, .back-outer {
	display: table;
	height: 100%;
	width: 100%;
}
.front-inner, .back-inner {
	display: table-cell;
	vertical-align: middle;
}
.back {
	background: #063052;
}
/* End Flipboxes */
======
jQuery(document).ready(function($){ 
	$('.f-wrap').flip({trigger:'hover'}).find('.front').each(function() {
		var t= $(this);
		if(t.parents('.cr-equal-height').length && t.parents('.cri').css('float') != 'none') {
			t.parent().children().css('height','100%');
		} else {
			t.css('height','auto');
			setTimeout(function() { t.parent().children('.front,.back').equalHeight(); }, 200);
		}
	});
});
======
https://use.fontawesome.com/releases/v5.7.2/css/all.css
{{cr_url}}assets/flip/jquery.flip.min.js
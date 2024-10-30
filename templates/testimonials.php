/* Testimonials */
<div class="tstm-box">
	<div class="cr-quote"></div>
	<div class="tstm-content" style="margin-left: 60px;">
		<div class="tstm">
			<div class="tstm-text cr-more-wrap" style="margin-bottom: 10px;">{{testimonial::editor::3::See More}}</div>
			<div class="tstm-info">
				<div class="tstm-author">
					<strong>{{person::text::nowrap}} </strong>
				</div>
				<div class="tstm-position">
					<em>{{company::text::nowrap}} </em>
				</div>
			</div>
		</div>
	</div>
</div>
======
/* Testimonials */
.cr-quote {
	position: absolute;
}
.cr-quote:before {
	content: "\201C";
	font-size: 80px;
	position: absolute;
	font-family: Georgia, 'Times New Roman', Times, serif;
	line-height: 0.9;
	font-weight: bold;
	opacity: 0.4;
	display: block;
}
/* End Testimonials */
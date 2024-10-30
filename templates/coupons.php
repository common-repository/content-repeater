/* Coupons */
<div class="c-wrap c-wrap-{{post_id}} clearfix">
	<div class="cr-col cr-col-4">
		<img src="{{Coupon Image::images::large::url}}" class="c-image" alt="Coupon Image">
		<div class="c-code">{{Coupon Code::text}}</div>
	</div>
	<div class="cr-col cr-col-8">
		<div class="c-title"><a href="{{Coupon Link::text}}">{{post_title}}</a></div>
		<div class="c-description">{{Coupon Description::editor}}</div>
		<div class="c-restriction">{{Coupon Restriction::text[Limit one coupon per customer. Not valid with any other offer/promotion.  Some restrictions may apply. No cash value.]}}</div>
		<div class="c-expiration">[if field="Expiration Date"]Expires on [/if]{{Expiration Date::date[Doesn't expire]::F j, Y}}</div>
	</div>
</div>
======
/* Coupons */
.c-wrap {
	width: 100%;
	height: 100%;
	border: 2px dashed #808181;
	padding: 20px;
	background: #f9f9f9;
	transform-origin: top left;
	overflow: hidden;
	clear: both;
	text-align: center;
}
.c-image {
	margin: 0 0 15px;
}
.c-code {
	font-size: 0.9em;
	color: #1f5f33;
	font-weight: 600;
	border-width: 2px;
	border-style: dashed;
	border-color: rgb(204, 204, 204);
	text-decoration: none !important;
	background: #e7e8e8 !important;
	padding: 4px 12px;
	line-height: 1.8;
	margin: 0 0 15px;
}
.c-title {
	color: #FFF;
	font-size: 1em;
	padding: 5px 10px;
	background: #393a39;
	font-weight: 400;
	line-height: 1.2;
	margin: 0 0 15px;
}
.c-title a {
	color: #FFF;
}
.c-description, .c-description p {
	line-height: 1.4;
}

.c-restriction {
	font-size: 0.7em;
	line-height: 1.4;
	margin-bottom: 10px;
	color: #999999;
}
.c-expiration {
	margin: 0;
	font-size: 0.8em;
	padding: 0;
	color: green;
}
.MD.cri .c-title {
	font-size: 2em;
}
/* End Coupons */
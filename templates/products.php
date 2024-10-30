/* Products */
<div class="p-wrap p-wrap-{{post_id}} clearfix">
	<div class="cr-col cr-col-5">
		<div class="p-image focus-img smoother" style="background-image: url({{Product Image::images::url}});">
			<div class="p-price">[if field="Sale Price"]<strike>[/if]${{Price::text}}[if field="Sale Price"]</strike>[/if]</div>
			{{Product Image::images}}
		</div>
	</div>
	<div class="cr-col cr-col-7">
		[if field="Sale Price"]<div class="p-sale"><label>New price: </label>${{Sale Price::text}}</div>[/if]
		<div class="p-title">[if has_single]<a href="{{post_url}}">[/if]{{post_title}}[if has_single]</a>[/if]</div>
		<div class="p-description">
			[if regular]{{Product Short Description::editor}}[/if]
			[if single]{{Product Description::editor}}[/if]
		</div>	
		<div class="p-buy">
			[if field="Buy Button"]{{Buy Button::editor::html}}[/if]
			[if nofield="Buy Button"]<a class="p-buy-btn" href="{{Buy Link::text}}"><span>Buy Now</span><img src="{{crurl}}assets/img/payment-cards.png" alt="Payment methods: Paypal, Visa, Mastercard, Amex, Discover"></a>[/if]	
		</div>
	</div>
</div>
======
/* Products */
.p-wrap {
	height: 100%;
	padding: 20px;
	background: #FFF;
	position: relative;
	text-align: center;
	border: 1px solid rgba(0,0,0,.1);
}
.cr-single .p-wrap {
	padding: 0;
	border: none;	
}
.p-title {
	font-weight: bold;
	margin: 0 0 20px;
	font-size: 1.3em;
	line-height: 1.3em;
}
.p-image {
	margin: 0 0 20px;
	position: relative;
	cursor: crosshair !important;
}
.p-image img {
	margin-bottom: 0;
}
.p-image:hover img {
	visibility: hidden;
}
.p-price {
	color: forestgreen;
	z-index: 1;
	text-align: center;
	margin: 0 0 20px;
	font-size: 1.3em;
	line-height: 3em;
	position: absolute;
	background: rgba(255,255,255,0.6);
	height: 3em;
	width: 3em;
	border-radius: 50%;
	right: 10px;
	top: 10px;
	font-weight: bold;
	text-shadow: 0px 0px 2px #FFF;
}
.p-description, .p-description p {
	line-height: 1.4;
}
.p-buy {
	text-align: center;
}
.p-buy-btn span {
	color: rgba(0,0,0,0.8);
	background: #ffc838;
	padding: 4px 10px;
	display: block;
	margin: 0 0 20px;
	border-radius: 3px;
	font-weight: bold;
	box-shadow: 0px 1px 1px rgba(0,0,0,0.4);	
}
.p-buy-btn span:hover {
	background: #ffce4e;
}
.p-sale {
	color: #FFFFFF;
    font-weight: bold;
    margin-bottom: 10px;
    background: coral;
    display: inline-block;
    padding: 3px 12px;
    border-radius: 20px;
}
.MD.cri .p-wrap {
	text-align: inherit;
}
.MD.cri .p-title {
	font-size: 2em;
}
.MD.cri .p-buy {
	text-align: left;
}
.MD.cri .p-buy-btn span {
	text-align: center;
	max-width: 130px;
	padding: 10px 20px;
}
/* End Products */
======
jQuery(document).ready(function($){ 
	Focus.init({
		smoother: true,
			elementID: '',
			cursor: 'crosshair',
			zoomFactor: '250%'
	});
});
======
{{cr_url}}assets/focus/vanilla/focus.js
{{cr_url}}assets/focus/styles/focus.css
/* Portfolio */
<div class="s-wrap s-wrap-{{post_id}} clearfix" style="background-image:url({{Front Image::images::large::url}});">
	[if single]
	<div class="s-col s-gallery">
		{{Gallery::images::slider}}
	</div>
	[/if]
	<div class="s-col s-text">
		[if regular]<div class="s-overlay">[/if]
		<div class="s-title">[if regular]<a href="{{post_url}}">[/if]{{post_title}}[if regular]</a>[/if]</div>
		<div class="s-description">
			[if regular]{{Portfolio Short Description::editor}}[/if]
			[if single]{{Portfolio Description::editor}}[/if]
		</div>	
		<div class="s-details">
			[if field="Client"]<div class="s-client"><label>Client: </label>{{Client::text}}</div>[/if]
			[if field="Link"]<div class="s-link"><label>Link: </label><a href="{{Link::text}}">{{Link::text}}</a></div>[/if]
		</div>
		[if regular]</div>[/if]
	</div>
</div>
======
/* Portfolio */
.s-wrap {
	height: 100%;
	background: #FFF;
	position: relative;
	text-align: center;
	background-size: cover;
	background-position: center;
}
.cr-single .s-wrap {
	padding: 0;
	background-image: none !important;
}
.s-text {
	padding: 40px;
	display: table;
	height: 100%;
	width: 100%;
}
.cr-single .s-text {
	padding: 0;
}
.s-overlay {
	background: rgba(255,255,255,0.7);
	padding: 20px;
	height: -webkit-fill-available;
	display: table-cell;
	vertical-align: middle;
}
.s-overlay:hover {
	background: rgba(255,255,255,0.8);
}
.s-overlay .s-link {
	display: none;
}
.s-col.s-text {
	text-align: center;
}
.s-col.s-gallery {
	margin-bottom: 20px;
}
.cr-single .s-col.s-gallery {
	margin-bottom: 40px;
}
.cri.LG .s-col.s-gallery {
	float: left;
	width: calc(100% - 340px);
	margin-right: 40px;
}
.cri.LG .s-col.s-text + .s-col.s-gallery {
	float: left;
	width: 300px;
	text-align: left;
}
.s-title {
	font-weight: bold;
	margin: 0 0 20px;
	font-size: 1.3em;
	line-height: 1.3em;
}
.s-description, .s-description p {
	line-height: 1.4;
}
.s-client {
}
.s-link {
	margin: 0;
}
.s-details label {
	font-weight: bold;
	display: inline-block;
	margin-right: 10px;
}
.cri.LG .s-details label {
	min-width: 60px;
}
.MD.cri .s-title {
	font-size: 2em;
}
/* End Portfolio */
/* Portals */
<div class="prt-outer">
	<a class="prt-inner" href="{{Link::text[#]}}" style="background-image:url({{Background Image::images::large::url}})">
		<div class="prt-border">
			<div class="prt-content">
				<h2>{{post_title}}</h2>
				<h3>{{Heading::text}}</h3>
			</div>
		</div>
	</a>
</div>
======
/* Portals */
.prt-outer {
	overflow: hidden;
	position: relative;
	display: block;
	height: 100%;
}
.prt-inner {
	padding: 20px;
	position: relative;
	color: #FFF;
	text-align: center;
	display: block;
	vertical-align: middle;
	height: 100%;
	width: 100%;
	background-size: cover;
	background-repeat: no-repeat;
	-webkit-transition: all .5s;
	-moz-transition: all .5s;
	-o-transition: all .5s;
	transition: all .5s;
}
.prt-inner h2 {
	color: #FFF;
	padding: 0;
	margin-bottom: 10px;
	text-transform: uppercase;
	font-weight: bold;
	font-size: 2em;
	line-height: 1.2em;
}
.prt-inner h3 {
	color: #FFF;
	padding: 0;
	font-weight: normal;
	font-size: 1.3em;
	line-height: 1.3em;
	margin-bottom: 0;
}
.prt-content {
	position: relative;
	display: table-cell;
	vertical-align: middle;
}
.prt-border {
	border: 1px solid #FFF;
	height: 100%;
	display: table;
	width: 100%;
	padding: 40px 20px;
}
.prt-outer:hover .prt-inner, .prt-outer:focus .prt-inner {
	-ms-transform: scale(1.1);
	-moz-transform: scale(1.1);
	-webkit-transform: scale(1.1);
	-o-transform: scale(1.1);
	transform: scale(1.1);
}
.prt-outer:hover .prt-border {
	border-color: transparent;
	-webkit-transition : border .3s ease-out;
	-moz-transition : border .3s ease-out;
	-o-transition : border .3s ease-out;
	transition : border .3s ease-out;
}
.prt-inner:before {
	content: "";
	height: 100%;
	width: 100%;
	position: absolute;
	top: 0;
	left: 0;
	background-color: rgba(0,0,0,0.2);
}
/* End Portals */
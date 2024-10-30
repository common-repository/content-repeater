/* Before & After */
<div class="ba-wrap">
  {{Image Before::images}}
  {{Image After::images}}
</div>
======
======
jQuery(document).ready(function($){ 
	setTimeout(function() { 
		$(".ba-wrap").twentytwenty();
	}, 4);
});
======
{{cr_url}}assets/twentytwenty/js/jquery.event.move.js
{{cr_url}}assets/twentytwenty/js/jquery.twentytwenty-modified.js
{{cr_url}}assets/twentytwenty/css/twentytwenty.css
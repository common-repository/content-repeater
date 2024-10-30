function setCookie(name,value,days) {
	var expires = "";
	if (days) {
		var date = new Date();
		date.setTime(date.getTime() + (days*24*60*60*1000));
		expires = "; expires=" + date.toUTCString();
	}
	document.cookie = name + "=" + (value || "")  + expires + "; path=/";
}
function getCookie(name) {
	var nameEQ = name + "=";
	var ca = document.cookie.split(';');
	for(var i=0;i < ca.length;i++) {
		var c = ca[i];
		while (c.charAt(0)==' ') c = c.substring(1,c.length);
		if (c.indexOf(nameEQ) == 0) return c.substring(nameEQ.length,c.length);
	}
	return null;
}   



jQuery(document).ready(function($){ 
	
	// prevent anything but numbers on input with class="numbers-only"
	$('.numbers-only input[type="text"]').keyup(function(){ 
		var t = $(this);
		if(!t.val().match(/^\d+$/) && t.val().length){
			$('<div class="cr-input-warning">Only numbers are allowed in this field</div>').insertBefore(this);
			this.value = this.value.replace(/[^0-9]/g,'');
			setTimeout(function() { 
				t.siblings('.cr-input-warning').remove();
			}, 1000);
		}
	});
	// prevent double quotes on input with class="no-double-quotes"
	$('.no-double-quotes input[type="text"]').keyup(function(){ 
		var t = $(this);
		if(t.val().match(/^.*".*$/) && t.val().length){
			$('<div class="cr-input-warning">Double quotes are not allowed in this field</div>').insertBefore(this);
			this.value = this.value.replace(/"/g,'');
			setTimeout(function() { 
				t.siblings('.cr-input-warning').remove();
			}, 1000);
		}
	});
	
	// auto submit Repeater select change
	$('#repeater-select').change(function() {
		var type = $(this).closest('form').data('type');
		window.location = 'edit.php?post_type='+type+'&page='+type+'-cr-repeaters&repeater='+$(this).val();
	});
	
	// "Rename fields" page: Types dropdown change
	$('#types-select').change(function() {
		$('#fields-select').remove();
		var t = $(this);
		t.siblings().hide();
		t.siblings('.spinner').show().css('visibility','visible');
		$.ajax({
			type: 'post',
			url: ajaxurl,
			data: {
				action: 'ajax_load_fields',
				type: t.val(),
			},
			success: function(result) {
				t.siblings('.cr-rename-label').show();
				t.siblings('.cr-rename-label').after(result);
				t.siblings('.spinner').css('visibility','hidden');
			}
		})
		return false;						    

	});

	// "Rename fields" page: display rename-to input on field selection
	$('body').on( 'change', '#fields-select', function() {
		$(this).siblings('.cr-to-label').show();
		$(this).siblings('.cr-rename-to').show();
	});	
	
	// check on document load
	if($('.cr-checkbox.more input[type="checkbox"]:checked').length > 0) {
     	$('.cr-more-hide').show();
	}
	
	// check/uncheck on change
	$('.cr-checkbox.more input[type="checkbox"]').change(function() {
	     if($(this).is(':checked')) {
	     	$('.cr-more-hide').show();
        } else {
	     	$('.cr-more-hide').hide();
        }
	});	
	
	$('body').on('click', '.cr-upload-image-button', function(e){
		e.preventDefault();
		if ( $(this).parents('.cr-template-background-image').length == 1 ) { 
			var multiple = false;
		} else {
			var multiple = true;
		}
		var button = $(this),
		custom_uploader = wp.media({
			title: 'Insert image',
			library : {
				// uncomment the next line if you want to attach image to the current post
				// uploadedTo : wp.media.view.settings.post.id, 
				type : 'image'
			},
			button: {
				text: 'Use image' 
			},
			multiple: multiple // for multiple image selection set to true
		}).on('select', function() { // it also has "open" and "close" events 
			var attachment = custom_uploader.state().get('selection').first().toJSON();
			$('.cr-template-background-image-options > div').css('display','inline-block');
			//if you set multiple to true, here is some code for getting the image IDs
			var attachments = custom_uploader.state().get('selection'),
				attachment_ids = new Array(),
				atts = new Array(),
				i = 0;
			attachments.each(function(attachment) {
				attachment_ids[i] = attachment['id'];
				atts[attachment['id']] = attachment;
// 				console.log( attachment );
				i++;
			});
			var parent = button.parents('.cr-upload-image-wrap');
			var input = parent.find('.cr-upload-image-holder');
			var values = input.val().split(',');
			values = values.map(function(v) { // make sure ids are treated as numbers
			  	return Number(v);
			});
			if(input.val().length > 0 && multiple == true) {
				var ids = values.concat(attachment_ids);
			} else {
				var ids = attachment_ids;
			}
			var unique_ids = [];
			$.each(ids, function(i, el){
				if($.inArray(el, unique_ids) === -1) unique_ids.push(el);
			});
			input.val(unique_ids.join(','));
			if(multiple == false) {
				parent.find('.cr-upload-image-list').empty();
				parent.find('.cr-upload-image-button').hide();
			}
			$.each(attachment_ids, function(i, el){
				if($.inArray(el, values) === -1 && atts[el]) {
					if(atts[el].attributes.sizes.thumbnail) {
						var src = atts[el].attributes.sizes.thumbnail.url;
					} else {
						var src = atts[el].attributes.url;
					}
					parent.find('.cr-upload-image-list').append('<li data-id="'+el+'" class="cr-upload-image" style="background-image:url('+src+')"><a href="post.php?post='+el+'&action=edit" class="cr-edit-image-button" target="_blank"></a><a href="#" class="cr-remove-image-button"></a></li>');
				}
			});
		})
		.open();	
	});	
	// remove image
	$('body').on('click', '.cr-remove-image-button', function(e){
		e.preventDefault();
		var input = $(this).parents('.cr-upload-image-wrap').find('.cr-upload-image-holder');
		var ids = input.val().split(',');
		ids = ids.map(function(v) { // make sure ids are treated as numbers
			return Number(v);
		});
		var id = Number($(this).data('id'));	
		if (ids.indexOf(id) > -1) ids.splice(ids.indexOf(id), 1);
		input.val(ids.join(','));
		// on template page:
		$('.cr-template-background-image-options > div').css('display','none');
		$('.cr-template-background-image-options option:selected').removeAttr('selected');
		$('.cr-template-background-image .cr-upload-image-holder').val('');
		$(this).parents('.cr-upload-image-wrap').find('.cr-upload-image-button').show();
		// end template page
		$(this).parents('.cr-upload-image').remove();
		return false;
	});   
	if($('.cr-template-background-image .cr-upload-image').length > 0) {
		$('.cr-template-background-image-options > div').css('display','inline-block');
	}
	
	// shorting images
	if($('.cr-upload-image').length > 0) {
		$('.cr-upload-image-list').sortable({
			cancel: '.cr-remove-image-button',
			update : function(e, ui) {
				var result = $(this).sortable('toArray', {attribute: 'data-id'});
				$(this).parents('.cr-upload-image-wrap').find('.cr-upload-image-holder').val(result);
			}
		});	
    }
	

	// ajax deleting type
	$('.cr-delete-type').click(function(e) {
		e.preventDefault();
		var t = $(this);
		var s = t.parents('.cr-type').find('.cr-type-name');
		if (window.confirm("Are you sure you want to delete the «"+s.text()+"» Content Type and all its content and settings?")) {
			t.parents('.cr-type').find('.spinner').css('visibility','visible');
			$.ajax({
				type: 'post',
				url: ajaxurl,
				data: {
					action: 'ajax_delete_type',
					type: t.data('type'),
				},
				success: function(result) {
					location.reload();
				}
			})
			return false;						    
		}
	});

	// ajax editing type
	$('.cr-edit-type').click(function(e) {
		e.preventDefault();
		var t = $(this);
		var w = t.data('target');
		var s = t.parents('.cr-type').find('span.cr-type-'+w);
		var n = t.parents('.cr-type').find('span.cr-type-name');
		s.hide();
		n.after('<input class="cr-type-'+w+'" type="text" data-type="'+t.data('type')+'" value="'+s.text()+'">');
		i = n.siblings('input.cr-type-'+w);
		var d = n.parents('.cr-type').find('.cr-delete-slug');
		if(w == 'slug' && s.length) {
			d.css('display','inline-block');
		}
		if(s.length) {
			i.css('width', s.outerWidth()+15).on('keydown keyup change', function() {
				s.text(i.val());
				i.css('width', s.outerWidth()+15);
			});		
		}
		i.show().select().on('focusout', function() {
			s.text(s.data(w)).show();
			i.remove();
			d.hide()
		});
		i.on('keydown',function(e) {
			if(e.which == 13) {
				e.preventDefault();
				if(i.val() == s.data(w)) return // if no change
				t.parents('.cr-type').find('.spinner').css('visibility','visible');
				$.ajax({
					type: 'post',
					url: ajaxurl,
					data: {
						action: 'ajax_edit_type',
						type: i.data('type'),
						target: w,
						value: i.val(),
					},
					success: function(result) {
						location.reload();
					}
				})
				return false;						    
			} else if(e.which == 27) { // blur if escape pressed
				i.blur();
			}
		});		
	});
	
	// ajax deleting type slug
	$('.cr-delete-slug').on('mousedown', function(e) {
		t = $(this);
		if (window.confirm("Are you sure you want to delete the slug?")) {
			t.hide();
			e.preventDefault();
			t.parents('.cr-type').find('.spinner').css('visibility','visible');
			$.ajax({
				type: 'post',
				url: ajaxurl,
				data: {
					action: 'ajax_edit_type',
					type: t.data('type'),
					target: 'delete-slug',
					value: 'delete-slug',
				},
				success: function(result) {
					location.reload();
				}
			})
			return false;		
		} else {
			t.hide();
		}				    
	
	});	
	
	// initialize color-picker field
	$('.color-field').each(function(){
			$(this).wpColorPicker();
	});
	
	// ajax ordering types
	if($('#cr-sortable').length > 0) {
		$('#cr-sortable').sortable({
			axis: 'y',
			handle: '.handle',		
			update : function(e, ui) {
				$('.cr-second-col h3 .spinner').css('visibility','visible');
				$.ajax({
					type: 'post',
					url: ajaxurl,
					data: {
						action: 'ajax_sort_types',
						order: $('#cr-sortable').sortable('serialize'),
					},
					success: function(result) {
						location.reload();
					}
				});
			}
		});	
    }
    
    $('.cr-categorize-type').change(function() {
    	var t = $(this);
    	t.attr('disabled', true);
        if(t.is(':checked')) {
        	var value = 'Yes';
        } else {
        	var value = 'No';
        }
		$.ajax({
			type: 'post',
			url: ajaxurl,
			data: {
				action: 'ajax_type_categorize',
				type: t.data('type'),
				categorize: value,
			},
			success: function(result) {
				console.log(result);
				location.reload();
			}
		});

    });    
    
	 
});

jQuery(document).ready(function($){ 

	// insert at cursor in plain textarea
	function insertAtCaret(areaId, text) {
	  var txtarea = document.getElementById(areaId);
	  if (!txtarea) {
		return;
	  }

	  var scrollPos = txtarea.scrollTop;
	  var strPos = 0;
	  var br = ((txtarea.selectionStart || txtarea.selectionStart == '0') ?
		"ff" : (document.selection ? "ie" : false));
	  if (br == "ie") {
		txtarea.focus();
		var range = document.selection.createRange();
		range.moveStart('character', -txtarea.value.length);
		strPos = range.text.length;
	  } else if (br == "ff") {
		strPos = txtarea.selectionStart;
	  }

	  var front = (txtarea.value).substring(0, strPos);
	  var back = (txtarea.value).substring(strPos, txtarea.value.length);
	  txtarea.value = front + text + back;
	  strPos = strPos + text.length;
	  if (br == "ie") {
		txtarea.focus();
		var ieRange = document.selection.createRange();
		ieRange.moveStart('character', -txtarea.value.length);
		ieRange.moveStart('character', strPos);
		ieRange.moveEnd('character', 0);
		ieRange.select();
	  } else if (br == "ff") {
		txtarea.selectionStart = strPos;
		txtarea.selectionEnd = strPos;
		txtarea.focus();
	  }

	  txtarea.scrollTop = scrollPos;
	}

	// insert at cursor in CodeMirror
	function insertTextAtCursor(editor, text) {
		var doc = editor.getDoc();
		var cursor = doc.getCursor();
		doc.replaceRange(text, cursor);
	}	
	
	// load pre-built Template
	$('#cr-prebuit-templates').change(function() {
		$('button#content-html').click(); // disable Visual editor
		var t = $(this);
		if(t.val() == 'none') return;
		t.parents('.cr-prebuit-templates-wrap').find('.spinner').css('visibility','visible');
		$.ajax({
			type: 'post',
			url: ajaxurl,
			data: {
				action: 'ajax_load_template',
				file: $(this).val(),
			},
			success: function(result) {
				$('#cr-html').trigger('click');
				if(result.indexOf('======') >= 0) {
					var results = result.split('======');
					$('#cr-template').siblings('.CodeMirror')[0].CodeMirror.getDoc().setValue(results[0].trim());
					if(results[1])
						$('#cr-template-custom-css').siblings('.CodeMirror')[0].CodeMirror.getDoc().setValue(results[1].trim());
					if(results[2])
						$('#cr-template-custom-js').siblings('.CodeMirror')[0].CodeMirror.getDoc().setValue(results[2].trim());
					if(results[3])
						$('#cr-template-custom-files').val(results[3].trim());
				} else {
					$('#cr-template').siblings('.CodeMirror')[0].CodeMirror.getDoc().setValue(result.trim()); 
				}
				t.parents('.cr-prebuit-templates-wrap').find('.spinner').css('visibility','hidden');
			}
		})
		return false;						    
	});

    // open lightbox for shortcodes generator
    function closeLightbox() {
    	$('.cr-buttons-wrap button').removeClass('active');
    	$('.cr-template-tags').appendTo('.cr-template-tags-wrap');
		$('#cr-lightbox-wrap').remove();
		$('.cr-insert,.cr-insert-code-wrap').hide(); 
    }
    $('.cr-insert-shortcode').click(function(e) {
    	e.preventDefault();
    	$('body').prepend('<div id="cr-lightbox-wrap"><div id="cr-lightbox"><a class="cr-lightbox-close">&times;</a></div></div>');
    	$('.cr-template-tags').appendTo('#cr-lightbox');
		$('body').on('keydown',function(e) {
			if(e.which == 27) { // blur if escape pressed
				closeLightbox();
			} else if(e.which == 13) { // insert when enter key pressed
				if($('#cr-insert').is(':visible') && !$(':focus').is('textarea')) {
					e.preventDefault();
					insertShortcode();
				}
			}
		});
		$('.cr-lightbox-close').click(function() {
			closeLightbox();
		});
		$('#cr-lightbox-wrap').click(function() {
			closeLightbox();
		});
		$("#cr-lightbox").click(function(e) {
			e.stopPropagation();
		});    	
    });
    
    
    function generateShortcode(parent,redo = false) {
		var out = parent.data('target');
		var init = parent.find('.cr-name').data('init');
		var name = parent.find('.cr-name');
		var format = parent.find('.cr-format');
		var choices = parent.find('.cr-choices');
		var lines = parent.find('.cr-lines');
		var more = parent.find('.cr-more');
		var nowrap = parent.find('.cr-nowrap');
		var nothumb = parent.find('.cr-nothumb');
		var nolink = parent.find('.cr-nolink');
		var fullheight = parent.find('.cr-fullheight');
		var novisual = parent.find('.cr-novisual');
		var stop = false;
		if(out == 'conditional') {
			var field = parent.find('.cr-field').val();
			var $value = parent.find('.cr-value');
			var value = $value.val();
			var operator = parent.find('.operator').val();
			var display = parent.find('.cr-conditional-display').val();
			var fieldKey = 'field';
			var condition = '';
			if(operator == 'field' || operator == 'nofield') {
				$value.parents('.cr-insert-inner').hide();
				fieldKey = operator;
			} else {
				$value.parents('.cr-insert-inner').show();
				condition = ' '+operator+'="'+value+'"';
			}			
    		out = '[if '+fieldKey+'="'+field+'"'+condition+']'+display+'[/if]';
			$('.cr-insert-code-wrap').show();
			$('#cr-insert').text(out);
			$out = '';
			return;    		
		}
		if(name.length) {
			if(name.val().trim().length) {
				out = name.val().trim() + '::' + out;
			} else {
				out = init + '::' + out;
			}
		}
		if(format.length && format.val().trim().length) {
			out = out + '::' + format.val().trim();
		}
		if(lines.length && lines.val().trim().length) {
			out = out + '::' + lines.val().trim();
		}
		if(more.length && more.val().trim().length) {
			out = out + '::' + more.val().trim();
		}
		if(nowrap.length && nowrap.is(':checked')) {
			out = out + '::nowrap';
		}
		if(novisual.length && novisual.is(':checked')) {
			out = out + '::html';
		}
		if(choices.length && choices.val().trim().length) {
			out = out + '::' + choices.val().replace(/(?:\r\n|\r|\n)/g, '::');
		}
		parent.find('select').each(function() {
			var value = $(this).children('option:selected').val();
			if($(this).hasClass('output') && value == 'id' && redo == false) { // if image id set, reset & hide size
				parent.find('select.size').val('none').parents('.cr-insert-inner').hide();
				generateShortcode(parent, true);
				stop = true;
				return;
			} else if($(this).hasClass('output') && value == 'slider' && redo == false) {
				parent.find('select.size').val('none').parents('.cr-insert-inner').hide();
				parent.find('.cr-default').val('').parents('.cr-insert-inner').hide();
				nothumb.parents('.cr-insert-inner').show();
				nolink.parents('.cr-insert-inner').show();
				fullheight.parents('.cr-insert-inner').show();
				generateShortcode(parent, true);
				stop = true;
				return;
			} else if(redo == true) { // hide irrelevant fields on redo generateShortcode()
				if(nothumb.length && nothumb.is(':checked') && value.includes('slider')) {
					value = value + '::nothumb';
				}
				if(nolink.length && nolink.is(':checked') && value.includes('slider')) {
					value = value + '::nolink';
				}
				if(fullheight.length && fullheight.is(':checked') && value.includes('slider')) {
					value = value + '::fullheight';
				}
				parent.find('select.size').parents('.cr-insert-inner').hide();
				parent.find('.cr-default').parents('.cr-insert-inner').hide();
			} else {
				parent.find('select.size').parents('.cr-insert-inner').show();
				parent.find('.cr-default').parents('.cr-insert-inner').show();
				nothumb.parents('.cr-insert-inner').hide();
				nolink.parents('.cr-insert-inner').hide();
				fullheight.parents('.cr-insert-inner').hide();
			}					
			if(value != 'none') {
				out = out + '::' + value;
			}
		});
		if(stop == true) return;
		if(parent.find('.cr-default').length && parent.find('.cr-default').val().trim() != '') {
			out = out.split('::');
			var pos = 1;
			if(!parent.find('.cr-name').length) pos = 0;
			out[pos] = out[pos] + '[' + parent.find('.cr-default').val().trim() + ']';
			out = out.join('::');
		}
		$('.cr-insert-code-wrap').show();
		$('#cr-insert').text(oo + out + cc);
		$out = '';
    }

    // take text from $('#cr-insert').text() in lightbox and insert at cursor
    function insertShortcode() {
    	$('.cr-insert input').blur(); // cause val() on inputs to update
		if (typeof(tinyMCE) != 'undefined' && tinyMCE.activeEditor != null && tinyMCE.activeEditor.isHidden() == false) {
			tinyMCE.execCommand('mceInsertContent', false, $('#cr-insert').text());
		} else if($('.CodeMirror')[0]) {
			insertTextAtCursor($('#cr-template').siblings('.CodeMirror')[0].CodeMirror, $('#cr-insert').text());
		} else {
			insertAtCaret('cr-template',$('#cr-insert').text());
		}
		closeLightbox();
    }

    // shortcode-type button click in lightbox
    $('.cr-buttons-wrap button').click(function(e) {
    	e.preventDefault();
    	$('.cr-buttons-wrap button').removeClass('active');
    	var t = $(this);
		t.addClass('active');    	
    	$('.cr-insert').hide();
    	if(t.data('target')) {
    		var s = $('.cr-insert[data-target="'+$(this).data('target')+'"]');
    		s.show();
    		var i = t.val().split('::')[0];
    		s.find('.cr-name, .cr-field').val(i).select();
    		s.find('.cr-name').data('init', i);
			generateShortcode(s);		
    	} else {
			$('#cr-insert').text(oo + $(this).val() + cc);
    		insertShortcode();
	   	}
    });    

	// watch of changes of selects in lightbox controls
	$('.cr-insert select, .cr-insert input[type="checkbox"]').on('change', function() {
		generateShortcode($(this).parents('.cr-insert'));
	});

	// watch of changes of texts in lightbox controls
	$('.cr-insert input[type="text"], .cr-insert textarea').on('keyup', function() {
		$(this).change(); // the val() to update
		generateShortcode($(this).parents('.cr-insert'));
	});

	// click "Insert shortcode" button in lightbox
	$('#cr-insert-button').click(function(e) {
    	e.preventDefault();
    	insertShortcode();
	});

	// open Insert Media thickbox 	
    if ($('.cr-insert-media').length > 0) {
        if ( typeof wp !== 'undefined' && wp.media && wp.media.editor) {
            $(document).on('click', '.cr-insert-media', function(e) {
                e.preventDefault();
                var button = $(this);
                var input = button.prev();
                wp.media.editor.send.attachment = function(props, att) {
					var link_beg = '', link_end = '';
					if(props.link != 'none') {
						var link = att.url;
						if(props.link == 'post') {
							link = att.link;
						} else if(props.link == 'custom') {
							link = props.linkUrl;
						}
						link_beg = '<a href="'+link+'" title="'+att.title+'">';
						link_end = '</a>';
					}
					var out = link_beg+'<img class="align'+props.align+' size-'+props.size+' wp-image-'+att.id+'" src="'+att.sizes[props.size].url+'" alt="'+att.alt+'" width="'+att.sizes[props.size].width+'" height="'+att.sizes[props.size].height+'">'+link_end;
					if (typeof(tinyMCE)!='undefined' && tinyMCE.activeEditor!=null && tinyMCE.activeEditor.isHidden()==false) {
						tinyMCE.execCommand('mceInsertContent', false, out);
					} else if($('.CodeMirror')[0].CodeMirror) {
	                    insertTextAtCursor($('#cr-template').siblings('.CodeMirror')[0].CodeMirror, out);
					}
                };
                wp.media.editor.open(button);
                return false;
            });
        }
    }
    
	// add field-tags highlighting to CodeMirror
	if( $('#cr-template').length ) {
		wp.CodeMirror.defineMode("cr-mode", function(config, parserConfig) {
		  var mustacheOverlay = {
			token: function(stream, state) {
			  var ch;
			  if (stream.match(/^\[(if|\/if).*?/)) {
				while ((ch = stream.next()) != null)
				  if (ch == "]") {
					return "shortcode";
				  }
			  }
			  if (stream.match("{{")) {
				while ((ch = stream.next()) != null)
				  if (ch == "}" && stream.next() == "}") {
					stream.eat("}");
					return "mustache";
				  }
			  }
			  while (stream.next() != null && !stream.match(/^(\[if.+\]|\[\/if\]|{{(.+)}})?/, false)) {}
			  return null;
			}
		  };
		  return wp.CodeMirror.overlayMode(wp.CodeMirror.getMode(config, parserConfig.backdrop || "text/html"), mustacheOverlay);
		});
	}
		    
	// show CodeMirror
	function showCodeMirror(id,mode='cr-mode') {
		if( $('#'+id).length ) {
			if(id == 'cr-template') {
				setCookie($('#'+id).attr('name'), 'code');
				$('.wp-editor-tabs button').removeClass('active');
				$('#cr-html').addClass('active');
			}
			if( $('#'+id).hasClass('nosyntax') ) return;
			$('#'+id).hide();
			var editorSettings = wp.codeEditor.defaultSettings ? _.clone( wp.codeEditor.defaultSettings ) : {};
			editorSettings.codemirror = _.extend(
				{},
				editorSettings.codemirror,
				{
					indentUnit: 2,
					tabSize: 2,
					mode: mode,
					extraKeys: {
						'Cmd-S':function(){
							$('#'+id).parents('form').find('input[type="submit"]').trigger('click');
						},
						'Ctrl-S':function(){
							$('#'+id).parents('form').find('input[type="submit"]').trigger('click');
						},
						'Ctrl-Space':'autocomplete',
						'Ctrl-\/':'toggleComment',
						'Cmd-\/':'toggleComment',
						'Alt-F':'findPersistent',
						'Ctrl-F':'findPersistent',
						'Cmd-F':'findPersistent',
						
					}  					
				}
			);
			var editor = wp.codeEditor.initialize( $('#'+id), editorSettings );			
		}
	}
	
	// show Visual TinyMCE editor
	function showVisualEditor(id) {
		var settings = {
			tinymce: {
				skin : 'lightgray',
				statusbar : false,
				toolbar1: 'bold, italic, strikethrough, bullist, numlist, blockquote, hr, alignleft, aligncenter, alignright, link, spellchecker, wp_adv',
				toolbar2: 'formatselect, underline, alignjustify, forecolor, pastetext, removeformat, charmap, outdent, indent, undo, redo, wp_help',
			},
		};		
		wp.editor.initialize(id, settings);
		setCookie($('#'+id).attr('name'), 'visual');
		$('.wp-editor-tabs button').removeClass('active');
		$('#cr-tmce').addClass('active');
	}	
	// check what editor was used before save and if visual is disabled
	if(getCookie($('#cr-template').attr('name')) == 'visual' && $('.wp-editor-tabs').length) {
		showVisualEditor('cr-template');
		$('#cr-template-editor-type').val('visual');
	} else {
		showCodeMirror('cr-template');
		$('#cr-template-editor-type').val('code');
	}
	// apply CodeMirror to CSS field
	showCodeMirror('cr-template-custom-css','text/css');
	// apply CodeMirror to JS field
	showCodeMirror('cr-template-custom-js','text/javascript');

	// click on Visual
	$('.cr-template-toolbar').on('click', 'button#cr-tmce', function() {
		$('#cr-template-editor-type').val('visual');
		var id = 'cr-template';
		if($('#'+id).siblings('.CodeMirror')[0]) {
			var codeVal = $('#'+id).siblings('.CodeMirror')[0].CodeMirror.getValue();
			$('#'+id).val(codeVal);
		}	
		$('#'+id).show();
		$('#'+id).siblings('.CodeMirror').remove();
		showVisualEditor(id);
	});
	// click on Code
	$('.cr-template-toolbar').on('click', 'button#cr-html', function() {
		$('#cr-template-editor-type').val('code');
		var id = 'cr-template';
		$('#'+id).siblings('.CodeMirror').remove();
		wp.editor.remove(id);
		showCodeMirror(id);
	});

	// save window position as cookie on CTR+S or CMD+S
	$(document).keydown(function(event) {
		if((event.ctrlKey || event.metaKey) && event.which == 83) {
			// Save Function
			event.preventDefault();
			var position = $(window).scrollTop();
			console.log(position);
			setCookie($('#cr-template').attr('name')+'-position', position);
			return false;
		}
	});

	// scroll to window position if cookie exists, unset cookie
	var position = getCookie($('#cr-template').attr('name')+'-position');
	if(position) {
	    $('html, body').animate({ scrollTop: position }, 1);
		setCookie($('#cr-template').attr('name')+'-position', 0);
	}
	
	// autoresize textarea scripts & styles
	$('#cr-template-custom-files').each(function () {
		this.setAttribute('style', 'height:' + (this.scrollHeight) + 'px;overflow-y:hidden;');
	}).on('input', function () {
		this.style.height = 'auto';
		this.style.height = (this.scrollHeight) + 'px';
	});

	 
});

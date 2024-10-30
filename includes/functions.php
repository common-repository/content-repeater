<?php
// unset empty subarrays
function rptr_array_unset_empty($array) {
	$array = array_map(function($item) {
		return is_array($item) ? rptr_array_unset_empty($item) : $item;
	}, $array);
	return array_filter($array, function($item) {
		return $item !== "" && $item !== null && (!is_array($item) || count($item) > 0);
	});
}		

// convert numeric to alpha
function rptr_to_alpha($data){
	$alphabet =   array('a','b','c','d','e','f','g','h','i','j','k','l','m','n','o','p','q','r','s','t','u','v','w','x','y','z');
	$alpha_flip = array_flip($alphabet);
		if($data <= 25){
		  return $alphabet[$data];
		} elseif($data > 25) {
		  $dividend = ($data + 1);
		  $alpha = '';
		  $modulo;
		  while ($dividend > 0){
			$modulo = ($dividend - 1) % 26;
			$alpha = $alphabet[$modulo] . $alpha;
			$dividend = floor((($dividend - $modulo) / 26));
		  } 
		  return $alpha;
		}

}		

function rptr_option($a) { 
	$td_full = ($a['type'] == 'textarea') ?' wide' : '';
	echo '<tr valign="top" class="'.$a['classes'].'">';
		echo '<th class="cr-options-title" scope="row">'.$a['title'].'</th>';
		echo '<td class="cr-options-field'.$td_full.'">';
				  if ($a['type'] == 'text') {
				echo '<input id="'.$a['id'].'" type="'.$a['type'].'" name="'.$a['name'].'" value="'.$a['value'].'">';
			} elseif ($a['type'] == 'select') {
				echo '<select id="'.$a['id'].'" name="'.$a['name'].'">';
					echo '<option value="none">— '.$a['title'].' —</option>';
					foreach ($a['value'] as $k => $v) { 
						echo '<option value="'.$k.'" '.selected( $a['current'], $k, true ).'>'.$v.'</option>';
					}
				echo '</select>';
			} elseif ($a['type'] == 'checkbox') {
				$a['id'] = isset($a['id']) ? $a['id'] : $a['name'];
				echo '<input type="hidden" name="'.$a['name'].'" value="No">';
				echo '<input id="'.$a['id'].'" type="'.$a['type'].'" name="'.$a['name'].'" value="'.$a['value'].'" '.checked($a['value'], $a['current'], false).'>';
				$label[0] = '<label for="'.$a['id'].'">';
				$label[1] = '</label>';
			} elseif ($a['type'] == 'textarea') {
				echo '<textarea id="'.$a['id'].'" name="'.$a['name'].'">'.$a['value'].'</textarea>';
			}
		echo '</td>';
		echo '<td class="cr-options-description'.$td_full.'">';
			echo '<p class="description">'.$label[0].$a['descr'].$label[0].'</p>';
		echo '</td>';
	echo '</tr>';
}

function rptr_list_shortcode($a) { 
	echo '<table class="cr-shortcode-table">';
	foreach($a as $k=>$v) {
		$descr = isset($v['descr']) ? $v['descr'] : $v;
		$row_classes = isset($v['row_classes']) ? ' class="'.$v['row_classes'].'"' : '';
		echo '<tr'.$row_classes.'>';
			echo '<th scope="row">';
				echo rptr_print_shortcode($k);
			echo '</th>';
			echo '<td class="cr-shortcode-explain-column">'.$descr.'</td>';
		echo '</tr>';
	}
	echo '</table>';
}

function rptr_print_shortcode($value) {
	return '<input onfocus="this.select();document.execCommand(\'copy\');" readonly="readonly" class="cr-shortcode" type="text" value="'.$value.'">';
}

function rptr_plugin_scope() {
	$types = rptr_get_option('types_all'); 
	$types = is_array($types) ? array_keys($types) : array();

	global $_GET;
	if(
		is_admin() && (
			$_GET['page'] == 'content-repeater'
			|| $_GET['page'] == 'cr-export-import-settings'
			|| $_GET['page'] == 'cr-rename-fields'
			|| in_array( $_GET['post_type'], $types )
			|| in_array( get_post_type( $_GET['post'] ), $types )
			|| strstr($_SERVER['REQUEST_URI'], 'admin-ajax.php')
			|| isset($_POST['rptr_prn_redirect']) // for publish-and-redirect-to-add-new-post to work
		)
	) {
		return true;
	} else {
		return false;
	}
}

// include all PHP files in subdirectories
function rptr_include_all($directory) {
	if(is_dir($directory)) {
		$scan = scandir($directory);
		unset($scan[0], $scan[1]); //unset . and ..
		foreach($scan as $file) {
			if(is_dir($directory."/".$file)) {
				rptr_include_all($directory."/".$file);
			} else {
				if(strpos($file, '.php') !== false) {
					include_once($directory."/".$file);
				}
			}
		}
	}
}

/* commented out to remove the Add Repeater button and thickbox popup in Editors
// add "Insert Repeater" shortcode
add_action( 'media_buttons', function($editor_id){
    echo '<a href="#" class="button cr-insert-repeater-thickbox" title="Add Repeater"> <style>.cr-insert-repeater-button::before { font: 400 18px/1 dashicons; content: \'\f207\'; }</style><span class="wp-media-buttons-icon cr-insert-repeater-button"></span>Add Repeater</a>';
} );
add_action( 'admin_footer', function() { ?>
	<script type="text/javascript">
	jQuery(document).ready(function($){
		var $modal = $('#cr-insert-repeater');
		var $modalCont = $modal.find('>*');
		$('.cr-insert-repeater-thickbox').click(function() {
			tb_show( 'Add Repeater', '/?TB_inline&inlineId=cr-insert-repeater' );
			$modalCont.html('<span class="spinner" style="visibility:visible;float:none;"></span>');
			$.ajax({
				type: 'post',
				url: ajaxurl,
				data: {
					action: 'rptr_ajax_load_repeaters_thickbox'
				},
				success: function(result) {
					$modalCont.html(result);
				}
			})
			return false;						    
		});
	});
	</script>
	<div id="cr-insert-repeater" style="display: none;">
		<div class="wrap">
		</div>
	</div>
<?php
} );
		
add_action( 'wp_ajax_rptr_ajax_load_repeaters_thickbox', 'rptr_ajax_load_repeaters_thickbox' );		
function rptr_ajax_load_repeaters_thickbox() {
	?>
	<script type="text/javascript">
		function crInsertRepeaterShortcode() {
			if($('#cr-insert').html().length > 0) {
				window.send_to_editor($('#cr-insert').html());
			}
		}
		jQuery(document).ready(function($){ 
			$('#cr-types-select-box').change(function() {
				var t = $(this);
				t.siblings('select').val('').find('option:not(.default)').remove();
				$('#cr-insert').html('');
				t.siblings().hide();
				if(t.val() == '') return false;
				t.siblings('.spinner').show().css('visibility','visible');
				$.ajax({
					type: 'post',
					url: ajaxurl,
					data: {
						action: 'rptr_ajax_load_repeaters',
						type: t.val(),
					},
					success: function(result) {
						t.siblings('#cr-repeaters-select').append(result).show();
						t.siblings('.spinner').css('visibility','hidden');
					}
				})
				return false;						    

			});
			$('#cr-repeaters-select').change(function() {
				var t = $(this);
				$('.cr-insert-code-wrap').hide();
				if(t.val() == '') {
					$('.cr-insert-code-wrap').hide();
					$('#cr-singles-select').hide();
				} else if (t.val() == 'cri') {
					t.siblings('.spinner').insertAfter($('#cr-singles-select')).show().css('visibility','visible');
					$.ajax({
						type: 'post',
						url: ajaxurl,
						data: {
							action: 'rptr_ajax_load_singles',
							type: t.siblings('#cr-types-select-box').val(),
						},
						success: function(result) {
							t.siblings('#cr-singles-select').append(result).show();
							t.siblings('.spinner').css('visibility','hidden');
						}
					})
					return false;						    
				} else {
					$('#cr-singles-select').hide().val('').find('option:not(.default)').remove();
					$('#cr-insert').html(t.val()).parents('.cr-insert-code-wrap').show();
				}
			});		
			$('#cr-singles-select').change(function() {
				var t = $(this);
				$('.cr-insert-code-wrap').hide();
				if(t.val() != '') {
					$('#cr-insert').html(t.val()).parents('.cr-insert-code-wrap').show();
				}
			});
		});
		
	</script>
	<style>#TB_ajaxContent { width: calc(100% - 30px) !important; }</style>

	<?php
	$types = rptr_get_option('types_all'); 				
	if( $types ) { ?>
		<select id="cr-types-select-box">
			<option value="">— Select Content Type —</option>
			<?php	foreach ( $types as $k=>$v ) {
						echo '<option value="' . $k . '">' . $v['type_name'] . '</option>';
					} ?>
		</select>
		<select id="cr-repeaters-select" style="display:none;">
			<option class="default" value="">— Choose Display —</option>
			<option class="default" value="cri">Single Item</option>
		</select>
		<select id="cr-singles-select" style="display:none;">
			<option class="default" value="">— Choose Post —</option>
		</select>
		<span class="cr spinner" style="float:none;margin:0 10px;"></span>
	<?php } else { echo 'No Content Types have been created yet. Please <a href="admin.php?page=content-repeater">create one</a> first and then you will be able to select it here.'; } ?>

		<div class="cr-insert-code-wrap" style="display:none;">
			<pre><code id="cr-insert"></code></pre>
			<a href="#" id="cr-insert-button" class="button button-primary" onclick="crInsertRepeaterShortcode();">Insert shortcode</a>
		</div>
	<?php
	wp_die();
}			

add_action( 'wp_ajax_rptr_ajax_load_repeaters', 'rptr_ajax_load_repeaters' );		
function rptr_ajax_load_repeaters() {
	$type = $_REQUEST['type'];
	echo '<option value="[all-'.$type.']">All List</option>';
	$rpts = Rptr_Class::$rpts;
	foreach($rpts as $k=>$v) {
		echo '<option value="['.$k.'-'.$type.']">'.$v['nice_name'].'</option>';
	}
	wp_die();
}			

add_action( 'wp_ajax_rptr_ajax_load_singles', 'rptr_ajax_load_singles' );		
function rptr_ajax_load_singles() {
	$type = $_REQUEST['type'];
    $posts = get_posts( array(
        'posts_per_page' => -1,
        'post_type' => $type,
        'status' => 'publish'
    ) );	
    $l = 30;
    foreach ($posts as $post) {
    	echo '<option value="[cri id='.$post->ID.']">'.((strlen($post->post_title)>$l) ? substr($post->post_title,0,$l).'...' : $post->post_title).'</option>';
	}
	wp_die();
}	
*/

function rptr_query_args($atts, $args, $type) {
	$atts = shortcode_atts( array( 
		'order' => 'ASC',
		'orderby' => 'menu_order',
		'offset' => '',
		'posts_per_page' => get_option( 'posts_per_page' ),
		'cat' => 0,
		'category__not_in' => 0,
		'post__not_in' => 0,
		'post__in' => '',
		'numberposts' => -1,
		'post_status' => 'publish',
		'post_type' => $type,
	), $atts );
// 	echo '<pre>';print_r($atts);echo '</pre>';
	if($atts['post__not_in']) 
		$atts['post__not_in'] = explode(',', $atts['post__not_in']);	
	if($atts['post__in']) 
		$atts['post__in'] = explode(',', $atts['post__in']);	
	if(trim($atts['cat']) != 0) {
		$atts['tax_query'] = array(
			array(
				'taxonomy' => $type.'-category',
				'field'    => 'term_id',
				'terms'    => array_map('trim', explode(',', $atts['cat'])),
			)
		);
		unset($atts['cat']);
	}			
	if(trim($atts['category__not_in']) != 0) {
		$atts['tax_query'][] = 
			array(
				'taxonomy' => $type.'-category',
				'field'    => 'term_id',
				'terms'    => array_map('trim', explode(',', $atts['category__not_in'])),
				'operator' => 'NOT IN'
			);
		$atts['tax_query'][] = array('relation' => 'AND');
		unset($atts['category__not_in']);
	}			
	if( is_front_page() ){
		$atts['paged'] = (get_query_var('page') ? get_query_var('page') : 1); 
	} else {
		$atts['paged'] = (get_query_var('paged') ? get_query_var('paged') : 1); 
	}
	foreach($atts as $k=>$v) {
		$args[$k] = $v;
	}
	return $args;
}

function rptr_get_taxonomy_hierarchy( $taxonomy, $parent = 0, $new_args = array() ) {
	// only 1 taxonomy
	$taxonomy = is_array( $taxonomy ) ? array_shift( $taxonomy ) : $taxonomy;
	$args = array( 'parent' => $parent, 'hide_empty' => false );
	$args = array_replace( $args, $new_args );
	// get all direct decendants of the $parent
	$terms = get_terms( $taxonomy, $args );
	// prepare a new array.  these are the children of $parent
	// we'll ultimately copy all the $terms into this new array, but only after they
	// find their own children
	$children = array();
	// go through all the direct decendants of $parent, and gather their children
	foreach ( $terms as $term ){
		// recurse to get the direct decendants of "this" term
		$term->children = rptr_get_taxonomy_hierarchy( $taxonomy, $term->term_id );
		// add the term to our new array
		$children[ $term->term_id ] = $term;
	}
	// send the results back to the caller
	return $children;
}
function rptr_terms_hierarchy_to_flat($element) {
	$arr = [];
	foreach($element as $k=>$v) {
		if(empty($v['children'])) {
			$arr[] = $v;
		} else {
			$children = $v['children'];
			$v['children'] = [];
			$arr[] = $v;
			$arr = array_merge($arr, rptr_terms_hierarchy_to_flat($children));
		}
	}
	return $arr;
}

function rptr_classes($type) {
	$classes = rptr_get_option('types_all')[$type]['wrap_classes'];
	if(trim($classes) != '') {
		return ' '.trim(preg_replace('/[,. ]+/', ' ', $classes));
	}
}
?>
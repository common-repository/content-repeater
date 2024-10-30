<?php
/**
 * Plugin Name: Content Repeater
 * Description: A tool to quickly set up custom content types and display them in interesting ways.
 * Version: 1.1.13
 * Author: Denis Buka
 * Text Domain: content-repeater
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define('RPTR_PATH', plugin_dir_path(__FILE__)); // has trailing slash
define('RPTR_URL', plugin_dir_url(__FILE__)); // has trailing slash

class Rptr_Class {
		
	public static $rpts = array(); // repeaters will store their setting here
	
	public static $d; // array for default repeater values
	public static $b; // string for repeater basename
	public static $r; // string for repeater activation state
	public static $o; public static $oo;
	public static $c; public static $cc;
	public static $pat;

	public function __construct() {
	
		require_once RPTR_PATH.'includes/functions.php';
		require_once RPTR_PATH.'includes/conditional.php';
		require_once RPTR_PATH.'includes/sorting.php';
					
// 		self::$d['breakpoints'] = 'SM:384,MD:576,LG:768,XL:960';
		self::$o = '{';
		self::$c = '}';
		self::$pat = '/'.str_repeat(addcslashes(self::$o,self::$o),2).'(.+)'.str_repeat(addcslashes(self::$c,self::$c),2).'/U';
		self::$oo = str_repeat(self::$o,2);
		self::$cc = str_repeat(self::$c,2);
	
		if ( is_admin() ) {

			if( rptr_plugin_scope() ) {
				wp_enqueue_script( 'cr-admin-scripts', RPTR_URL.'assets/js/cr-admin-scripts.js', array('jquery'));
				wp_enqueue_style( 'cr-admin-styles', RPTR_URL. 'assets/css/cr-admin-styles.css', false, '1.1', 'all');	
			}			
			add_action( 'admin_menu', 	array( 'Rptr_Class', 'add_admin_menu' ) );
			add_action( 'admin_menu', 	array( 'Rptr_Class', 'register_all_admin' ) );
			add_action( 'admin_init', 	array( 'Rptr_Class', 'rptr_admin_columns' ) );
			add_action( 'admin_init', 	array( 'Rptr_Class', 'register_settings' ) );
			add_action(	'admin_head', 	array( 'Rptr_Class', 'rptr_admin_scripts_styles' ) );				
			add_filter( 'post_updated_messages', array( 'Rptr_Class', 'rptr_custom_update_messages' ) );
			
			add_action('edit_form_after_title', function() { // Move all "advanced" metaboxes above the default editor
				global $post, $wp_meta_boxes;
				do_meta_boxes(get_current_screen(), 'advanced', $post);
				unset($wp_meta_boxes[get_post_type($post)]['advanced']);
			});

		} else {
			self::rptr_front_scripts_styles();
		}

		add_action( 'init', 	array( 'Rptr_Class', 'register_all' ) );
	}
	
	public static function rptr_custom_update_messages($messages) {
		$types = self::rptr_get_option('types_all'); 
		if( is_array( $types ) and !empty( $types ) ) {
			foreach ( $types as $type => $v ) {
				$msg = ' <a href="' . wp_nonce_url('admin.php?action=rptr_rd_duplicate_post&post=' . $_GET['post'], basename(__FILE__), 'duplicate_nonce' ) . '&cr-duplicate-redirect=post" title="Duplicate this item" rel="permalink">Duplicate</a> | <a href="edit.php?post_type='.$type.'&page='.$type.'-cr-repeaters">Select Repeater</a> | Shortcode: '.rptr_print_shortcode('[cri id='.$_GET['post'].']');
				$messages[$type] = array( 
					1  => __( 'Post updated.'.$msg ),
					6  => __( 'Post published.'.$msg ),
				);
			}
		}
		return $messages;		
	}

	public static function rptr_admin_scripts_styles() { ?>
<script type="text/javascript">
var ajaxurl = '<?php echo admin_url( 'admin-ajax.php' ); ?>';
var siteurl = '<?php echo get_bloginfo('url'); ?>';
var rptrurl = '<?php echo RPTR_URL; ?>';
var oo = '<?php echo self::$oo; ?>';
var cc = '<?php echo self::$cc; ?>';	
</script>		
<style>
.menu-top.toplevel_page_content-repeater .wp-menu-image, .cr-insert-repeater-button {
-webkit-transform: rotate(90deg);
-moz-transform: rotate(90deg);
-o-transform: rotate(90deg);
-ms-transform: rotate(90deg);
transform: rotate(90deg);	
}
</style>
	<?php }
	
	public static function rptr_front_scripts_styles() { 
		add_action( 'wp_head', 	function() { 
			$breakpoints = (rptr_get_option('breakpoints') !== null) ? rptr_get_option('breakpoints') : '0';
		?>
<script type="text/javascript">
var ajaxurl = '<?php echo admin_url( 'admin-ajax.php' ); ?>';
var siteurl = '<?php echo get_bloginfo('url'); ?>';
var rptrurl = '<?php echo RPTR_URL; ?>';
</script>
<?php if( $breakpoints !== '0' ) { ?>
<style>
[data-observe-resizes] {
display: block;
opacity: 0;
transition: opacity .2s ease-in-out;
animation: .5s ease-in-out 2s 1 forwards responsive-container-nojs
}
@keyframes responsive-container-nojs {
from {opacity: 0} to {opacity: 1}
}
[data-observe-resizes][data-observing] {
opacity: 1 !important;
animation: none
}
</style>
<noscript>
<style>
[data-observe-resizes] {
opacity: 1;
animation: none;
}
</style>
</noscript>
<script>
function observeResizes() {
  if ('ResizeObserver' in self) {
	var ro = new ResizeObserver(function(entries) {
	  var defaultBreakpoints = {<?php echo $breakpoints; ?>};
	  entries.forEach(function(entry) {
		var breakpoints = entry.target.dataset.breakpoints ?
			JSON.parse(entry.target.dataset.breakpoints) :
			defaultBreakpoints;
		if (entry.contentRect.width === 0) {
		  entry.target.dataset.observing = false;
		} else {
		  entry.target.dataset.observing = true;
		}
		Object.keys(breakpoints).forEach(function(breakpoint) {
		  var minWidth = breakpoints[breakpoint];
		  if (entry.contentRect.width >= minWidth) {
			entry.target.classList.add(breakpoint);
		  } else {
			entry.target.classList.remove(breakpoint);
		  }
		});
	  });
	});
	var elements = document.querySelectorAll('[data-observe-resizes]');
	for (var element, i = 0; element = elements[i]; i++) {
	  ro.observe(element);
	}
	var eachObserveableElement = function(nodes, fn) {
	  if (nodes) {
		[].slice.call(nodes).forEach(function(node) {
		  if (node.nodeType === 1) {
			var containers = [].slice.call(
				node.querySelectorAll('[data-observe-resizes]'));
			if (node.hasAttribute('data-observe-resizes')) {
			  containers.push(node);
			}
			for (var container, i = 0; container = containers[i]; i++) {
			  fn(container);
			}
		  }
		});
	  }
	};
	var mo = new MutationObserver(function(entries) {
	  entries.forEach(function(entry) {
		eachObserveableElement(entry.addedNodes, ro.observe.bind(ro));
	  });
	});
	mo.observe(document.body, {childList: true, subtree: true});
  }
}
function loadScript(src, done) {
  var js = document.createElement('script');
  js.src = src;
  js.onload = done;
  document.head.appendChild(js);
}

jQuery(document).ready(function($){ 
  if (window.ResizeObserver) {
	observeResizes();
  } else if (window.matchMedia('(min-width: 48em)').matches) {
	loadScript(rptrurl+'assets/js/ResizeObserver.js', observeResizes);
  }
});
</script>
<?php }		
		}, 100 );
		wp_enqueue_script( 'cr-scripts', RPTR_URL.'assets/js/cr-scripts.js', array('jquery'));
		wp_enqueue_style( 'cr-styles', RPTR_URL.'assets/css/cr-styles.css', false, '1.1', 'all');	
		wp_enqueue_script( 'imagesloaded', RPTR_URL.'assets/js/imagesloaded.pkgd.min.js'); 
		wp_enqueue_script( 'dotdotdot', RPTR_URL.'assets/js/jquery.dotdotdot.js', array('jquery'));
		wp_enqueue_script( 'matchHeight', RPTR_URL.'assets/js/jquery.matchHeight-min.js', array('jquery'));
	}
	
	// get field default
	public static function field_default( $field, $default ){
		if( preg_match("/.+\[(.+)\]$/U", $field, $m) && empty($default) ) {
			$out = $m[1];
			return $out;
		} else {
			return $default;
		}
	}
	
	// shortcode: [cri id='...']
	public static function rptr_item_shortcode( $atts, $content, $tag ) {
		$atts = shortcode_atts( array( 'id' => 0 ), $atts );
		$type = substr($tag,5);
		$out .= '<div class="cr-single-item">';
		$out .= self::display_item( $atts['id'] );	
		$out .= '</div>';
		return $out;	
	}	
	
	// shortcode: [cr-more text='...']
	public static function rptr_more_shortcode( $atts, $content, $tag ){
		$atts = shortcode_atts( array( 
			'text' => 'See more',
		), $atts );
		$id = get_the_ID();
		$type = get_post_type($id);
		$types = self::rptr_get_option('types_all');		
		if( isset($types[$type]['type_slug']) ) {
			$more_text = $atts['text'];
			$output = '<a class="cr-more" href="'.get_permalink($id).'">'.$more_text.'</a>';
		} elseif(is_user_logged_in()) {
			$output = '<a href="'.get_admin_url().'?page=content-repeater&type='.$type.'&action=set-slug" class="cr-more set-slug">Add slug to enable Single view</a>';
		}
		return $output;
	}

	// shortcode: [all-... order='DESC']
	public static function all_shortcode( $atts, $content, $tag ) {
		$type = substr($tag,4);
		$args = rptr_query_args($atts, $args, $type);
		$query = new WP_Query( $args );
	  
		if($query->have_posts()) {
			$output;
			$classes = self::rptr_get_option('types_all')[$type]['wrap_classes'];
			$classes = ' '.trim(preg_replace('/[,. ]+/', ' ', $classes));
			$output = '<div class="cr-all '.$type.'-all'.$classes.' clearfix">';
			while ($query->have_posts()) : $query->the_post();
				$output .= self::display_item( get_the_ID() );	
			endwhile;
			$output .= '</div>';
			if($query->found_posts > $args['posts_per_page']) {
				// navigation
				$big = 999999999; // need an unlikely integer
				$current = (is_front_page()) ? max( 1, get_query_var('page') ) : max( 1, get_query_var('paged') );
				$output .= '<div class="navigation">';
				$output .= paginate_links( array(
					'base' => str_replace( $big, '%#%', get_pagenum_link( $big ) ),
					'format' => '?paged=%#%',
					'current' => $current,
					'total' => $query->max_num_pages
				) );
				$output .= '</div>';		
			}
		} else {
			$output .= '<p>There are no posts to display</p>';
		}
		wp_reset_postdata();			
		return $output;	
	}
	

	// add main menu page
	public static function add_admin_menu() {
		add_menu_page(
			esc_html__( 'Content Repeater', 'content-repeater' ),
			esc_html__( 'Content Repeater', 'content-repeater' ),
			'manage_options',
			'content-repeater',
			array( 'Rptr_Admin_Page', 'create_admin_page' ),
			'dashicons-editor-aligncenter'
		);
		add_submenu_page( 
			'content-repeater', 
			esc_html__( 'Content Repeater Types', 'content-repeater' ),
			esc_html__( 'Content Types', 'content-repeater' ),
			'manage_options', 
			'content-repeater'
		);
	}
	
	// display type
	public static function display_item( $id, $template = 'template' ){
		$GLOBALS['rptr_template'] = ($template == 'template') ? 'template' : 'template_single';
		$type = get_post_type($id);
		$types = self::rptr_get_option('types_all');

		if($GLOBALS['rptr_'.$type.'_'.$template] != 'used') {
			$pars = array( $type, $template );
			$files = $types[$type][$template.'_custom_files'];
			if(isset($files)) {
				$files = str_replace('{{site_url}}', get_bloginfo('url').'/', $files);
				$files = str_replace('{{cr_url}}', RPTR_URL, $files);
				$files = explode(PHP_EOL, $files);
				foreach($files as $file) {
					$file = trim($file);
					if(pathinfo($file)['extension'] == 'css') {
						wp_enqueue_style( pathinfo($file)['filename'], $file, false, '1.1', 'all');	
					} else if(pathinfo($file)['extension'] == 'js') {
						wp_enqueue_script( pathinfo($file)['filename'], $file, array('jquery'));
					}		
				}
			}
			add_action( 'wp_footer', function() use ( $pars ) {
				$type = $pars[0];
				$template = $pars[1];
				$types = self::rptr_get_option('types_all'); 
				if(isset($types[$type][$template.'_custom_css'])) {
echo "\n<style type='text/css'>\n";
echo $types[$type][$template.'_custom_css'];
echo "\n</style>\n";
				}
				if(isset($types[$type][$template.'_custom_js'])) {
echo "\n<script type='text/javascript'>\n";
echo $types[$type][$template.'_custom_js'];
echo "\n</script>\n";
				}
			}, 100 );
		}
		$GLOBALS['rptr_'.$type.'_'.$template] = 'used';

		$content = $types[$type]['template'];
		
		global $post; 
		$post = get_post( $id, OBJECT );
		setup_postdata( $post ); // reset global $post for current $id
		$content = self::parse_template( $content, $id, $template );	
		$content = do_shortcode($content);
		wp_reset_postdata();

		$classes = ' '.trim(preg_replace('/[,. ]+/', ' ', $types[$type]['item_classes']));
		// apply box CSS
		$css = $types[$type]['box_css'];
		$bg = $css['background'];
		$bg['image'] = isset($bg['image']) ? 'url('.wp_get_attachment_image_src($bg['image'], 'full')[0].')' : '';
		if(is_array($bg)) {
			foreach($bg as $k=>$v) {
				if(trim($v) != '') {
					$bg[$k] = isset($bg[$k]) ? 'background-'.$k.':'.$bg[$k] : '';
				}
			}
		}
		if(is_array($css['padding'])) {
			$top = isset($css['padding']['top']) ? $css['padding']['top'] : 0;
			$right = isset($css['padding']['right']) ? $css['padding']['right'] : 0;
			$bottom = isset($css['padding']['bottom']) ? $css['padding']['bottom'] : 0;
			$left = isset($css['padding']['left']) ? $css['padding']['left'] : 0;
			$padding = 'padding:'.$top.' '.$right.' '.$bottom.' '.$left.';';
		}
		if(is_array($css['border'])) {
			$width = isset($css['border']['width']) ? $css['border']['width'] : 0;
			$style = isset($css['border']['style']) ? $css['border']['style'] : 'none';
			$color = isset($css['border']['color']) ? $css['border']['color'] : 'transparent';
			$border = 'border:'.$width.' '.$style.' '.$color.';';
		}
		$color = isset($css['color']) ? 'color:'.$css['color'].';' : '';
		$styles = $color.$padding.$border.implode(';',$bg);
		$style = ' style="'.$styles.'opacity:0;"';

		$classes .= (($template == 'template_single') ? ' cr-single '.$type.'-single' : '');
		if(rptr_get_option('breakpoints') && rptr_get_option('breakpoints') !== '0') {
			$dor = ' data-observe-resizes';
		} else {
			$dor = '';
		}
		$output = '<div class="crid-'.$id.' cri'.$classes.' clearfix"'.$style.$dor.'>';	
		$output .= $content;			
		$output .= '</div>';	
		return $output;
	}
	
	
	public static function parse_template( $content, $id = null, $template = 'template' ) {
		$id = ($id == null) ? get_the_ID() : $id;
		$types = self::rptr_get_option('types_all'); 
		$type = get_post_type($id);
		$content = $types[$type][$template];
		$content = preg_replace_callback(
			self::$pat,
			function ($matches) use($id) {
				$break = explode('::',$matches[1]);
				$out;				

				if ( $break[0] == 'cr_url' ) {
					$out = RPTR_URL;
				} elseif ( $break[0] == 'site_url' ) {
					$out = get_bloginfo('url').'/';
				// default value possible
				} elseif (preg_match("/^post_image(.*)/U", $break[0]) > 0) {
					$default = get_post_thumbnail_id( $id );
					$default = self::field_default($break[0], $default);
					$size = ($break[1] != '' && $break[1] != 'url') ? $break[1] : 'full';
					if($break[1] == 'url' || $break[2] == 'url') {
						$out = wp_get_attachment_image_src( $default, $size )[0];
					} elseif($break[1] == 'id' || $break[2] == 'id') {
						$out = $default;
					} else {
						$out = wp_get_attachment_image( $default, $size );
					}
				} elseif (preg_match("/^text(.*)/U", $break[1]) > 0) {
					$default = get_post_meta( $id, $break[0], true );
					$default = self::field_default($break[1], $default);
					$out = $default;
					if($break[2] == 'nowrap') {
						$out = '<div class="cr-nowrap">'.$out.'</div>';
					}
				} elseif (preg_match("/^images(.*)/U", $break[1]) > 0) {
					if(preg_match("/^slider(.*)/U", $break[2]) > 0) {
						wp_enqueue_script( 'slick', RPTR_URL.'assets/slick/slick.min.js', array('jquery'));
						wp_enqueue_style( 'slick', RPTR_URL.'assets/slick/slick.css', false, '1.1', 'all');
						wp_enqueue_style( 'slick-theme', RPTR_URL.'assets/slick/slick-theme.css', false, '1.1', 'all');

						wp_enqueue_style( 'photoswipe', RPTR_URL.'assets/photoswipe/photoswipe.css', false, '1.1', 'all');
						wp_enqueue_style( 'photoswipe-default-skin', 
								RPTR_URL.'assets/photoswipe/default-skin/default-skin.css', false, '1.1', 'all');
						wp_enqueue_script( 'photoswipe', RPTR_URL.'assets/photoswipe/photoswipe.min.js', array('jquery'));
						wp_enqueue_script( 'photoswipe-ui-default', 
								RPTR_URL.'assets/photoswipe/photoswipe-ui-default.min.js', array('jquery'));
						add_action( 'wp_footer', function() {
							include(RPTR_PATH.'assets/photoswipe/photoswipe.html');
						}, 100 );

						wp_enqueue_script( 'cr-slider', RPTR_URL.'assets/js/cr-slider.js', array('jquery'));
						$ids = get_post_meta( $id, $break[0], false )[0];
						if(trim($ids) != '') {
							$options = array_slice($break, 3);
							$ids = explode(',', $ids);
							if(in_array('fullheight', $options)) {
							  $fullheight = ' data-fullheight="1"';
							}
							$out .= '<div class="cr-slider-wrap"'.$fullheight.'>';
							  $out .= '<div id="slider-for-'.time().rand().'" class="slider slider-for">';
							  foreach($ids as $k=>$v) {
								$img = wp_get_attachment_image_src($v, 'full');
								if(!in_array('nolink', $options)) {
								  $data = ' class="slider-link" data-href="'.$img[0].'" data-index="'.$k.'" data-width="'.$img[1].'" data-height="'.$img[2].'"';
								}
								if($img[1]<$img[2]) $vert = ' class="vert"';
								$out .= '<div>
										   <div'.$data.'>
											 <img'.$vert.' data-lazy="'.wp_get_attachment_image_src( $v, 'large' )[0].'">
										   </div>
										 </div>';
							  }
							  $out .= '</div>';
							  if(!in_array('nothumb', $options)) {
								$out .= '<div id="slider-nav-'.time().rand().'" class="slider slider-nav">';
								foreach($ids as $k=>$v) {
									$out .= '<div><img data-lazy="'.wp_get_attachment_image_src( $v, 'thumbnail' )[0].'"></div>';
								}
								$out .= '</div>';
							  }
							$out .= '</div>';
						}						
					} else {
						$default = get_post_meta( $id, $break[0], false )[0];
						$default = self::field_default($break[1], $default);
						$size = ($break[2] != '' && $break[2] != 'url') ? $break[2] : 'full';
						if($break[2] == 'url' || $break[3] == 'url') {
							$out = wp_get_attachment_image_src( $default, $size )[0];
						} elseif($break[2] == 'id' || $break[3] == 'id') {
							$out = $default;
						} else {
							$out = wp_get_attachment_image( $default, $size );
						}
					}
				} elseif (preg_match("/^dropdown(.*)/U", $break[1]) > 0) {
					$saved = get_post_meta( $id, $break[0], true );
					$default = self::field_default($break[1], $saved);
					if( $saved == $default ) {
						$out = $default;
					} else {
						$out = array_slice($break,2)[$default-1];
					}
				
				} elseif (preg_match("/^date(.*)/U", $break[1]) > 0) {
					$saved = get_post_meta( $id, $break[0], true );
					$default = self::field_default($break[1], $saved);
					if( $saved == $default ) {
						$saved = strtotime( $saved );
						$out = isset( $break[2] ) ? date( $break[2], $saved ) : date( 'F j, Y', $saved );
					} else {
						$out = $default;
					}
				} elseif (preg_match("/^color(.*)/U", $break[1]) > 0) {
					$default = get_post_meta( $id, $break[0], true );
					$default = self::field_default($break[1], $default);
					$out = $default;
				} elseif (preg_match("/^icon(.*)/U", $break[1]) > 0) {
					wp_enqueue_style( 'fontawesome', 
						'https://use.fontawesome.com/releases/v5.5.0/css/all.css', false, '1.1', 'all');	
					$default = get_post_meta( $id, $break[0], true );
					$default = self::field_default($break[1], $default);
					$out = '<i class="'.$default.'"></i>';

				// default value not possible
				} elseif ($break[1] == 'editor') {
					if (($key = array_search('html', $break)) !== false) {
						unset($break[$key]);
						$out = get_post_meta($id, $break[0], true);
					} else {
						$out = wpautop(get_post_meta($id, $break[0], true));
					}
					if(isset($break[3])) $more = do_shortcode('[cr-more text="'.$break[3].'"]');
					if(isset($break[2])) $out = '<div class="cr-lines-'.$break[2].'">'.$out.$more.'</div>';
				} elseif ($break[0] == 'post_title') {
					$out = get_the_title( $id );
					if($break[1] == 'nowrap') {
						$out = '<div class="cr-nowrap">'.$out.'</div>';
					}
				} elseif ($break[0] == 'post_url') {
					$out = get_permalink( $id );
				} elseif ($break[0] == 'post_content') {
					$out = get_post_field( 'post_content', $id );
				} elseif ($break[0] == 'post_id') {
					$out = $id;
				} elseif ($break[1] == 'checkboxes') {
					$saved = get_post_meta( $id, $break[0], false );
					if(!empty($saved) && is_array($saved)) {
						if(count($saved[0]) > 1) {
							foreach($saved[0] as $k=>$v) {
								$out .= '<span class="cr-checkbox-wrap">'.$v.'</span>';
							}
						} else {
							$out = reset($saved[0]);
						}
					}
				} else { // select, color ...
					$out = get_post_meta( $id, $break[0], true );
				}
				if(trim($out) != '') return $out;
			},
			$content
		);	
		return $content;
	}
	
	
	// register all stuff in admin & frontend
	public static function register_all() {
		$types = self::rptr_get_option('types_all'); 
		if( is_array( $types ) and !empty( $types ) ) {
			foreach ( $types as $type => $v ) {
				$labels = array(
					'add_new_item' => __('Add New'),
					'add_new' => __('Add New'),
					'edit_item' => __('Edit'),
				);
				$args = array(
					'public' => true,
					'publicly_queryable'  => false,
					'label'  => $v['type_name'],
					'menu_icon' => (isset($v['icon'])?$v['icon']:'dashicons-arrow-right'),
					'show_ui' => true,
					'show_in_menu' => true,
					'supports' => array( 'title', 'thumbnail' ),
					'labels' => $labels,
				);
				if( isset($v['type_slug']) ) {
					$args['publicly_queryable'] = true;
					$args['rewrite'] = array( 'slug' => $v['type_slug'] );		
				}
				if( isset($v['categorize']) ) {
					$cat_slug = isset($args['rewrite']['slug']) ? $args['rewrite']['slug'].'-category' : $type.'-category';					
					register_taxonomy(  
						$type.'-category',
						$type,
						array(  
							'hierarchical' => true,  
							'label' => __('Categories'),
							'show_admin_column' => true,
							'query_var' => true,
							'rewrite' => array(
								'slug' => $cat_slug, // This controls the base slug that will display before each term
							)
						)  
					);  
				}
				if( preg_match( '{{post_content}}', $v['template'] ) ) $args['supports'] = array( 'title', 'editor', 'thumbnail' );
				if( preg_match( self::$pat, $v['template'] ) || preg_match( self::$pat, $v['template_single'] ) ) {
					register_post_type( $type, $args );
					add_shortcode( 	'all-'.$type, array( 'Rptr_Class', 'all_shortcode' ) );
				} else {
					$args['capabilities'] = array( 'create_posts' => 'do_not_allow' );
					add_action(	'admin_head', function() use ( $type ) {
						echo "\n<style type='text/css'>#menu-posts-".$type." .wp-first-item{display: none;}</style>\n";
					} );
					register_post_type( $type, $args );
				}
			}
			add_shortcode( 	'cri', array( 'Rptr_Class', 'rptr_item_shortcode' ) );
			add_shortcode( 	'cr-more', array( 'Rptr_Class', 'rptr_more_shortcode' ) );

			// parse Template Single if on Type Single page
			add_filter( 'the_content', function( $content ) use ( $types ) {
				$type = get_post_type();
				if(array_key_exists($type, $types)) {
					if( preg_match( self::$pat, $types[$type]['template_single'] ) ) {
						// if only {{post_content}} is in the Template Single, let's display the content straight
						if( trim($types[$type]['template_single']) == '{{post_content}}' ) {
							$content = $content;
						} else {
							$content = self::display_item( get_the_ID(), 'template_single' );
						}
					} elseif(is_user_logged_in()) {
// 						$content = '<p><a href="'.get_admin_url().'edit.php?post_type='.$type.'&page='.$type.'-cr-template-single">Click to create a Template for the Single view</a></p>';
					}
				}
				return $content;
			} );
		
		}
	}	
	
	// register all stuff in admin only
	public static function register_all_admin() {
		$types = self::rptr_get_option('types_all'); 
		if( is_array( $types ) ) {
			foreach ( $types as $type => $v ) {
				add_submenu_page( // register Template pages
					'edit.php?post_type=' . $type,
					esc_html__( $v['type_name'].': Template', 'content-repeater' ),
					esc_html__( 'Template', 'content-repeater' ),
					'manage_options',
					$type . '-cr-template',
					array( 'Rptr_Template_Editor', 'create_template_page' )
				);
				if( isset($v['type_slug']) ) {
					add_submenu_page(
						'edit.php?post_type=' . $type,
						esc_html__( $v['type_name'].': Single View Template', 'content-repeater' ),
						esc_html__( 'Template Single', 'content-repeater' ),
						'manage_options',
						$type . '-cr-template-single',
						array( 'Rptr_Template_Single_Editor', 'create_template_single_page' )
					);
				}
				$template_matches = preg_match_all( self::$pat, $v['template'], $matches );
				$template_single_matches = preg_match_all( self::$pat, $v['template_single'], $matches_single );
				if( $template_matches || $template_single_matches ) { // if Template with tags exists
					add_submenu_page( // register Repeaters pages
						'edit.php?post_type=' . $type,
						esc_html__( $v['type_name'] . ': Repeaters', 'content-repeater' ),
						esc_html__( 'Repeaters', 'content-repeater' ),
						'manage_options',
						$type . '-cr-repeaters',
						array( 'Rptr_Repeaters_Page', 'create_repeaters_page' )
					);
					$matches[1] = isset($matches[1]) ? $matches[1] : array();
					$matches_single[1] = isset($matches_single[1]) ? $matches_single[1] : array();
					$merged = array_merge($matches[1], $matches_single[1]);
					foreach($merged as $field) {
						// display meta boxes only if Template has meta fields
						if(	strpos($field, 'post_id') === 0 
							|| strpos($field, 'post_url') === 0
							|| strpos($field, 'post_title') === 0
							|| strpos($field, 'post_content') === 0
							|| strpos($field, 'post_image') === 0
							|| strpos($field, 'site_url') === 0
							) {
							continue;
						} else {
							add_meta_box( 
								'cr-meta-boxes', 
								$v['type_name'].': '.__('Custom Fields'), 
								array ( 'Rptr_Class', 'build_meta_box' ), 
								$type, 
								'normal', 
								'high' 
							);
							add_action( 'save_post_' . $type, array( 'Rptr_Class', 'save_meta_box_data' ) );						
							break;
						}
					}
				}
			}
		}
	}
	
	// register admin column 'Shortcode'
	public static function rptr_admin_columns() {
		$types = self::rptr_get_option('types_all'); 
		if(!$types) return;
		foreach($types as $type=>$v) {
			add_filter( 'manage_'.$type.'_posts_columns', function( $columns ) {
				$columns['cr-shortcode'] = __('Shortcode');
				return $columns;
			} );
			add_action( 'manage_'.$type.'_posts_custom_column' , function( $column, $post_id ) {
				if($column == 'cr-shortcode') echo rptr_print_shortcode('[cri id='.$post_id.']');
			}, 10, 2 );
		}
	}
	
	
	// build meta boxes
	public static function build_meta_box( $post ){
		
		$types = self::rptr_get_option('types_all');
		$type = get_post_type( $post );
		
		// make sure the form request comes from WordPress
		wp_nonce_field( basename(__FILE__), $type . '_meta_box_nonce' );

		$template .= ( isset( $types[$type]['template'] ) ) ? $types[$type]['template'] : ''; 			
		$template .= ( isset( $types[$type]['template_single'] ) ) ? $types[$type]['template_single'] : ''; 			
		?>

		<div class="inside cr-meta-fields">
			<?php
			preg_match_all(self::$pat,
				$template,
				$out, PREG_PATTERN_ORDER);
			$used = array();
			foreach ($out[1] as $k => $v) {
				$break = explode( '::', $v );
				$field = array_slice($break, 0, 2);
				$field[0] = preg_replace('/(.+)\[(.+)\]$/U', '$1', $field[0]);
				$field[1] = preg_replace('/(.+)\[(.+)\]$/U', '$1', $field[1]);
				$field_name = $type.'_fields['.implode('::',$field).']';
				if( in_array( $field[0], $used ) ) continue;
				$used[] = $field[0];
				if( !in_array( $field[0], array( 'post_title', 'post_content', 'post_image', 'post_id', 'post_url', 'site_url' ) ) ) {
					echo '<label class="cr-field-label">' . $field[0] . '</label>';
				}
				echo '<div class="cr-field-content">';
				if ( $field[1] == 'text' ) {
					echo '<input type="text" name="'.$field_name.'" value="'.htmlentities(get_post_meta($post->ID, $field[0], true)).'">';
				} elseif ( $field[1] == 'images' ) {
					if ( ! did_action( 'wp_enqueue_media' ) ) {
						wp_enqueue_media();
					}			
					wp_enqueue_script('jquery-ui-sortable');
					$value = get_post_meta($post->ID, $field[0], true);
					echo '
					<div class="cr-upload-image-wrap">
					  <input type="hidden" name="'.$field_name.'" value="'.$value.'" class="cr-upload-image-holder">
					  <ul class="cr-upload-image-list">';
				  if(isset($value) && trim($value)!='') {
					  foreach(explode(',', $value) as $kk=>$vv) {
						  $src = wp_get_attachment_image_src($vv,'thumbnail')[0];
				  echo '<li data-id="'.$vv.'" class="cr-upload-image" style="background-image:url('.$src.')">
						  <a href="post.php?post='.$vv.'&action=edit" class="cr-edit-image-button" target="_blank"></a>
						  <a href="#" class="cr-remove-image-button" data-id="'.$vv.'"></a>
						</li>';
					  }
				  }
				echo '</ul>
					  <a href="#" class="cr-upload-image-button button">'.__('Upload').'</a>
					</div>';														
				} elseif ( $field[1] == 'editor' ) {
					wp_editor( 	get_post_meta( $post->ID, $field[0], true ), 
								$type.'-editor-'.time().rand(), 
								$settings = array(
									'textarea_name' => $field_name,
									'media_buttons' => true,
									'tinymce' => (in_array('html',$break) ? false : true),
									'quicktags' => array('buttons'=>'strong,em,link,block,img,ul,ol,li,code,close' ),
									'wpautop' => true
								) );
				} elseif ( $field[1] == 'dropdown' ) {
					$choices = array_slice($break, 2);
					echo '<select name="'.$field_name.'">';
					echo '<option value="">— Choose one —</option>';
						$decode = htmlspecialchars_decode(get_post_meta( $post->ID, $field[0], true ));
						foreach( $choices as $k=>$v ) {
							$encode = htmlspecialchars($v);
							echo '<option value="'.$encode.'" '.selected($v, $decode, false).'>'.$v.'</option>';
						};
					echo '</select>';
				} elseif ( $field[1] == 'checkboxes' ) {
					$choices = array_slice($break, 2);
					$saved = get_post_meta( $post->ID, $field[0], true );
					foreach( $choices as $k=>$v ) {
						$encode = htmlspecialchars($v);
						echo '<label>';
							echo '<input type="hidden" name="'.$field_name.'['.$k.']" value="">';
							echo '<input type="checkbox" name="'.$field_name.'['.$k.']" value="'.$encode.'" '.checked($saved[$k], $v, false).'>';
							echo $v;
						echo '</label>';
					}
				} elseif ( $field[1] == 'date' ) {
					wp_enqueue_script('jquery-ui-datepicker');
					wp_enqueue_style('jquery-ui');		
					echo '<input type="date" name="'.$field_name.'" value="'.get_post_meta($post->ID, $field[0], true).'">';
				} elseif ( $field[1] == 'icon' ) {
					wp_enqueue_script( 'fontawesome-iconpicker', 
						RPTR_URL.'assets/fontawesome-iconpicker/js/fontawesome-iconpicker.js', array('jquery'));
					wp_enqueue_style( 'fontawesome-iconpicker', 
						RPTR_URL.'assets/fontawesome-iconpicker/css/fontawesome-iconpicker.min.css', false, '1.1', 'all');	
					wp_enqueue_style( 'fontawesome', 
						'https://use.fontawesome.com/releases/v5.5.0/css/all.css', false, '1.1', 'all');	
					echo '<div class="input-group iconpicker-container">
						<input autocomplete="off" data-placement="bottomLeft" class="form-control icp icp-auto iconpicker-element iconpicker-input" value="'.get_post_meta($post->ID, $field[0], true).'" name="'.$field_name.'" type="text">
						<span class="input-group-addon"><i class="fas"></i></span>
					</div>';						
					echo "\n<script type='text/javascript'>
							jQuery(document).ready(function($){ 
								$('.icp-auto').iconpicker();
								//$('.fontawesome-iconpicker').iconpicker({placement:'bottomLeft'});
							});
							</script>\n";
				} elseif ( $field[1] == 'color' ) {
					wp_enqueue_style( 'wp-color-picker');
					wp_enqueue_script( 'wp-color-picker');						
					echo '<input class="color-field" type="text" name="'.$field_name.'" value="'.get_post_meta($post->ID, $field[0], true).'">';
				}
				echo '</div>';
			}
				
				
			
			?>
		</div>
		<?php
	}

	// save meta boxes
	public static function save_meta_box_data( $post_id ){			
		$type = $_POST['post_type'];

		// verify meta box nonce
		if ( !isset( $_POST[$type . '_meta_box_nonce'] ) || !wp_verify_nonce( $_POST[$type . '_meta_box_nonce'], basename(__FILE__) ) ) {
			return;
		}
		// return if autosave
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}
		// Check the user's permissions.
		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}
		
		$fields = $_POST[$type . '_fields'];
		if ( is_array( $fields ) ) {
// 			$fields = rptr_array_unset_empty($fields);
			foreach ( $fields as $k => $v ) {
				$break = explode( '::', $k );
				update_post_meta( $post_id, $break[0], $v );
			}
		}
	}



	// return all plugin options
	public static function rptr_get_options() {
		return get_option( 'rptr_options' );
	}

	// return single plugin option
	public static function rptr_get_option( $id ) {
		$options = self::rptr_get_options();
		if ( isset( $options[$id] ) ) {
			return $options[$id];
		}
	}
	 
	
	// developer: print_r all options
	public static function debugger() {
			return; // disable
			function filter(&$value) {
			  $value = preg_replace('/(?<=>)\s+(?=<)/', ' ', $value);
			  $value = preg_replace('/[\r\n\t ]+/', ' ', $value);
			  $value = htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
			}
			$results = self::rptr_get_options();
			if(!is_array($results)) return;
			array_walk_recursive($results, 'filter');
			echo '<pre class="cr-debug">'; print_r($results); echo '</pre>'; 
	}		

	// register a setting and its sanitization callback
	public static function register_settings() {
		register_setting( 'rptr_options', 'rptr_options', array( 'Rptr_Class', 'sanitize' ) );
	}
	public static function deregister_settings() {
		unregister_setting( 'rptr_options', 'rptr_options' );
	}
			

	// sanitize options before saving
	public static function sanitize( $options ) {

		if ( $options ) {
		
			$saved = self::rptr_get_options();
			if ( ! empty( $options['types_all']['type_name'] ) ) { // if new type submitted
				$saved['types_all'][rptr_to_alpha(time())]['type_name'] = sanitize_text_field( $options['types_all']['type_name'] );
				$options = $saved;
			} 
			if( is_array( $saved ) ) { // check if options are saved for the first time
				$options = array_replace_recursive( $saved, $options );
			}

		}

		$options = is_array($options) ? rptr_array_unset_empty($options) : $options;

// 			$temp = rptr_array_unset_empty($options);
// 			echo 'Options SUBMITTED <pre>'; print_r(rptr_array_unset_empty($options)); echo '</pre>';
// 			echo 'Options OLD <pre>'; print_r($options); echo '</pre>';
// 			echo 'Options NEW <pre>'; print_r($temp); echo '</pre>';
// 			die();

		return $options;

	}
	

	public static function rptr_template_fields($type) {
		$types = self::rptr_get_option('types_all'); 
		preg_match_all(self::$pat,
			$types[$type]['template'],
			$out, PREG_PATTERN_ORDER);
		preg_match_all(self::$pat,
			$types[$type]['template_single'],
			$out_single, PREG_PATTERN_ORDER);
		$merged = array_unique(array_merge($out[1], $out_single[1]));
		if(!empty($out)) { ?>
			<div class="cr-inputs-wrap">
				<p>Fields for <?php echo $types[$type]['type_name']; ?>:</p>
				<span class="cr-template-fields">
				<?php
				foreach ($merged as $k => $v) {
					$split = explode('::', $v); 
					$fields[] = preg_replace('/(.+)\[(.+)\]$/U', '$1', $split[0]);
				}
				echo '<code>'.implode('</code><code>', array_unique($fields)).'</code>';
				?>
				</span>
			</div>
		<?php
		}	
	}			
}
new Rptr_Class();


// Helper function to use outside class 
function rptr_get_option( $id = '' ) {
	return Rptr_Class::rptr_get_option( $id );
}

if(rptr_plugin_scope()) rptr_include_all(RPTR_PATH.'includes');
rptr_include_all(RPTR_PATH.'repeaters');

?>
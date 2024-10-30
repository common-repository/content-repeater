<?php
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Rptr_Ajax_Reload extends Rptr_Class {

	public static $b;
	public static $d;

	public function __construct() {
	
		self::$b = basename(__FILE__, '.php');
		
		// options defaults
		self::$d['timer'] = 8000;
		self::$d['fade_speed'] = 500;
		self::$d['lines'] = 3;
		self::$d['line_height'] = 20;
		self::$d['fixed_height'] = 'No';
		self::$d['more'] = 'No';
		self::$d['more_all'] = 'No';
	
		parent::$rpts[self::$b] = array();
		parent::$rpts[self::$b]['class_name'] = get_class();
		parent::$rpts[self::$b]['nice_name'] = "Ajax Reload";
		
		add_action( 'wp_ajax_ajax_load_type', 			array( 'Rptr_Ajax_Reload', 'ajax_load_type' ) );
		add_action( 'wp_ajax_nopriv_ajax_load_type', 	array( 'Rptr_Ajax_Reload', 'ajax_load_type' ) );
		$types = self::rptr_get_option('types_all'); 
		if( is_array( $types ) and ! empty( $types ) ) {
			foreach ( $types as $k => $v ) {
				add_shortcode( 	self::$b.'-'.$k,	array( 'Rptr_Ajax_Reload', 'ajax_reload_shortcode' ) );
			}
		}
		
	}
	
	
	// shortcode: [ajax-reload-...]
	public static function ajax_reload_shortcode( $atts, $content, $tag ){
		$type = substr($tag,strlen(self::$b)+1);
		if(isset($atts['nowrap']) && $atts['nowrap']=="true") {
			$nowrap =  true;
			unset($atts['nowrap']);
		} else {
			$nowrap = false;
		}
		if(is_array($atts)) {
			foreach($atts as $k=>$v) {
				$atts_line .= $k.(isset($v) ? '="'.$v.'"' : '').' ';
			}
		}
		$atts_line = (trim($atts_line)!='') ? ' '.trim($atts_line) : '';
		$shortcode = $tag.$atts_line.' nowrap="true"';
		$shortcode = htmlspecialchars($shortcode);

		$args = rptr_query_args($atts, $args, $type);
		$args['posts_per_page'] = -1; // fix: WP doesn't seem to randomize when using ajax
		$args['numberposts'] = -1;
		$args['orderby'] = 'rand';

		$posts = get_posts($args);
		shuffle($posts); // fix: WP doesn't seem to randomize when using ajax

		if($nowrap == false) { 
			$output .= '<div class="'.$type.'-'.self::$b.'-wrap'.rptr_classes($type).'"><div class="'.$type.'-'.self::$b.'">';	
		}
		$output .= '<div class="'.$type.'-'.self::$b.'-inner" data-id="'.$posts[0]->ID.'" data-shortcode="'.$shortcode.'">';
		$output .= self::display_item( $posts[0]->ID );	
		$output .= '</div>';
		if($nowrap == false) {
			$output .= '</div></div>';
		}

		wp_enqueue_script( self::$b.'-scripts', plugin_dir_url(__FILE__).
			'js/'.self::$b.'-scripts.js', array('jquery'));
		add_action( 'wp_footer', function() use ( $type ) { 
			$types = self::rptr_get_option('types_all'); 
			$rpt = $types[$type]['repeater'][self::$b];
			$disp = ($rpt['timer']!='') ? $rpt['timer'] : self::$d['timer'];
			$fade = ($rpt['fade_speed']!='') ? $rpt['fade_speed'] : self::$d['fade_speed'];
			$jargs = "'$type',$disp,$fade,'".self::$b."'";
		?>
<script type="text/javascript">
(function($){				
$(document).ready(function(){
	new RotateFade(<?php echo $jargs; ?>).onPageLoad();
});
})(jQuery);
</script>
		
		<?php
		}, 100 );

		return $output;
	}	
	
	// ajax load type
	public static function ajax_load_type() {
		if($_POST['type']) { 
			$type = $_POST['type'];
			$shortcode = '[' . $_POST['shortcode'] . ']';
			$shortcode = stripslashes(htmlspecialchars_decode($shortcode));
			$shortcode = str_replace(']', ' post__not_in='.$_POST['prev'].']', $shortcode);
			echo do_shortcode($shortcode);
		} else {
			echo 'Error Loading Testimonials';
		}
		wp_die();
	?>	
	<?php	
	} 
	
	// display options on type settings subpage
	public static function display_options( $type, $rpt_name ) { ?>
	
		<?php 
		$rpt = self::rptr_get_option('types_all')[$type]['repeater'];
// 			$rpt_name = $rpt['repeater_current'];
		$prefix = 'rptr_options[types_all]['.$type.'][repeater]['.$rpt_name.']';
		?>

			<?php rptr_option(array(
					'title' => 'Display time',
					'type' => 'text',
					'name' => $prefix.'[timer]',
					'value' => (isset($rpt[$rpt_name]['timer'])) ? $rpt[$rpt_name]['timer'] : '',
					'descr' => "For how long the testimonial will be displayed (in milliseconds, default: ".self::$d['timer'].")",
					'classes' => 'numbers-only'
			)); ?>

			<?php rptr_option(array(
					'title' => 'Fade-in/fade-out speed',
					'type' => 'text',
					'name' => $prefix.'[fade_speed]',
					'value' => (isset($rpt[$rpt_name]['fade_speed'])) ? $rpt[$rpt_name]['fade_speed'] : '',
					'descr' => "Smoothness of fade-in/fade-out animation (in milliseconds, default: ".self::$d['fade_speed'].")",
					'classes' => 'numbers-only'
			)); ?>
							
			<?php // Shortcodes ?>
			<tr valign="top" class="cr-shortcodes-row">
				<th scope="row"><?php esc_html_e( parent::$rpts[self::$b]['nice_name'] . ' shortcodes', 'content-repeater' ); ?></th>
				<td colspan="2">
					<?php rptr_list_shortcode(array(
						'['.self::$b.'-'.$type.']' => 'Display Repeater',
						'[cr-more]' => array(
							'descr' => 'Place this right before the closing tag of the element that should be truncated in Repeater view. The truncated element should have class <code>cr-more-wrap</code>',
							'row_classes' => 'cr-more-hide',
							),
						)); ?>
				</td>
			</tr>

	<?php 
	}

}

new Rptr_Ajax_Reload();
?>
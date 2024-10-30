<?php
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Rptr_Slick_Slider extends Rptr_Class {

	public static $b;
	public static $d;

	public function __construct() {

		self::$b = basename(__FILE__, '.php');

		// options defaults
		self::$d['padding'] = 20;
		self::$d['auto_height'] = 'Yes';
		self::$d['equal_height'] = 'No';
	
		parent::$rpts[self::$b] = array();
		parent::$rpts[self::$b]['class_name'] = get_class();
		parent::$rpts[self::$b]['nice_name'] = "Slick Slider";

		$types = self::rptr_get_option('types_all'); 
		if( is_array( $types ) and ! empty( $types ) ) {
			foreach ( $types as $k => $v ) {
				add_shortcode( 	self::$b.'-'.$k,		array( 'Rptr_Slick_Slider', 'slick_slider_shortcode' ) );
			}
		}
		
	}
	
			
	// shortcode: [slick-slider-...]
	public static function slick_slider_shortcode( $atts, $content, $tag ){
		$type = substr($tag,strlen(self::$b)+1);            
		$types = self::rptr_get_option('types_all'); 
		$rpt = $types[$type]['repeater'][self::$b];
		$data_equal_height = ($rpt['equal_height'] == 'Yes') ? ' data-equal-height' : '';

		$args = rptr_query_args($atts, $args, $type);
		$args['posts_per_page'] = -1; // disable pagination for slider
		$query = new WP_Query( $args );

		if($query->have_posts()) : $output;
			$output .= '<div class="'.$type.'-'.self::$b.' '.self::$b.rptr_classes($type).'">';
			while ($query->have_posts()) : $query->the_post();
				$output .= '<div class="'.$type.'-'.self::$b.'-item '.self::$b.'-item" '.$data_equal_height.'>';
				$output .= self::display_item( get_the_ID() );	
				$output .= '</div>';
			endwhile;
			$output .= '</div>';
			
		else:
			$output .= '<p>There are no slides to display</p>';
		endif;
		wp_reset_postdata();

		wp_enqueue_script( 'slick', RPTR_URL.'assets/slick/slick.min.js', array('jquery'));
		wp_enqueue_style( 'slick', RPTR_URL.'assets/slick/slick.css', false, '1.1', 'all');
		wp_enqueue_style( 'slick-theme', RPTR_URL.'assets/slick/slick-theme.css', false, '1.1', 'all');

		add_action( 'wp_footer', function() use ( $type ) {
			$types = self::rptr_get_option('types_all'); 
			$rpt = $types[$type]['repeater'][self::$b];
			$options = isset($rpt['options']) ? $rpt['options'] : array('');
			$padding = ($rpt['padding']!='') ? $rpt['padding'] : self::$d['padding'];
			$auto_height = ($rpt['auto_height']!='') ? $rpt['auto_height'] : self::$d['auto_height'];
			$equal_height = ($rpt['equal_height']!='') ? $rpt['equal_height'] : self::$d['equal_height'];
?>
<style type="text/css">
.<?php echo $type; ?>-<?php echo self::$b; ?> {
	opacity: 0;
}
.slick-dots {
	position: relative;
	bottom: auto;
	margin-top: 20px;
	margin-left: 0;
	margin-right: 0;
	padding: 0 !important;
}
.slick-prev:before, .slick-next:before {
	color: rgba(0,0,0,.25);
}

.cr-equal-height .slick-track {
	display: flex !important;
}
.cr-equal-height .slick-track .slick-slide {
	display: flex !important;
	height: auto !important;
	align-items: center;
	justify-content: center;
}
.cr-equal-height .slick-slide .cri {
	width: 100%;
}
.<?php echo $type; ?>-<?php echo self::$b; ?> .cri {
padding: 0 <?php echo ($padding/2); ?>px 0 <?php echo ($padding/2); ?>px;
}
</style>
<script type='text/javascript'>
jQuery(document).ready(function($){
	$('.<?php echo $type; ?>-<?php echo self::$b; ?>').on('init', function(){ 
		var t = $(this);
		setTimeout(function() { 
// 			if (typeof observeResizes === "function") observeResizes();	
	<?php /*if( $equal_height == 'Yes' ) { ?>
			// equalize heights
			if(t.find('img').length) {
				t.imagesLoaded( function() {
					t.find('.slick-track .slick-slider-item').equalHeight();	
					t.find('.slick-track .slick-slider-item').matchHeight();	
					t.animate({opacity: 1}, 800);
				});	
			} else {
				t.find('.slick-track .slick-slider-item').equalHeight();	
				t.find('.slick-track .slick-slider-item').matchHeight();	
				t.animate({opacity: 1}, 800);
			}
			t.find('.cri').outerHeight(t.find('.slick-track').outerHeight());
			t.animate({opacity: 1}, 800);
	<?php } else { ?>
			t.animate({opacity: 1}, 800);
	<?php }*/ ?>
	<?php if($auto_height != 'No' && $equal_height != 'Yes') {  ?>
	
			/* adjust slider view height */
			setCarHeight(t); 
	<?php } ?>
			
			t.animate({opacity: 1}, 800);
		}, 800);
	});
	$('.<?php echo $type; ?>-<?php echo self::$b; ?>').slick({
<?php echo $options[0]; ?>
	});
	$('.<?php echo $type; ?>-<?php echo self::$b; ?>').on('afterChange', function(event, slick, currentSlide, nextSlide){
<?php if($auto_height == 'Yes') { ?>
		setCarHeight($(this));
<?php } ?>
	});
});	
</script>
<?php
		}, 100 );
			
		return $output;
	}
			
	
	// display options on type settings subpage
	public static function display_options( $type, $rpt_name ) { ?>	
		<?php 
		$rpt = self::rptr_get_option('types_all')[$type]['repeater'];
		$prefix = 'rptr_options[types_all]['.$type.'][repeater]['.$rpt_name.']';
		?>

		<?php rptr_option(array(
				'title' => 'Padding between items',
				'type' => 'text',
				'name' => $prefix.'[padding]',
				'value' => (isset($rpt[$rpt_name]['padding'])) ? $rpt[$rpt_name]['padding'] : '',
				'descr' => "in pixels, put 0 for no padding (default: ".self::$d['padding'].")",
				'classes' => 'numbers-only'
		)); ?>

		<?php rptr_option(array(
				'title' => 'Auto-height',
				'type' => 'checkbox',
				'name' => $prefix.'[auto_height]',
				'value' => 'Yes',
				'current' => (isset($rpt[$rpt_name]['auto_height'])) ? $rpt[$rpt_name]['auto_height'] : self::$d['auto_height'],
				'descr' => "",
				'classes' => 'cr-checkbox'
		)); ?>

		<?php rptr_option(array(
				'title' => 'Equal height',
				'type' => 'checkbox',
				'name' => $prefix.'[equal_height]',
				'value' => 'Yes',
				'current' => (isset($rpt[$rpt_name]['equal_height'])) ? $rpt[$rpt_name]['equal_height'] : self::$d['equal_height'],
				'descr' => "All slider elements will have equal height and will be centered vertically",
				'classes' => 'cr-checkbox'
		)); ?>

		<?php 
		if(isset($rpt[$rpt_name]['options'][0])) {
			$value = $rpt[$rpt_name]['options'][0];
		} else {
			$value = 'dots: true,
arrows: true,
autoplay: true,
autoplaySpeed: 7000,
infinite: true,
speed: 300,
slidesToShow: 3,
slidesToScroll: 3,
draggable: false,
responsive: [
{
breakpoint: 767,
settings: {
  slidesToShow: 2,
  slidesToScroll: 2,
  infinite: true,
  dots: true
}
},
{
breakpoint: 600,
settings: {
  slidesToShow: 1,
  slidesToScroll: 1
}
}
]';
		}
		rptr_option(array(
				'title' => 'Slick Slider options',
				'type' => 'textarea',
				'name' => $prefix.'[options][0]',
				'value' => $value,
				'descr' => 'See full list of options here: <a href="http://kenwheeler.github.io/slick/#settings">http://kenwheeler.github.io/slick/#settings</a>',
				'classes' => 'cr-textarea'
		)); ?>
		
		<?php // Shortcodes ?>
		<tr valign="top" class="cr-shortcodes-row">
			<th scope="row"><?php esc_html_e( parent::$rpts[self::$b]['nice_name'] . ' shortcodes', 'content-repeater' ); ?></th>
			<td colspan="2">
				<?php rptr_list_shortcode(array(
					'['.self::$b.'-'.$type.']' => 
					'Display Slick Slider',
					)); ?>
			</td>

		</tr>

	<?php 
	}
	

}

new Rptr_Slick_Slider();
?>
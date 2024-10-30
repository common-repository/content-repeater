<?php
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Rptr_Sinlge_Row extends Rptr_Class {

	public static $b;
	public static $d;

	public function __construct() {

		self::$b = basename(__FILE__, '.php');

		// options defaults
		self::$d['padding'] = 20;
		self::$d['breakpoint'] = 768;
		self::$d['equal_height'] = 'Yes';
	
		parent::$rpts[self::$b] = array();
		parent::$rpts[self::$b]['class_name'] = get_class();
		parent::$rpts[self::$b]['nice_name'] = "Single Row";

		
		$types = self::rptr_get_option('types_all'); 
		if( is_array( $types ) and ! empty( $types ) ) {
			foreach ( $types as $k => $v ) {
				add_shortcode( 	self::$b.'-'.$k, array( 'Rptr_Sinlge_Row', 'single_row_shortcode' ) );
			}
		}
		
	}
	
	
	// shortcode: [single-row-...]
	public static function single_row_shortcode( $atts, $content, $tag ){
		$type = substr($tag,strlen(self::$b)+1);

		if(is_array($atts) && !isset($atts['order'])) $atts['order'] = 'DESC';
		$args = rptr_query_args($atts, $args, $type);

		$query = new WP_Query( $args );

		if($query->have_posts()) : $output;
			$count = $query->post_count;
			$types = self::rptr_get_option('types_all'); 
			$rpt = $types[$type]['repeater'][self::$b];
			$classes = rptr_classes($type);
			$classes .= (($rpt['equal_height'] == 'No') ? '' : ' cr-equal-height');
			$output = '<div class="'.self::$b.' '.$type.'-'.self::$b.$classes.'">';
			while ($query->have_posts()) : $query->the_post();
				$output .= self::display_item( get_the_ID() );	
			endwhile;
			$output .= '<div class="cr-clear" style="clear:both"></div>';
			$output .= '</div>';				
		else:
			$output .= '<p>There are no posts to display</p>';
		endif;
		wp_reset_postdata();

		add_action( 'wp_footer', function() use ( $type, $count ) { ?>
		
<style type='text/css'>
.<?php echo self::$b; ?> .cri {
	float: left;
}
.<?php echo self::$b; ?> .cri:first-child {
	margin-left: 0;
}
</style>
			<?php		
			$types = self::rptr_get_option('types_all'); 
			if($count === 0) return;
			$rpt = $types[$type]['repeater'][self::$b];
			$padding = ($rpt['padding']!='') ? $rpt['padding'] : self::$d['padding'];
			$breakpoint = ($rpt['breakpoint']!='') ? $rpt['breakpoint'] : self::$d['breakpoint'];
			?>
<style type='text/css'>
.<?php echo $type; ?>-<?php echo self::$b; ?> .cri {
margin: 0 0 <?php echo $padding; ?>px <?php echo $padding; ?>px;
width: calc(<?php echo (100 / $count); ?>% - <?php echo ($padding*($count-1)/$count); ?>px);
}
.single-row {
margin-bottom: 20px;
}
@media screen and (max-width: <?php echo $breakpoint; ?>px) {
.<?php echo $type; ?>-<?php echo self::$b; ?> .cri {
	float: none;
	width: 100%;
	margin: 0 0 <?php echo $padding; ?>px 0;
}
}							
</style>
		<?php
		}, 100 );

		return $output;
	}
			
	
	// display options on type settings subpage
	public static function display_options( $type, $rpt_name ) { ?>
	
		<?php 
		$type = $_GET['post_type'];
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
				'title' => 'Responsive breakpoint',
				'type' => 'text',
				'name' => $prefix.'[breakpoint]',
				'value' => (isset($rpt[$rpt_name]['breakpoint'])) ? $rpt[$rpt_name]['breakpoint'] : '',
				'descr' => "in pixels (default: ".self::$d['breakpoint'].")",
				'classes' => 'numbers-only'
		)); ?>

		<?php rptr_option(array(
				'title' => 'Equal height',
				'type' => 'checkbox',
				'name' => $prefix.'[equal_height]',
				'value' => self::$d['equal_height'],
				'current' => (isset($rpt[$rpt_name]['equal_height'])) ? $rpt[$rpt_name]['equal_height'] : self::$d['equal_height'],
				'descr' => "",
				'classes' => 'cr-checkbox'
		)); ?>
		
		<?php // Shortcodes ?>
		<tr valign="top" class="cr-shortcodes-row">
			<th scope="row"><?php esc_html_e( parent::$rpts[self::$b]['nice_name'] . ' shortcodes', 'content-repeater' ); ?></th>
			<td colspan="2">
				<?php rptr_list_shortcode(array(
					'['.self::$b.'-'.$type.']' => 
					'Display row
					<ul>
						<li><code>order=DESC</code> to display in descending order</li>
					</ul>',
					)); ?>
			</td>
		</tr>

	<?php 
	}


}

new Rptr_Sinlge_Row();
?>
<?php
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Rptr_Masonry extends Rptr_Class {

	public static $b;
	public static $d;

	public function __construct() {

		self::$b = basename(__FILE__, '.php');

		// options defaults
		self::$d['columns'] = 3;
		self::$d['padding'] = 20;
		self::$d['min_width'] = 200;
	
		parent::$rpts[self::$b] = array();
		parent::$rpts[self::$b]['class_name'] = get_class();
		parent::$rpts[self::$b]['nice_name'] = "Masonry";
		
		$types = self::rptr_get_option('types_all'); 
		if( is_array( $types ) and ! empty( $types ) ) {
			foreach ( $types as $k => $v ) {
				add_shortcode( 	'masonry-'.$k, array( 'Rptr_Masonry', 'masonry_shortcode' ) );
			}
		}
		
	}
	
			
	// shortcode: [masonry-...]
	public static function masonry_shortcode( $atts, $content, $tag ){
		$type = substr($tag,strlen(self::$b)+1);
		$args = rptr_query_args($atts, $args, $type);
		$query = new WP_Query( $args );

		if($query->have_posts()) : $output;
			$output .= '<div class="'.self::$b.'-wrap '.$type.'-'.self::$b.'-wrap'.rptr_classes($type).'">';
			$output .= '<div class="grid '.$type.'-'.self::$b.'">';
			$output .= '<div class="grid-sizer"></div>';
			while ($query->have_posts()) : $query->the_post();
				$output .= '<div class="grid-item">';
				$output .= self::display_item( get_the_ID() );	
				$output .= '</div>';
			endwhile;
			$output .= '</div>';

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
			$output .= '</div>';
			
		else:
			$output .= '<p>There are no posts to display</p>';
		endif;
		wp_reset_postdata();
		
		wp_enqueue_script( 'masonry', plugin_dir_url(__FILE__).
			'js/masonry.pkgd.min.js', array('jquery'));
		add_action( 'wp_footer', function() use ( $type ) {
			$types = self::rptr_get_option('types_all'); 
			$rpt = $types[$type]['repeater'][self::$b];
			$columns = ($rpt['columns']!='' && $rpt['columns']>1) ? $rpt['columns'] : self::$d['columns'];
			$padding = ($rpt['padding']!='') ? $rpt['padding'] : self::$d['padding'];
			$min_width = ($rpt['min_width']!='') ? $rpt['min_width'] : self::$d['min_width'];
			?>
<style type='text/css'>
.<?php echo $type; ?>-<?php echo self::$b; ?>.grid {
visibility: hidden;
margin: 0 auto;
}
.<?php echo $type; ?>-<?php echo self::$b; ?> .grid-sizer, .<?php echo $type; ?>-<?php echo self::$b; ?> .grid-item {
width: calc(<?php echo (100/$columns); ?>% - <?php echo ((($columns-1)/$columns)*$padding); ?>px);
min-width: <?php echo $min_width; ?>px;
}
.<?php echo $type; ?>-<?php echo self::$b; ?> .grid-item {
margin-bottom: <?php echo $padding; ?>px;
}
</style>
<script type='text/javascript'>
(function($){	
// $(window).load(function() {
$(document).ready(function(){	
setTimeout(function() {
$('.<?php echo $type; ?>-<?php echo self::$b; ?>.grid').masonry({
	columnWidth: '.grid-sizer',
	itemSelector: '.grid-item',
	isFitWidth: true,
	gutter: <?php echo $padding; ?>,
	isAnimated: false,
});	
$('.<?php echo $type; ?>-<?php echo self::$b; ?>.grid').css('visibility','visible').hide().fadeIn('slow');
}, 4);
});
})(jQuery);
</script>
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
					'title' => 'Number of columns',
					'type' => 'text',
					'name' => $prefix.'[columns]',
					'value' => (isset($rpt[$rpt_name]['columns'])) ? $rpt[$rpt_name]['columns'] : '',
					'descr' => "(minimum: 2, default: ".self::$d['columns'].")",
					'classes' => 'numbers-only'
			)); ?>

			<?php rptr_option(array(
					'title' => 'Padding between items',
					'type' => 'text',
					'name' => $prefix.'[padding]',
					'value' => (isset($rpt[$rpt_name]['padding'])) ? $rpt[$rpt_name]['padding'] : '',
					'descr' => "in pixels, put 0 for no padding (default: ".self::$d['padding']."px)",
					'classes' => 'numbers-only'
			)); ?>

			<?php rptr_option(array(
					'title' => 'Minimun item width',
					'type' => 'text',
					'name' => $prefix.'[min_width]',
					'value' => (isset($rpt[$rpt_name]['min_width'])) ? $rpt[$rpt_name]['min_width'] : '',
					'descr' => "in pixels (default: ".self::$d['min_width']."px)",
					'classes' => 'numbers-only'
			)); ?>

			
			<?php // Shortcodes ?>
			<tr valign="top" class="cr-shortcodes-row">
				<th scope="row"><?php esc_html_e( parent::$rpts[self::$b]['nice_name'] . ' shortcodes', 'content-repeater' ); ?></th>
				<td colspan="2">
					<?php rptr_list_shortcode(array(
						'[masonry-'.$type.']' => 
						'Display all items
						<ul>
							<li><code>order=DESC</code> to display in descending order</li>
							<li><code>posts_per_page=5</code> - change the <a href="options-reading.php">default</a> "posts per page" value</li>
						</ul>',
						)); ?>
				</td>
			</tr>

	<?php 
	}

}

new Rptr_Masonry();
?>
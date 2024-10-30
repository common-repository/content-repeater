<?php
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Rptr_Isotope extends Rptr_Class {

	public static $b;
	public static $d;

	public function __construct() {

		self::$b = basename(__FILE__, '.php');

		// options defaults
		self::$d['columns'] = 3;
		self::$d['padding'] = 20;
		self::$d['item_width'] = 200;
	
		parent::$rpts[self::$b] = array();
		parent::$rpts[self::$b]['class_name'] = get_class();
		parent::$rpts[self::$b]['nice_name'] = "Isotope";
		
		$types = self::rptr_get_option('types_all'); 
		if( is_array( $types ) and ! empty( $types ) ) {
			foreach ( $types as $k => $v ) {
				add_shortcode( 	'isotope-'.$k, array( 'Rptr_Isotope', 'isotope_shortcode' ) );
			}
		}
		
	}
	
			
	// shortcode: [isotope-...]
	public static function isotope_shortcode( $atts, $content, $tag ) {
		$type = substr($tag,strlen(self::$b)+1);
		$args = rptr_query_args($atts, $args, $type);
		$query = new WP_Query( $args );
		$dor = (rptr_get_option('breakpoints') !== '0') ? ' data-observe-resizes' : '';

		if($query->have_posts()) : $output;
			$output .= '<div class="'.self::$b.'-wrap '.$type.'-'.self::$b.'-wrap'.rptr_classes($type).'" style="visibility:hidden"'.$dor.'>';
			$taxonomy = $type.'-category';
			if(taxonomy_exists($taxonomy)) {
				$term_ids = $args['tax_query'][0]['terms']; 
				if($term_ids) { // if shortcode has cat=# attribute
					foreach($term_ids as $k=>$v) { // create string with all children terms ids
						$term_tree .= $v . ' ' . implode(' ', get_term_children($v, $taxonomy)) . ' ';
					}
				}
				$term_tree = explode(' ',trim($term_tree)); 
				foreach($query->posts as $k=>$v) { $post_ids[] = $v->ID; } // get current post ids from $query
				if(isset($term_tree) && !empty($term_tree) && trim(implode('',$term_tree))!='') {
					$args = [
						'object_ids' => $post_ids,
						'include' => $term_tree,
					];
				} else {
					$args = [
						'object_ids' => $post_ids,
					];
				} 
				
				$terms = rptr_get_taxonomy_hierarchy( $taxonomy, 0, $args ); // get hierarchical array of objects
				$terms = json_decode(json_encode($terms), true); // convert objects to arrays
				$terms = rptr_terms_hierarchy_to_flat($terms); // convert hierarchical to flat array
				
				foreach($terms as $k=>$v) { // remove if post count is 0
					if($v['count'] == 0) unset($terms[$k]);
				}
// 					echo '<pre>'; print_r($terms); echo '</pre>';
				if($terms && !( (count($terms)==1) && ($terms[0]['count'] == $query->post_count) ) ) {
					$output .= '<div class="button-group filters-button-group">';
					$output .= '<a href="#" class="all">'.__('All').'</a>';
					foreach($terms as $k=>$v) {
						if($v['parent'] == 0) {
							$output .= '<span class="isotope-separator"></span>';
							$class_child = '';
						} else {
							$class_child = ' child';
						}
						$output .= '<a href="#" class="'.$v['slug'].$class_child.'">'.$v['name'].'</a>';
					}
					$output .= '</div>';
				}
			}
			$output .= '<div class="grid '.$type.'-'.self::$b.$classes.'">';
			$output .= '<div class="grid-sizer"></div>';
			while ($query->have_posts()) : $query->the_post();
				$terms = get_the_terms( get_the_ID(), $taxonomy );
				$slugs = [];
				if($terms) foreach($terms as $k=>$v) { $slugs[] .= $v->slug; }
				$slugs = implode(' ',array_unique($slugs));
				$output .= '<div class="grid-item '.$slugs.'">';
				$output .= self::display_item( get_the_ID() );	
				$output .= '</div>';
			endwhile;
			$output .= '</div>';
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
			
		else:
			$output .= '<p>'.__('No items found.').'</p>';
		endif;
		wp_reset_postdata();
		
		wp_enqueue_script( 'isotope', plugin_dir_url(__FILE__).
			'js/isotope.pkgd.min.js', array('jquery'));
		add_action( 'wp_footer', function() use ( $type ) {
			$types = self::rptr_get_option('types_all'); 
			$rpt = $types[$type]['repeater'][self::$b];
			$columns = ($rpt['columns']!='' && $rpt['columns']>1) ? $rpt['columns'] : self::$d['columns'];
			$padding = ($rpt['padding']!='') ? $rpt['padding'] : self::$d['padding'];
			$item_width = ($rpt['item_width']!='') ? $rpt['item_width'] : self::$d['item_width'];
			?>
<style type='text/css'>
.<?php echo self::$b; ?>-wrap {
visibility: hidden;
}
.<?php echo self::$b; ?>-wrap .button-group a.is-checked {
border-bottom: 1px solid;
}
.<?php echo self::$b; ?>-wrap .button-group a {
display: inline-block;
margin: 0 10px 10px;
}
.<?php echo self::$b; ?>-wrap .grid {
margin: 0 auto;
}
.<?php echo $type; ?>-<?php echo self::$b; ?>-wrap .button-group {
text-align: center;
margin-bottom: <?php echo $padding; ?>px;
}
.<?php echo $type; ?>-<?php echo self::$b; ?> .grid-sizer, .<?php echo $type; ?>-<?php echo self::$b; ?> .grid-item {
<?php /* width: calc(<?php echo (100/$columns); ?>% - <?php echo ((($columns-1)/$columns)*$padding); ?>px); */ ?>
width: <?php echo $item_width; ?>px;
}

.<?php echo $type; ?>-<?php echo self::$b; ?> .grid-item {
margin-bottom: <?php echo $padding; ?>px;
}
</style>
<script type='text/javascript'>
(function($){	
	// $(window).load(function() {
	$(document).ready(function(){	
		$('.filters-button-group').on( 'click', 'a', function(e) {
			e.preventDefault();
			var filterValue = '.'+$(this).attr('class').split(' ')[0];
			if(filterValue.length == 1 || filterValue == '.all') filterValue = '*';
			parent = $(this).parents('.<?php echo self::$b; ?>-wrap');
			parent.find('.grid').isotope({ filter: filterValue });
		});
		// change is-checked class on buttons
		$('.button-group').each( function( i, buttonGroup ) {
			var $buttonGroup = $(buttonGroup);
			$buttonGroup.on( 'click', 'a', function(e) {
				e.preventDefault();
				document.location.hash = $(this).attr('class').split(' ')[0];
				$buttonGroup.find('.is-checked').removeClass('is-checked');
				$(this).addClass('is-checked');
			});
		});

		setTimeout(function() {
			var filterValue = '.'+window.location.hash.substring(1);
			if(filterValue.length == 1 || filterValue=='.all') filterValue = '*';
			var g = $('.<?php echo $type; ?>-<?php echo self::$b; ?>');
			g.isotope({
				filter: filterValue,
				itemSelector: '.grid-item',
				masonry: {
					columnWidth: '.grid-sizer',
					gutter: <?php echo $padding; ?>,
					fitWidth: true,
				}
			});
			if(filterValue != '*') g.closest('.<?php echo self::$b; ?>-wrap').find(filterValue).addClass('is-checked');
			g.imagesLoaded().progress( function() { // reposition layout on imagesLoaded
				g.isotope('layout');
			});
			$('.<?php echo self::$b; ?>-wrap').css('visibility','visible');
		}, 200);
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

			<?php /*rptr_option(array(
					'title' => 'Number of columns',
					'type' => 'text',
					'name' => $prefix.'[columns]',
					'value' => (isset($rpt[$rpt_name]['columns'])) ? $rpt[$rpt_name]['columns'] : '',
					'descr' => "(minimum: 2, default: ".self::$d['columns'].")",
					'classes' => 'numbers-only'
			));*/ ?>

			<?php rptr_option(array(
					'title' => 'Padding between items',
					'type' => 'text',
					'name' => $prefix.'[padding]',
					'value' => (isset($rpt[$rpt_name]['padding'])) ? $rpt[$rpt_name]['padding'] : '',
					'descr' => "in pixels, put 0 for no padding (default: ".self::$d['padding']."px)",
					'classes' => 'numbers-only'
			)); ?>

			<?php rptr_option(array(
					'title' => 'Item width',
					'type' => 'text',
					'name' => $prefix.'[item_width]',
					'value' => (isset($rpt[$rpt_name]['item_width'])) ? $rpt[$rpt_name]['item_width'] : '',
					'descr' => "in pixels (default: ".self::$d['item_width']."px)",
					'classes' => 'numbers-only'
			)); ?>

			
			<?php // Shortcodes ?>
			<tr valign="top" class="cr-shortcodes-row">
				<th scope="row"><?php esc_html_e( parent::$rpts[self::$b]['nice_name'] . ' shortcodes', 'content-repeater' ); ?></th>
				<td colspan="2">
					<?php rptr_list_shortcode(array(
						'[isotope-'.$type.']' => 
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

new Rptr_Isotope();
?>
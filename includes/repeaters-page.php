<?php
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Rptr_Repeaters_Page extends Rptr_Class {
	
	// output type settings subpage
	public static function create_repeaters_page() { ?>

		<div class="wrap">
			
			<h1><?php echo get_admin_page_title(); ?></h1>
			
			<?php 
			if( isset($_GET['settings-updated']) ) {
				echo '<div id="message" class="updated notice notice-success"><p>Settings saved</p></div>';
			}
			 
			$types = self::rptr_get_option('types_all');
			$type = $_GET['post_type'];
			if( $_GET['repeater'] ) {
				$rpt_name = $_GET['repeater'];
			} else {
				$rpt_name = $types[$type]['repeater']['repeater_current']; 
			}
			$rpt = $types[$type]['repeater'][$rpt_name];

			foreach (self::$rpts as $k => $v) { 
				$values[$k] = $v['nice_name'];
			}
			?>

			<form method="post" action="options.php" id="cr-type-settings" data-type="<?php echo $type; ?>">

				<?php settings_fields( 'rptr_options' ); ?>

				<table class="form-table cr">

					<?php 
					rptr_option(array(
							'title' => 'Select Repeater',
							'type' => 'select',
							'name' => 'rptr_options[types_all]['.$type.'][repeater][repeater_current]',
							'value' => $values,
							'current' => $rpt_name,
							'descr' => $descr,
							'id' => 'repeater-select',
							'classes' => 'cr-repeater-select'
					)); ?>

					<?php
					foreach (self::$rpts as $k => $v) { 
						if( $k == $rpt_name ) {
							$v['class_name']::display_options($type, $rpt_name);
							break;
						}
					}
					?>	

					<?php // Global Shortcodes ?>
					<tr valign="top" class="cr-shortcodes-row">
						<th scope="row"><?php esc_html_e( 'Global shortcodes', 'content-repeater' ); ?></th>
						<td colspan="2">
							<?php rptr_list_shortcode(array(
								'[all-'.$type.']' => 
								'Display all items
								<ul>
									<li><code>order=DESC</code> - display in descending order</li>
									<li><code>posts_per_page=5</code> - change the <a href="options-reading.php">default</a> "posts per page" value</li>
								</ul>',
								'[cri id=#]' => 'Display a single item',
								)); ?>
						</td>
					</tr>
								
				</table>

				<?php submit_button('Save'); ?>

			</form>

			<?php echo self::debugger(); ?>

		</div><!-- .wrap -->
	<?php }

}
?>
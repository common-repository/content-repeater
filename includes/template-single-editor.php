<?php
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Rptr_Template_Single_Editor extends Rptr_Template_Editor {

	// output template page
	public static function create_template_single_page() { 

		$type = $_GET['post_type'];
		$types = self::rptr_get_option('types_all');
		$rpt = $types[$type]['repeater'];
		$template = 'template_single';

		self::template_page_start($type);
		self::editor_above($type, $types, $template);
		self::editor($type, $types, $template);
		?>
		<div class="cr-template-additional">
			<h2>Additional</h2>
		<?php
		self::custom_css($type, $types, $template);
		self::custom_js($type, $types, $template);
		self::custom_files($type, $types, $template);
		submit_button(__('Save'));
		self::template_page_end();
		?>
		</div>
		<?php			
		echo self::debugger();
		
	}		
	
}
?>
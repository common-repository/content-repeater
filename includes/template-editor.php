<?php
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Rptr_Template_Editor extends Rptr_Class {

	public function __construct() {		

		if(strpos($_SERVER['REQUEST_URI'], '-cr-template')) {
			add_action( 'admin_enqueue_scripts', function() {
				wp_enqueue_script( 'cr-template', RPTR_URL.'assets/js/cr-template.js', array('jquery'));
				wp_enqueue_code_editor( array( 'type' => 'text/html' ) );
				wp_enqueue_media();
				wp_enqueue_editor();
			} );
			add_action( 'media_buttons', function($editor_id){
				echo '<a href="#" class="button cr-insert-shortcode"><span class="dashicons dashicons-editor-code"></span>Insert Field</a>';
			} );						
		}
		add_action( 'wp_ajax_ajax_load_template', array( 'Rptr_Template_Editor', 'ajax_load_template' ) );

	}	

	// ajax load template
	public static function ajax_load_template() {
		$template = file_get_contents(RPTR_PATH.'templates/'.$_REQUEST['file'].'.php');
		$template = preg_replace('/^.+\n/', '', $template);
		echo $template;
		wp_die();
	} 
	
	// output template page
	public static function create_template_page() { 

		$type = $_GET['post_type'];
		$types = self::rptr_get_option('types_all');
		$rpt = $types[$type]['repeater'];
		$template = 'template';

		self::template_page_start($type);
		self::editor_above($type, $types, $template);
		self::editor($type, $types, $template);
		?>
		<div class="cr-template-additional">
			<h2>Additional</h2>
		<?php
// 		self::css_controls($type, $types);
		self::css_classes($type, $types);
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
	
	// template page: start
	public static function template_page_start($type) { ?>
		<div class="wrap">			
			<?php include(RPTR_PATH.'includes/template-tags.html');	?>
			<form id="cr-template-form" method="post" action="options.php">
				<?php settings_fields( 'rptr_options' ); ?>
				<div class="cr-template-editor-wrap">
					<div class="cr-template-editor-side">
						<h1><?php echo get_admin_page_title(); ?></h1>
						<?php 
						if( isset($_GET['settings-updated']) ) {
							echo '<div id="message" class="updated notice notice-success"><p>Template saved. <a href="post-new.php?post_type='.$type.'">Add content</a> | <a href="edit.php?post_type='.$type.'&page='.$type.'-cr-repeaters">Select Repeater</a></p></div>';
						} ?>				
						<div class="cr-prebuit-templates-wrap">
							<select id="cr-prebuit-templates">
								<option value="none">— Choose a pre-built template —</option>
								<?php
									foreach(glob(RPTR_PATH.'templates/*.php') as $file) {
									$name = fgets(fopen($file, 'r'));
									preg_match('/\/\* (.+) \*\//U', $name, $m);
									$name = $m[1];
									echo '<option value="'.basename($file,'.php').'">'.$name.'</option>';
									}
								?>
							</select>
							<span class="cr spinner"></span>
						</div>
	<?php } 
	// template page: end
	public static function template_page_end() { ?>
					</div>
				</div>
			</form>
		</div><!-- .wrap -->
	<?php }

	// template page: above editor
	public static function editor_above($type, $types, $which) {
		if( !preg_match( self::$pat, $types[$type][$which] ) ) { 
			echo '<h2>Create a Template or load a pre-built one &rarr;</h2>';
		} else {
			self::rptr_template_fields($type);
		}		
	}
	
	// template page: editor
	public static function editor($type, $types, $which) { 
		$prefix = 'rptr_options[types_all]['.$type.']';				
		?>
		<div class="cr-template-editor-outer">
			<div class="cr-template-toolbar">
				<div id="wp-content-editor-tools" class="wp-editor-tools hide-if-no-js">
					<div id="wp-content-media-buttons" class="wp-media-buttons">
						<a href="#" class="button cr-insert-shortcode"><span class="dashicons dashicons-editor-code"></span>Insert Field</a>
						<a href="#" class="button cr-insert-media"><span class="dashicons dashicons-admin-media"></span>Insert Media</a>
					</div>
					<div class="wp-editor-tabs">
						<div class="cr-template-disable-visual">
							<input type="hidden" name="<?php echo $prefix; ?>[<?php echo $which; ?>_disable_visual]" value="No">
							<label><input type="checkbox" name="<?php echo $prefix; ?>[<?php echo $which; ?>_disable_visual]" value="Yes" <?php checked($types[$type][$which.'_disable_visual'], 'Yes'); ?>> <span>Disable Visual editor <span class="description cr-opaque">(may be useful to prevent accidental code removal by the Visual editor)</span></span></label>
						</div>
						<?php if($types[$type][$which.'_disable_visual'] != 'Yes') { ?>
						<button type="button" id="cr-tmce" class="wp-switch-editor switch-html">Visual</button>
						<?php } ?>
						<button type="button" id="cr-html" class="wp-switch-editor switch-html">Code</button>
					</div>
				</div>
			</div>
			<?php 
			$options = self::rptr_get_options();
			$nosyntax = ($options['nosyntax'] == 'Yes') ? 'nosyntax ' : '';
			echo '<textarea id="cr-template" class="'.$nosyntax.'theEditor cr-template-editor" rows="15" cols="40" tabindex="1" autocomplete="off" name="'.$prefix.'['.$which.']">'.$types[$type][$which].'</textarea>';
			?>
			<?php submit_button(__('Save')); ?>
		</div>
	<?php }

	// template page: visual CSS settings
/*	public static function css_controls($type, $types) {
		$css_prefix = 'rptr_options[types_all]['.$type.'][box_css]'; 
		$css_path = $types[$type]['box_css']; 
		?>	
		<div class="cr-template-box-settings">
			<p><strong>Background</strong></p>
			<div class="cr-template-background">
				<div class="cr-template-background-color">
					<p>color: </p>
					<?php
					wp_enqueue_style( 'wp-color-picker');
					wp_enqueue_script( 'wp-color-picker');						
					?>
			
					<div class="cr-template-background-color-inner">
						<input class="color-field" type="text" name="<?php echo $css_prefix; ?>[background][color]" value="<?php echo $css_path['background']['color']; ?>">
					</div>
				</div>
				<div class="cr-template-background-image">
					<p>image: </p>
					<?php								
					if ( ! did_action( 'wp_enqueue_media' ) ) {
						wp_enqueue_media();
					}			
					$value = $css_path['background']['image'];
					echo '
					<div class="cr-upload-image-wrap">
					  <input type="hidden" name="'.$css_prefix.'[background][image]" value="'.$value.'" class="cr-upload-image-holder">
					  <ul class="cr-upload-image-list">';
					if(isset($value)) { 
						$display = ' style="display: none;"';
				  echo '<li class="cr-upload-image">
						  <img src="'.wp_get_attachment_image_src($value, 'thumbnail')[0].'">
						  <a href="#" class="cr-remove-image-button"></a>
						</li>';
				  }
				echo '</ul>
					  <a href="#" class="cr-upload-image-button button"'.$display.'">Upload</a>
					</div>';														
					?>
				</div>
				<div class="cr-template-background-image-options">
					<div class="cr-template-background-size">
						<p>size: </p>
						<select name="<?php echo $css_prefix; ?>[background][size]">
							<option value="">— Select —</option>;
						<?php
							$options = array( 'cover','contain','auto' );
							foreach ($options as $k=>$v) {
								echo '<option value="'.$v.'" '.selected($css_path['background']['size'],$v).'>'.$v.'</option>';
							}
						?>
						</select>
					</div>
					<div class="cr-template-background-repeat">
						<p>repeat: </p>
						<select name="<?php echo $css_prefix; ?>[background][repeat]">
							<option value="">— Select —</option>;
						<?php
							$options = array( 'no-repeat','repeat','repeat-x','repeat-y','inherit' );
							foreach ($options as $k=>$v) {
								echo '<option value="'.$v.'" '.selected($css_path['background']['repeat'],$v).'>'.$v.'</option>';
							}
						?>
						</select>
					</div>
					<div class="cr-template-background-position">
						<p>position: </p>
						<select name="<?php echo $css_prefix; ?>[background][position]">
							<option value="">— Select —</option>;
						<?php
							$options = array( 'center center','center top','center bottom','left center','left top','left bottom','right center','right top','right bottom' );
							foreach ($options as $k=>$v) {
								echo '<option value="'.$v.'" '.selected($css_path['background']['position'],$v).'>'.$v.'</option>';
							}
						?>
						</select>
					</div>
					<div class="cr-template-background-attachment">
						<p>attachment: </p>
						<select name="<?php echo $css_prefix; ?>[background][attachment]">
							<option value="">— Select —</option>;
						<?php
							$options = array( 'fixed','scroll','local' );
							foreach ($options as $k=>$v) {
								echo '<option value="'.$v.'" '.selected($css_path['background']['attachment'],$v).'>'.$v.'</option>';
							}
						?>
						</select>
					</div>
				</div>
			</div>
			<div class="cr-template-padding">
				<p><strong>Padding:</strong></p>
				<div class="cr-template-padding-inner">
					<span>top</span> <input type="text" name="<?php echo $css_prefix; ?>[padding][top]" value="<?php echo $css_path['padding']['top']; ?>">
					<span>right</span> <input type="text" name="<?php echo $css_prefix; ?>[padding][right]" value="<?php echo $css_path['padding']['right']; ?>">
					<span>bottom</span> <input type="text" name="<?php echo $css_prefix; ?>[padding][bottom]" value="<?php echo $css_path['padding']['bottom']; ?>">
					<span>left</span> <input type="text" name="<?php echo $css_prefix; ?>[padding][left]" value="<?php echo $css_path['padding']['left']; ?>">
					<span class="description cr-opaque">(example: 20px, 1.2%, 1em, etc.)</span>										
				</div>
			</div>
			<div class="cr-template-border">
				<p><strong>Border:</strong></p>
				<div class="cr-template-border-inner">
					<input type="text" name="<?php echo $css_prefix; ?>[border][width]" value="<?php echo $css_path['border']['width']; ?>">
					<select name="<?php echo $css_prefix; ?>[border][style]">
						<option value="">— Select —</option>;
					<?php
						$options = array( 'solid','dotted','dashed','double' );
						foreach ($options as $k=>$v) {
							echo '<option value="'.$v.'" '.selected($css_path['border']['style'],$v).'>'.$v.'</option>';
						}
					?>
					</select>
					<?php
					wp_enqueue_style( 'wp-color-picker');
					wp_enqueue_script( 'wp-color-picker');						
					?>
	
					<div class="cr-template-border-color-inner">
						<input class="color-field" type="text" name="<?php echo $css_prefix; ?>[border][color]" value="<?php echo $css_path['border']['color']; ?>">
					</div>
					<span class="description cr-opaque">(example: 1px sold #CCCCCC)</span>
				</div>
			</div>
			<div class="cr-template-color">
				<p><strong>Font color:</strong></p>
				<?php
				wp_enqueue_style( 'wp-color-picker');
				wp_enqueue_script( 'wp-color-picker');						
				?>
				<div class="cr-template-color-inner">
					<input class="color-field" type="text" name="<?php echo $css_prefix; ?>[color]" value="<?php echo $css_path['color']; ?>">
				</div>
			</div>
		</div>
	<?php }
*/	
	// template page: CSS classes
	public static function css_classes($type, $types) { ?>
		<div class="cr-template-classes">
			<div class="cr-template-classes-inner">
				<p>Wrapper class(es): </p>
				<input type="text" name="rptr_options[types_all][<?php echo $type; ?>][wrap_classes]" value="<?php echo $types[$type]['wrap_classes']; ?>">
			</div>
			<div class="cr-template-classes-inner">
				<p>Single item class(es): </p>
				<input type="text" name="rptr_options[types_all][<?php echo $type; ?>][item_classes]" value="<?php echo $types[$type]['item_classes']; ?>">
			</div>
		</div>
	<?php }
	
	// template page: custom CSS textarea
	public static function custom_css($type, $types, $which) { ?>
		<div class="cr-template-custom-css">
			<p>Custom CSS:</p>
			<?php
			$options = self::rptr_get_options();
			$nosyntax = ($options['nosyntax'] == 'Yes') ? 'nosyntax ' : '';
			?>
			<textarea id="cr-template-custom-css" class="<?php echo $nosyntax; ?>" name="rptr_options[types_all][<?php echo $type; ?>][<?php echo $which; ?>_custom_css]"><?php echo $types[$type][$which.'_custom_css']; ?></textarea>
		</div>
	<?php 
	}
	
	// template page: custom JS textarea
	public static function custom_js($type, $types, $which) { ?>
		<div class="cr-template-custom-js">
			<p>Custom Javascript:</p>
			<?php
			$options = self::rptr_get_options();
			$nosyntax = ($options['nosyntax'] == 'Yes') ? 'nosyntax ' : '';
			?>
			<textarea id="cr-template-custom-js" class="<?php echo $nosyntax; ?>" name="rptr_options[types_all][<?php echo $type; ?>][<?php echo $which; ?>_custom_js]"><?php echo $types[$type][$which.'_custom_js']; ?></textarea>
		</div>
	<?php 
	}
	
	// template page: custom JS textarea
	public static function custom_files($type, $types, $which) { ?>
		<div class="cr-template-custom-files">
			<p>Custom Files (*.js or *.css one per line):</p>
			<textarea id="cr-template-custom-files" name="rptr_options[types_all][<?php echo $type; ?>][<?php echo $which; ?>_custom_files]"><?php echo $types[$type][$which.'_custom_files']; ?></textarea>
		</div>
	<?php 
	}
	
}
new Rptr_Template_Editor();
?>
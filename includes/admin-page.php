<?php
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Rptr_Admin_Page extends Rptr_Class {
	
	public function __construct() {
	
		wp_enqueue_script('jquery');
		wp_enqueue_script('jquery-ui-sortable');
		wp_enqueue_script( 'dashicons-picker', RPTR_URL.'assets/js/dashicons-picker.js', array('jquery'));
		wp_enqueue_style( 'dashicons-picker', RPTR_URL.'assets/css/dashicons-picker.css', false, '1.1', 'all');	 	
		
		add_action( 'wp_ajax_ajax_edit_type', 	array( 'Rptr_Admin_Page', 'ajax_edit_type' ) );					
		add_action( 'wp_ajax_ajax_delete_type', array( 'Rptr_Admin_Page', 'ajax_delete_type' ) );
		add_action( 'wp_ajax_ajax_sort_types', 	array( 'Rptr_Admin_Page', 'ajax_sort_types' ) );
		add_action( 'wp_ajax_ajax_type_icon', 	array( 'Rptr_Admin_Page', 'ajax_type_icon' ) );
		add_action( 'wp_ajax_ajax_type_categorize', 	array( 'Rptr_Admin_Page', 'ajax_type_categorize' ) );

	}
	
	// ajax edit type
	public static function ajax_edit_type() {
		if($_POST['type'] && $_POST['value'] && $_POST['target']) { 
			$options = get_option('rptr_options');
			$types = $options['types_all'];
			if( $_POST['target'] == 'delete-slug') {
				unset($types[$_POST['type']]['type_slug']);
			} else {
				$types[$_POST['type']]['type_'.$_POST['target']] = sanitize_text_field($_POST['value']);
			}
			$options['types_all'] = $types;
			self::deregister_settings();
			update_option('rptr_options', $options);
			self::register_settings();
			self::register_all();
			flush_rewrite_rules();
		} else {
			echo 'Error';
		}
		wp_die();
	} 

	// ajax delete type
	public static function ajax_delete_type() {
		if($_POST['type']) { 
			$options = get_option('rptr_options');
			$types = $options['types_all'];
			unset($types[$_POST['type']]);
			$options['types_all'] = $types;
			self::deregister_settings();
			update_option('rptr_options', $options);
			self::register_settings();
		} else {
			echo 'Error';
		}
		wp_die();
	} 
	
	// ajax sort types
	public static function ajax_sort_types() {
		if($_POST['order']) {
			$options = get_option('rptr_options');
			$types = $options['types_all'];
			parse_str($_POST['order'], $data);

			$types_sorted = array();
			foreach($data['type'] as $value) {
			   if(array_key_exists($value,$types)) {
				  $types_sorted[$value] = $types[$value];
			   }
			}
			$options['types_all'] = $types_sorted;
			self::deregister_settings();
			update_option('rptr_options', $options);
			self::register_settings();
		}
		wp_die();
	} 
	
	// ajax type icon
	public static function ajax_type_icon() {
		if($_POST['icon'] && $_POST['type']) {
			$options = get_option('rptr_options');
			$types = $options['types_all'];
			$types[$_POST['type']]['icon'] = sanitize_text_field($_POST['icon']);
			$options['types_all'] = $types;
			self::deregister_settings();
			update_option('rptr_options', $options);
			self::register_settings();
		}
		wp_die();
	} 
	
	// ajax type icon
	public static function ajax_type_categorize() {
		if($_POST['categorize'] && $_POST['type']) {
			$options = get_option('rptr_options');
			$types = $options['types_all'];
			$types[$_POST['type']]['categorize'] = sanitize_text_field($_POST['categorize']);
			$options['types_all'] = $types;
			self::deregister_settings();
			update_option('rptr_options', $options);
			self::register_settings();
			self::register_all();
			flush_rewrite_rules();
		}
		wp_die();
	} 

	// creat main Content Repeater settings page
	public static function create_admin_page() { ?>

		<div class="wrap">
			
			<h1><?php echo get_admin_page_title(); ?></h1>

			<form method="post" action="options.php">
			
				<?php settings_fields( 'rptr_options' ); ?>

				<table class="form-table cr-admin">

					<?php 
					$options = self::rptr_get_options();
					$types = $options['types_all']; 
					?>
					<tr valign="top">
						<td class="cr-first-col">
							<h3><?php esc_html_e( 'Add new Content Type', 'content-repeater' ); ?></h3>
							<?php $value = self::rptr_get_option( 'types_all' ); ?>
							<input type="text" name="rptr_options[types_all][type_name]" value="" autofocus>
							<p class="description">Examples: Testimonials, Listings, Portfolio, etc.</p>
							<?php submit_button(__('Add')); ?>
						</td>
						<td class="cr-second-col">
							<h3>
								<?php echo (is_array($types) ? 'Registered Content Types' : ''); ?>
								<span class="cr spinner"></span>
							</h3>
							<ul class="cr-types-list" id="cr-sortable">
							<?php 
// 								echo '<pre>'; print_r($options); echo '</pre>';
							if(is_array( $types ) ) {
								foreach ( $types as $type => $v ) {
echo '	<li id="type-'.$type.'" class="cr-type">
		<span class="handle dashicons dashicons-menu"></span>';
echo '		<input type="hidden" id="cr-type-icon-'.$type.'">
		<a title="Change icon" class="dashicons-picker dashicons '.(isset($v['icon'])?$v['icon']:'dashicons-arrow-right').'" data-target="#cr-type-icon-'.$type.'" data-type="'.$type.'"></a>';			
echo '		<span class="cr-type-name" data-name="'.$v['type_name'].'">'.$v['type_name'].'</span>';
if( isset($v['type_slug']) )
echo '	<span class="cr-type-slug" data-slug="'.$v['type_slug'].'">'.$v['type_slug'].'</span>';
echo '	<span class="cr spinner"></span>
		<div class="cr-type-links">';
if( !isset($v['type_slug']) ) {
echo '		<a href="#" data-type="'.$type.'" data-target="slug" class="cr-edit-type" title="Add Slug">Add Slug</a> ';
} else {
echo '		<a href="#" data-type="'.$type.'" class="cr-delete-slug">Delete Slug</a> ';
echo '		<a href="#" data-type="'.$type.'" data-target="slug" class="cr-edit-type" title="Edit Slug">Edit Slug</a> ';
}
echo '			<a href="#" data-type="'.$type.'" data-target="name" class="cr-edit-type" title="Rename Type">Rename</a> ';
if( isset($v['type_slug']) ) {
echo '		<a class="cr-template-link" href="edit.php?post_type='.$type.'&page='.$type.'-cr-template-single">Single View Template</a> ';
}
echo '			<a class="cr-template-link" href="edit.php?post_type='.$type.'&page='.$type.'-cr-template">Template</a> 
			<label href="#" title="Has categories?">Cats <input class="cr-categorize-type" data-type="'.$type.'" type="checkbox" '.checked('Yes', $v['categorize'], false).'></label> 
			<a href="#" data-type="'.$type.'" class="cr-delete-type" title="Delete Type">Delete</a> 
		</div>
	</li>';
								}
							}
							?>
							</ul>
						</td>
					</tr>

				</table>
				
				<h3><?php echo __( 'Additional' ); ?></h3>
				<table class="form-table cr cr-admin-additional">
		
					<?php rptr_option(array(
							'title' => '',
							'type' => 'checkbox',
							'name' => 'rptr_options[nosyntax]',
							'value' => 'Yes',
							'current' => (isset($options['nosyntax']) ? $options['nosyntax'] : 'No'),
							'descr' => "Disable Template editor syntax highlighting",
							'classes' => 'cr-checkbox'
					)); ?>
					
					<?php rptr_option(array(
							'title' => '',
							'type' => 'text',
							'name' => 'rptr_options[breakpoints]',
							'value' => (isset($options['breakpoints'])) ? $options['breakpoints'] : '0',
							'descr' => "Responsive breakpoints, 0 to disable Resize Observer (example: SM:384,MD:576,LG:768,XL:960)",
							'classes' => 'cr-breakpoints'
					)); ?>

		
				</table>					
				<?php submit_button(__('Save')); ?>
									
			</form>
			
			<?php if($_GET['action'] == 'set-slug') { ?>
			<script>
			jQuery(document).ready(function($){ 
				// focus on slug editing field
				$('a[data-type="<?php echo $_GET['type']; ?>"]').parents('.cr-type-links').find('a[data-target="slug"]').trigger('click');
				// then remove $_GET['action'] from URL
				var uri = window.location.href.toString();
				if (uri.indexOf('&action') > 0) {
					var clean_uri = uri.substring(0, uri.indexOf('&action'));
					window.history.replaceState({}, document.title, clean_uri);
				}
			});				
			</script>
			<?php } ?>
			
			<?php echo self::debugger(); ?>

		</div><!-- .wrap -->
	<?php }	

}

new Rptr_Admin_Page();
?>
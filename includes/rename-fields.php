<?php
class Rptr_Rename_Fields extends Rptr_Class {

	public function __construct() {
	
		add_action( 'admin_menu', function() {
			add_submenu_page( 
				'content-repeater', 
				esc_html__( 'Rename Type Fields', 'content-repeater' ), 
				esc_html__( 'Rename Fields', 'content-repeater' ), 
				'manage_options', 
				'cr-rename-fields', 
				array( 'Rptr_Rename_Fields', 'create_rename_fields_page' ) 
			); 
		} );
		add_action( 'wp_ajax_ajax_load_fields', array( 'Rptr_Rename_Fields', 'ajax_load_fields' ) );
		
	}

	public static function ajax_load_fields() {
		global $wpdb;
		$results = $wpdb->get_results(
			"SELECT $wpdb->posts.ID, $wpdb->posts.post_type, $wpdb->postmeta.meta_key FROM $wpdb->posts 
			INNER JOIN $wpdb->postmeta ON $wpdb->posts.ID = $wpdb->postmeta.post_id
			WHERE $wpdb->posts.post_type LIKE '".$_REQUEST['type']."'
			GROUP BY $wpdb->postmeta.meta_key
			ORDER BY $wpdb->postmeta.meta_key DESC"
			);
		?>	
		<select id="fields-select" name="cr-rename-from">
			<option value="none">— Choose Custom Field —</option>
			<option value="post_content">post_content</option>
			<?php
			foreach ($results as $k => $v) { 
				echo '<option value="'.$v->meta_key.'">'.$v->meta_key.'</option>';
			}
			?>
		</select>
		<?php
	
		wp_die();
	}
			
	public static function create_rename_fields_page() { ?>

		<div class="wrap">
	
			<h1><?php echo get_admin_page_title(); ?></h1>

			<?php
			if(isset($_POST['cr-rename-to']) && isset($_POST['cr-rename-from']) && isset($_POST['cr-rename-type'])) {
				global $wpdb;
				if($_POST['cr-rename-from'] == 'post_content') {
					$posts = get_posts(array(
						'post_type' => $_POST['cr-rename-type'],
						'posts_per_page' => -1
						));
// 						print_r($the_posts);
					foreach($posts as $k=>$v) {
// 						if(!metadata_exists('post', $v->ID, $_POST['cr-rename-to'])) {
							update_post_meta($v->ID, sanitize_text_field($_POST['cr-rename-to']), $v->post_content);
							$my_post = array();
							$my_post['ID'] = $v->ID;
							$my_post['post_content'] = '';
							wp_update_post( wp_slash($my_post) );
// 						}
					}
					$results = count($posts);
				} elseif($_POST['cr-rename-to'] == 'post_content') {
					$posts = get_posts(array(
						'post_type' => $_POST['cr-rename-type'],
						'posts_per_page' => -1
						));
// 						print_r($the_posts);
					foreach($posts as $k=>$v) {
						$my_post = array();
						$my_post['ID'] = $v->ID;
						$my_post['post_content'] = get_post_meta( $v->ID, $_POST['cr-rename-from'], true );
						wp_update_post( wp_slash($my_post) );
						delete_post_meta( $v->ID, $_POST['cr-rename-from'] );
					}
					$results = count($posts);
				} else {
					$results = $wpdb->query(
						"UPDATE $wpdb->postmeta
						INNER JOIN $wpdb->posts ON $wpdb->posts.ID = $wpdb->postmeta.post_id
						SET $wpdb->postmeta.meta_key = '".sanitize_text_field($_POST['cr-rename-to'])."' 
						WHERE $wpdb->postmeta.meta_key = '".sanitize_text_field($_POST['cr-rename-from'])."'
						AND $wpdb->posts.post_type LIKE '".sanitize_text_field($_POST['cr-rename-type'])."'"
						);
				}
				if(!empty($results)) {
					$results = $results;
				} else {
					$results = '0';
					$error = ' error';
				}
				echo '<div id="message" class="updated notice notice-success'.$error.'"><p>Fields updated: '.$results.'</p></div>';
			}
			?>
			
			<form id="cr-rename-fields-form" action="" method="post" enctype="multipart/form-data">

				<h2>Use with caution!</h2>
				<table class="form-table">
					<tr valign="top">
						<td>	
							<?php
							$types = get_post_types(['_builtin'=>false]);
							?>
							<select id="types-select" name="cr-rename-type">
								<option value="none">— Choose Post Type —</option>
								<?php
								foreach ($types as $k => $v) { 
									echo '<option value="'.$k.'">'.get_post_type_object($v)->labels->name.' ('.$v.')</option>';
								}
								?>
							</select>
							<span class="cr spinner"></span>
							<span class="cr-rename-label">Rename:</span>
							<span class="cr-to-label">To:</span>
							<input class="cr-rename-to" type="text" name="cr-rename-to">
						</td>
					</tr>
				</table>
				
				<?php submit_button(__('Submit')); ?>
						
			</form>
		
		</div>
	
	<?php
	}		
}
new Rptr_Rename_Fields();
?>
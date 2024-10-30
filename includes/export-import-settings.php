<?php
class Rptr_Export_Settings extends Rptr_Class {

	public function __construct() {
	
		add_action( 'admin_menu', function() {
			add_submenu_page( 
				'content-repeater', 
				esc_html__( 'Export / Import Content Repeater Settings', 'content-repeater' ), 
				esc_html__( 'Export / Import', 'content-repeater' ), 
				'manage_options', 
				'cr-export-import-settings', 
				array( 'Rptr_Export_Settings', 'create_export_options_page' ) 
			); 
		} );
		
	}
			
	public static function create_export_options_page() { ?>

		<div class="wrap">
	
			<h1><?php echo get_admin_page_title(); ?></h1>
			
			<?php 
			if($_FILES['rptr_import']) {
				if($_FILES['rptr_import']['error'] > 0) {
					echo '<p>There was no problem uploading file...</p>';
				} else {
					$options = unserialize(file_get_contents($_FILES['rptr_import']['tmp_name']));
					parent::deregister_settings();
					update_option('rptr_options', $options);
					parent::register_settings();
					echo '<div id="message" class="updated notice notice-success"><p>Settings imported successfully!</p></div>';
				}
			}
			?>
							
			<form id="cr-export-import-form" action="" method="post" enctype="multipart/form-data">

				<table class="form-table">
					<tr valign="top">
						<th scope="row"><?php esc_html_e( 'Download settings as a file', 'content-repeater' ); ?></th>
						<td>		
							<input name="rptr_export" class="button button-primary cr-export-btn" type="submit" value="Export Settings" name="submit">
						</td>
					</tr>
					<tr valign="top">
						<th scope="row"><?php esc_html_e( 'Choose a settings file', 'content-repeater' ); ?></th>
						<td>		
							<input name="rptr_import" type="file"> <?php submit_button(__('Import Settings')); ?>

						</td>
					</tr>
				</table>
						
			</form>
		
		</div>
	
	<?php
	}		
}
new Rptr_Export_Settings();


if(isset($_POST['rptr_export'])) {
	add_action('wp_loaded', 'rptr_export_to_text_download', 1);
	function rptr_export_to_text_download() {	
		$filename = 'content-repeater.' . date( 'Ymd-Hi' ) . '.txt';
		header( 'Content-Description: File Transfer' );
		header( 'Content-Disposition: attachment; filename=' . $filename );
		header( 'Content-Type: text/plain;' . get_option( 'blog_charset' ), true );
		echo serialize( get_option( 'rptr_options' ) );		
 		die();
	}
}



?>
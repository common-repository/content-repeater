<?php
add_action( 'post_submitbox_misc_actions', 'rptr_prn_post_submitbox_misc_actions_function', 999 );
add_action( 'admin_head', 'rptr_prn_admin_head_function' );
add_action( 'redirect_post_location', 'rptr_prn_wp_insert_post_function' );

function rptr_prn_post_submitbox_misc_actions_function() {
	global $post;
	?>
	<div class="cr-post-save-buttons">
		<input type="hidden" name="rptr_prn_redirect_post_type" id="rptr_prn_redirect_post_type" value="<?php echo $post->post_type; ?>">
		<input type="hidden" name="rptr_prn_redirect" id="rptr_prn_redirect" value="no">
		<input type="button" id="rptr_prn_publish" name="submit-new" value="Publish & Add New" class="button prn_button">
		<?php if( $post->post_status != 'auto-draft' ) echo rptr_rd_generate_duplicate_post_link( $post->ID, $classes = 'button', $redirect = 'post' ); ?>
	</div>
	<?php
}
function rptr_prn_admin_head_function() { ?>
	<script>
		jQuery(document).ready(function($) {
			jQuery('#rptr_prn_publish').click(function(){
				jQuery('#rptr_prn_redirect').val('yes');
				jQuery('#publish').trigger('click');
				jQuery('input#title').focus();
			});
		});
	</script>
	
	<?php
}
function rptr_prn_wp_insert_post_function($url)
{
	if(isset($_POST['rptr_prn_redirect']) AND $_POST['rptr_prn_redirect']=='yes')
	{
		wp_redirect(admin_url('post-new.php?post_type='.esc_attr($_POST['rptr_prn_redirect_post_type'])));
		die();
	}
	elseif(isset($_POST['rptr_prn_redirect']) AND $_POST['rptr_prn_redirect']=='visit')
	{
		wp_redirect(get_permalink($_POST['post_ID']));
		die();
	}
	return $url;
}
?>
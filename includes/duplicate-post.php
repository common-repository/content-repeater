<?php
function rptr_rd_duplicate_post(){
	global $wpdb;
	if ( !( isset($_GET['post']) || isset($_GET['cr-duplicate-redirect']) || isset($_POST['post'])  || ( isset($_REQUEST['action']) && 'rptr_rd_duplicate_post' == $_REQUEST['action'] ) ) ) {
		wp_die('No post to duplicate has been supplied!');
	}

	// Nonce verification
	add_action('init', function(){
		if ( !isset($_GET['duplicate_nonce']) || !wp_verify_nonce($_GET['duplicate_nonce'], 'duplicate_nonce') )
			return;
	});
 
	//get the original post id
	$post_id = (isset($_GET['post']) ? absint( $_GET['post'] ) : absint( $_POST['post'] ) );
	// and all the original post data
	$post = get_post( $post_id );
 
	/*
	 * if you don't want current user to be the new post author,
	 * then change next couple of lines to this: $new_post_author = $post->post_author;
	 */
	$current_user = wp_get_current_user();
	$new_post_author = $current_user->ID;
 
	//if post data exists, create the post duplicate
	if (isset( $post ) && $post != null) {
	
		// here we create new names for cloned posts
		$title_base = $post->post_title;		
		$title = $post->post_title;
		global $wpdb;
		$i = 0;
		$titles = array( $title_base );
		while( in_array( $title, $titles ) ) {
			$i++;
			$title = $title_base . ' (copy '.$i.')';
			$posts = $wpdb->get_results( 
				"SELECT ID, post_title FROM $wpdb->posts 
				WHERE post_title LIKE '".$title."'
				AND post_type LIKE '".$post->post_type."'
				AND post_status NOT LIKE 'trash'
				" 
			);
			foreach($posts as $k=>$v) {
				$titles[] = $v->post_title;
			}
			$titles = array_unique($titles);
		}

 
		//new post data array
		$args = array(
			'comment_status' => $post->comment_status,
			'ping_status'    => $post->ping_status,
			'post_author'    => $new_post_author,
			'post_content'   => $post->post_content,
			'post_excerpt'   => $post->post_excerpt,
			'post_name'      => $post->post_name,
			'post_parent'    => $post->post_parent,
			'post_password'  => $post->post_password,
			'post_status'    => 'publish', // db: let's publish it right away
 			'post_title'     => $title,
			'post_type'      => $post->post_type,
			'to_ping'        => $post->to_ping,
			'menu_order'     => $post->menu_order
		);
 
		// insert the post by wp_insert_post() function
		$new_post_id = wp_insert_post( $args );
 
		// get all current post terms ad set them to the new post draft
		$taxonomies = get_object_taxonomies($post->post_type); // returns array of taxonomy names
		foreach ($taxonomies as $taxonomy) {
			$post_terms = wp_get_object_terms($post_id, $taxonomy, array('fields' => 'slugs'));
			wp_set_object_terms($new_post_id, $post_terms, $taxonomy, false);
		}
 
		// duplicate all post meta
		$post_meta_infos = $wpdb->get_results("SELECT meta_key, meta_value FROM $wpdb->postmeta WHERE post_id=$post_id");
		if (count($post_meta_infos)!=0) {
			$sql_query = "INSERT INTO $wpdb->postmeta (post_id, meta_key, meta_value) ";
			foreach ($post_meta_infos as $meta_info) {
				$meta_key = $meta_info->meta_key;
				if( $meta_key == '_wp_old_slug' ) continue;
				$meta_value = addslashes($meta_info->meta_value);
				$sql_query_sel[]= "SELECT $new_post_id, '$meta_key', '$meta_value'";
			}
			$sql_query.= implode(" UNION ALL ", $sql_query_sel);
			$wpdb->query($sql_query);
		}
 
 
		// redirect to the edit post screen
		if( $_GET['cr-duplicate-redirect'] == 'post' ) {
			wp_redirect( admin_url( 'post.php?action=edit&post=' . $new_post_id ) );
		} else {
			// db: let's stay on page
			wp_redirect( admin_url( 'edit.php?post_type='.$post->post_type.'&new_post_id='.$new_post_id ) ); 
		}
		exit;
	} else {
		wp_die('Post creation failed, could not find original post: ' . $post_id);
	}
}
add_action( 'admin_action_rptr_rd_duplicate_post', 'rptr_rd_duplicate_post' );

// Add the duplicate link to action list for post_row_actions
function rptr_rd_duplicate_post_link( $actions, $post ) {
	if (current_user_can('edit_posts')) {
		$actions['duplicate'] = rptr_rd_generate_duplicate_post_link( $post->ID );
	}
	return $actions;
}

function rptr_rd_generate_duplicate_post_link( $post_id, $classes = '', $redirect = 0 ) {
	return '<a class="'.$classes.'" href="' . wp_nonce_url('admin.php?action=rptr_rd_duplicate_post&post=' . $post_id, basename(__FILE__), 'duplicate_nonce' ) . '&cr-duplicate-redirect='.$redirect.'" title="Duplicate this item" rel="permalink">Duplicate</a>';
}
add_filter( 'post_row_actions', 'rptr_rd_duplicate_post_link', 10, 2 );

 
?>
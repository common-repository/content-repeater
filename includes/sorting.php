<?php
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Rptr_Sorting extends Rptr_Class {
	
	public function __construct() {

		if( rptr_plugin_scope() 
			&& strstr($_SERVER['REQUEST_URI'], 'edit.php')
			&& !strstr($_SERVER['REQUEST_URI'], '-cr-template-') 
			) {
			
			wp_enqueue_script('jquery');
			wp_enqueue_script('jquery-ui-sortable');

			add_action(	'admin_head', 	array( 'Rptr_Sorting', 'rptr_sorting_scripts_styles' ) );				

		}
        add_action( 'admin_init', array( 'Rptr_Sorting', 'rptr_refresh' ) );
		add_action( 'pre_get_posts', array( 'Rptr_Sorting', 'rptr_pre_get_posts' ) );
		add_filter( 'get_previous_post_where', array( 'Rptr_Sorting', 'rptr_previous_post_where' ) );
		add_filter( 'get_previous_post_sort', array( 'Rptr_Sorting', 'rptr_previous_post_sort' ) );
		add_filter( 'get_next_post_where', array( 'Rptr_Sorting', 'rptr_next_post_where' ) );
		add_filter( 'get_next_post_sort', array( 'Rptr_Sorting', 'rptr_next_post_sort' ) );
		add_action( 'wp_ajax_rptr_ajax_menu_order', array( 'Rptr_Sorting', 'rptr_ajax_menu_order' ) );
		        
	}
	
	public static function rptr_sorting_scripts_styles() { ?>
<style>
.ui-sortable tr:not(.inline-edit-row):hover {
	cursor: move;
}
.ui-sortable tr.alternate {
	background-color: #F9F9F9;	
}
.ui-sortable tr.ui-sortable-helper {
	background-color: #F9F9F9;
	border-top: 1px solid #DFDFDF;
}
</style>
<script>
jQuery(document).ready(function($){ 
	$('table.posts #the-list').sortable({
		'items': 'tr:not(.inline-edit-row)',
		'axis': 'y',
		'helper': fixHelper,
		'cancel': 'pre, .cr-shortcode',
		'update' : function(e, ui) {
			$.ajax({
				type: 'post',
				url: ajaxurl,
				data: {
					action: 'rptr_ajax_menu_order',
					order: $('#the-list').sortable('serialize'),
				}/*,
				success: function(result) {
					console.log(result);
				}*/
			});
		}
	});  
	var fixHelper = function(e, ui) {
		ui.children().children().each(function() {
			$(this).width($(this).width());
		});
		return ui;
	};
});				
</script>	
	<?php
	} 

    public function rptr_ajax_menu_order() {
        global $wpdb;
        parse_str($_POST['order'], $data);

        if (!is_array($data)) return false;

        $id_arr = array();
        foreach ($data as $key => $values) {
            foreach ($values as $position => $id) {
                $id_arr[] = $id;
            }
        }
        $menu_order_arr = array();
        foreach ($id_arr as $key => $id) {
            $results = $wpdb->get_results("SELECT menu_order FROM $wpdb->posts WHERE ID = " . intval($id));
            foreach ($results as $result) {
                $menu_order_arr[] = $result->menu_order;
            }
        }
        sort($menu_order_arr);
        foreach ($data as $key => $values) {
            foreach ($values as $position => $id) {
                $wpdb->update($wpdb->posts, array('menu_order' => $menu_order_arr[$position]), array('ID' => intval($id)));
            }
        }
		wp_die();
    }
    
    public function rptr_refresh() {
        global $wpdb;
        $objects = self::rptr_sortable_objects();
        if (!empty($objects)) {
            foreach ($objects as $object) {
                $result = $wpdb->get_results("
                    SELECT count(*) as cnt, max(menu_order) as max, min(menu_order) as min
                    FROM $wpdb->posts
                    WHERE post_type = '" . $object . "' AND post_status IN ('publish', 'pending', 'draft', 'private', 'future')
                ");

                if ($result[0]->cnt == 0 || $result[0]->cnt == $result[0]->max)
                    continue;

                // Here's the optimization
                $wpdb->query("SET @row_number = 0;");
                $wpdb->query("UPDATE $wpdb->posts as pt JOIN (
                  SELECT ID, (@row_number:=@row_number + 1) AS rank
                  FROM $wpdb->posts
                  WHERE post_type = '$object' AND post_status IN ( 'publish', 'pending', 'draft', 'private', 'future' )
                  ORDER BY menu_order ASC
                ) as pt2
                ON pt.id = pt2.id
                SET pt.menu_order = pt2.rank;");

            }
        }
    }
    
    public function rptr_sortable_objects() {
		$options = self::rptr_get_options();
		if($options && is_array($options['types_all'])) {
			$objects = array_keys(self::rptr_get_options()['types_all']);
	        return $objects;
	    } else {
	    	return;
	    }
    }
    
    public function rptr_pre_get_posts($wp_query) {
        $objects = self::rptr_sortable_objects();
        if (empty($objects)) return false;
        if (is_admin()) {
            if (isset($wp_query->query['post_type']) && !isset($_GET['orderby'])) {
                if (in_array($wp_query->query['post_type'], $objects)) {
                    $wp_query->set('orderby', 'menu_order');
                    $wp_query->set('order', 'ASC');
                }
            }
        } else {
            $active = false;
            if (isset($wp_query->query['post_type'])) {
                if (!is_array($wp_query->query['post_type'])) {
                    if (in_array($wp_query->query['post_type'], $objects)) {
                        $active = true;
                    }
                }
            }
            if (!$active) return false;
            if (isset($wp_query->query['suppress_filters'])) {
                if ($wp_query->get('orderby') == 'date')
                    $wp_query->set('orderby', 'menu_order');
                if ($wp_query->get('order') == 'DESC')
                    $wp_query->set('order', 'ASC');
            } else {
                if (!$wp_query->get('orderby'))
                    $wp_query->set('orderby', 'menu_order');
                if (!$wp_query->get('order'))
                    $wp_query->set('order', 'ASC');
            }
        }
    }
    
    public function rptr_previous_post_where($where) {
        global $post;
        $objects = self::rptr_sortable_objects();
        if (empty($objects)) return $where;
        if (isset($post->post_type) && in_array($post->post_type, $objects)) {
            $where = preg_replace("/p.post_date < \'[0-9\-\s\:]+\'/i", "p.menu_order > '" . $post->menu_order . "'", $where);
        }
        return $where;
    }

    public function rptr_previous_post_sort($orderby) {
        global $post;
        $objects = self::rptr_sortable_objects();
        if (empty($objects)) return $orderby;
        if (isset($post->post_type) && in_array($post->post_type, $objects)) {
            $orderby = 'ORDER BY p.menu_order ASC LIMIT 1';
        }
        return $orderby;
    }

    public function rptr_next_post_where($where) {
        global $post;
        $objects = self::rptr_sortable_objects();
        if (empty($objects)) return $where;
        if (isset($post->post_type) && in_array($post->post_type, $objects)) {
            $where = preg_replace("/p.post_date > \'[0-9\-\s\:]+\'/i", "p.menu_order < '" . $post->menu_order . "'", $where);
        }
        return $where;
    }

    public function rptr_next_post_sort($orderby) {
        global $post;
        $objects = self::rptr_sortable_objects();
        if (empty($objects)) return $orderby;
        if (isset($post->post_type) && in_array($post->post_type, $objects)) {
            $orderby = 'ORDER BY p.menu_order DESC LIMIT 1';
        }
        return $orderby;
    }

}

new Rptr_Sorting();
?>
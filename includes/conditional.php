<?php
function rptr_field( $par ) {
	$out = get_post_meta( get_the_ID(), $par[0], true );
	$out = (isset($out) && trim($out) != '') ? true : false;
	return $out;
}
function rptr_nofield( $par ) {
	$out = get_post_meta( get_the_ID(), $par[0], true );
	$out = (isset($out) && trim($out) != '') ? false : true;
	return $out;
}
function rptr_is( $par ) {
	$par[1] = get_post_meta( get_the_ID(), $par[1], true );
	$out = ($par[1] == $par[0]) ? true : false;
	return $out;
}
function rptr_not( $par ) {
	$par[1] = get_post_meta( get_the_ID(), $par[1], true );
	$out = ($par[1] != $par[0]) ? true : false;
	return $out;
}
function rptr_has( $par ) {
	$par[1] = get_post_meta( get_the_ID(), $par[1], true );
	$out = (strpos($par[1], $par[0]) !== false) ? true : false;
	return $out;
}
function rptr_hasnot( $par ) {
	$par[1] = get_post_meta( get_the_ID(), $par[1], true );
	$out = (strpos($par[1], $par[0]) === false) ? true : false;
	return $out;
}
function rptr_more( $par ) {
	$par[1] = get_post_meta( get_the_ID(), $par[1], true );
	$out = ($par[1] > $par[0]) ? true : false;
	return $out;
}
function rptr_less( $par ) {
	$par[1] = get_post_meta( get_the_ID(), $par[1], true );
	$out = ($par[1] < $par[0]) ? true : false;
	return $out;
}
function rptr_emore( $par ) {
	$par[1] = get_post_meta( get_the_ID(), $par[1], true );
	$out = ($par[1] >= $par[0]) ? true : false;
	return $out;
}
function rptr_eless( $par ) {
	$par[1] = get_post_meta( get_the_ID(), $par[1], true );
	$out = ($par[1] <= $par[0]) ? true : false;
	return $out;
}
function rptr_single() {
	return ( ($GLOBALS['rptr_template'] == 'template_single') ? true : false );
}
function rptr_regular() {
	return ( ($GLOBALS['rptr_template'] == 'template') ? true : false );
}
function rptr_has_single() {
	$type = get_post_type(get_the_ID());
	$types = rptr_get_option('types_all'); 
	return ( isset($types[$type]['type_slug']) ? true : false );
}
function rptr_has_no_single() {
	$type = get_post_type(get_the_ID());
	$types = rptr_get_option('types_all'); 
	return ( !isset($types[$type]['type_slug']) ? true : false );
}


function rptr_conditional_tags_shortcode( $atts, $content ) {

// 	echo '<pre>'; print_r($atts); echo '</pre>';

	$out = '';
	$template = $GLOBALS['rptr_template'];

	foreach( $atts as $key => $value ) {
		/* normalize empty attributes */
		if( is_int( $key ) ) {
			$key = $value;
			$value = true;
		}

		$rptr_operators = array('field', 'nofield', 'is', 'not', 'has', 'hasnot', 'more', 'less', 'emore', 'eless', 'single', 'regular', 'has_single', 'has_no_single');
		$cr = 'rptr_';
		if( in_array( $key, $rptr_operators ) ) {
			$key = $cr.$key;
		} 
		$reverse_logic = false;
		if( substr( $key, 0, 4 ) == 'not_' ) {
			$reverse_logic = true;
			$key = substr( $key, 4 );
		}
		if ( preg_match( '/has_term_(.*)/', $key, $matches ) ) {
			$result = has_term( $value, $matches[1] );
		} elseif( function_exists( $key ) ) {
			$values = ( true === $value ) ? null : array_filter( explode( ',', $value ) );
			if( in_array( substr( $key, strlen($cr) ), $rptr_operators ) ) {
				$values[] = $atts['field'];
			}
			$result = call_user_func( $key, $values );
		}
		if ( ! isset( $result ) ) {
			return '';
		}
		if( $result !== $reverse_logic ) {
			return do_shortcode( $content );
		}
	}
	return '';

}
add_shortcode( 'if', 'rptr_conditional_tags_shortcode' );
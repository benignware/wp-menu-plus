<?php
/**
 * Enqueue scripts and styles.
 *
 * @since 1.0.0
 */
function ja_global_enqueues() {
	wp_enqueue_style(
		'jquery-auto-complete',
		'https://cdnjs.cloudflare.com/ajax/libs/jquery-autocomplete/1.0.7/jquery.auto-complete.css',
		array(),
		'1.0.7'
	);

	wp_enqueue_script(
		'jquery-auto-complete',
		// 'https://cdnjs.cloudflare.com/ajax/libs/jquery-autocomplete/1.0.7/jquery.auto-complete.js',
    plugins_url( 'autocomplete.js', __FILE__ ),
		array( 'jquery' ),
		'1.0.7',
		true
	);
  
  wp_enqueue_style(
		'menu-plus-icon-search',
		plugins_url( 'menu-icon-search.css', __FILE__ )
	);

	wp_enqueue_script(
		'menu-plus-icon-search',
		plugins_url( 'menu-icon-search.js', __FILE__ ),
		array( 'jquery' , 'jquery-auto-complete'),
		'1.0.0',
		true
	);

	wp_localize_script(
		'menu-plus-icon-search',
		'global',
		array(
			'ajax' => admin_url( 'admin-ajax.php' ),
		)
	);
}
add_action( 'admin_enqueue_scripts', 'ja_global_enqueues' );

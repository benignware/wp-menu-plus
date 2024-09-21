<?php
/**
 * Plugin Name: Menu Plus
 * Plugin URI: http://github.com/benignware/wp-menu-plus
 * Description: Enhance menus with buttons and search
 * Version: 1.0.0
 * Author: Rafael Nowrotek, Benignware
 * Author URI: http://benignware.com
 * License: MIT
*/

require 'lib/agnosticon/agnosticon.php';

require_once('features/menu-icon/menu-icon.php');
require_once('features/menu-button/menu-button.php');
require_once('features/menu-search-form/menu-search-form.php');



add_action('admin_enqueue_scripts', function() {
	wp_enqueue_style( 'wp-color-picker' );
	wp_enqueue_script( 'menu-plus-admin', plugins_url( 'menu-plus.js', __FILE__ ), ['jquery', 'wp-color-picker'] );
	wp_enqueue_style( 'menu-plus-admin', plugins_url( 'menu-plus.css', __FILE__ ), [] );

	$palette = get_theme_support('editor-color-palette');
	$palette = $palette ? $palette[0] : [];

	wp_localize_script(
		'menu-plus-admin',
		'MenuPlusOptions', 
		[ 
			'palettes' => array_map(function($item) {
				return $item['color'];
			}, $palette)
		]
	);
});

<?php
namespace benignware\wp\menu_plus;

use benignware\wp\agnosticon\get_icon;
use benignware\wp\agnosticon\get_icon_meta;

require 'menu-icon-block.php';
require 'menu-icon-search.php';


add_action( 'wp_nav_menu_item_custom_fields', function( $item_id, $item ) {
	if (!function_exists('benignware\wp\agnosticon\get_icon_meta')) {
		return false;
	}

  // $menu_item_icon = get_post_meta( $item_id, '_menu_item_icon', true );
	$menu_item_icon_id = get_post_meta( $item_id, '_menu_item_icon_id', true );
	$menu_item_icon_hide_title = get_post_meta( $item_id, '_menu_item_icon_hide_title', true );
	
	$icon = null;
	$is_valid = true;

	if ($menu_item_icon_id) {
		$icon = \benignware\wp\agnosticon\get_icon($menu_item_icon_id, [
			'class' => 'menu-plus-icon-input-icon'
		]);

		if (!$icon) {
			$is_valid = false;
		}
	}

	if (!$icon) {
		$icon = '<span class="menu-plus-icon-input-icon">' . (!$is_valid ? '?' : '') . '</span>';
	}

	?>
	<div style="clear: both;">
		<input type="hidden" class="nav-menu-id" value="<?php echo $item_id ;?>" />
		<!-- <input
			type="checkbox"
			name="menu_item_icon[<?php echo $item_id ;?>]"
			id="menu-item-icon-<?php echo $item_id ;?>"
			value="1"
			data-toggle="menu-plus-settings-panel"
			<?php if (esc_attr( $menu_item_icon ) === '1'): ?> checked<?php endif; ?>
		/>
		<label
			for="menu-item-icon-<?php echo $item_id ;?>"
		><?php _e( "Icon", 'menu-plus' ); ?>
		</label> -->

		<div class="menu-plus-settings-panel">
			<div class="menu-plus-settings-panel-body">
				<div style="clear: both;">
					<label class="icon-id"><?php _e( "Icon", 'menu-item-icon-id' ); ?></label><br />
					<div class="menu-plus-icon-input-wrapper">
						<input
							type="text"
							class="menu-plus-icon-input <?php if (!$is_valid): ?>is-invalid<?php endif; ?>"
							style="width: 250px; max-width: 100%"
							name="menu_item_icon_id[<?php echo $item_id ;?>]"
							id="menu-item-icon-id<?php echo $item_id ;?>"
							value="<?= esc_attr( $menu_item_icon_id ) ?>"
							data-value="<?= esc_attr( $menu_item_icon_id ) ?>"
							placeholder="<?= __('Search Icon'); ?>"
							<?php if (!$is_valid): ?>invalid<?php endif; ?>
						/>
						<div class="menu-plus-icon-input-prepend">
							<?= $icon; ?>
						</div>
						<div class="menu-plus-icon-input-append">
							<span class="menu-plus-icon-input-hide-title">
							<input
								class="menu-plus-icon-input-hide-title"
								type="checkbox"
								name="menu_item_icon_hide_title[<?php echo $item_id ;?>]"
								id="menu-item-icon-hide-title<?php echo $item_id ;?>"
								value="1"
								<?php if (esc_attr( $menu_item_icon_hide_title ) === '1'): ?> checked<?php endif; ?>
							/>
							<label
								for="menu-item-icon-hide-title<?php echo $item_id ;?>"
							><?php _e( "ABC", 'menu-plus' ); ?>
							</label>
							</span>
							<span class="menu-plus-icon-input-clear"><i class="dashicons dashicons-trash"></i></span>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
<?php
}, 10, 2 );


add_action( 'wp_update_nav_menu_item', function( $menu_id, $menu_item_db_id ) {
	if (!function_exists('benignware\wp\agnosticon\get_icon_meta')) {
		return false;
	}

	if ( isset( $_POST['menu_item_icon'][$menu_item_db_id]  ) ) {
		$sanitized_data = filter_var($_POST['menu_item_icon'][$menu_item_db_id], FILTER_SANITIZE_NUMBER_INT);
		update_post_meta( $menu_item_db_id, '_menu_item_icon', $sanitized_data );
	} else {
		delete_post_meta( $menu_item_db_id, '_menu_item_icon' );
	}

	if ( isset( $_POST['menu_item_icon_id'][$menu_item_db_id]  ) ) {
		$sanitized_data = sanitize_text_field( $_POST['menu_item_icon_id'][$menu_item_db_id] );
		update_post_meta( $menu_item_db_id, '_menu_item_icon_id', $sanitized_data );
	} else {
		delete_post_meta( $menu_item_db_id, '_menu_item_icon_id' );
	}

	if ( isset( $_POST['menu_item_icon_id'][$menu_item_db_id]  ) ) {
		$id = sanitize_text_field( $_POST['menu_item_icon_id'][$menu_item_db_id] );
		$icon = \benignware\wp\agnosticon\get_icon_meta($id);

		if ($icon) {
			update_post_meta( $menu_item_db_id, '_menu_item_icon_class', $icon->class );
		} else {
			delete_post_meta( $menu_item_db_id, '_menu_item_icon_class' );
		}
	}

	if ( isset( $_POST['menu_item_icon_hide_title'][$menu_item_db_id]  ) ) {
		$sanitized_data = filter_var($_POST['menu_item_icon_hide_title'][$menu_item_db_id], FILTER_SANITIZE_NUMBER_INT);
		update_post_meta( $menu_item_db_id, '_menu_item_icon_hide_title', $sanitized_data );
	} else {
		delete_post_meta( $menu_item_db_id, '_menu_item_icon_hide_title' );
	}
}, 10, 2 );

// add_action( 'admin_enqueue_scripts', function() {
//   wp_enqueue_style('agnosticon');
// });

// add_action( 'enqueue_block_editor_assets', function() {
//   wp_enqueue_style('agnosticon');
// } );

add_filter( 'nav_menu_item_title', function($title, $item) {
	if (!function_exists('benignware\wp\agnosticon\get_icon_meta')) {
		return $title;
	}

  if ( is_object( $item ) && isset( $item->ID ) ) {
    $icon_id = get_post_meta( $item->ID, '_menu_item_icon_id', true );
		$icon_class = get_post_meta( $item->ID, '_menu_item_icon_class', true );

		if ($icon_class) {
			$icon_html = "<i class=\"$icon_class\"></i>";
		} else if ($icon_id) {
      $icon_html = \benignware\wp\agnosticon\get_icon($icon_id, [
				'class' => 'menu-item-icon'
			]);
    }

		if ($icon_html) {
			$hide_title = get_post_meta( $item->ID, '_menu_item_icon_hide_title', true );
			$title = $icon_html . '&nbsp;' . sprintf('<span class="menu-item-title"%s>%s</span>', $hide_title ? ' hidden' : '', $title);
		}
  }

  return $title;
}, 10, 2);

add_action('init', function() {
	wp_enqueue_style(
		'menu-plus-icon',
		plugins_url( 'menu-icon.css', __FILE__ )
	);
});


function enqueue_icon_scripts() {
  wp_enqueue_script(
      'menuplus-icon-assets',
      plugin_dir_url( __FILE__ ) . 'menu-icon.css',
      filemtime( plugin_dir_path( __FILE__ ) . 'menu-icon.css' ), // Version based on file modification time
      true // Load in footer
  );
}
add_action( 'enqueue_scripts', 'benignware\wp\menu_plus\enqueue_icon_scripts' );


function get_theme_breakpoints() {
	// Get the theme object
	$merged_data = \WP_Theme_JSON_Resolver::get_merged_data();
	
	if (method_exists($merged_data, 'get_data')) {
		$theme_json = $merged_data->get_data();
	} else {
		$theme_json = $merged_data;
	}

	$theme_json = $merged_data->get_data();
	$default_theme_json = json_decode(file_get_contents(ABSPATH . WPINC . '/theme.json'), true);
	
	$theme_json = array_merge(
		$default_theme_json,
		$theme_json,
	);

	// Check if the breakpoints setting exists
	$breakpoints = isset($theme_json['settings']['breakpoints']) ? $theme_json['settings']['breakpoints'] : [];

	// Provide default breakpoints if not set
	$default_breakpoints = [
		'mobile' => 600,   // max-width 600px
		'tablet' => 900,   // max-width 900px
		'desktop' => 1200, // min-width 1200px
	];

	// Allow filtering of breakpoints, merging theme settings with defaults
	return apply_filters('menu_plus_breakpoints', wp_parse_args($breakpoints, $default_breakpoints));
}

function render_custom_navigation_link_block($block_content, $block) {
    // Only modify the core/navigation-link block
    if ($block['blockName'] !== 'core/navigation-link') {
        return $block_content;
    }

    // Retrieve attributes
    $attributes = $block['attrs'];

    // Build classes based on the attributes
    $classNames = 'has-icon';
    if (!empty($attributes['hideLabelMobile'])) {
        $classNames .= ' hide-label-mobile';
    }
    if (!empty($attributes['hideLabelTablet'])) {
        $classNames .= ' hide-label-tablet';
    }
    if (!empty($attributes['hideLabelDesktop'])) {
        $classNames .= ' hide-label-desktop';
    }

    // Add classes to the <a> tag
    $block_content = preg_replace(
        '/<a([^>]+)class="([^"]*)"/',
        '<a$1class="$2 ' . esc_attr($classNames) . '"',
        $block_content
    );

    return $block_content;
}
add_filter('render_block', 'benignware\wp\menu_plus\render_custom_navigation_link_block', 10, 2);

function generate_dynamic_menu_icon_css() {
	$breakpoints = get_theme_breakpoints(); // Retrieve the breakpoints as defined in the theme
	$labelSelector = '.wp-block-navigation-item__label'; // Define the label class
	$labelSelector = 'span';

	if (!empty($breakpoints)) {
			$css = '';

			// Generate CSS for the mobile breakpoint
			if (isset($breakpoints['mobile'])) {
					$css .= "
					@media (max-width: {$breakpoints['mobile']}px) {
							.has-icon.hide-label-mobile $labelSelector {
									display: none;
							}
					}
					";
			}

			// Generate CSS for the tablet breakpoint (from mobile+1 to tablet max width)
			if (isset($breakpoints['tablet'])) {
					$tablet_min = $breakpoints['mobile'] + 1;
					$css .= "
					@media (min-width: {$tablet_min}px) and (max-width: {$breakpoints['tablet']}px) {
							.has-icon.hide-label-tablet $labelSelector {
									display: none;
							}
					}
					";
			}

			// Generate CSS for the desktop breakpoint (min-width tablet+1)
			if (isset($breakpoints['desktop'])) {
					$desktop_min = $breakpoints['tablet'] + 1;
					$css .= "
					@media (min-width: {$desktop_min}px) {
							.has-icon.hide-label-desktop $labelSelector {
									display: none;
							}
					}
					";
			}

			return $css;
	}
}


function enqueue_dynamic_menu_css() {
	// Register and enqueue a placeholder style for dynamic CSS
		wp_register_style('menuplus-dynamic-menu', false); // 'false' means no external file, it's just for inline styles.
		wp_enqueue_style('menuplus-dynamic-menu'); // Enqueue the registered placeholder style

    // Generate dynamic CSS content
    $css = generate_dynamic_menu_icon_css();

		// echo $css;
		// exit;
    // Add the generated CSS as inline styles
    wp_add_inline_style('menuplus-dynamic-menu', $css);
}
add_action('wp_enqueue_scripts', 'benignware\wp\menu_plus\enqueue_dynamic_menu_css');
<?php


require 'menu-icon-search.php';


add_action( 'wp_nav_menu_item_custom_fields', function( $item_id, $item ) {
	
  // $menu_item_icon = get_post_meta( $item_id, '_menu_item_icon', true );
	$menu_item_icon_id = get_post_meta( $item_id, '_menu_item_icon_id', true );
	$menu_item_icon_hide_title = get_post_meta( $item_id, '_menu_item_icon_hide_title', true );
	
	$icon = null;
	$is_valid = true;

	if ($menu_item_icon_id) {
		$icon = get_agnosticon($menu_item_icon_id, [
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

	if ( isset( $_POST['menu_item_icon_hide_title'][$menu_item_db_id]  ) ) {
		$sanitized_data = filter_var($_POST['menu_item_icon_hide_title'][$menu_item_db_id], FILTER_SANITIZE_NUMBER_INT);
		update_post_meta( $menu_item_db_id, '_menu_item_icon_hide_title', $sanitized_data );
	} else {
		delete_post_meta( $menu_item_db_id, '_menu_item_icon_hide_title' );
	}
}, 10, 2 );

add_action( 'admin_enqueue_scripts', function() {
  wp_enqueue_style('agnosticon');
});

add_action( 'enqueue_block_editor_assets', function() {
  wp_enqueue_style('agnosticon');
} );

add_filter( 'nav_menu_item_title', function($title, $item) {
  if ( is_object( $item ) && isset( $item->ID ) ) {
    $icon_id = get_post_meta( $item->ID, '_menu_item_icon_id', true );

    if ($icon_id) {
      $icon = get_agnosticon($icon_id, [
				'class' => 'menu-item-icon'
			]);

      if ($icon) {
				$hide_title = get_post_meta( $item->ID, '_menu_item_icon_hide_title', true );
        $title = $icon . '&nbsp;' . sprintf('<span class="menu-item-title"%s>%s</span>', $hide_title ? ' hidden' : '', $title);
      }
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

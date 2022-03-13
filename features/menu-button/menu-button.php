<?php

add_action( 'wp_nav_menu_item_custom_fields', function( $item_id, $item ) {
	$block_types = WP_Block_Type_Registry::get_instance()->get_all_registered();

	if (!isset($block_types['core/button'])) {
		return;
	}

	if ($item->type === 'search-form') {
		return;
	}

	$button_block_type = $block_types['core/button'];

	$menu_item_button = get_post_meta( $item_id, '_menu_item_button', true );
	$menu_item_button_background_color = get_post_meta( $item_id, '_menu_item_button_background_color', true );
	$menu_item_button_style = get_post_meta( $item_id, '_menu_item_button_style', true );

	?>
	<div style="clear: both;">
		<input type="hidden" class="nav-menu-id" value="<?php echo $item_id ;?>" />
		<input
			type="checkbox"
			name="menu_item_button[<?php echo $item_id ;?>]"
			id="menu-item-button-<?php echo $item_id ;?>"
			value="1"
			data-toggle="menu-plus-settings-panel"
			<?php if (esc_attr( $menu_item_button ) === '1'): ?> checked<?php endif; ?>
		/>
		<label
			for="menu-item-button-<?php echo $item_id ;?>"
			class="button-type"><?php _e( "Button", 'menu-item-button' ); ?>
		</label>

		<div class="menu-plus-settings-panel">
			<div class="menu-plus-settings-panel-body">
				<!-- Background Color -->
				<div style="clear: both;">
					<label class="button-background-color"><?php _e( "Background Color", 'menu-item-button' ); ?></label><br />
					<div class="logged-input-holder">
						<input
							type="text"
							name="menu_item_button_background_color[<?php echo $item_id ;?>]"
							id="menu-item-button-background-color<?php echo $item_id ;?>"
							value="<?= esc_attr( $menu_item_button_background_color ) ?>"
							data-menu-plus-color-picker
						/>
					</div>
				</div>
		
				<!-- Style -->
				<div style="clear: both;">
					<label class="button-style"><?php _e( "Style", 'menu-item-button' ); ?></label><br />
				
					<div class="logged-input-holder">
						<select
							name="menu_item_button_style[<?php echo $item_id ;?>]"
							id="menu-item-button-style<?php echo $item_id ;?>"
						>
							<?php foreach ($button_block_type->styles as ['name' => $style, 'label' => $label]): ?>
								<option
									value="<?= $style ?>"
									<?php if (esc_attr( $menu_item_button_style ) === $style ): ?>selected<?php endif; ?>
								>
									<?= $label ?>
								</option>
							<?php endforeach; ?>
						</select>
					</div>
				</div>
			</div>
		</div>
	</div>

<?php
}, 10, 2 );


add_action( 'wp_update_nav_menu_item', function( $menu_id, $menu_item_db_id ) {
	if ( isset( $_POST['menu_item_button'][$menu_item_db_id]  ) ) {
		$sanitized_data = filter_var($_POST['menu_item_button'][$menu_item_db_id], FILTER_SANITIZE_NUMBER_INT);
		update_post_meta( $menu_item_db_id, '_menu_item_button', $sanitized_data );
	} else {
		delete_post_meta( $menu_item_db_id, '_menu_item_button' );
	}

	if ( isset( $_POST['menu_item_button_background_color'][$menu_item_db_id]  ) ) {
		$sanitized_data = sanitize_text_field( $_POST['menu_item_button_background_color'][$menu_item_db_id] );
		update_post_meta( $menu_item_db_id, '_menu_item_button_background_color', $sanitized_data );
	} else {
		delete_post_meta( $menu_item_db_id, '_menu_item_button_background_color' );
	}

	if ( isset( $_POST['menu_item_button_style'][$menu_item_db_id]  ) ) {
		$sanitized_data = sanitize_text_field( $_POST['menu_item_button_style'][$menu_item_db_id] );
		update_post_meta( $menu_item_db_id, '_menu_item_button_style', $sanitized_data );
	} else {
		delete_post_meta( $menu_item_db_id, '_menu_item_button_style' );
	}
}, 10, 2 );


add_filter( 'nav_menu_link_attributes', function( $atts, $item, $args ) {
  if ( is_object( $item ) && isset( $item->ID ) ) {
		$menu_item_button = get_post_meta( $item->ID, '_menu_item_button', true );

		if ( ! empty( $menu_item_button ) && $menu_item_button === '1' ) {
			$menu_item_button_options = [
				'background_color' => get_post_meta( $item->ID, '_menu_item_button_background_color', true ),
				'style' => get_post_meta( $item->ID, '_menu_item_button_style', true )
			];

			$atts = array_merge($atts, [
				'data-menu-button' => base64_encode(json_encode($menu_item_button_options, JSON_UNESCAPED_SLASHES))
			]);
		}
	}

	return $atts;
}, 10, 3 );

add_filter( 'wp_nav_menu', function($nav_menu = '', $args = array()) {
	// Parse menu dom
  $doc = new DOMDocument();
  @$doc->loadHTML("<?xml encoding=\"utf-8\" ?>$nav_menu");
  $doc_xpath = new DOMXpath($doc);

  $elements = $doc_xpath->query('//*[@data-menu-button]');

	if (count($elements) === 0) {
		return $nav_menu;
	}

	foreach ($elements as $element) {
		$options = json_decode(
			base64_decode(
				$element->getAttribute('data-menu-button')
			)
		);

		$url = $element->getAttribute('href');

		if (!$url) {
			continue;
		}

		$text = $element->textContent;

		// Block Markup
		$html = strtr(<<<EOT
		<div class="wp-block-button is-style-%style">
			<a class="wp-block-button__link button" href="%url">
				%text
			</a>
		</div>
		EOT, [
			'%style' => $options->style,
			'%url' => $url,
			'%text' => $text
		]);

		$block = [
			'blockName' => 'core/button',
			'attrs' => [
				'text' => $text,
				'url' => $url,
				'backgroundColor' => $options->background_color,
				'className' => "is-style-$options->style"
			],
			'innerHTML' => $html,
			'innerContent' => [$html]
		];

		$html = (new WP_Block( $block ))->render();

		// Parse button dom
		$button_doc = new DOMDocument();
  	@$button_doc->loadHTML("<?xml encoding=\"utf-8\" ?>$html");
		$button_doc_xpath = new DOMXpath($button_doc);

		// Insert button
		$button = $button_doc_xpath->query('/html/body/*')->item(0);

		if (!$button) {
			continue;
		}

		$button = $doc->importNode($button, true);

		// Merge classname
    $class = implode(' ', array_unique(
      array_filter(
        array_merge(
          preg_split('/\s+/', $element->getAttribute('class')),
          preg_split('/\s+/', $button->getAttribute('class'))
        )
      )
    ));
    $button->setAttribute('class', $class);

		// Copy all other attributes
    foreach ($element->attributes as $attr) {
      if (!$button->hasAttribute($attr->nodeName)) {
        $button->setAttribute($attr->nodeName, $attr->nodeValue);
      }
    }

		$element->parentNode->insertBefore($button, $element);
		$element->parentNode->removeChild($element);
	}

	$nav_menu = preg_replace('~(?:<\?[^>]*>|<(?:!DOCTYPE|/?(?:html|head|body))[^>]*>)\s*~i', '', $doc->saveHTML());

  return $nav_menu;
}, 12);

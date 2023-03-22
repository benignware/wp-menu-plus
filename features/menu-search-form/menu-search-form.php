<?php

function menu_plus_contains_node($parent, $node) {
  $xpath = new DOMXpath($parent->ownerDocument);
  $elements = $xpath->query('.//*', $parent);

  foreach ($elements as $element) {
    if ($element === $node) {
      return true;
    }
  }

  return false;
}

function menu_plus_get_common_ancestor($node_a, $node_b) {
  while ($node_a = $node_a->parentNode) {
    if (menu_plus_contains_node($node_a, $node_b)) {
      return $node_a;
    }
  }

  return null;
}

function menu_plus_get_search_form($options = []) {
  $html = menu_plus_render_search_block($options);
  // $html = get_search_form(false);

  return $html;
}

add_action('admin_init', 'menu_item_search_form_nav_menu_meta_box');

function menu_item_search_form_nav_menu_meta_box() {
  add_meta_box(
      'menu-item-search-form-nav-box',
      __('Search Form'),
      'menu_item_search_form_display_menu_custom_box',
      'nav-menus',
      'side',
      'default'
  );
}

function menu_item_search_form_display_menu_custom_box() {
    ?>
    <div id="posttype-menu-item-search-form" class="posttypediv">
        <div id="tabs-panel-wishlist-login" class="tabs-panel tabs-panel-active">
          <ul id ="wishlist-login-checklist" class="categorychecklist form-no-clear">
            <li>
              <label class="menu-item-title">
                <input style="visibility: hidden; width: 0; min-width:0; height: 0; " checked type="checkbox" class="menu-item-checkbox" name="menu-item[-1][menu-item-object-id]" value="-1">
                <?= __('Display search-form'); ?>
              </label>
              <input type="hidden" class="menu-item-type" name="menu-item[-1][menu-item-type]" value="search-form">
              <input type="hidden" class="menu-item-title" name="menu-item[-1][menu-item-title]" value="<?= __('Search'); ?>">
              <input type="hidden" class="menu-item-url" name="menu-item[-1][menu-item-url]" value="#search-form">
              <input type="hidden" class="menu-item-classes" name="menu-item[-1][menu-item-classes]" value="menu-item-search-form">
            </li>
          </ul>
        </div>
        <p class="button-controls">
          <span class="add-to-menu">
            <input type="submit" class="button-secondary submit-add-to-menu right" value="Add to Menu" name="add-post-type-menu-item" id="submit-posttype-menu-item-search-form">
            <span class="spinner"></span>
          </span>
        </p>
      </div>
    <?php
}

add_action( 'wp_nav_menu_item_custom_fields', function( $item_id, $item ) {
  // if (!metadata_exists('post', $item_id, '_menu_item_search_form_button')) {
  //   $menu_item_search_form_button = '1';
  // }

  if ($item->type !== 'search-form') {
    return;
  }

  $menu_item_search_form_expandable = get_post_meta( $item_id, '_menu_item_search_form_expandable', true );
  $menu_item_search_form_button = get_post_meta( $item_id, '_menu_item_search_form_button', true );
  $menu_plus_input_style = get_post_meta( $item_id, '_menu_plus_input_style', true );

  $input_styles = [
    'solid' => 'Solid',
    'outline' => 'Outline',
    'underline' => 'Underline'
  ];

  ?>
	<div style="clear: both;">
		<input type="hidden" class="nav-menu-id" value="<?php echo $item_id ;?>" />

    <div class="description description-wide">
      <?= _e('Search Form') ?>
    </div>
    <div class="menu-plus-settings-panel">
      <div class="menu-plus-settings-body">
        <div class="menu-plus-settings-field-group">
          <input
            type="checkbox"
            name="menu_item_search_form_expandable[<?php echo $item_id ;?>]"
            id="menu-item-search-form-expandable-<?php echo $item_id ;?>"
            value="1"
            <?php if (esc_attr( $menu_item_search_form_expandable ) === '1'): ?> checked<?php endif; ?>
          />
          <label
            for="menu-item-search-form-expandable-<?php echo $item_id ;?>"
            class="label"><?php _e( "Expandable", 'menu-plus' ); ?>
          </label>
        </div>
        <div class="menu-plus-settings-field-group">
          <input
            type="checkbox"
            name="menu_item_search_form_button[<?php echo $item_id ;?>]"
            id="menu-item-search-form-button-<?php echo $item_id ;?>"
            value="1"
            <?php if (esc_attr( $menu_item_search_form_button ) === '1'): ?> checked<?php endif; ?>
          />
          <label
            for="menu-item-search-form-button-<?php echo $item_id ;?>"
            class="label"><?php _e( "Button", 'menu-plus' ); ?>
          </label>
        </div>
        <!-- Style -->
				<div class="menu-plus-settings-field-group">
					<label
            for="menu-plus-input-style<?php echo $item_id ;?>"
            class="menu-plus-label"><?php _e( "Input Style", 'menu-plus' ); ?>
          </label><br />
				
					<select
            name="menu_plus_input_style[<?php echo $item_id ;?>]"
            id="menu-plus-input-style<?php echo $item_id ;?>"
          >
            <?php foreach ($input_styles as $style => $label): ?>
              <option
                value="<?= $style ?>"
                <?php if (esc_attr( $menu_plus_input_style ) === $style ): ?>selected<?php endif; ?>
              >
                <?= $label ?>
              </option>
            <?php endforeach; ?>
          </select>
				</div>
      </div>
    </div>
  </div>
<?php

}, 10, 2);

add_action( 'wp_update_nav_menu_item', function( $menu_id, $menu_item_db_id ) {
	if ( isset( $_POST['menu_item_search_form_expandable'][$menu_item_db_id]  ) ) {
		$sanitized_data = filter_var($_POST['menu_item_search_form_expandable'][$menu_item_db_id], FILTER_SANITIZE_NUMBER_INT);
		update_post_meta( $menu_item_db_id, '_menu_item_search_form_expandable', $sanitized_data );
	} else {
		delete_post_meta( $menu_item_db_id, '_menu_item_search_form_expandable' );
	}

  if ( isset( $_POST['menu_item_search_form_button'][$menu_item_db_id]  ) ) {
		$sanitized_data = filter_var($_POST['menu_item_search_form_button'][$menu_item_db_id], FILTER_SANITIZE_NUMBER_INT);
		update_post_meta( $menu_item_db_id, '_menu_item_search_form_button', $sanitized_data );
	} else {
		delete_post_meta( $menu_item_db_id, '_menu_item_search_form_button' );
	}

  if ( isset( $_POST['menu_plus_input_style'][$menu_item_db_id]  ) ) {
		$sanitized_data = sanitize_text_field( $_POST['menu_plus_input_style'][$menu_item_db_id] );
		update_post_meta( $menu_item_db_id, '_menu_plus_input_style', $sanitized_data );
	} else {
		delete_post_meta( $menu_item_db_id, '_menu_plus_input_style' );
	}
}, 10, 2 );

add_filter( 'nav_menu_link_attributes', function( $atts, $item, $args ) {
  if ( is_object( $item ) && isset( $item->ID ) && $item->type === 'search-form' ) {
    $menu_item_search_form_expandable = get_post_meta( $item->ID, '_menu_item_search_form_expandable', true );
    $menu_item_search_form_button = get_post_meta( $item->ID, '_menu_item_search_form_button', true );

    $menu_item_search_form_options = [
      'title' => $item->title,
      'expandable' => empty($menu_item_search_form_expandable) || $menu_item_search_form_expandable === '0' ? false : true,
      'button' => empty($menu_item_search_form_button) || $menu_item_search_form_button === '0' ? false : true,
      'input_style' => get_post_meta( $item->ID, '_menu_plus_input_style', true ) ?: 'solid'
    ];

		$atts = array_merge($atts, [
      'data-menu-search-form' => base64_encode(json_encode($menu_item_search_form_options, JSON_UNESCAPED_SLASHES))
    ]);
	}

	return $atts;
}, 10, 3 );

add_filter( 'wp_nav_menu', function($nav_menu = '', $args = array()) {
  $doc = new DOMDocument();
  @$doc->loadHTML('<?xml encoding="utf-8" ?>' . $nav_menu );

  $doc_xpath = new DOMXpath($doc);
  $elements = $doc_xpath->query('//*[@data-menu-search-form]');

  if (count($elements) === 0) {
    return $nav_menu;
  }

  $html = menu_plus_get_search_form();
  
  foreach ($elements as $element) {
    // Clone original menu-item for use as button link
    $menu_link = $element->cloneNode(true);

    // Remove data attributes from menu item clone
    foreach ($menu_link->attributes as $key => $attr) {
      if (strpos($key, 'data-') === 0) {
        $menu_link->removeAttribute('data-menu-search-form');
      }
    }

    $options = json_decode(
			base64_decode(
				$element->getAttribute('data-menu-search-form')
			)
		);

    $content_doc = new DOMDocument();
    @$content_doc->loadHTML('<?xml encoding="utf-8" ?>' . $html );

    $content_doc_xpath = new DOMXpath($content_doc);    

    $wrapper = $content_doc_xpath->query('/html/body/*')->item(0);

    // if (!$wrapper) {
    //   return '';
    // }

    $wrapper = $doc->importNode($wrapper, true);

    $form = $wrapper->cloneNode(true);

    // Merge classes
    $classes = array_unique(
      array_filter(
        array_merge(
          preg_split('/\s+/', $element->getAttribute('class')),
          preg_split('/\s+/', $form->getAttribute('class'))
        )
      )
    );
    $classes[] = 'menu-search-form';
  
    if ($options->expandable) {
      $classes[] = 'menu-search-form-expandable is-collapsed';
    }
   
    $form->setAttribute('class', implode(' ', $classes));

    // Copy all other attributes
    foreach ($element->attributes as $attr) {
      if (!$form->hasAttribute($attr->nodeName)) {
        $form->setAttribute($attr->nodeName, $attr->nodeValue);
      }
    }

    $element->parentNode->insertBefore($form, $element);
    $button = $doc_xpath->query('.//button|.//input[@type = "submit"]', $form)->item(0);
    $input = $doc_xpath->query('.//input[@name="s"]', $form)->item(0);

    $input_classes = explode(' ', $input->getAttribute('class'));

    if ($options->input_style) {
      $input_classes[] = 'menu-plus-input is-style-' . $options->input_style;
    }

    $input_class = implode(' ', array_values(array_filter(array_unique($input_classes))));

    $input->setAttribute('class', $input_class);

    $input_id = $input->getAttribute('id');

    $placeholder = $input->getAttribute('placeholder');

    $label = $input_id
      ? $doc_xpath->query(sprintf('.//label[@for="%s"]', $input_id), $form)->item(0)
      : null;

    if ($label) {
      if (!$placeholder) {
        $placeholder = $label->textContent;
      }

      if (menu_plus_contains_node($label, $input)) {
        $label->parentNode->insertBefore($input, $label);
      }

      $label->parentNode->removeChild($label);
    }

    $input->setAttribute('placeholder', $placeholder);

    if ($button) {
      $button->textContent = '';

      foreach ($element->childNodes as $child) {
        $clone = $child->cloneNode(true);

        $button->appendChild($clone);
      }

      if (!$options->button) {
        $common_ancestor = menu_plus_get_common_ancestor($button, $input);
        $input_child = null;

        foreach ($common_ancestor->childNodes as $child) {
          if ($child === $input || menu_plus_contains_node($child, $input)) {
            $input_child = $child;
            break;
          }
        }

        if ($input_child) {
          while ($form->firstChild) {
            $form->removeChild($form->firstChild);
          }

          $form->appendChild($input_child);
        }

        if ($options->expandable) {
          $menu_link->setAttribute('class', $menu_link->getAttribute('class') . ' menu-search-submit');
          $form->appendChild($menu_link);
        }
      }
    }

    $element->parentNode->removeChild($element);
  }

  $nav_menu = preg_replace('~(?:<\?[^>]*>|<(?:!DOCTYPE|/?(?:html|head|body))[^>]*>)\s*~i', '', $doc->saveHTML());

  return $nav_menu;
});

function menu_plus_render_search_block() {
  global $__menu_plus_search_incr;

  if (!isset($__menu_plus_search_incr)) {
    $__menu_plus_search_incr = 0;
  } else {
    $__menu_plus_search_incr++;
  }

  // $has_block_widgets = apply_filters( 'use_widgets_block_editor', get_theme_support( 'widgets-block-editor' ) );
  $has_block_widgets = wp_use_widgets_block_editor();

  // $has_block_widgets = false;
  $block_types = WP_Block_Type_Registry::get_instance()->get_all_registered();

	if ($has_block_widgets && isset($block_types['core/search'])) {

    $input_id = "menu-plus-search-form-$__menu_plus_search_incr";
    $label = __('Search');
    $url = get_site_url();

    // Block Markup
    $html = strtr(<<<EOT
      <form
        role="search"
        method="get"
        action="%url"
        class="wp-block-search__button-outside wp-block-search__text-button wp-block-search"
      >
        <label for="%input-id" class="wp-block-search__label">%label</label>
        <div class="wp-block-search__inside-wrapper">
          <input
            type="search"
            id="%input-id"
            class="wp-block-search__input"
            name="s"
            value=""
            placeholder=""
            required=""
          >
          <button type="submit" class="wp-block-search__button ">%label</button>
        </div>
      </form>
    EOT, [
      '%input_id' => $input_id,
      '%url' => $url,
      '%label' => $label
    ]);

    $block = [
      'blockName' => 'core/search',
      'attrs' => [],
      'innerHTML' => $html,
      'innerContent' => [$html]
    ];

    $html = (new WP_Block( $block ))->render();

    return $html;
  }

  $html = get_search_form(false);

  // echo '<textarea>' . $html . '</textarea>';

  return $html;
}

// Enqueue scripts
add_action('wp_enqueue_scripts', function() {
  wp_register_style('menu-search-form', plugin_dir_url( __FILE__ ) . 'menu-search-form.css', [], '1.0');
  wp_enqueue_style('menu-search-form');

  wp_register_script('menu-search-form', plugin_dir_url( __FILE__ ) . 'menu-search-form.js', [], '1.0', true);
  wp_enqueue_script('menu-search-form');
}, 1);

// add_filter( 'nav_menu_item_title', function($title, $item) {
//   if ( is_object( $item ) && $item->type === 'search-form' ) {
//     return "<span data-menu-plus-menu-item-title=\"\">$title</span>";
//   }

//   return $title;
// }, 10, 2);

// add_filter('render_block', function($html, $block) {
//   if ($block['blockName'] === 'core/search') {
//     echo $block['blockName'];
//     echo '<pre>';
//     var_dump($block);
//     echo '</pre>';
//   }
  
//   return $html;
// }, 10, 2);

// add_action('after_setup_theme', function($bool) {
//   $has_block_widgets = apply_filters( 'use_widgets_block_editor', false );
//   $has_block_widgets = wp_use_widgets_block_editor();

//   echo '-->' . $has_block_widgets;
//   exit;
// }, 2000);

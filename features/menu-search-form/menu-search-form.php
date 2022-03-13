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

function menu_plus_render_button($attrs = []) {
  $attrs = array_merge([
    'url' => null,
    'text' => '',
    'className' => ''
  ]);

  $template = $attrs['url']
    ? <<<EOT
      <div class="wp-block-button %className">
        <a class="wp-block-button__link button" href="%url">
          %text
        </a>
      </div>
    EOT
    : <<<EOT
      <div class="wp-block-button %className">
        %text
      </div>
    EOT;

	// Block Markup
  $html = strtr($template, [
    '%className' => $attrs['className'],
    '%url' => $attrs['url'],
    '%text' => $attrs['text']
  ]);

  $block = [
    'blockName' => 'core/button',
    'attrs' => $attrs,
    'innerHTML' => $html,
    'innerContent' => [$html]
  ];

  $html = (new WP_Block( $block ))->render();

  return $html;
};

function menu_plus_get_search_form($options = []) {
  $html = get_search_form(false);

  return $html;

  $doc = new DOMDocument();
  @$doc->loadHTML('<?xml encoding="utf-8" ?>' . $html );

  $xpath = new DOMXpath($doc);

  $input = $xpath->query('//input[@name="s"]')->item(0);
  // Replace submit input with button
  $submit = $xpath->query('//input[@type="submit"]')->item(0);

  if ($submit) {
    $button = $doc->createElement('button');
    $button->textContent = $submit->getAttribute('value');
    $button->setAttribute('class', $submit->getAttribute('class'));
    $submit->parentNode->insertBefore($button, $submit);
    $submit->parentNode->removeChild($submit);

    $submit = $button;
  }

  $form = $xpath->query('//form')->item(0);
  
	$common_ancestor = menu_plus_get_common_ancestor($input, $submit);

  if ($common_ancestor == $form) {
    $wrapper = $doc->createElement('div');
    $children = [];

    foreach ($common_ancestor->childNodes as $child) {
      if (
        $child === $submit
        || menu_plus_contains_node($child, $submit)
        || $child === $input
        || menu_plus_contains_node($child, $input)
      ) {
        $children[] = $child;
      }
    }

    if (count($children) > 0) {
      $common_ancestor->insertBefore($wrapper, $children[0]);

      foreach ($children as $child) {
        $wrapper->appendChild($child);
      }
    }
  } else {
    $wrapper = $common_ancestor;
  }

  $wrapper->setAttribute('class', 'form-group');

  $block_types = WP_Block_Type_Registry::get_instance()->get_all_registered();

  $classes = array_unique(array_filter(explode(' ', $form->getAttribute('class'))));
  $classes[] = 'wp-block-search__button-outside';
  $classes[] = 'wp-block-search__text-button';
  $classes[] = 'wp-block-search';
  $form->setAttribute('class', implode(' ', $classes));

  if ($label) {
    $label_classes = array_unique(array_filter(explode(' ', $label->getAttribute('class'))));
    $label_classes[] = 'wp-block-search__label';
  }

  $wrapper_classes = array_unique(array_filter(explode(' ', $wrapper->getAttribute('class'))));
  $wrapper_classes[] = 'menu-search-wrapper';
  $wrapper_classes[] = 'wp-block-search__inside-wrapper';
  $wrapper->setAttribute('class', implode(' ', $wrapper_classes));

  $input_classes = array_unique(array_filter(explode(' ', $input->getAttribute('class'))));
  $input_classes[] = 'menu-search-input';
  $input_classes[] = 'wp-block-search__input';
  $input->setAttribute('class', implode(' ', $input_classes));

  $submit_classes = array_unique(array_filter(explode(' ', $submit->getAttribute('class'))));
  $submit_classes[] = 'menu-search-submit';
  $submit_classes[] = 'wp-block-search__button';
  $submit->setAttribute('class', implode(' ', $submit_classes));

  if (isset($block_types['core/search'])) {
    $html = preg_replace('~(?:<\?[^>]*>|<(?:!DOCTYPE|/?(?:html|head|body))[^>]*>)\s*~i', '', $doc->saveHTML());

    $block = [
      'blockName' => 'core/search',
      'attrs' => [],
      'innerHTML' => $html,
      'innerContent' => [$html]
    ];

    $html = (new WP_Block( $block ))->render();
    
    $doc = new DOMDocument();
    @$doc->loadHTML('<?xml encoding="utf-8" ?>' . $html );
    $xpath = new DOMXpath($doc);
  }

  $input = $xpath->query('//input[@name="s"]')->item(0);
  $input_id = $input->getAttribute('id');

  $placeholder = $input->getAttribute('placeholder');

  $label = $input_id
    ? $xpath->query(sprintf('//label[@for="%s"]', $input_id))->item(0)
    : null;

  if ($label) {
    if (!$placeholder) {
      $placeholder = $label->textContent;
    }

    $label->parentNode->removeChild($label);
  }

  $input->setAttribute('placeholder', $placeholder);
  $input->setAttribute('class', implode(' ', $input_classes));

  $submit = $xpath->query('//button')->item(0);
  $submit->setAttribute('class', implode(' ', $submit_classes));

  $html = preg_replace('~(?:<\?[^>]*>|<(?:!DOCTYPE|/?(?:html|head|body))[^>]*>)\s*~i', '', $doc->saveHTML());

  return $html;
};

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
  $menu_item_search_form_expandable = get_post_meta( $item_id, '_menu_item_search_form_expandable', true );
  $menu_item_search_form_button = get_post_meta( $item_id, '_menu_item_search_form_button', true );

  // if (!metadata_exists('post', $item_id, '_menu_item_search_form_button')) {
  //   $menu_item_search_form_button = '1';
  // }

  if ($item->type !== 'search-form') {
    return;
  }

  ?>
	<div style="clear: both;">
		<input type="hidden" class="nav-menu-id" value="<?php echo $item_id ;?>" />

    <div class="description description-wide">
      <?= _e('Search Form') ?>
    </div>
    <div class="menu-plus-settings-panel">
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
}, 10, 2 );

add_filter( 'nav_menu_link_attributes', function( $atts, $item, $args ) {
  if ( is_object( $item ) && isset( $item->ID ) && $item->type === 'search-form' ) {
    $menu_item_search_form_expandable = get_post_meta( $item->ID, '_menu_item_search_form_expandable', true );
    $menu_item_search_form_button = get_post_meta( $item->ID, '_menu_item_search_form_button', true );

    $menu_item_search_form_options = [
      'title' => $item->title,
      'expandable' => empty($menu_item_search_form_expandable) || $menu_item_search_form_expandable === '0' ? false : true,
      'button' => empty($menu_item_search_form_button) || $menu_item_search_form_button === '0' ? false : true
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
    $button = $doc_xpath->query('//button', $form)->item(0);
    $input = $doc_xpath->query('//input[@name="s"]', $form)->item(0);

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

// Enqueue scripts
add_action('wp_enqueue_scripts', function() {
  wp_register_style('menu-search-form', plugin_dir_url( __FILE__ ) . 'menu-search-form.css', [], '1.0');
  wp_enqueue_style('menu-search-form');

  wp_register_script('menu-search-form', plugin_dir_url( __FILE__ ) . 'menu-search-form.js', [], '1.0', true);
  wp_enqueue_script('menu-search-form');
}, 1);

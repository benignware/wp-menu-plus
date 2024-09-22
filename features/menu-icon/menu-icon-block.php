<?php
namespace benignware\wp\menu_plus;

function enqueue_icon_block_editor_assets() {
  wp_enqueue_script(
      'menuplus-icon-block-editor-assets',
      plugin_dir_url( __FILE__ ) . '../../dist/menuplus-icon-editor.js',
      [ 'wp-blocks', 'wp-rich-text', 'wp-element', 'wp-editor', 'wp-components' ],
      filemtime( plugin_dir_path( __FILE__ ) . '../../dist/menuplus-icon-editor.js' ), // Version based on file modification time
      true // Load in footer
  );
}
add_action( 'enqueue_block_editor_assets', 'benignware\wp\menu_plus\enqueue_icon_block_editor_assets' );



function render_navigation_link($block_content, $block) {
  if ($block['blockName'] === 'core/navigation-link') {
      $icon = isset($block['attrs']['icon']) ? $block['attrs']['icon'] : [];
      $icon_class = isset($icon['class']) ? 'agnosticon ' . esc_attr($icon['class']) : '';

      if ($icon_class) {
          // Use regex to find the opening <a> tag and capture existing classes
          $pattern = '/<a([^>]*)class="([^"]*)"/';
          $replacement = sprintf(
              '<a$1 class="has-icon $2"',
              isset($block['attrs']['className']) ? esc_attr($block['attrs']['className']) : ''
          );

          // Replace the <a> tag while preserving existing classes
          $block_content = preg_replace($pattern, $replacement, $block_content);

          // Add the icon HTML after the opening <a> tag
          $icon_html = sprintf(
              '<i class="%s" style="display: inline-block; margin-right: 0.4em; vertical-align: middle;"></i> ',
              $icon_class
          );

          // Insert icon HTML after the opening <a> tag
          $block_content = preg_replace('/(<a[^>]*>)/', '$1' . $icon_html, $block_content);
      }
  }

  return $block_content;
}
add_filter('render_block', 'benignware\wp\menu_plus\render_navigation_link', 10, 2);
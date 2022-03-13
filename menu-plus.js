((jQuery, options) => {
  jQuery( document ).ready( function () {
    if ( typeof jQuery.fn.wpColorPicker === 'function' ) {
      jQuery( '[data-menu-plus-color-picker]' ).wpColorPicker({
        palettes: options.palettes
      });
    }
  } );
})(window.jQuery, window.MenuPlusOptions);

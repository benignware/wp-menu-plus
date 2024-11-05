(function () {
    'use strict';

    const {
      __
    } = wp.i18n;
    const {
      TextControl,
      PanelBody,
      ToggleControl
    } = wp.components;
    const {
      Fragment,
      useState
    } = wp.element;
    const {
      addFilter
    } = wp.hooks;
    const {
      InspectorControls
    } = wp.blockEditor;

    // Add new attributes to the navigation-link block.
    const addIconAttribute = settings => {
      if (settings.name !== 'core/navigation-link') {
        return settings;
      }
      return {
        ...settings,
        attributes: {
          ...settings.attributes,
          icon: {
            type: 'object',
            default: {}
          },
          hideLabel: {
            type: 'boolean',
            default: false
          }
        }
      };
    };
    addFilter('blocks.registerBlockType', 'menu-plus/navigation-link/icon-attribute', addIconAttribute);

    // IconAutoSuggest component for icon search.
    const IconAutoSuggest = ({
      value,
      onChange
    }) => {
      const [searchResults, setSearchResults] = useState([]);
      const handleSearch = search => {
        if (!search) {
          setSearchResults([]);
          return;
        }
        fetch(`${window.location.origin}/wp-admin/admin-ajax.php?action=agnosticon_search&search=${encodeURIComponent(search)}`).then(response => response.json()).then(response => {
          if (response.success) {
            setSearchResults(response.data);
          }
        }).catch(error => {
          console.error('AJAX error:', error);
        });
      };
      const handleSelect = iconData => {
        onChange(iconData);
        setSearchResults([]);
      };
      return /*#__PURE__*/React.createElement(Fragment, null, /*#__PURE__*/React.createElement(TextControl, {
        label: __("Icon", "menu-plus"),
        value: value ? value.name : '',
        onChange: newValue => {
          onChange(newValue);
          handleSearch(newValue);
        },
        placeholder: __("Search for an icon...", "menu-plus")
      }), searchResults.length > 0 && /*#__PURE__*/React.createElement("ul", {
        className: "components-autocomplete__results"
      }, searchResults.map(icon => /*#__PURE__*/React.createElement("li", {
        key: icon.id,
        className: "components-autocomplete__result",
        onClick: () => handleSelect(icon)
      }, /*#__PURE__*/React.createElement("span", {
        className: icon.class,
        style: {
          marginRight: '10px'
        }
      }), " ", icon.name))));
    };

    // Add icon controls and hide label controls to the block inspector.
    const withInspectorControls = BlockEdit => {
      return props => {
        if (props.name !== 'core/navigation-link') {
          return /*#__PURE__*/React.createElement(BlockEdit, props);
        }
        const {
          attributes,
          setAttributes
        } = props;
        const {
          icon,
          hideLabel
        } = attributes;
        return /*#__PURE__*/React.createElement(Fragment, null, /*#__PURE__*/React.createElement(InspectorControls, null, /*#__PURE__*/React.createElement(PanelBody, {
          title: __("Icon Settings", "menu-plus"),
          initialOpen: true
        }, /*#__PURE__*/React.createElement(IconAutoSuggest, {
          value: icon,
          onChange: newIcon => setAttributes({
            icon: newIcon
          })
        }), /*#__PURE__*/React.createElement(ToggleControl, {
          label: __("Hide Label", "menu-plus"),
          checked: hideLabel,
          onChange: newValue => setAttributes({
            hideLabel: newValue
          })
        }))), icon && icon.entity && /*#__PURE__*/React.createElement("i", {
          className: "menuplus-pagelink-icon",
          style: {
            ...(icon.style ? {
              fontFamily: icon.font_family
            } : {}),
            fontStyle: 'normal'
          },
          dangerouslySetInnerHTML: {
            __html: icon.entity
          }
        }), /*#__PURE__*/React.createElement(BlockEdit, props));
      };
    };
    addFilter('editor.BlockEdit', 'menu-plus/navigation-link/with-inspector-controls', withInspectorControls);

})();

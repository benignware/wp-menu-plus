import "./menu-icon-editor.css";

const { __ } = wp.i18n;
const { TextControl, PanelBody, ToggleControl } = wp.components;
const { Fragment, useState } = wp.element;
const { addFilter } = wp.hooks;
const { InspectorControls } = wp.blockEditor;

// Add new attributes to the navigation-link block.
const addIconAttribute = (settings) => {
    if (settings.name !== 'core/navigation-link') {
        return settings;
    }

    return {
        ...settings,
        attributes: {
            ...settings.attributes,
            icon: {
                type: 'object',
                default: {},
            },
            hideLabelMobile: { // Hide label on mobile
                type: 'boolean',
                default: false,
            },
            hideLabelTablet: { // Hide label on tablet
                type: 'boolean',
                default: false,
            },
            hideLabelDesktop: { // Hide label on desktop
                type: 'boolean',
                default: false,
            },
        },
    };
};

addFilter(
    'blocks.registerBlockType',
    'menu-plus/navigation-link/icon-attribute',
    addIconAttribute
);

// IconAutoSuggest component for icon search.
const IconAutoSuggest = ({ value, onChange }) => {
    const [searchResults, setSearchResults] = useState([]);

    const handleSearch = (search) => {
        if (!search) {
            setSearchResults([]);
            return;
        }

        fetch(`${window.location.origin}/wp-admin/admin-ajax.php?action=agnosticon_search&search=${encodeURIComponent(search)}`)
            .then((response) => response.json())
            .then((response) => {
                if (response.success) {
                    setSearchResults(response.data);
                }
            })
            .catch((error) => {
                console.error('AJAX error:', error);
            });
    };

    const handleSelect = (iconData) => {
        onChange(iconData);
        setSearchResults([]);
    };

    return (
        <Fragment>
            <TextControl
                label={__("Icon", "menu-plus")}
                value={value ? value.name : ''}
                onChange={(newValue) => {
                    onChange(newValue);
                    handleSearch(newValue);
                }}
                placeholder={__("Search for an icon...", "menu-plus")}
            />
            {searchResults.length > 0 && (
                <ul className="components-autocomplete__results">
                    {searchResults.map((icon) => (
                        <li 
                            key={icon.id} 
                            className="components-autocomplete__result" 
                            onClick={() => handleSelect(icon)}
                        >
                            <span className={icon.class} style={{ marginRight: '10px' }}></span> {icon.name}
                        </li>
                    ))}
                </ul>
            )}
        </Fragment>
    );
};

// Add icon controls and hide label controls to the block inspector.
const withInspectorControls = (BlockEdit) => {
    return (props) => {
        if (props.name !== 'core/navigation-link') {
            return <BlockEdit {...props} />;
        }

        const { attributes, setAttributes } = props;
        const { icon, hideLabelMobile, hideLabelTablet, hideLabelDesktop } = attributes;

        // Build class names based on attributes
        const labelClasses = [];
        if (hideLabelMobile) {
            labelClasses.push('hide-label-mobile');
        }
        if (hideLabelTablet) {
            labelClasses.push('hide-label-tablet');
        }
        if (hideLabelDesktop) {
            labelClasses.push('hide-label-desktop');
        }

        return (
            <Fragment>
                <InspectorControls>
                    <PanelBody title={__("Icon Settings", "menu-plus")} initialOpen={true}>
                        <IconAutoSuggest
                            value={icon}
                            onChange={(newIcon) => setAttributes({ icon: newIcon })}
                        />
                        <ToggleControl
                            label={__("Hide Label on Mobile", "menu-plus")}
                            checked={hideLabelMobile}
                            onChange={(newValue) => setAttributes({ hideLabelMobile: newValue })}
                        />
                        <ToggleControl
                            label={__("Hide Label on Tablet", "menu-plus")}
                            checked={hideLabelTablet}
                            onChange={(newValue) => setAttributes({ hideLabelTablet: newValue })}
                        />
                        <ToggleControl
                            label={__("Hide Label on Desktop", "menu-plus")}
                            checked={hideLabelDesktop}
                            onChange={(newValue) => setAttributes({ hideLabelDesktop: newValue })}
                        />
                    </PanelBody>
                </InspectorControls>
                {icon && icon.entity && (
                    <i
                        className="menuplus-pagelink-icon"
                        style={{
                            ...icon.style ? { fontFamily: icon.font_family } : {},
                            fontStyle: 'normal',
                        }}
                        dangerouslySetInnerHTML={{ __html: icon.entity }}
                    ></i>
                )}
                {/* Assuming label is a span or div for your label text */}
                <span className={`label ${labelClasses.join(' ')}`}>
                    {/* Your label text here */}
                </span>
                <BlockEdit {...props} />
            </Fragment>
        );
    };
};

addFilter(
    'editor.BlockEdit',
    'menu-plus/navigation-link/with-inspector-controls',
    withInspectorControls
);

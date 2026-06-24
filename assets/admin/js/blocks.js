const { registerBlockType } = wp.blocks;
const { useBlockProps } = wp.blockEditor;
const { useEffect } = React;

(function() {
    let el = wp.element.createElement;

    function addSelect2(selectClass, properties, blockId, attributeName = 'selectedWidget') {
        useEffect(() => {
            let selects = jQuery('#' + blockId).find('select.' + selectClass + '.livewebinar-select2');
            if (!isNaN(selects.length)) {
                for (let idx = 0; idx < selects.length; idx++) {
                    jQuery(selects[idx]).removeClass('livewebinar-select2').addClass('livewebinar-select2-added');
                    jQuery(selects[idx]).select2({
                        theme: 'bootstrap4',
                    }).on('select2:select', (e) => {
                        setSelectedOption(properties, attributeName, e.params.data.id);
                    });
                }
            }
        }, []);
    }

    function setSelectedOption(properties, attributeName, selectedId) {
        selectedId = parseInt(selectedId);

        if ('selectedForm' === attributeName) {
            let form = livewebinar_blocks.livewebinar_forms[selectedId];
            properties.setAttributes({
                selectedForm: selectedId,
                embedCode: form && form.has_embed_code ? form.embed_code : '',
            });
            return;
        }

        properties.setAttributes({selectedWidget: selectedId})
    }

    registerBlockType('livewebinar/embed-room', {
        category: 'livewebinar-blocks',
        edit: function (properties) {
            let blockId = 'livewebinar_block_' + Date.now();
            addSelect2('livewebinar-embed-room-widget-select', properties, blockId);
            let showLinkAttr = properties.attributes.showLink;
            let selected = properties.attributes.selectedWidget;

            let titleLabel = el('label', {for: "lw-embed-room-title"}, livewebinar_blocks.title_label);
            let titleInput = el('input', {
                name: 'lw-embed-title',
                placeholder: livewebinar_blocks.title_placeholder,
                onChange: (event) => {
                    properties.setAttributes({title: event.target.value});
                },
                value: properties.attributes.title,
            });
            let title = el('div', {class: 'livewebinar-form-group'}, titleLabel, titleInput);

            let selectLabel = el('label', {for: 'lw-embed-room-select-widget'}, livewebinar_blocks.selected_room_label);
            let options = [el('option', {
                value: 0,
                selected: 'undefined' === typeof selected
            }, livewebinar_blocks.select_one_option)];
            for (const id in livewebinar_blocks.livewebinar_widgets) {
                options.push(el('option', {
                    value: id,
                    selected: parseInt(id) === parseInt(selected)
                }, livewebinar_blocks.livewebinar_widgets[id]));
            }

            let select = el(
                'select',
                {
                    name: 'lw-embed-select-widget',
                    class: 'livewebinar-embed-room-widget-select livewebinar-select2',
                    onChange: (event) => {
                        properties.setAttributes({selectedWidget: parseInt(event.target.value)})
                    },
                }, options);

            let selectWrapper = el('div', {class: 'livewebinar-form-group'}, selectLabel, select);

            let showLinkLabel = el('label', {for: 'lw-embed-show-link'}, livewebinar_blocks.show_join_link_label);
            let showLink = el('input', {
                type: 'checkbox',
                name: 'lw-embed-show-link',
                checked: showLinkAttr,
                onChange: (event) => {
                    properties.setAttributes({showLink: event.target.checked})
                },
            });
            let showLinkWrapper = el('div', {class: 'livewebinar-form-group'}, showLinkLabel, showLink);

            return el('div', {id: blockId, class: 'lw-embed-room-wrapper livewebinar-wrapper'}, title, selectWrapper, showLinkWrapper);
        },
    });

    registerBlockType('livewebinar/room-info', {
        category: 'livewebinar-blocks',
        edit: (properties) => {
            let blockId = 'livewebinar_block_' + Date.now();
            addSelect2('livewebinar-room-info-widget-select', properties, blockId);
            let titleAttr = properties.attributes.title;
            let selectedAttr = properties.attributes.selectedWidget;
            let showLinkOnlyAttr = properties.attributes.showLinkOnly;

            let titleLabel = el('label', {for: "lw-room-info-title"}, livewebinar_blocks.title_label);
            let titleInput = el('input', {
                name: 'lw-room-info-title',
                placeholder: livewebinar_blocks.title_placeholder,
                onChange: (event) => {
                    properties.setAttributes({title: event.target.value})
                },
                value: titleAttr,
            });
            let title = el('div', {class: 'livewebinar-form-group'}, titleLabel, titleInput);

            let selectLabel = el('label', {for: 'lw-room-info-select-widget'}, livewebinar_blocks.selected_room_label)
            let options = [el('option', {
                value: 0,
                selected: 'undefined' === typeof selectedAttr
            }, livewebinar_blocks.select_one_option)];
            for (const id in livewebinar_blocks.livewebinar_widgets) {
                options.push(el('option', {
                    value: id,
                    selected: parseInt(id) === parseInt(selectedAttr)
                }, livewebinar_blocks.livewebinar_widgets[id]));
            }
            let select = el(
                'select',
                {
                    name: 'lw-room-info-select-widget',
                    class: 'livewebinar-room-info-widget-select livewebinar-select2',
                    onChange: (event) => {
                        properties.setAttributes({selectedWidget: parseInt(event.target.value)})
                    },
                },
                options);
            let selectWrapper = el('div', {class: 'livewebinar-form-group'}, selectLabel, select);

            let showLinkOnlyLabel = el('label', {for: 'lw-room-info-show-link-only'}, livewebinar_blocks.show_link_only_label);
            let showLinkOnly = el('input', {
                type: 'checkbox',
                name: 'lw-room-info-show-link-only',
                checked: showLinkOnlyAttr,
                onChange: (event) => {
                    properties.setAttributes({showLinkOnly: event.target.checked})
                },
                onLoad: (event) => {
                    jQuery(event.target).select2();
                }
            });
            let showLinkOnlyWrapper = el('div', {class: 'livewebinar-form-group'}, showLinkOnlyLabel, showLinkOnly);

            return el('div', {id: blockId, class: 'lw-room-info-wrapper livewebinar-wrapper'}, title, selectWrapper, showLinkOnlyWrapper);
        }
    });

    registerBlockType('livewebinar/embed-form', {
        category: 'livewebinar-blocks',
        edit: (properties) => {
            let blockId = 'livewebinar_block_' + Date.now();
            addSelect2('livewebinar-embed-form-select', properties, blockId, 'selectedForm');
            let titleAttr = properties.attributes.title;
            let selectedAttr = properties.attributes.selectedForm;

            let titleLabel = el('label', {for: "lw-embed-form-title"}, livewebinar_blocks.title_label);
            let titleInput = el('input', {
                name: 'lw-embed-form-title',
                placeholder: livewebinar_blocks.title_placeholder,
                onChange: (event) => {
                    properties.setAttributes({title: event.target.value})
                },
                value: titleAttr,
            });
            let title = el('div', {class: 'livewebinar-form-group'}, titleLabel, titleInput);

            let selectLabel = el('label', {for: 'lw-embed-form-select'}, livewebinar_blocks.selected_form_label);
            let options = [el('option', {
                value: 0,
                selected: 'undefined' === typeof selectedAttr
            }, livewebinar_blocks.select_one_option)];

            for (const id in livewebinar_blocks.livewebinar_forms) {
                let form = livewebinar_blocks.livewebinar_forms[id];
                let label = form.name;
                if (!form.has_embed_code) {
                    label += ' - ' + livewebinar_blocks.embed_form_disabled_label;
                }

                options.push(el('option', {
                    value: id,
                    selected: parseInt(id) === parseInt(selectedAttr),
                    disabled: !form.has_embed_code,
                }, label));
            }

            let select = el(
                'select',
                {
                    name: 'lw-embed-form-select',
                    class: 'livewebinar-embed-form-select livewebinar-select2',
                    onChange: (event) => {
                        setSelectedOption(properties, 'selectedForm', event.target.value);
                    },
                },
                options);
            let selectWrapper = el('div', {class: 'livewebinar-form-group'}, selectLabel, select);
            let helper = el('p', {class: 'description'},
                livewebinar_blocks.embed_form_enable_prefix,
                el('a', {
                    href: livewebinar_blocks.embed_forms_url,
                    target: '_blank',
                    rel: 'noopener noreferrer',
                }, livewebinar_blocks.embed_form_panel_label),
                livewebinar_blocks.embed_form_enable_suffix
            );

            return el('div', {id: blockId, class: 'lw-embed-form-wrapper livewebinar-wrapper'}, title, selectWrapper, helper);
        }
    });
}());

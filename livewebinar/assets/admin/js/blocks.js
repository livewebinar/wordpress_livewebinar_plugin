const { registerBlockType } = wp.blocks;
const { ResizableBox } = wp.components;
const { useBlockProps } = wp.blockEditor;
const { useEffect } = React;

(function() {
    let el = wp.element.createElement;

    function addSelect2(selectClass, properties, blockId) {
        useEffect(() => {
            let selects = jQuery('#' + blockId).find('select.' + selectClass + '.livewebinar-select2');
            if (!isNaN(selects.length)) {
                for (let idx = 0; idx < selects.length; idx++) {
                    jQuery(selects[idx]).removeClass('livewebinar-select2').addClass('livewebinar-select2-added');
                    jQuery(selects[idx]).select2({
                        theme: 'bootstrap4',
                    }).on('select2:select', (e) => {
                        if ('livewebinar-image-storage-image-select' === selectClass) {
                            let src = jQuery(e.params.data.element).data('url');
                            let imgTmp = new Image();
                            imgTmp.src = src;
                            imgTmp.onload = function () {
                                properties.setAttributes({
                                    selectedImage: parseInt(e.params.data.id),
                                    width: !isNaN(parseInt(this.width)) ? this.width : 200,
                                    height: !isNaN(parseInt(this.height)) ? this.height : 200,
                                });
                            }
                        } else {
                            properties.setAttributes({selectedWidget: parseInt(e.params.data.id)})
                        }
                    });
                }
            }
        }, []);
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

    registerBlockType('livewebinar/image-storage', {
        category: 'livewebinar-blocks',
        edit: (properties) => {
            let blockId = 'livewebinar_block_' + Date.now();
            addSelect2('livewebinar-image-storage-image-select', properties, blockId);
            let titleAttr = properties.attributes.title;
            let selectedAttr = properties.attributes.selectedImage;
            let captionAttr = properties.attributes.caption;
            let widthAttr = properties.attributes.width;
            let heightAttr = properties.attributes.height;

            let titleLabel = el('label', {for: "lw-image-storage-title"}, livewebinar_blocks.title_label);
            let titleInput = el('input', {
                name: 'lw-image-storage-title',
                placeholder: livewebinar_blocks.title_placeholder,
                onChange: (event) => {
                    properties.setAttributes({title: event.target.value})
                },
                value: titleAttr,
            });
            let title = el('div', {class: 'livewebinar-form-group'}, titleLabel, titleInput);

            let selectLabel = el('label', {for: 'lw-image-storage-select-image'}, livewebinar_blocks.select_image_label)
            let options = [el('option', {
                value: 0,
                selected: 'undefined' === typeof selectedAttr
            }, livewebinar_blocks.select_one_option)];
            for (const id in livewebinar_blocks.livewebinar_images) {
                options.push(el('option', {
                    value: id,
                    selected: parseInt(id) === parseInt(selectedAttr),
                    'data-url': livewebinar_blocks.livewebinar_images[id].url,
                }, livewebinar_blocks.livewebinar_images[id].name));
            }
            let select = el(
                'select',
                {
                    name: 'lw-image-storage-select-image',
                    class: 'livewebinar-image-storage-image-select livewebinar-select2',
                    onChange: (event) => {
                        properties.setAttributes({selectedImage: parseInt(event.target.value)})
                    },
                },
                options);
            let selectWrapper = el('div', {class: 'livewebinar-form-group'}, selectLabel, select);

            let captionLabel = el('label', {for: 'lw-image-storage-caption'}, livewebinar_blocks.caption_label);
            let caption = el('input', {
                name: 'lw-image-storage-caption',
                placeholder: livewebinar_blocks.caption_placeholder,
                onChange: (event) => {
                    properties.setAttributes({caption: event.target.value})
                },
                value: captionAttr
            });
            let captionWrapper = el('div', {class: 'livewebinar-room-content '}, captionLabel, caption);

            let img = el(
                'img',
                {
                    class: 'livewebinar-image-storage-image',
                    src: !isNaN(parseInt(selectedAttr)) && 'undefined' !== typeof livewebinar_blocks.livewebinar_images[selectedAttr] ? livewebinar_blocks.livewebinar_images[selectedAttr].url : '',
                    alt: !isNaN(parseInt(selectedAttr)) && 'undefined' !== typeof livewebinar_blocks.livewebinar_images[selectedAttr] ? livewebinar_blocks.livewebinar_images[selectedAttr].name : '',
                });

            let resizableBox = el(
                ResizableBox,
                {
                    size: {
                        width: widthAttr,
                        height: heightAttr,
                    },
                    minHeight: 50,
                    minWidth: 50,
                    enable: {
                        top: false,
                        right: true,
                        bottom: true,
                        left: false,
                        topRight: false,
                        bottomRight: true,
                        bottomLeft: false,
                        topLeft: false,
                    },
                    onResizeStop: (event, direction, elt, delta) => {
                        properties.setAttributes({
                            height: parseInt(heightAttr + delta.height, 10),
                            width: parseInt(widthAttr + delta.width, 10),
                        });
                        properties.toggleSelection(true);
                    },
                    onResizeStart: () => {
                        properties.toggleSelection(false);
                    }
                },
                img
            );

            let figure = el(
                'figure', {}, resizableBox
            );

            return el('div', {id: blockId, class: 'lw-image-storage-wrapper livewebinar-wrapper'}, title, selectWrapper, captionWrapper, figure);
        }
    });
}());
<?php

namespace Livewebinar\Includes;

use Livewebinar\Admin\Livewebinar_Api;
use Livewebinar\Admin\Livewebinar_Widget;

class Blocks
{
    private static ?Blocks $_instance = null;

    /**
     * @return Blocks
     */
    public static function instance(): Blocks
    {
        if (is_null(self::$_instance)) {
            self::$_instance = new self();
        }

        return self::$_instance;
    }

    public function __construct()
    {
        global $wp_version;

        if (version_compare($wp_version, '5.8', '>=')) {
            add_filter('block_categories_all', [$this, 'register_block_categories'], 10, 2);
        }else{
            add_filter('block_categories', [$this, 'register_block_categories'], 10, 2);
        }

        if (function_exists('register_block_type') && is_admin()) {
            add_action('init', [$this, 'register_scripts']);
        }

        if (function_exists('register_block_type')) {
            add_action('init', [$this, 'register_blocks']);
        }
    }

    /**
     * @param array $categories
     * @return array|\string[][]
     */
    public function register_block_categories(array $categories): array
    {
        return array_merge(
            [
                [
                    'slug'  => 'livewebinar-blocks',
                    'title' => 'Livewebinar',
                ],
            ],
            $categories
        );
    }

    /**
     * @return void
     */
    public function register_scripts(): void
    {
        wp_register_script(
            'livewebinar-blocks',
            LIVEWEBINAR_PLUGIN_ADMIN_JS_URL . '/blocks.js',
            [
                'wp-blocks',
                'wp-hooks',
                'wp-element',
                'wp-components',
                'wp-server-side-render',
                'wp-api-fetch',
                'wp-block-editor',
                'lodash',
                'react',
                'livewebinar-select2-js',
            ],
            LIVEWEBINAR_PLUGIN_VERSION
        );

        wp_localize_script('livewebinar-blocks','livewebinar_blocks', [
            'livewebinar_widgets' => $this->list_widgets(),
            'livewebinar_images' => $this->list_images(),
            'title_label' => __('Title (optional)', 'livewebinar'),
            'title_placeholder' => __('Title', 'livewebinar'),
            'selected_room_label' => __('Selected room', 'livewebinar'),
            'select_one_option' => __('--- select one ---', 'livewebinar'),
            'show_join_link_label' => __('Show join link', 'livewebinar'),
            'show_link_only_label' => __('Show link only', 'livewebinar'),
            'select_image_label' => __('Select image', 'livewebinar'),
            'caption_label' => __('Caption (optional)', 'livewebinar'),
            'caption_placeholder' => __('Caption', 'livewebinar'),
        ]);
    }

    /**
     * @return void
     */
    public function register_blocks(): void
    {
        register_block_type( 'livewebinar/embed-room', [
            'apiVersion'      => 2,
            'title'           => __('LiveWebinar - Embed room', 'livewebinar'),
            'attributes'      => [
                'selectedWidget'   => [
                    'type' => 'integer',
                ],
                'title'             => [
                    'type'    => 'string',
                    'default' => '',
                ],
                'showLink' => [
                    'type'    => 'boolean',
                    'default' => false,
                ],
            ],
            'category'        => 'livewebinar-blocks',
            "icon"            => 'format-image',
            'description'     => __('Embeds room', 'livewebinar'),
            'textdomain'      => 'livewebinar',
            'editor_script'   => 'livewebinar-blocks',
            'style'           => 'livewebinar-main-style',
            'render_callback' => [$this, 'render_embed_room']
        ] );

        register_block_type('livewebinar/room-info', [
            'apiVersion'      => 2,
            'title'           => __('LiveWebinar - Room info', 'livewebinar'),
            'attributes'      => [
                'selectedWidget'   => [
                    'type' => 'integer',
                ],
                'title'             => [
                    'type'    => 'string',
                    'default' => '',
                ],
                'showLinkOnly' => [
                    'type'    => 'boolean',
                    'default' => false,
                ],
            ],
            'category'        => 'livewebinar-blocks',
            'icon'            => 'editor-table',
            'description'     => __('Shows room info', 'livewebinar'),
            'textdomain'      => 'livewebinar',
            'editor_script'   => 'livewebinar-blocks',
            'style'           => 'livewebinar-main-style',
            'render_callback' => [$this, 'render_room_info']
        ]);

        register_block_type('livewebinar/image-storage', [
            'apiVersion'      => 2,
            'title'           => __('LiveWebinar - Image from storage', 'livewebinar'),
            'attributes'      => [
                'selectedImage' => [
                    'type'    => 'integer',
                ],
                'title'       => [
                    'type'    => 'string',
                    'default' => '',
                ],
                'caption'     => [
                    'type'    => 'string',
                    'default' => '',
                ],
                'width'       => [
                    'type'    => 'number',
                ],
                'height'      => [
                    'type'    => 'number',
                ],
            ],
            'category'        => 'livewebinar-blocks',
            'icon'            => 'format-image',
            'description'     => __('Shows image from LiveWebinar storage', 'livewebinar'),
            'textdomain'      => 'livewebinar',
            'editor_script'   => 'livewebinar-blocks',
            'style'           => 'livewebinar-main-style',
            'render_callback' => [$this, 'render_image_from_storage'],
        ]);
    }

    /**
     * @param array $attributes
     * @param $content
     * @return false|string
     */
    public function render_embed_room(array $attributes, $content)
    {
        $shortcode_args = '';
        if (isset($attributes['selectedWidget']) && ! empty($attributes['selectedWidget'])) {
            $shortcode_args .= ' widget_id="' . $attributes['selectedWidget'] . '"';
        }
        if (isset($attributes['title']) && ! empty($attributes['title'])) {
            $shortcode_args .= ' title="' . $attributes['title'] . '"';
        }
        if (isset($attributes['showLink']) && ! empty($attributes['showLink'])) {
            $shortcode_args .= ' show_link="' . $attributes['showLink'] . '"';
        }

        ob_start();

        echo do_shortcode( '[livewebinar_embed_room' . $shortcode_args . ']' );

        return ob_get_clean();
    }

    /**
     * Array for select options.
     *
     * @return array
     */
    public function list_widgets(): array
    {
        $result = [];

        if ( is_admin() ) {
            $widgets = Livewebinar_Api::instance()->list_widgets();
            $widgetsObj = json_decode($widgets);


            if (isset($widgetsObj->data)) {
                foreach ($widgetsObj->data as $widget) {
                    $result[$widget->id] = $widget->name . ' - ' . $widget->token
                        . (Livewebinar_Widget::TYPE_SCHEDULED === $widget->type && $widget->start_date ?
                            ' - ' . date('Y-m-d H:i', $widget->start_date) : ' - PERMANENT');
                }
            }
        }

        return $result;
    }

    /**
     * Array for select options.
     *
     * @return array
     */
    public function list_images(): array
    {
        $images = null;
        $result = [];

        if ( is_admin() ) {
            $images = Livewebinar_Api::instance()->list_images();
        }

        if ($images) {
            foreach ($images as $image) {
                $result[$image['id']]['name'] = $image['name'] . '.' . $image['file_extension'];
                $result[$image['id']]['url'] = $image['url'];
            }
        }

        return $result;
    }

    /**
     * @param array $attributes
     * @param $content
     * @return false|string
     */
    public function render_room_info(array $attributes, $content)
    {
        $shortcode_args = '';
        if (isset($attributes['selectedWidget']) && ! empty($attributes['selectedWidget'])) {
            $shortcode_args .= ' widget_id="' . $attributes['selectedWidget'] . '"';
        }
        if (isset($attributes['title']) && ! empty($attributes['title'])) {
            $shortcode_args .= ' title="' . $attributes['title'] . '"';
        }
        if (isset($attributes['showLinkOnly']) && ! empty($attributes['showLinkOnly'])) {
            $shortcode_args .= ' show_link_only="' . $attributes['showLinkOnly'] . '"';
        }

        ob_start();

        echo do_shortcode( '[livewebinar_room_info' . $shortcode_args . ']' );

        return ob_get_clean();
    }

    /**
     * @param array $attributes
     * @param $content
     * @return false|string
     */
    public function render_image_from_storage(array $attributes, $content)
    {
        $shortcode_args = '';
        if (isset($attributes['title']) && !empty($attributes['title'])) {
            $shortcode_args .= ' title="' . $attributes['title'] . '"';
        }
        if (isset($attributes['selectedImage']) && !empty($attributes['selectedImage'])) {
            $shortcode_args .= ' image_id="' . $attributes['selectedImage'] . '"';
        }
        if (isset($attributes['caption']) && !empty($attributes['caption'])) {
            $shortcode_args .= ' caption="' . $attributes['caption'] . '"';
        }
        if (isset($attributes['width']) && !empty($attributes['width'])) {
            $shortcode_args .= ' width="' . $attributes['width'] . '"';
        }
        if (isset($attributes['height']) && !empty($attributes['height'])) {
            $shortcode_args .= ' height="' . $attributes['height'] . '"';
        }

        ob_start();

        echo do_shortcode('[livewebinar_image_storage' . $shortcode_args . ']');

        return ob_get_clean();
    }
}

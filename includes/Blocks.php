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
            add_action('enqueue_block_editor_assets', [$this, 'localize_block_editor_data']);
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
            LIVEWEBINAR_PLUGIN_VERSION . '.' . filemtime(LIVEWEBINAR_PLUGIN_DIR_PATH . 'assets/admin/js/blocks.js')
        );
    }

    /**
     * @return void
     */
    public function localize_block_editor_data(): void
    {
        $widgets = [];
        $forms = [];

        if (Livewebinar_Api::can_request_api()) {
            $widgets = $this->list_widgets();
            $forms = $this->list_forms();
        }

        wp_localize_script('livewebinar-blocks', 'livewebinar_blocks', [
            'livewebinar_widgets'   => $widgets,
            'livewebinar_forms'     => $forms,
            'title_label'           => __('Title (optional)', 'livewebinar'),
            'title_placeholder'     => __('Title', 'livewebinar'),
            'selected_room_label'   => __('Selected room', 'livewebinar'),
            'selected_form_label'   => __('Selected form', 'livewebinar'),
            'select_one_option'     => __('--- select one ---', 'livewebinar'),
            'show_join_link_label'  => __('Show join link', 'livewebinar'),
            'show_link_only_label'  => __('Show link only', 'livewebinar'),
            'embed_form_disabled_label' => __('embed disabled', 'livewebinar'),
            'embed_form_enable_prefix' => __('Enable embed form in the ', 'livewebinar'),
            'embed_form_panel_label' => __('LiveWebinar panel', 'livewebinar'),
            'embed_form_enable_suffix' => __(' to use disabled forms.', 'livewebinar'),
            'embed_forms_url' => 'https://app.livewebinar.com/forms',
            'embed_code_label' => __('Embed code', 'livewebinar'),
            'embed_code_help' => __('You can edit this code before saving the post.', 'livewebinar'),
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

        register_block_type('livewebinar/embed-form', [
            'apiVersion'      => 2,
            'title'           => __('LiveWebinar - Embed form', 'livewebinar'),
            'attributes'      => [
                'selectedForm' => [
                    'type' => 'integer',
                ],
                'title'        => [
                    'type'    => 'string',
                    'default' => '',
                ],
                'embedCode'    => [
                    'type'    => 'string',
                    'default' => '',
                ],
            ],
            'category'        => 'livewebinar-blocks',
            'icon'            => 'feedback',
            'description'     => __('Embeds form', 'livewebinar'),
            'textdomain'      => 'livewebinar',
            'editor_script'   => 'livewebinar-blocks',
            'style'           => 'livewebinar-main-style',
            'render_callback' => [$this, 'render_embed_form']
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

        if ( Livewebinar_Api::can_request_api() ) {
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
    public function list_forms(): array
    {
        $result = [];

        if (Livewebinar_Api::can_request_api()) {
            $forms = Livewebinar_Api::instance()->list_forms();
            $formsObj = json_decode($forms);

            if (isset($formsObj->data)) {
                foreach ($formsObj->data as $form) {
                    $embed_code = $form->EmbedCode->data->code ?? '';
                    $result[$form->id] = [
                        'name'           => $form->name,
                        'has_embed_code' => !empty($embed_code),
                        'embed_code'     => $embed_code,
                    ];
                }
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
    public function render_embed_form(array $attributes, $content)
    {
        $embed_code = '';
        if (!empty($attributes['embedCode'])) {
            $embed_code = $this->prepare_embed_form_code($attributes['embedCode']);
        }

        if (empty($attributes['selectedForm'])) {
            $error_message = __('No form selected', 'livewebinar');
        } elseif (empty($embed_code)) {
            $error_message = __('Selected form does not have embed enabled. Enable embed form in the LiveWebinar panel.', 'livewebinar');
        }

        ob_start();

        require(LIVEWEBINAR_PLUGIN_VIEWS_PATH . '/blocks/embed-form.php');

        return ob_get_clean();
    }

    /**
     * @param string $embed_code
     * @return string
     */
    private function prepare_embed_form_code(string $embed_code): string
    {
        $container_id = wp_unique_id('livewebinar-embed-form-');

        $embed_code = str_replace(
            '<div id="FormContainer"></div>',
            '<div id="' . esc_attr($container_id) . '"></div>',
            $embed_code
        );

        return (string) preg_replace_callback(
            "/('_form_containerID'\\s*:\\s*)'[^']*'/",
            static function ($matches) use ($container_id) {
                return $matches[1] . "'" . esc_js($container_id) . "'";
            },
            $embed_code,
            1
        );
    }
}

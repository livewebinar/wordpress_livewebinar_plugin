<?php

namespace Livewebinar\Includes;

class Shortcodes
{
    private static ?Shortcodes $_instance = null;

    public array $shortcodes = [];

    /**
     * @return Shortcodes
     */
    public static function instance(): Shortcodes
    {
        if (is_null(self::$_instance)) {
            self::$_instance = new self();
        }

        return self::$_instance;
    }

    public function __construct()
    {
        $widgets = Widget::instance();
        $storage = Storage::instance();

        $this->shortcodes = [
            'livewebinar_embed_room' => [$widgets, 'embed_room'],
            'livewebinar_room_info' => [$widgets, 'room_info'],
            'livewebinar_image_storage' => [$storage, 'image'],
        ];

        $this->init_shortcodes();
    }

    /**
     * @return void
     */
    public function init_shortcodes(): void
    {
        foreach ($this->shortcodes as $tag => $callback) {
            add_shortcode($tag, $callback);
        }
    }
}
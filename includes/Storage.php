<?php

namespace Livewebinar\Includes;

use Livewebinar\Admin\Livewebinar_Api;

class Storage
{
    private static ?Storage $_instance = null;

    /**
     * @return Storage
     */
    public static function instance(): Storage
    {
        if (is_null(self::$_instance)) {
            self::$_instance = new self();
        }

        return self::$_instance;
    }

    /**
     * @param array|string $attributes
     * @return false|string
     */
    public function image($attributes)
    {
        if (!empty($attributes['image_id'])) {
            $image = Livewebinar_Api::instance()->get_image($attributes['image_id']);
            if (Livewebinar_Api::instance()->is_error) {
                $error_message = Livewebinar_Api::instance()->error_message;
            }
        } else {
            $error_message = __('No image selected', 'livewebinar');
        }

        ob_start();
        require(LIVEWEBINAR_PLUGIN_VIEWS_PATH . '/blocks/image.php');
        return ob_get_clean();
    }
}
<?php

namespace Livewebinar\Admin;

class Livewebinar_File
{
    public static array $image_extensions = [
        'jpg',
        'jpeg',
        'png',
    ];

    public ?int $id;
    public string $name;
    public string $extension;
    public string $url;

    public function __construct(\stdClass $obj) {
        $this->id = $obj->id ?? null;
        $this->name = $obj->name ?? '';
        $this->extension = $obj->file_extension ?? '';
        $this->url = $obj->url ?? '';
    }

    /**
     * Returns full filename
     *
     * @return string
     */
    public function get_filename(): string
    {
        return $this->name . '.' . $this->extension;
    }
}
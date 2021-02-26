<?php

namespace idoit\Component\Upload\Types;

use Exception;
use Intervention\Image\ImageManager;

/**
 * Class ObjectTypeImage
 *
 * @package idoit\Component\Upload\Types
 */
class ObjectTypeImage
{
    /**
     * Method for processing the object type icon.
     *
     * @param string $imagePath
     */
    public static function processUpload(string $imagePath)
    {
        (new ImageManager())
            ->make($imagePath)
            ->resize(200, 200, function ($constraint) {
                $constraint->aspectRatio();
                $constraint->upsize();
            })
            ->save($imagePath);
    }
}

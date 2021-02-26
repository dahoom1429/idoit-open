<?php

namespace idoit\Component\Upload\Types;

use Exception;
use Intervention\Image\ImageManager;

/**
 * Class ObjectTypeIcon
 *
 * @package idoit\Component\Upload\Types
 */
class ObjectTypeIcon
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
            ->resize(16, 16, function ($constraint) {
                $constraint->aspectRatio();
                $constraint->upsize();
            })
            ->save($imagePath);
    }
}
